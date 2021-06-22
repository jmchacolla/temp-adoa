<?php
namespace ProcessMaker\Package\Adoa\Http\Middleware;

use Auth;
use Closure;
use ProcessMaker\Models\ProcessRequest;
use ProcessMaker\Models\ProcessRequestToken;

class Redirect
{
    private $inAdminGroup = false;
    
    private $inAgencyGroup = false;
    
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $this->setGroupStatus();
            
            if (! $this->inAdminGroup && ! $this->inAgencyGroup) {
                switch ($request->path()) {
                    case 'tasks':
                    case 'requests':
                        return redirect()->route('package.adoa.listToDo');
                }
                
                /*if ($request->route()->getName() == 'requests.show') {
                    if (isset($request->route()->parameters['request'])) {
                        $processRequest = $request->route()->parameters['request'];
                        $task = $this->getTask($processRequest);
                        
                        if (! $task) {
                            if (isset($processRequest->data['EMA_FORM_ACTION']) && $processRequest->data['EMA_FORM_ACTION'] == 'DELETE') {
                                return redirect()->route('package.adoa.listRequests');
                            } elseif (isset($processRequest->data['FORM_ACTION']) && $processRequest->data['FORM_ACTION'] == 'DELETE') {
                                return redirect()->route('package.adoa.listRequests');
                            } else {
                                return redirect()->route('package.adoa.getPdfFile', ['request' => $processRequest->id]);
                            }
                        }
                    }
                }*/
            }
        }

        return $next($request);
    }

    private function setGroupStatus()
    {
        $groups = Auth::user()->groups->pluck('id');
        $this->inAdminGroup = $groups->contains(config('adoa.admin_group_id'));
        $this->inAgencyGroup = $groups->contains(config('adoa.admin_agency_group_id'));
    }
    
    private function getTask(ProcessRequest $processRequest) {
        return ProcessRequestToken::where('process_request_id', $processRequest->id)
            ->where('element_type', 'task')
            ->where('status', 'ACTIVE')
            ->where('user_id', Auth::user()->id)
            ->first();
    }
}
