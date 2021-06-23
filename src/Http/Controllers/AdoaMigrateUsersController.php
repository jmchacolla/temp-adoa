<?php

namespace ProcessMaker\Package\Adoa\Http\Controllers;
use ProcessMaker\Http\Controllers\Controller;
use ProcessMaker\Http\Resources\ApiCollection;
use ProcessMaker\Adoa\classes\MigrateUsersProd;
use ProcessMaker\Adoa\classes\MigrateUsersDev;
use ProcessMaker\Adoa\classes\MigrateAdministrators;
use RBAC;
use Illuminate\Http\Request;
use URL;
use \DateTime;
use \DB;

class AdoaMigrateUsersController extends Controller
{
    public function migratedUsersProd()
    {
        require_once dirname(__DIR__, 3) . '/classes/MigrateUsersProd.php';
        $migrateUsers = new MigrateUsersProd();
        $result = $migrateUsers->migrateUserInformation();
        return ['result' => $result];
    }

    public function migratedUsersDev()
    {
        require_once dirname(__DIR__, 3) . '/classes/MigrateUsersDev.php';
        $migrateUsers = new MigrateUsersDev();
        $result = $migrateUsers->migrateUserInformation();
        return ['result' => $result];
    }

    public function migrateAdministrators()
    {
        $groupId = config('adoa.admin_group_id');
        require_once dirname(__DIR__, 3) . '/classes/MigrateAdministrators.php';
        ini_set('memory_limit', '-1');
        ini_set('set_time_limit', 0);
        ini_set('max_execution_time', 0);
        $tiempo = microtime(true);
        $migrateUsers = new MigrateAdministrators();
        $result = $migrateUsers->migrateAdminInformation($groupId);
        return $result;
    }
}
