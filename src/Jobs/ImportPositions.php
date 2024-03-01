<?php

namespace ProcessMaker\Package\PackageZjAdoa\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Log;
use ProcessMaker\Facades\WorkflowManager;
use ProcessMaker\Models\EnvironmentVariable;
use Throwable;
use DB;

class ImportPositions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $positions = [];

    private $createdPositions = 0;

    private $updatedPositions = 0;

    private $url;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 7200;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->loadExistingPositions();
        $this->importAdoaExternalPositions([$this, 'savePositionInformation']);

        WorkflowManager::throwSignalEvent('adoa_positions', [
            'created_positions' => $this->createdPositions,
            'updated_positions' => $this->updatedPositions,
        ]);
    }

    private function loadExistingPositions()
    {
        $getPositions = DB::table('collection_' . EnvironmentVariable::whereName('id_adoa_positions_collection')->first()->value)
        ->select('id', 'data->POSITION as POSITION')
        ->get();
        
        foreach($getPositions as $position) {
            $this->positions[$position->id] = $position->POSITION;
        }
    }

    public function importAdoaExternalPositions($callback)
    {
        $csvPath = $this->download();
        $this->readCsv($csvPath, $callback);
        unlink($csvPath);
    }

    private function savePositionInformation($import)
    {
        $id = array_search($import['POSITION'], $this->positions);
        try {
            if ($id !== false) {
                DB::table('collection_' . EnvironmentVariable::whereName('id_adoa_positions_collection')->first()->value)
                ->where('id', $id)
                ->update([
                    'data' => json_encode([
                        'POSITION' => trim($import['POSITION']),
                        'MANAGER' => trim($import['MANAGER']),
                        'SUPER_POSITION' => trim($import['SUPER_POSITION']),
                        'TITLE' => trim($import['TITLE']),
                        'AT_WILL_STATUS' => trim($import['AT_WILL_STATUS']),
                        'AGENCY' => trim($import['AGENCY']),
                        'AGENCY_NAME' => trim($import['AGENCY_NAME']),
                        'PROCESS_LEVEL' => trim($import['PROCESS_LEVEL']),
                        'PROC_LEVEL_NAME' => trim($import['PROC_LEVEL_NAME']),
                        'DEPARTMENT' => trim($import['DEPARTMENT']),
                        'DEPT_NAME' => trim($import['DEPT_NAME']),
                        'JOB_CODE' => trim($import['JOB_CODE']),
                        'JOB_CODE_DESC' => trim($import['JOB_CODE_DESC']),
                        'ADDRESS' => trim($import['ADDRESS']),
                        'ACTIVE_FLAG' => trim($import['ACTIVE_FLAG']),
                        'UPDATE_DATE' => trim($import['UPDATE_DATE']),
                        'INDIRECT_SUPER_POSITION' => trim($import['INDIRECT_SUPER_POSITION'])
                    ]),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                DB::table('collection_' . EnvironmentVariable::whereName('id_adoa_positions_collection')->first()->value)
                ->insert([
                    'data' => json_encode([
                        'POSITION' => trim($import['POSITION']),
                        'MANAGER' => trim($import['MANAGER']),
                        'SUPER_POSITION' => trim($import['SUPER_POSITION']),
                        'TITLE' => trim($import['TITLE']),
                        'AT_WILL_STATUS' => trim($import['AT_WILL_STATUS']),
                        'AGENCY' => trim($import['AGENCY']),
                        'AGENCY_NAME' => trim($import['AGENCY_NAME']),
                        'PROCESS_LEVEL' => trim($import['PROCESS_LEVEL']),
                        'PROC_LEVEL_NAME' => trim($import['PROC_LEVEL_NAME']),
                        'DEPARTMENT' => trim($import['DEPARTMENT']),
                        'DEPT_NAME' => trim($import['DEPT_NAME']),
                        'JOB_CODE' => trim($import['JOB_CODE']),
                        'JOB_CODE_DESC' => trim($import['JOB_CODE_DESC']),
                        'ADDRESS' => trim($import['ADDRESS']),
                        'ACTIVE_FLAG' => trim($import['ACTIVE_FLAG']),
                        'UPDATE_DATE' => trim($import['UPDATE_DATE']),
                        'INDIRECT_SUPER_POSITION' => trim($import['INDIRECT_SUPER_POSITION'])
                    ]),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => null,
                    'created_by_id' => 1,
                    'updated_by_id' => 1
                ]);
            }
        } catch (Throwable $e) {
            Log::error('Unable to import ADOA position ' . $import['POSITION'], [
                'error' => $e->getMessage(),
                'adoa_position' => $import,
            ]);
        }
    }

    private function readCsv($csvPath, $callback)
    {
        $handle = fopen($csvPath, "r");
        $row = 0;
        $headers = null;
        while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
            if ($row === 0) {
                $headers = $data;
                $row++;
                continue;
            }
            $data = array_combine($headers, $data);
            $response = $callback($data, $row);
            if ($response === false) {
                break;
            }
            $row++;
        }
        fclose($handle);
    }

    private function download()
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'import');
        $this->client()->request('GET', $this->url, ['sink' => $tempPath]);
        return $tempPath;
    }

    public function client()
    {
        $adoaHeaders = [
            "Authorization" => "Bearer 3-5738379ecfaa4e9fb2eda707779732c7"
        ];

        return new Client([
            'headers' => $adoaHeaders
        ]);
    }
}
