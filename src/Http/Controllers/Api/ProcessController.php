<?php
namespace ProcessMaker\Package\Adoa\Http\Controllers\Api;

use Illuminate\Http\Request;
use ProcessMaker\Http\Controllers\Api\ProcessController as BaseProcessController;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class ProcessController extends BaseProcessController 
{
    // Override core's startProcesses list
    public function startProcesses(Request $request)
    {
        $user = $request->user();
        $result = parent::startProcesses($request);
        
        if ($user->is_administrator) {
            return $result;
        }

        $result->collection = $result->collection->filter(function($process) use ($user) {
            if (stripos($process->name, 'AZPerforms') === false) {
                return true;
            }

            if ($this->isUsersAgencyActive($user)) {
                return true;
            }

            return false;
        });
        return $result;
    }

    private function isUsersAgencyActive($user)
    {
        if ($user->meta && $user->meta->agency) {
            return $this->agencyIsActive($user->meta->agency);
        }
        return false;
    }

    private function agencyIsActive(string $agency)
    {
        $result = Cache::remember("agency-active-$agency", 600, function () use ($agency) {
            $client = new \GuzzleHttp\Client();
            $url = "https://hrsieapi.azdoa.gov/api/hrorg/AzPerformAgencyCFG.json?agency=" . $agency;
            $headers = [
                'Authorization' => 'Bearer 3-5738379ecfaa4e9fb2eda707779732c7',
            ];
            $response = $client->request('GET', $url, ['headers' => $headers]);
            $response = json_decode($response->getBody(), true);

            return Arr::get($response, 'rows.0.0') === 'Y' ? true : false;
        });
        return $result;
    }
}