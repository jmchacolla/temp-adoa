<?php
namespace ProcessMaker\Package\PackageZjAdoa\Http\Controllers;

use ProcessMaker\Http\Controllers\Controller;
use ProcessMaker\Http\Resources\ApiCollection;
use ProcessMaker\Package\PackageZjAdoa\Models\Sample;
use ProcessMaker\Models\ProcessRequest;
use ProcessMaker\Models\ProcessRequestToken;
use ProcessMaker\Models\EnvironmentVariable;
use ProcessMaker\Events\ActivityAssigned;
use ProcessMaker\Http\Resources\Task as Resource;
use ProcessMaker\Models\Media;
use RBAC;
use Illuminate\Http\Request;
use URL;
use DB;
use Auth;
use Illuminate\Support\Facades\Mail;

class AdoaController extends Controller
{
    public function index(){
        return view('package-zj-adoa::index');
    }

    public function fetch(Request $request){
        $query = Sample::query();

        $filter = $request->input('filter', '');
        if (!empty($filter)) {
            $filter = '%' . $filter . '%';
            $query->where(function ($query) use ($filter) {
                $query->Where('name', 'like', $filter);
            });
        }

        $order_by = $request->has('order_by') ? $order_by = $request->get('order_by') : 'name';
        $order_direction = $request->has('order_direction') ? $request->get('order_direction') : 'ASC';

        $response =
            $query->orderBy(
                $request->input('order_by', $order_by),
                $request->input('order_direction', $order_direction)
            )->paginate($request->input('per_page', 10));

        return new ApiCollection($response);
    }

    public function store(Request $request){
        $sample = new Sample();
        $sample->fill($request->json()->all());
        $sample->saveOrFail();
        return $sample;
    }

    public function update(Request $request, $license_generator){
        Sample::where('id', $license_generator)->update([
            'name' => $request->get("name"),
            'status' => $request->get("status")
            ]);
        return response([], 204);
    }

    public function destroy($license_generator){
        Sample::find($license_generator)->delete();
        return response([], 204);
    }

    public function generate($license_generator){

    }

    public function getListToDo() {
        $adoaListToDo = DB::table('process_request_tokens')
            ->leftJoin('process_requests', 'process_request_tokens.process_request_id', '=', 'process_requests.id')
            ->select('process_request_tokens.id AS task_id',
                'process_request_tokens.element_name',
                'process_request_tokens.process_request_id as request_id',
                'process_request_tokens.status as task_status',
                'process_requests.name',
                'process_requests.status as request_status',
                'process_requests.data',
                'process_request_tokens.created_at')
            ->where('process_request_tokens.element_type', 'task')
            ->where('process_request_tokens.status', 'ACTIVE')
            ->whereNotIn('process_requests.process_id', [EnvironmentVariable::whereName('process_id_regeneration')->first()->value])
            ->where('process_request_tokens.user_id', Auth::user()->id)
            ->orderBy('process_request_tokens.process_request_id', 'desc')
            ->get();

        return view('package-zj-adoa::adoaListToDo', ['adoaListToDo' => $adoaListToDo]);
    }

    public function getListRequests() {
        $adoaListRequests = DB::table('process_requests')
            ->join('processes', 'process_requests.process_id', '=', 'processes.id')
            ->select('process_requests.id as request_id',
                'process_requests.process_id',
                'process_requests.name',
                'process_requests.status as request_status',
                'process_requests.data',
                'process_requests.created_at',
                'process_requests.completed_at')
            ->whereNotIn('processes.process_category_id', [1, 2])
            ->whereNotIn('process_requests.process_id', [EnvironmentVariable::whereName('process_id_regeneration')->first()->value])
            ->whereIn('process_requests.status', ['ACTIVE', 'COMPLETED'])
            ->where('process_requests.user_id', Auth::user()->id)
            ->orderBy('process_requests.id', 'desc')
            ->get();

        $finalRequestList = array();
        foreach ($adoaListRequests as $request) {
            if($request->request_status == 'ACTIVE') {
                $listRequestTokens = DB::table('process_request_tokens')
                    ->leftJoin('users', 'process_request_tokens.user_id', '=', 'users.id')
                    ->select('process_request_tokens.id as task_id',
                        'process_request_tokens.element_name',
                        'process_request_tokens.element_type',
                        'process_request_tokens.status as task_status',
                        'process_request_tokens.user_id as user_id',
                        'users.firstname',
                        'users.lastname')
                    ->where('process_request_tokens.element_type', 'task')
                    ->where('process_request_tokens.process_request_id', $request->request_id)
                    ->where('process_request_tokens.status', 'ACTIVE')
                    ->get();

                if (count($listRequestTokens) > 0) {
                    foreach ($listRequestTokens as $requestToken) {
                        $request->file_id = null;
                        $request->custom_properties = null;
                        $request = (object) array_merge((array) $request, (array) $requestToken);
                        $finalRequestList[] = $request;
                    }
                } else {
                    $request->task_id = null;
                    $request->element_name = null;
                    $request->element_type = null;
                    $request->task_status = 'ACTIVE';
                    $request->user_id = null;
                    $request->firstname = null;
                    $request->lastname = null;
                    $request->file_id = null;
                    $request->custom_properties = null;
                    $finalRequestList[] = $request;
                }
            } else {
                $listRequestTokens = DB::table('media')
                    ->select('id AS file_id',
                        'custom_properties')
                    ->where('model_id', $request->request_id)
                    // ->where(function ($query) {
                    //     $query->where('name', 'like', 'Formal_Appraisal_%')
                    //         ->orWhere('name', 'like', 'Informal_Appraisal_%')
                    //         ->orWhere('name', 'like', 'Coaching_Note_%')
                    //         ->orWhere('name', 'like', 'Coaching_Note_%')
                    //         ->orWhere('name', 'like', 'Self_Appraisal_%')
                    //         ->orWhere('name', 'Remote_Work_Agreement');
                    // })
                    ->get();

                $request->task_id = null;
                $request->element_name = 'Completed';
                $request->element_type = null;
                $request->task_status = 'COMPLETED';
                $request->user_id = null;
                $request->firstname = null;
                $request->lastname = null;

                if (count($listRequestTokens) == 0) {
                    $request->file_id = null;
                    $request->custom_properties = null;
                } else {
                    $request->file_id = $listRequestTokens[0]->file_id;
                    $request->custom_properties = $listRequestTokens[0]->custom_properties;
                }
                $finalRequestList[] = $request;
            }
        }

        return view('package-zj-adoa::adoaListRequests', ['adoaListRequests' => $finalRequestList, 'process_id_terminate_rwa_send_email_and_pdf' => EnvironmentVariable::whereName('process_id_terminate_rwa_send_email_and_pdf')->first()->value]);
    }

