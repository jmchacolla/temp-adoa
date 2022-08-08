<?php
namespace ProcessMaker\Package\Adoa\Http\Controllers;

use ProcessMaker\Http\Controllers\Controller;
use ProcessMaker\Http\Resources\ApiCollection;
use ProcessMaker\Package\Adoa\Models\Sample;
use ProcessMaker\Models\ProcessRequest;
use ProcessMaker\Models\ProcessRequestToken;
use ProcessMaker\Models\EnvironmentVariable;
use ProcessMaker\Events\ActivityAssigned;
use ProcessMaker\Http\Resources\Task as Resource;
use Spatie\MediaLibrary\Models\Media;
use RBAC;
use Illuminate\Http\Request;
use URL;
use DB;
use Auth;
use Illuminate\Support\Facades\Mail;

class AdoaController extends Controller
{
    public function index(){
        return view('adoa::index');
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
            ->where('process_request_tokens.user_id', Auth::user()->id)
            ->orderBy('process_request_tokens.process_request_id', 'desc')
            ->get();

        return view('adoa::adoaListToDo', ['adoaListToDo' => $adoaListToDo]);
    }

    public function getListRequests() {
        $adoaListRequests = DB::table('process_request_tokens')
            ->leftJoin('process_requests', 'process_request_tokens.process_request_id', '=', 'process_requests.id')
            ->leftJoin('media', 'process_request_tokens.process_request_id', '=', 'media.model_id')
            ->leftJoin('users', 'process_request_tokens.user_id', '=', 'users.id')
            ->join('processes', 'process_request_tokens.process_id', '=', 'processes.id')
            ->select('process_request_tokens.id AS task_id',
                'process_request_tokens.element_name',
                'process_request_tokens.element_type',
                'process_request_tokens.process_request_id as request_id',
                'process_request_tokens.status as task_status',
                'process_requests.process_id',
                'process_requests.name',
                'process_requests.status as request_status',
                'process_requests.data',
                'process_requests.created_at',
                'process_requests.completed_at',
                'media.id AS file_id',
                'media.custom_properties',
                'users.firstname',
                'users.lastname',
                'process_request_tokens.user_id as user_id')
            ->whereIn('process_request_tokens.element_type', ['task', 'end_event'])
            ->whereNotIn('processes.process_category_id', [1, 2])
            ->whereIn('process_requests.status', ['ACTIVE', 'COMPLETED'])
            ->where(function ($query) {
                $query->where('process_requests.user_id', Auth::user()->id)
                    ->orWhere('process_requests.data->TA_USER_ID', Auth::user()->id);
            })
            ->whereIn('process_request_tokens.id', function($query) {
                $query->selectRaw('max(id)')
                    ->from('process_request_tokens')
                    ->groupBy('process_request_id')
                    ->groupBy('element_name');
            })
            ->orderBy('process_request_tokens.process_request_id', 'desc')
            ->get();

        return view('adoa::adoaListRequests', ['adoaListRequests' => $adoaListRequests, 'process_id_terminate_rwa_send_email_and_pdf' => EnvironmentVariable::whereName('process_id_terminate_rwa_send_email_and_pdf')->first()->value]);
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

            return view('adoa::adoaAdminAgency', ['groupId' => config('adoa.agency_admin_group_id'), 'agenciesArray' => $agenciesArray, 'levelsArray' => $levelsArray]);
        } else {
            return abort(403, 'Unauthorized action.');
        }
    }

    public function getListShared() {
        $adoaListShared = DB::table('process_requests')
            ->join('media', 'process_requests.id', '=', 'media.model_id')
            ->join('processes', 'process_requests.process_id', '=', 'processes.id')
            ->select('process_requests.id as request_id',
                'process_requests.process_id',
                'process_requests.name',
                'process_requests.data',
                'process_requests.created_at',
                'process_requests.completed_at',
                'media.id AS file_id',
                'media.custom_properties')
            ->where('media.disk', 'public')
            ->where('media.custom_properties->createdBy', 'null')
            ->where('process_requests.status', 'COMPLETED')
            ->whereNotIn('processes.process_category_id', [1, 2])
            ->where(function ($query) {
                $query->where('process_requests.data->EMA_EMPLOYEE_EIN', Auth::user()->username)
                    ->orWhere('process_requests.data->CON_EMPLOYEE_EIN', Auth::user()->username)
                    ->orWhere('process_requests.data->EMA_SUPERVISOR_EIN', Auth::user()->username)
                    ->orWhere('process_requests.data->EMA_UPLINE_EIN', Auth::user()->username)
                    ->orWhere('process_requests.data->CON_SUPERVISOR_EIN', Auth::user()->username)
                    ->orWhere('process_requests.data->CON_UPLINE_EIN', Auth::user()->username)
                    ->orWhere('process_requests.data->SUPERVISOR_EIN', Auth::user()->username)
                    ->orWhere('process_requests.data->UPLINE_EIN', Auth::user()->username);
            })
            ->orderBy('process_requests.id', 'desc')
            ->get();

        return view('adoa::adoaListShared', ['adoaListShared' => $adoaListShared, 'process_id_terminate_rwa_send_email_and_pdf' => EnvironmentVariable::whereName('process_id_terminate_rwa_send_email_and_pdf')->first()->value]);
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

        return view('adoa::adoaViewPdf', ['pdf' => $pdf]);
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
        $member = $this->getGroupAdminAgency(Auth::user()->id, $groupId);
        if (count($member) > 0 && $groupId == config('adoa.agency_admin_group_id')) {
            //Getting Agency Information from meta data
            if (empty($request->input('filterAgency'))) {
                $agencies = explode(',', Auth::user()->meta->agency);
            } else {
                $agencies = $request->input('filterAgency');
            }

            //Getting Agency Information from meta data
            $processes = explode(',', Auth::user()->meta->pm_process_id);
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
                $levels = explode(',', Auth::user()->meta->employee_process_level);
            } else {
                $levels = $request->input('filterLevel');
            }

            //Query to get requests for agency admin
            $adoaListRequestsAgency = DB::table('process_request_tokens')
                ->leftJoin('process_requests', 'process_request_tokens.process_request_id', '=', 'process_requests.id')
                ->leftJoin('media', 'process_request_tokens.process_request_id', '=', 'media.model_id')
                ->leftJoin('users', 'process_requests.user_id', '=', 'users.id')
                ->join('processes', 'process_request_tokens.process_id', '=', 'processes.id')
                ->select('process_request_tokens.id AS task_id',
                    'process_requests.process_id',
                    'process_request_tokens.element_name',
                    'process_request_tokens.element_type',
                    'process_request_tokens.process_request_id as request_id',
                    'process_request_tokens.status as task_status',
                    'process_requests.name',
                    'process_requests.status as request_status',
                    'process_requests.data->EMA_EMPLOYEE_FIRST_NAME as ema_employee_first_name',
                    'process_requests.data->EMA_EMPLOYEE_LAST_NAME as ema_employee_last_name',
                    'process_requests.data->EMA_EMPLOYEE_EIN as ema_employee_ein',
                    'process_requests.data->CON_EMPLOYEE_FIRST_NAME as con_employee_first_name',
                    'process_requests.data->CON_EMPLOYEE_LAST_NAME as con_employee_last_name',
                    'process_requests.data->CON_EMPLOYEE_EIN as con_employee_ein',
                    'process_requests.data->FA_OWNER as FA_OWNER',
                    'process_requests.created_at',
                    'process_requests.completed_at',
                    'media.id AS file_id',
                    'media.custom_properties',
                    'process_request_tokens.user_id as user_id_task')
                ->whereIn('process_request_tokens.element_type', ['task', 'end_event'])
                ->whereNotIn('processes.process_category_id', [1, 2]);

            $positionsArray = array();
            if ($agencies[0] != 'ALL' || $levels[0] != 'ALL') {
                $positions = $this->getAdoaPositionsByFilter($agencies, $levels);

                foreach ($positions->rows as $position) {
                    $positionsArray[] = $position[0];
                }
            }

            if (count($positionsArray) > 0) {
                $adoaListRequestsAgency = $adoaListRequestsAgency
                ->whereIn('process_requests.data->EMA_EMPLOYEE_POSITION_NUMBER', $positionsArray)
                ->orWhereIn('process_requests.data->CON_EMPLOYEE_POSITION_NUMBER', $positionsArray)
                ->orWhereIn('process_requests.data->EMPLOYEE_POSITION_NUMBER', $positionsArray)
                ->orWhereIn('process_requests.data->ADOA_RWA_POSITION', $positionsArray)
                ->orWhereIn('process_requests.data->terminate_data->ADOA_TA_POSITION', $positionsArray);
            }

            if ($flagProcess == 1) {
                $adoaListRequestsAgency = $adoaListRequestsAgency
                    ->whereIn('process_requests.process_id', $processesArray);
            }

            if (!empty($request->input('filterInitDate'))) {
                $adoaListRequestsAgency = $adoaListRequestsAgency
                    ->where('process_requests.created_at', '>=', $request->input('filterInitDate'));
            }

            if (!empty($request->input('filterEndDate'))) {
                $adoaListRequestsAgency = $adoaListRequestsAgency
                    ->where('process_requests.created_at', '<=', $request->input('filterEndDate'));
            }

            if (empty($request->input('filterStatus'))) {
                $adoaListRequestsAgency = $adoaListRequestsAgency
                    ->whereIn('process_requests.status', ['ACTIVE', 'COMPLETED']);
            } else {
                $adoaListRequestsAgency = $adoaListRequestsAgency
                    ->whereIn('process_requests.status', $request->input('filterStatus'));
            }

            $adoaListRequestsAgency = $adoaListRequestsAgency
                ->whereIn('process_request_tokens.id', function($query) {
                    $query->selectRaw('max(id) as id')
                        ->from('process_request_tokens')
                        ->groupBy('id');
                })
                ->orderBy('process_requests.id', 'desc')
                ->get()
                ->unique('task_id');

            $process_id_terminate_rwa_send_email_and_pdf = EnvironmentVariable::whereName('process_id_terminate_rwa_send_email_and_pdf')->first()->value;
            $count = count($adoaListRequestsAgency);

            $dataTableFormat = array();
            $dataTable = array();

            if ($count > 0) {
                foreach ($adoaListRequestsAgency as $request) {
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

                        if (($request->element_type == 'task' && $request->task_status == 'ACTIVE') || ($request->element_type == 'end_event' && $request->task_status == 'CLOSED' && $request->element_name == 'Completed' && $request->request_status == 'COMPLETED')) {
                            if (!empty($request->file_id) || !is_null($request->file_id)) {
                                $employeeName = '';
                                $employeeEin = '';
                                if (is_null($newCustomProperties->createdBy)) {
                                    if ($request->process_id == $process_id_terminate_rwa_send_email_and_pdf) {
                                        $dataName = $newCustomProperties->data_name;
                                        $nameFile = explode('_', $dataName);
                                        if (array_key_exists(3, $nameFile) && array_key_exists(4, $nameFile)) {
                                            $employeeName = $nameFile[3] . ' ' . $nameFile[4];
                                        }
                                    } else {
                                        if (!empty($request->ema_employee_first_name)) {
                                            $employeeName = $request->ema_employee_first_name . ' ' . $request->ema_employee_last_name;
                                        } elseif (!empty($request->con_employee_first_name)) {
                                            $employeeName = $request->con_employee_first_name . ' ' . $request->con_employee_last_name;
                                        }
                                    }

                                    if ($request->process_id == $process_id_terminate_rwa_send_email_and_pdf) {
                                        if (array_key_exists(5, $nameFile)) {
                                            $employeeEin = $nameFile[5];
                                        }
                                    } else {
                                        if (!empty($request->ema_employee_ein)) {
                                            $employeeEin = $request->ema_employee_ein;
                                        } elseif (!empty($request->con_employee_ein)) {
                                            $employeeEin = $request->con_employee_ein;
                                        }
                                    }

                                    $options = '<a href="#"><i class="fas fa-eye" style="color: #71A2D4;" title="View PDF" onclick="viewPdf(' . $request->request_id . ', ' . $request->file_id . ');"></i></a>&nbsp;<a href="#"><i class="fas fa-print" style="color: #71A2D4;" title="Print PDF" onclick="printPdf(' . $request->request_id . ', ' . $request->file_id . ');"></i></a>&nbsp;<a href="/request/' . $request->request_id . '/files/' . $request->file_id . '"><i class="fas fa-download" style="color: #71A2D4;" title="Download PDF"></i></a>&nbsp;';

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
                                }
                            } else {
                                if ((empty($request->ema_employee_ein) && empty($request->con_employee_ein)) && $request->request_status == 'COMPLETED') {
                                } else {
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
        $unasiggnedRequestsPart1 = DB::select(DB::raw("SELECT id FROM process_requests AS table2 WHERE status = 'ACTIVE' AND id IN (SELECT table1.process_request_id FROM (SELECT process_request_id, COUNT(CASE WHEN status = 'ACTIVE' THEN 'ACTIVES' ELSE NULL END) AS 'ACTIVES', COUNT(CASE WHEN status != 'ACTIVE' THEN 'INACTIVES' ELSE NULL END) AS 'INACTIVES' FROM process_request_tokens WHERE process_request_tokens.process_id in (" . EnvironmentVariable::whereName('process_ids_unassigned')->first()->value . ") GROUP BY process_request_id) AS table1 WHERE ACTIVES = 0);"));

        $unasiggnedRequestsPart2 = DB::table('process_request_tokens')
            ->join('process_requests', 'process_request_tokens.process_request_id', '=', 'process_requests.id')
            ->select('process_request_tokens.process_request_id')
            ->where('process_requests.status', 'ACTIVE')
            ->where('process_request_tokens.element_type', 'gateway')
            ->where('process_request_tokens.status', 'INCOMING')
            ->groupBy('process_request_tokens.process_request_id')
            ->get();

        return array_merge($unasiggnedRequestsPart1, $unasiggnedRequestsPart2);
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
}
