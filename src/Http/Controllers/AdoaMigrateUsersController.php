<?php

namespace ProcessMaker\Package\PackageZjAdoa\Http\Controllers;
use ProcessMaker\Http\Controllers\Controller;
use ProcessMaker\Http\Resources\ApiCollection;
use ProcessMaker\Package\PackageZjAdoa\Jobs\MigrateUsers;
use ProcessMaker\PackageZjAdoa\classes\MigrateAdministrators;
use RBAC;
use Illuminate\Http\Request;
use URL;

class AdoaMigrateUsersController extends Controller
{
    public function migratedUsersProd()
    {
        MigrateUsers::dispatch('https://hrsieapi.azdoa.gov/api/hrorg/PMEmployInfo.csv');
        return response(['status' => true], 201);
    }

    public function migratedUsersDev()
    {
        MigrateUsers::dispatch('https://hrsieapitest.azdoa.gov/api/hrorg/PMEmployInfo.csv');
        return response(['status' => true], 201);
    }

    public function migrateAdministrators()
    {
        $groupId = config('adoa.agency_admin_group_id');
        require_once dirname(__DIR__, 3) . '/classes/MigrateAdministrators.php';
        ini_set('memory_limit', '-1');
        ini_set('set_time_limit', 0);
        ini_set('max_execution_time', 0);
        $tiempo = microtime(true);
        $migrateUsers = new MigrateAdministrators();
        return $migrateUsers->migrateAdminInformation($groupId);
    }
}