    public function getListRequestsAgency($groupId) {
        $member = $this->getGroupAdminAgency(Auth::user()->id, $groupId);
        if (count($member) > 0 && $groupId == config('adoa.agency_admin_group_id')) {
            $agencies = explode(',', Auth::user()->meta->agency);
            $agenciesArray = array();

            if (count($agencies) == 1 && $agencies[0] == 'ALL') {
                $agenciesArray = ['PR', 'RT', 'AB', 'AD', 'AE', 'AF', 'AG', 'AH', 'AT', 'BB', 'BD', 'BH', 'BN', 'BR', 'CB', 'CC', 'CD', 'CE', 'CH', 'CL', 'CO', 'CS', 'CT', 'DC', 'DE', 'DJ', 'DT', 'EB', 'ED', 'EP', 'EQ', 'EV', 'FI', 'FO', 'GF', 'GH', 'GM', 'GS', 'GV', 'HC', 'HD', 'HG', 'HI', 'HL', 'HO', 'HS', 'HU', 'IA', 'IB', 'IC', 'ID', 'JC', 'JL', 'LA', 'LC', 'LD', 'LL', 'LO', 'LW', 'MA', 'ME', 'MI', 'MN', 'NB', 'OB', 'OS', 'PA', 'PE', 'PH', 'PI', 'PM', 'PS', 'RB', 'RC', 'RD', 'RE', 'RG', 'RV', 'SD', 'SF', 'SN', 'SP', 'ST', 'TO', 'TR', 'UL', 'VS', 'VT', 'WC', 'WF', 'WM', 'AM', 'AN', 'AU', 'BA', 'BF', 'CN', 'CR', 'DF', 'DO', 'DX', 'EC', 'EO', 'FA', 'FD', 'FX', 'HE', 'MT', 'NC', 'NS', 'OT', 'PB', 'PO', 'PP', 'PT', 'PV', 'RS', 'SY', 'TE', 'TX', 'UO'];
            } else {
                foreach($agencies as $agency) {
                    $agenciesArray[] = $agency;
                }
            }

            $levels = explode(',', Auth::user()->meta->employee_process_level);
            $levelsArray = array();

            if (count($levels) == 1 && $levels[0] == 'ALL') {
                $levelsArray = [];
            } else {
                foreach($levels as $level) {
                    $levelsArray[] = $level;
                }
            }

            return view('package-zj-adoa::adoaAdminAgency', ['groupId' => config('adoa.agency_admin_group_id'), 'agenciesArray' => $agenciesArray, 'levelsArray' => $levelsArray]);
        } else {
            return abort(403, 'Unauthorized action.');
        }
    }

