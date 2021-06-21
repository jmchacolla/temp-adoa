<?php
namespace ProcessMaker\Package\Adoa;

use ProcessMaker\Models\Process;
use ProcessMaker\Models\ProcessRequest;
use ProcessMaker\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Auth;
use ProcessMaker\Models\EnvironmentVariable;

class StartProcessRequestRules {
    private $process;
    private $user;
    public $message;

    public function __construct(Process $process, User $user)
    {
        $this->process = $process;
        $this->user = $user;
    }

    public function agencyAllowed() : bool
    {
        if (stripos($this->process->name, 'AZPerforms') === false) {
            return true;
        }

        if ($this->user->meta && $this->user->meta->agency) {
            if ($this->agencyIsActive($this->user->meta->agency)) {
                return true;
            }
        }

        $this->message = 'Agency Inactive';
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

    public function remoteWorkAgreementInProgress() : bool
    {
        if (stripos($this->process->name, 'Remote Work - Initiate or Terminate Agreement') === false) {
            return true;
        }

        if (!$this->getRemoteWorkAgreementInProgress()) {
            return true;
        }

        $this->message = 'Currently you have a request In Progress';
        return false;
    }

    private function getRemoteWorkAgreementInProgress() {
        $processID = EnvironmentVariable::whereName('rwa_initiate_terminate_process_id')->first()->value;
        $result = ProcessRequest::select('id')
            ->where('process_id', $processID)
            ->where('status', 'ACTIVE')
            ->where('user_id', Auth::user()->id)
            ->first();
        return empty($result) ? false : true;
    }
}
