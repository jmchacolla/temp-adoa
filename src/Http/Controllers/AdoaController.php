<?php
namespace ProcessMaker\Package\Adoa\Http\Controllers;

use ProcessMaker\Http\Controllers\Controller;
use ProcessMaker\Http\Resources\ApiCollection;
use ProcessMaker\Package\Adoa\Models\Sample;
use ProcessMaker\Models\ProcessRequest;
use Spatie\MediaLibrary\Models\Media;
use RBAC;
use Illuminate\Http\Request;
use URL;
use DB;
use Auth;

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
            ->select('process_request_tokens.id AS task_id',
                'process_request_tokens.element_name',
                'process_request_tokens.element_type',
                'process_request_tokens.process_request_id as request_id',
                'process_request_tokens.status as task_status',
                'process_requests.name',
                'process_requests.status as request_status',
                'process_requests.data',
                'process_requests.created_at',
                'process_requests.completed_at',
                'media.id AS file_id',
                'media.custom_properties',
                'users.firstname',
                'users.lastname')
            ->whereIn('process_request_tokens.element_type', ['task', 'end_event'])
            ->whereIn('process_requests.status', ['ACTIVE', 'COMPLETED'])
            ->where('process_requests.user_id', Auth::user()->id)
            ->whereIn('process_request_tokens.id', function($query) {
                $query->selectRaw('max(id)')
                    ->from('process_request_tokens')
                    ->groupBy('process_request_id')
                    ->groupBy('element_name');
            })
            ->orderBy('process_request_tokens.process_request_id', 'desc')
            ->get();

        return view('adoa::adoaListRequests', ['adoaListRequests' => $adoaListRequests]);
    }

    public function getListRequestsAgency($groupId) {
        $member = $this->getGroupAdminAgency(Auth::user()->id, $groupId);
        if (count($member) > 0 && $groupId == config('adoa.admin_agency_group_id')) {
            $agencies = explode(',', Auth::user()->meta->agency);
            $agenciesArray = array();
            if (count($agencies) == 1 && $agencies[0] == 'ALL') {
                $flagAgency = 0;
            } else {
                foreach($agencies as $agency) {
                    $agenciesArray[] = $agency;
                }
                $flagAgency = 1;
            }
            if ($flagAgency == 1) {
                $adoaListRequestsAgency = DB::table('process_requests')
                    ->leftJoin('media', 'process_requests.id', '=', 'media.model_id')
                    ->leftJoin('users', 'process_requests.user_id', '=', 'users.id')
                    ->select('process_requests.id as request_id',
                        'process_requests.name',
                        'process_requests.status as request_status',
                        'process_requests.data',
                        'process_requests.created_at',
                        'process_requests.completed_at',
                        'media.id AS file_id',
                        'media.custom_properties',
                        'users.firstname',
                        'users.lastname')
                    ->whereIn('process_requests.status', ['ACTIVE', 'COMPLETED'])
                    ->whereIn('users.meta->agency', $agenciesArray)
                    ->orderBy('process_requests.id', 'desc')
                    ->get();
            } else {
                $adoaListRequestsAgency = DB::table('process_requests')
                    ->leftJoin('media', 'process_requests.id', '=', 'media.model_id')
                    ->leftJoin('users', 'process_requests.user_id', '=', 'users.id')
                    ->select('process_requests.id as request_id',
                        'process_requests.name',
                        'process_requests.status as request_status',
                        'process_requests.data',
                        'process_requests.created_at',
                        'process_requests.completed_at',
                        'media.id AS file_id',
                        'media.custom_properties',
                        'users.firstname',
                        'users.lastname')
                    ->whereIn('process_requests.status', ['ACTIVE', 'COMPLETED'])
                    ->orderBy('process_requests.id', 'desc')
                    ->get();
            }

            return view('adoa::adoaAdminAgency', ['adoaListRequestsAgency' => $adoaListRequestsAgency, 'agencyName' => Auth::user()->meta->agency]);
        } else {
            return abort(403, 'Unauthorized action.');
        }
    }

    public function getListShared() {
        $adoaListShared = DB::table('process_requests')
            ->join('media', 'process_requests.id', '=', 'media.model_id')
            ->select('process_requests.id as request_id',
                'process_requests.name',
                'process_requests.data',
                'process_requests.created_at',
                'process_requests.completed_at',
                'media.id AS file_id',
                'media.custom_properties')
            ->where('process_requests.status', 'COMPLETED')
            ->where('process_requests.data->EMA_EMPLOYEE_EIN', Auth::user()->username)
            ->orWhere('process_requests.data->CON_EMPLOYEE_EIN', Auth::user()->username)
            ->orWhere('process_requests.data->SUPERVISOR_EIN', Auth::user()->username)
            ->orWhere('process_requests.data->UPLINE_EIN', Auth::user()->username)
            ->orderBy('process_requests.id', 'desc')
            ->get();

        return view('adoa::adoaListShared', ['adoaListShared' => $adoaListShared]);
    }

    public function viewFile(ProcessRequest $request, Media $media)
    {
        $ids = $request->getMedia()->pluck('id');
        if (!$ids->contains($media->id)) {
            abort(403);
        }
        return response()->file($media->getPath());
    }

	/*public function getRequestByProcessAndUser($process_id, $user_id) {
        return DB::table('process_requests')
            ->where('process_id', $process_id)
            ->where('user_id', $user_id)
			->where('status', 'COMPLETED')
			->get();
    }*/

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

    public function getOpenTask($user_id, $request_id){
        return DB::table('process_request_tokens')
            ->select('id')
            ->where('status', 'ACTIVE')
            ->where('process_request_id', $request_id)
            ->where('user_id', $user_id)
            ->get();
    }

    public function getAgencyEnabled($agency)
    {
        try {
            $adoaHeaders = array(
                "Accept: application/json",
                "Authorization: Bearer 3-5738379ecfaa4e9fb2eda707779732c7",
            );
            $url = 'https://hrsieapi.azdoa.gov/api/hrorg/AzPerformAgencyCFG.json?agency=' . $agency;
            //$url = 'https://hrsieapitest.azdoa.gov/api/hrorg/AzPerformAgencyCFG.json?agency=' . $agency;

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
}