    public function getListShared() {
        $adoaListShared = DB::table('process_requests')
            ->join('media', 'process_requests.id', '=', 'media.model_id')
            ->join('processes', 'process_requests.process_id', '=', 'processes.id')
            ->select('process_requests.id as request_id',
                'process_requests.user_id',
                'process_requests.process_id',
                'process_requests.name',
                'process_requests.data',
                'process_requests.created_at',
                'process_requests.completed_at',
                'media.id AS file_id',
                'media.custom_properties')
            ->where('media.disk', 'public')
            ->where('process_requests.status', 'COMPLETED')
            ->whereNotIn('processes.process_category_id', [1, 2])
            ->whereNotIn('process_requests.process_id', [EnvironmentVariable::whereName('process_id_regeneration')->first()->value])
            ->where(function ($query) {
                $query->where('process_requests.data->EMA_EMPLOYEE_EIN', Auth::user()->username)
                    ->orWhere('process_requests.data->CON_EMPLOYEE_EIN', Auth::user()->username)
                    ->orwhere('process_requests.data->EMA_SUPERVISOR_EIN', Auth::user()->username)
                    ->orwhere('process_requests.data->EMA_MANAGER_EIN', Auth::user()->username)
                    ->orWhere('process_requests.data->EMA_UPLINE_EIN', Auth::user()->username)
                    ->orWhere('process_requests.data->CON_SUPERVISOR_EIN', Auth::user()->username)
                    ->orWhere('process_requests.data->CON_UPLINE_EIN', Auth::user()->username)
                    ->orWhere('process_requests.data->SUPERVISOR_EIN', Auth::user()->username)
                    ->orWhere('process_requests.data->UPLINE_EIN', Auth::user()->username);
            })
            ->orderBy('process_requests.id', 'desc')
            ->get();

        return view('package-zj-adoa::adoaListShared', ['adoaListShared' => $adoaListShared, 'process_id_terminate_rwa_send_email_and_pdf' => EnvironmentVariable::whereName('process_id_terminate_rwa_send_email_and_pdf')->first()->value]);
    }

    public function viewFile(ProcessRequest $request, Media $media)
    {
        $ids = $request->getMedia()->pluck('id');
        if (!$ids->contains($media->id)) {
            abort(403);
        }
        return response()->file($media->getPath());
    }

	public function getRequestByProcessAndUser($process_id, $user_id) {
        return DB::table('process_requests')
            ->where('process_id', $process_id)
            ->where('user_id', $user_id)
			->where('status', 'COMPLETED')
			->get();
    }

    public function getEnvs() {
        return [
            'DATA_DB_USERNAME'=> config('database.connections.processmaker.username'),
            'DATA_DB_PASSWORD'=> config('database.connections.processmaker.password')
        ];
    }

    public function getTask($request) {
        return DB::table('process_request_tokens')
            ->join('process_requests', 'process_request_tokens.process_request_id', '=', 'process_requests.id')
            ->select('process_request_tokens.id AS task_id',
                'process_requests.status')
            ->where('process_request_tokens.process_request_id', $request)
            ->where('process_request_tokens.element_type', 'task')
            ->where('process_request_tokens.status', 'ACTIVE')
            ->where('process_request_tokens.user_id', Auth::user()->id)
            ->get();
    }

    public function getFile($request) {
        $pdf = DB::table('media')
            ->select('model_id as request_id',
                'id as file_id')
            ->where('model_id', $request)
            ->first();

        return view('package-zj-adoa::adoaViewPdf', ['pdf' => $pdf]);
    }

    public function getGroupAdminAgency($user_id, $groupId) {
        return DB::table('group_members')
            ->select('id')
            ->where('member_id', $user_id)
            ->where('group_id', $groupId)
            ->get();
    }

    public function getGroupAdmin($user_id) {
        return DB::table('group_members')
            ->select('id')
            ->where('member_id', $user_id)
            ->where('group_id', '3')
            ->get();
    }

    public function getUserInformation($ein) {
        return DB::table('users')
            ->select('id')
            ->where('status', 'ACTIVE')
            ->where('username', $ein)
            ->get();
    }

    public function getUserByEin($ein) {
        return DB::table('users')
            ->where('meta->ein', $ein)
            ->get();
    }

    public function getUserById($user_id) {
        return DB::table('users')
            ->where('id', $user_id)
            ->first();
    }

    public function getOpenTask($user_id, $request_id){
        return DB::table('process_request_tokens')
            ->select('id')
            ->where('status', 'ACTIVE')
            ->where('process_request_id', $request_id)
            ->where('user_id', $user_id)
            ->get();
    }

    public function getTaskAgency($task_id) {
        return DB::table('process_request_tokens')
            ->join('users', 'process_request_tokens.user_id', '=', 'users.id')
            ->select('process_request_tokens.id',
                'process_request_tokens.element_name',
                'users.firstname',
                'users.lastname')
            ->where('process_request_tokens.status', 'ACTIVE')
            ->where('process_request_tokens.id', $task_id)
            ->get();
    }

