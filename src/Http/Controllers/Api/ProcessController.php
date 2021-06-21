<?php
namespace ProcessMaker\Package\Adoa\Http\Controllers\Api;

use Illuminate\Http\Request;
use ProcessMaker\Http\Controllers\Api\ProcessController as BaseProcessController;
use ProcessMaker\Package\Adoa\StartProcessRequestRules;
use ProcessMaker\Models\Process;

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
            $startProcessRequestRules = new StartProcessRequestRules($process, $user);
            if($startProcessRequestRules->remoteWorkAgreementInProgress()) {
                return true;
            } else {
                return false;
            }
            if ($startProcessRequestRules->agencyAllowed()) {
                return true;
            } else {
                return false;
            }
        })->values();
        return $result;
    }

    // Override core's triggerStartEvent
    public function triggerStartEvent(Process $process, Request $request)
    {
        $startProcessRequestRules = new StartProcessRequestRules($process, $request->user());
        if (!$startProcessRequestRules->agencyAllowed()) {
            throw new \Exception("User's agency is disabled");
        }
        if (!$startProcessRequestRules->remoteWorkAgreementInProgress()) {
            throw new \Exception("Currently you have a open request");
        }
        return parent::triggerStartEvent($process, $request);
    }

}
