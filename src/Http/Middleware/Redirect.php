<?php
namespace ProcessMaker\Package\Adoa\Http\Middleware;

use Auth;
use Closure;
use ProcessMaker\Models\ProcessRequest;
use ProcessMaker\Models\ProcessRequestToken;

class Redirect
{
    const ADMIN_GROUP_ID = 3;
    
    const AGENCY_GROUP_ID = 8;
    
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
                if ($request->route()->getName() == 'requests.show') {
                    if (isset($request->route()->parameters['request'])) {
                        $processRequest = $request->route()->parameters['request'];
                        $userId = Auth::user()->id;
                        if ($processRequest['user_id'] == $userId && isset($processRequest['data']['pdf'])) {
                            return redirect()->route('package.adoa.getPdfFile', ['request' => $processRequest->id]);
                        }
                        if ($processRequest['user_id'] != $userId) {
                            return redirect()->route('package.adoa.listRequests');
                        }
                        if ($task = $this->getTask($processRequest, $userId)) {
                            return redirect()->route('tasks.edit', ['task' => $task->id]);
                        }
                    }
                }
            }
        }

        return $next($request);
    }

    private function setGroupStatus()
    {
        $groups = Auth::user()->groups->pluck('id');
        $this->inAdminGroup = $groups->contains(self::ADMIN_GROUP_ID);
        $this->inAgencyGroup = $groups->contains(self::AGENCY_GROUP_ID);
    }
    
    private function getTask(ProcessRequest $processRequest, $userId) {
        return ProcessRequestToken::where('process_request_id', $processRequest->id)
            ->where('element_type', 'task')
            ->where('status', 'ACTIVE')
            ->where('user_id', $userId)
            ->first();
    }
}