    public function getAgencyEnabled($agency)
    {
        try {
            $adoaHeaders = array(
                "Accept: application/json",
                "Authorization: Bearer 3-5738379ecfaa4e9fb2eda707779732c7",
            );
            $url = EnvironmentVariable::whereName('base_url_api_adoa')->first()->value . "AzPerformAgencyCFG.json?agency=" . $agency;

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $adoaHeaders);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $resp = curl_exec($curl);
            curl_close($curl);

            return $resp;
        } catch (Exception $error) {
            return $response['error'] = 'There are errors in the Function: getAgencyEnabled ' . $error->getMessage();
        }
    }

    public function getUsersByAgency(Request $request) {
        $searchTerm  = $request['searchTerm'];

        //Getting Agencies Information from meta data
        $agencies = explode(',', $request['agency']);
        $agenciesArray = array();

        if (count($agencies) == 1 && $agencies[0] == 'ALL') {
            $flagAgency = 0;
        } else {
            foreach($agencies as $agency) {
                $agenciesArray[] = $agency;
            }
            $flagAgency = 1;
        }

        //Getting Process Level Information from meta data
        $levels = explode(',', $request['employee_process_level']);
        $levelsArray = array();

        if (count($levels) == 1 && $levels[0] == 'ALL') {
            $flagLevel = 0;
        } else {
            foreach($levels as $level) {
                $levelsArray[] = $level;
            }
            $flagLevel = 1;
        }

        $currentUser = DB::table('users')
            ->select('id', 'firstname', 'lastname', 'username', 'meta->agency as agency', 'meta->ein as ein')
            ->where('status', 'ACTIVE')
            ->where('id', Auth::user()->id)
            ->when($searchTerm, function ($query, $searchTerm) {
                return $query->where(DB::raw('CONCAT_WS(" ", firstname, lastname, username)'), 'like', '%' . $searchTerm . '%');
            });

        $usersByAgency = DB::table('users')
            ->select('id', 'firstname', 'lastname', 'username', 'meta->agency as agency', 'meta->ein as ein')
            ->where('status', 'ACTIVE')
            ->where('meta->position', '!=', '')
            ->union($currentUser);

        if ($flagAgency == 1) {
            $usersByAgency = $usersByAgency
                ->whereIn('users.meta->agency', $agenciesArray);
        }

        if ($flagLevel == 1) {
            $usersByAgency = $usersByAgency
                ->whereIn('users.meta->process_level', $levelsArray);
        }

        $usersByAgency = $usersByAgency
            ->when($searchTerm, function ($query, $searchTerm) {
                return $query->where(DB::raw('CONCAT_WS(" ", firstname, lastname, username)'), 'like', '%' . $searchTerm . '%');
                })
            ->orderBy('firstname')
            ->get();

        return $usersByAgency;
    }

    public function updateTaskRequest(Request $request, ProcessRequestToken $task)
    {
        if ($request->input('status') === 'COMPLETED') {
            if ($task->status === 'CLOSED') {
                return abort(422, __('Task already closed'));
            }
            // Skip ConvertEmptyStringsToNull and TrimStrings middlewares
            $data = json_decode($request->getContent(), true);
            $data = SanitizeHelper::sanitizeData($data['data'], $task->getScreen());
            //Call the manager to trigger the start event
            $process = $task->process;
            $instance = $task->processRequest;
            WorkflowManager::completeTask($process, $instance, $task, $data);
            return new Resource($task->refresh());
        } elseif (!empty($request->input('user_id'))) {
            $userToAssign = $request->input('user_id');
            if ($task->is_self_service && $userToAssign == Auth::id() && !$task->user_id) {
                // Claim task
                $task->is_self_service = 0;
                $task->user_id = $userToAssign;
                $task->persistUserData($userToAssign);
            } else {
                // Reassign user
                $task->user_id = $userToAssign;
                $task->persistUserData($userToAssign);
            }
            $task->save();

            // Send a notification to the user
            event(new ActivityAssigned($task));
            return new Resource($task->refresh());
        } else {
            return abort(422);
        }
    }

    public function getListRequestsAgencyDashboard($groupId, Request $request) {
        $member = $this->getGroupAdminAgency($request->input('userId'), $groupId);
        if (count($member) > 0 && $groupId == config('adoa.agency_admin_group_id')) {
            //Getting Agency Information from meta data
            if (empty($request->input('filterAgency'))) {
                $agencies = explode(',', $request->input('userAgency'));
            } else {
                $agencies = $request->input('filterAgency');
            }

            //Getting Agency Information from meta data
            $processes = explode(',', $request->input('processId'));
            $processesArray = array();

            if (count($processes) == 1 && $processes[0] == 'ALL') {
                $flagProcess = 0;
            } else {
                foreach($processes as $process) {
                    $processesArray[] = $process;
                }
                $flagProcess = 1;
            }

            //Getting Agency Information from meta data
            if (empty($request->input('filterLevel'))) {
                $levels = explode(',', $request->input('processLevel'));
            } else {
                $levels = $request->input('filterLevel');
            }

            //Query to get requests for agency admin
            $adoaListRequestsAgency = DB::table('process_requests')
            ->leftjoin('processes', 'process_requests.process_id', '=', 'processes.id')
            ->select('process_requests.id as request_id',
                'process_requests.process_id',
                'process_requests.name',
                'process_requests.status as request_status',
                'process_requests.EMA_EMPLOYEE_FIRST_NAME as ema_employee_first_name',
                'process_requests.EMA_EMPLOYEE_LAST_NAME as ema_employee_last_name',
                'process_requests.EMA_EMPLOYEE_EIN as ema_employee_ein',
                'process_requests.CON_EMPLOYEE_FIRST_NAME as con_employee_first_name',
                'process_requests.CON_EMPLOYEE_LAST_NAME as con_employee_last_name',
                'process_requests.CON_EMPLOYEE_EIN as con_employee_ein',
                'process_requests.FA_OWNER as FA_OWNER',
                'process_requests.created_at',
                'process_requests.completed_at')
            ->whereNotIn('processes.process_category_id', [1, 2])
            ->whereNotIn('process_requests.process_id', [EnvironmentVariable::whereName('process_id_regeneration')->first()->value]);

            $positionsArray = array();
            if ($agencies[0] != 'ALL' || $levels[0] != 'ALL') {
                $collectionId = EnvironmentVariable::whereName('id_adoa_positions_collection')->first()->value;
                $adoaListRequestsAgency = $adoaListRequestsAgency
                ->whereIn('process_requests.POSITION_NUMBER', function($query) use ($agencies, $levels, $collectionId) {
                    $query->from('collection_' . $collectionId)
                    ->select('data->POSITION as position')
                    ->whereIn('data->AGENCY', $agencies);

                    if (count($levels) > 0 && $levels[0] != 'ALL') {
                        $query = $query
                        ->whereIn('data->PROCESS_LEVEL', $levels);
                    }
                });
            }

            if (!empty($request->input('filterInitDate'))) {
                $adoaListRequestsAgency = $adoaListRequestsAgency
                ->whereDate('process_requests.created_at', '>=', $request->input('filterInitDate'));
            }

            if (!empty($request->input('filterEndDate'))) {
                $adoaListRequestsAgency = $adoaListRequestsAgency
                ->whereDate('process_requests.created_at', '<=', $request->input('filterEndDate'));
            }

            if (!empty($request->input('filterEmployeeName'))) {
                $adoaListRequestsAgency = $adoaListRequestsAgency
                ->where(function ($query) use ($request) {
                    $query->where('process_requests.EMA_EMPLOYEE_EIN', $request->input('filterEmployeeName'))
                        ->orWhere('process_requests.CON_EMPLOYEE_EIN', $request->input('filterEmployeeName'));
                });
            }

            if (!empty($request->input('filterEIN'))) {
                $adoaListRequestsAgency = $adoaListRequestsAgency
                ->where(function ($query) use ($request) {
                    $query->where('process_requests.EMA_EMPLOYEE_EIN', $request->input('filterEIN'))
                        ->orWhere('process_requests.CON_EMPLOYEE_EIN', $request->input('filterEIN'));
                });
            }

            if (!empty($request->input('filterRequestId'))) {
                $adoaListRequestsAgency = $adoaListRequestsAgency
                ->where('process_requests.id', $request->input('filterRequestId'));
            }

            if (!empty($request->input('filterDocument'))) {
                $processes = $this->getProcessId($request->input('filterDocument'));

                $processesArray = array();
                foreach ($processes as $process) {
                    $processesArray[] = $process->id;
                }

                $adoaListRequestsAgency = $adoaListRequestsAgency
                    ->whereIn('process_requests.process_id', $processesArray);
            }

            if ($flagProcess == 1) {
                $adoaListRequestsAgency = $adoaListRequestsAgency
                    ->whereIn('process_requests.process_id', $processesArray);
            }

            if (empty($request->input('filterStatus'))) {
                $adoaListRequestsAgency = $adoaListRequestsAgency
                    ->whereIn('process_requests.status', ['ACTIVE', 'COMPLETED']);
            } else {
                $adoaListRequestsAgency = $adoaListRequestsAgency
                    ->whereIn('process_requests.status', $request->input('filterStatus'));
            }

            $adoaListRequestsAgency = $adoaListRequestsAgency
                ->orderBy('process_requests.id', 'desc')
                ->get()
                ->unique('request_id');

            $finalRequestList = array();
            foreach ($adoaListRequestsAgency as $request) {
                if($request->request_status == 'ACTIVE') {
                    $listRequestTokens = DB::table('process_request_tokens')
                        ->leftJoin('users', 'process_request_tokens.user_id', '=', 'users.id')
                        ->select('process_request_tokens.id as task_id',
                            'process_request_tokens.element_name',
                            'process_request_tokens.element_type',
                            'process_request_tokens.status as task_status',
                            'process_request_tokens.user_id as user_id_task')
                        ->where('process_request_tokens.element_type', 'task')
                        ->where('process_request_tokens.process_request_id', $request->request_id)
                        ->where('process_request_tokens.status', 'ACTIVE')
                        ->get();

                    if (count($listRequestTokens) > 0) {
                        foreach ($listRequestTokens as $requestToken) {
                            $request->file_id = null;
                            $request->custom_properties = null;
                            $request = (object) array_merge((array) $request, (array) $requestToken);
                            $finalRequestList[] = $request;
                        }
                    } else {
                        $request->task_id = null;
                        $request->element_name = null;
                        $request->element_type = null;
                        $request->task_status = 'ACTIVE';
                        $request->user_id_task = null;
                        $request->file_id = null;
                        $request->custom_properties = null;
                        $finalRequestList[] = $request;
                    }
                } else {
                    $listRequestTokens = DB::table('media')
                        ->select('id AS file_id',
                            'custom_properties')
                        ->where('model_id', $request->request_id)
                        ->get();

                    $request->task_id = null;
                    $request->element_name = 'Completed';
                    $request->element_type = null;
                    $request->task_status = 'COMPLETED';
                    $request->user_id_task = null;

                    if (count($listRequestTokens) == 0) {
                        $request->file_id = null;
                        $request->custom_properties = null;
                    } else {
                        $request->file_id = $listRequestTokens[0]->file_id;
                        $request->custom_properties = $listRequestTokens[0]->custom_properties;
                    }
                    $finalRequestList[] = $request;
                }
            }

            $process_id_terminate_rwa_send_email_and_pdf = EnvironmentVariable::whereName('process_id_terminate_rwa_send_email_and_pdf')->first()->value;
            $count = count($finalRequestList);

            $dataTableFormat = array();
            $dataTable = array();

            if ($count > 0) {
                foreach ($finalRequestList as $request) {
                    if ($request->name != 'Email Notification Sub Process') {
                        $createdDate = $request->created_at;
                        $newCreatedDate = new \DateTime($createdDate);
                        $newCreatedDate->setTimezone(new \DateTimeZone('America/Phoenix'));
                        if ($request->completed_at != null) {
                            $completedDate = $request->completed_at;
                            $newCompletedDate = new \DateTime($completedDate);
                            $newCompletedDate->setTimezone(new \DateTimeZone('America/Phoenix'));
                            $newCompletedDateFormat = $newCompletedDate->format('m/d/Y h:i:s A');
                        } else {
                            $newCompletedDateFormat = '';
                        }

                        if (!is_null($request->custom_properties)) {
                            $customProperties = $request->custom_properties;
                            $newCustomProperties = json_decode($customProperties);
                        }

                        if ($request->task_status == 'COMPLETED') {
                            $employeeName = '';
                            $employeeEin = '';

                            if ($request->process_id == $process_id_terminate_rwa_send_email_and_pdf) {
                                $dataName = $newCustomProperties->data_name;
                                $nameFile = explode('_', $dataName);
                                if (array_key_exists(3, $nameFile) && array_key_exists(4, $nameFile)) {
                                    $employeeName = $nameFile[3] . ' ' . $nameFile[4];
                                }

                                if (array_key_exists(5, $nameFile)) {
                                    $employeeEin = $nameFile[5];
                                }
                            } else {
                                if (!empty($request->ema_employee_first_name)) {
                                    $employeeName = $request->ema_employee_first_name . ' ' . $request->ema_employee_last_name;
                                } elseif (!empty($request->con_employee_first_name)) {
                                    $employeeName = $request->con_employee_first_name . ' ' . $request->con_employee_last_name;
                                }

                                if (!empty($request->ema_employee_ein)) {
                                    $employeeEin = $request->ema_employee_ein;
                                } elseif (!empty($request->con_employee_ein)) {
                                    $employeeEin = $request->con_employee_ein;
                                }
                            }

                            $options = '';
                            if (!empty($request->file_id) || !is_null($request->file_id)) {
                                $options = '<a href="#"><i class="fas fa-eye" style="color: #71A2D4;" title="View PDF" onclick="viewPdf(' . $request->request_id . ', ' . $request->file_id . ');"></i></a>&nbsp;<a href="#"><i class="fas fa-print" style="color: #71A2D4;" title="Print PDF" onclick="printPdf(' . $request->request_id . ', ' . $request->file_id . ');"></i></a>&nbsp;';
                            }

                            $dataTable[] = [
                                'request_id' => $request->request_id,
                                'process_name' => $request->process_id == $process_id_terminate_rwa_send_email_and_pdf ? 'Remote Work - Terminate Agreement' : $request->name,
                                'employee_name' => $employeeName,
                                'employee_ein' => $employeeEin,
                                'started' => $newCreatedDate->format('m/d/Y h:i:s A'),
                                'completed' => $newCompletedDateFormat,
                                'current_task' => '',
                                'current_user' => '',
                                'status' => $request->request_status,
                                'options' => $options
                            ];
                        } elseif ($request->task_status == 'ACTIVE') {
                            $userOwnerTask = $this->getUserById($request->user_id_task);
                            $request->firstname = !empty($userOwnerTask->firstname) ? $userOwnerTask->firstname : '';
                            $request->lastname = !empty($userOwnerTask->lastname) ? $userOwnerTask->lastname : '';

                            $employeeName = '';
                            $employeeEin = '';
                            if ($request->process_id != $process_id_terminate_rwa_send_email_and_pdf) {
                                if (!empty($request->ema_employee_first_name)) {
                                    $employeeName = $request->ema_employee_first_name . ' ' . $request->ema_employee_last_name;
                                } elseif (!empty($request->con_employee_first_name)) {
                                    $employeeName = $request->con_employee_first_name . ' ' . $request->con_employee_last_name;
                                }
                                if (!empty($request->ema_employee_ein)) {
                                    $employeeEin = $request->ema_employee_ein;
                                } elseif (!empty($request->con_employee_ein)) {
                                    $employeeEin = $request->con_employee_ein;
                                }
                            }

                            $options = '';
                            if ($request->request_status != 'COMPLETED') {
                                $options .= '<a href="#"><i class="fas fa-people-arrows" style="color: #71A2D4;" title="Reassign Request" onclick="reassign(' . $request->request_id . ', ' . $request->task_id . ');"></i></a>&nbsp;';
                            }
                            $options .= '&nbsp;<a href="/requests/' . $request->request_id . '"><i class="fas fa-external-link-square-alt" style="color: #71A2D4;" title="Open request"></i></a>';

                            $dataTable[] = [
                                'request_id' => $request->request_id,
                                'process_name' => $request->process_id == $process_id_terminate_rwa_send_email_and_pdf ? 'Remote Work - Terminate Agreement' : $request->name,
                                'employee_name' => $employeeName,
                                'employee_ein' => $employeeEin,
                                'started' => $newCreatedDate->format('m/d/Y h:i:s A'),
                                'completed' => $newCompletedDateFormat,
                                'current_task' => $request->element_name,
                                'current_user' => $request->firstname . ' ' . $request->lastname,
                                'status' => $request->request_status,
                                'options' => $options
                            ];
                        }
                    }
                }
            }
            $dataTableFormat = [
                'recordsTotal' => count($dataTable),
                'recordsFiltered' => count($dataTable),
                'data' => $dataTable
            ];

            return json_encode($dataTableFormat);
        } else {
            return abort(403, 'Unauthorized action.');
        }
    }

    public function getValidAgreement($collectionId) {
        $date = date('m/d/Y');
        $reminder2Weeks = date("m/d/Y", strtotime($date . "+ 2 week"));

        $reminders = DB::table('collection_' . $collectionId)
            ->select('id',
                'data->EMPLOYEE_FIRST_NAME as EMPLOYEE_FIRST_NAME',
                'data->EMPLOYEE_LAST_NAME as EMPLOYEE_LAST_NAME',
                'data->ADOA_RWA_REMOTE_AGREEMENT_START_DATE as ADOA_RWA_REMOTE_AGREEMENT_START_DATE',
                'data->ADOA_RWA_REMOTE_AGREEMENT_END_DATE as ADOA_RWA_REMOTE_AGREEMENT_END_DATE',
                'data->REQUEST_ID as REQUEST_ID',
                'data->ADOA_RWA_REMOTE_AGREEMENT_VALID as ADOA_RWA_REMOTE_AGREEMENT_VALID',
                'data->USER_ID as USER_ID',
                'data->ADOA_RWA_POSITION as ADOA_RWA_POSITION',
                'data->ADOA_RWA_EMPLOYEE_EMAIL as ADOA_RWA_EMPLOYEE_EMAIL')
            ->where('data->ADOA_RWA_REMOTE_AGREEMENT_VALID', 'Y')
            ->where('data->ADOA_RWA_REMOTE_AGREEMENT_END_DATE', $reminder2Weeks)
            ->get();

        $expirated1Day = date("m/d/Y", strtotime($date . "- 1 day"));

        $expirated = DB::table('collection_' . $collectionId)
            ->select('id',
                'data->EMPLOYEE_FIRST_NAME as EMPLOYEE_FIRST_NAME',
                'data->EMPLOYEE_LAST_NAME as EMPLOYEE_LAST_NAME',
                'data->ADOA_RWA_REMOTE_AGREEMENT_START_DATE as ADOA_RWA_REMOTE_AGREEMENT_START_DATE',
                'data->ADOA_RWA_REMOTE_AGREEMENT_END_DATE as ADOA_RWA_REMOTE_AGREEMENT_END_DATE',
                'data->REQUEST_ID as REQUEST_ID',
                'data->ADOA_RWA_REMOTE_AGREEMENT_VALID as ADOA_RWA_REMOTE_AGREEMENT_VALID',
                'data->USER_ID as USER_ID',
                'data->ADOA_RWA_POSITION as ADOA_RWA_POSITION',
                'data->ADOA_RWA_EMPLOYEE_EMAIL as ADOA_RWA_EMPLOYEE_EMAIL')
            ->where('data->ADOA_RWA_REMOTE_AGREEMENT_VALID', 'Y')
            ->where('data->ADOA_RWA_REMOTE_AGREEMENT_END_DATE', $expirated1Day)
            ->get();

        return [
            'reminders' => $reminders,
            'expirated' => $expirated
        ];
    }

    public function getRequestsUnassigned() {
        $unassignedRequestsPart1 = DB::select(DB::raw("SELECT table2.id, table2.name, table2.status, JSON_EXTRACT(user.meta, '$.ein') as ein, table2.created_at, table2.updated_at, CONCAT(user.firstname, ' ', user.lastname) as fullname, JSON_EXTRACT(user.meta, '$.agency') as agency FROM process_requests AS table2, users as user WHERE table2.status = 'ACTIVE' AND table2.user_id = user.id AND table2.id IN (SELECT table1.process_request_id FROM (SELECT process_request_id, COUNT(CASE WHEN status = 'ACTIVE' THEN 'ACTIVES' ELSE NULL END) AS 'ACTIVES', COUNT(CASE WHEN status != 'ACTIVE' THEN 'INACTIVES' ELSE NULL END) AS 'INACTIVES' FROM process_request_tokens WHERE process_request_tokens.process_id in (" . EnvironmentVariable::whereName('process_ids_unassigned')->first()->value . ") GROUP BY process_request_id) AS table1 WHERE ACTIVES = 0);"));

        $unassignedRequestsPart2 = DB::table('process_request_tokens')
            ->join('process_requests', 'process_request_tokens.process_request_id', '=', 'process_requests.id')
            ->join('users', 'process_request_tokens.user_id', '=', 'users.id')
            ->select('process_request_tokens.process_request_id as id,
                process_requests.name as name,
                process_requests.status as status,
                users.meta->ein as ein,
                process_requests.created_at as created_at,
                process_requests.updated_at as updated_at,
                users.firstname as fullname,
                users.meta->agency as agency')
            ->where('process_requests.status', 'ACTIVE')
            ->where('process_request_tokens.element_type', 'gateway')
            ->where('process_request_tokens.status', 'INCOMING')
            ->groupBy('process_request_tokens.process_request_id')
            ->get()
            ->toArray();

        return array_merge($unassignedRequestsPart1, $unassignedRequestsPart2);
    }

    public function getProcessId($processesArray) {
        return DB::table('processes')
            ->select('id', 'name')
            ->whereIn('name', $processesArray)
            ->where('status', 'ACTIVE')
            ->get();
    }

    public function getAdoaPositionsByFilter($agencies = '', $processLevels = '', $next = '')
    {
        try {
            $adoaHeaders = array(
                "Accept: application/json",
                "Authorization: Bearer 3-5738379ecfaa4e9fb2eda707779732c7",
            );

            $query = '';
            if (!empty($agencies) && $agencies[0] != 'ALL') {
                $query .= '&AGENCY__in=' . implode(",", $agencies);
            }
            if (!empty($processLevels) && $processLevels[0] != 'ALL') {
                $query .= '&PROCESS_LEVEL__in=' . implode(",", $processLevels);
            }
            if (!empty($next)) {
                $query .= '&_next=' . $next;
            }

            $url = EnvironmentVariable::whereName('base_url_api_adoa')->first()->value . 'position.json?_sort=POSITION' . $query . '&_size=max';
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $adoaHeaders);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $resp = curl_exec($curl);
            curl_close($curl);

            $positionInformationList = json_decode($resp);
            return $positionInformationList;
        } catch (Exception $error) {
            return $response['error'] = 'There are errors in the Function: getAdoaPositionsByFilter ' . $error->getMessage();
        }
    }

    public function getListDirectReports() {
        $userId = auth()->user()->id;
         return view('package-zj-adoa::adoaListManager', ['userId' => $userId]);
    }
  
    public function getListRequestsManager($userId) {
        $adoaListDirectReport = DB::table('process_requests')
            ->join('processes', 'process_requests.process_id', '=', 'processes.id')
            ->select('process_requests.id as request_id',
                'process_requests.process_id',
                'process_requests.name',
                'process_requests.status as request_status',
                'process_requests.data',
                'process_requests.created_at',
                'process_requests.completed_at')
            ->whereNotIn('processes.process_category_id', [1, 2])
            ->whereNotIn('process_requests.process_id', [EnvironmentVariable::whereName('process_id_regeneration')->first()->value])
            ->whereIn('process_requests.status', ['ACTIVE', 'COMPLETED'])
            ->where('process_requests.user_id', $userId)
            ->orderBy('process_requests.id', 'desc')
            ->get();
        $finalRequestList = array();
        foreach ($adoaListDirectReport as $request) {
            if($request->request_status == 'ACTIVE') {
                $listRequestTokens = DB::table('process_request_tokens')
                    ->leftJoin('users', 'process_request_tokens.user_id', '=', 'users.id')
                    ->select('process_request_tokens.id as task_id',
                        'process_request_tokens.element_name',
                        'process_request_tokens.element_type',
                        'process_request_tokens.status as task_status',
                        'process_request_tokens.user_id as user_id',
                        'users.firstname',
                        'users.lastname')
                    ->where('process_request_tokens.element_type', 'task')
                    ->where('process_request_tokens.process_request_id', $request->request_id)
                    ->where('process_request_tokens.status', 'ACTIVE')
                    ->get();
              
                if (count($listRequestTokens) > 0) {
                    foreach ($listRequestTokens as $requestToken) {
                        $request->file_id = null;
                        $request->custom_properties = null;
                        $request = (object) array_merge((array) $request, (array) $requestToken);
                        $finalRequestList[] = $request;
                    }
                } else {
                    $request->task_id = null;
                    $request->element_name = null;
                    $request->element_type = null;
                    $request->task_status = 'ACTIVE';
                    $request->user_id = null;
                    $request->firstname = null;
                    $request->lastname = null;
                    $request->file_id = null;
                    $request->custom_properties = null;
                    $finalRequestList[] = $request;
                }
            } else {
                $listRequestTokens = DB::table('media')
                    ->select('id AS file_id',
                        'custom_properties')
                    ->where('model_id', $request->request_id)
                    ->get();
                $request->task_id = null;
                $request->element_name = 'Completed';
                $request->element_type = null;
                $request->task_status = 'COMPLETED';
                $request->user_id = null;
                $request->firstname = null;
                $request->lastname = null;
                if (count($listRequestTokens) == 0) {
                    $request->file_id = null;
                    $request->custom_properties = null;
                } else {
                    $request->file_id = $listRequestTokens[0]->file_id;
                    $request->custom_properties = $listRequestTokens[0]->custom_properties;
                }
                $finalRequestList[] = $request;
            }
        }
         return $finalRequestList;
    }
 }
