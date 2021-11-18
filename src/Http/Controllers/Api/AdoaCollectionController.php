<?php
namespace ProcessMaker\Package\Adoa\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ProcessMaker\Models\EnvironmentVariable;
use ProcessMaker\Http\Controllers\Controller;
use ProcessMaker\Plugins\Collections\Models\Record;

class AdoaCollectionController extends Controller
{
    public function getRwaReportByUser(Request $request)
    {
        $collectionId = EnvironmentVariable::whereName('agreements_collection_id')->first()->value;
        $requestData = $request->all();

        $records = DB::table('collection_' .  $collectionId)
        ->where('data->USER_ID', $requestData['user_id'])
        ->where('data->STATUS', 'COMPLETED')
        ->select(
            'data->REQUEST_ID as REQUEST_ID',
            'data->ADOA_RWA_EMPLOYEE_NAME as ADOA_RWA_EMPLOYEE_NAME',
            'data->ADOA_RWA_EIN as ADOA_RWA_EIN',
            'data->ADOA_RWA_TYPE_REQUEST as ADOA_RWA_TYPE_REQUEST',
            'data->ADOA_RWA_REMOTE_AGREEMENT_START_DATE as ADOA_RWA_REMOTE_AGREEMENT_START_DATE',
            'data->ADOA_RWA_REMOTE_AGREEMENT_END_DATE as ADOA_RWA_REMOTE_AGREEMENT_END_DATE',
            'data->FILE_ID as FILE_ID',
        )
        ->get();
        return $records;
    }

    public function getAzpReportByUser(Request $request)
    {
        $collectionId = EnvironmentVariable::whereName('azp_lookup_collection_id')->first()->value;
        $requestData = $request->all();

        $records = DB::table('collection_' .  $collectionId)
        ->where('data->EMPLOYEE_ID', $requestData['employee_id'])
        ->where('data->STATUS', 'COMPLETED')
        ->where('data->DATE', '>=', $requestData['initDate'])
        ->where('data->DATE', '<=', $requestData['endDate'])
        ->whereIn('data->AZP_PROCESS', $requestData['documentSelected'])

        ->select(
            'data->REQUEST_ID as REQUEST_ID',
            'data->EMPLOYEE_ID as EMPLOYEE_ID',
            'data->EMPLOYEE_EIN as EMPLOYEE_EIN',
            'data->EMPLOYEE_FIRST_NAME as EMPLOYEE_FIRST_NAME',
            'data->EMPLOYEE_LAST_NAME as EMPLOYEE_LAST_NAME',
            'data->EVALUATOR_FIRST_NAME as EVALUATOR_FIRST_NAME',
            'data->EVALUATOR_LAST_NAME as EVALUATOR_LAST_NAME',
            'data->AZP_PROCESS as AZP_PROCESS',
            'data->CONTENT as CONTENT',
            'data->DATE as DATE',
            'data->FILE_ID as FILE_ID'
        )
        ->get();
        return $records;

    }
}