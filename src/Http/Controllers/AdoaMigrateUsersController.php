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
    public function migratedUsersProd($groupIdEmployee, $groupIdManager)
    {
        require_once dirname(__DIR__, 3) . '/classes/MigrateUsersProd.php';
        ini_set('memory_limit', '-1');
        ini_set('set_time_limit', 0);
        ini_set('max_execution_time', 0);
        $tiempo = microtime(true);
        $migrateUsers = new MigrateUsersProd();
        $result = $migrateUsers->migrateUserInformation($groupIdEmployee, $groupIdManager);
        echo round($tiempo, 2);
        dd('finalizado', $result);
    }

    public function migratedUsersDev($groupIdEmployee, $groupIdManager)
    {
        require_once dirname(__DIR__, 3) . '/classes/MigrateUsersDev.php';
        ini_set('memory_limit', '-1');
        ini_set('set_time_limit', 0);
        ini_set('max_execution_time', 0);
        $tiempo = microtime(true);
        $migrateUsers = new MigrateUsersDev();
        $result = $migrateUsers->migrateUserInformation($groupIdEmployee, $groupIdManager);
        echo round($tiempo, 2);
        dd('finalizado', $result);
    }

    public function migrateAdministrators($groupId)
    {
        require_once dirname(__DIR__, 3) . '/classes/MigrateAdministrators.php';
        ini_set('memory_limit', '-1');
        ini_set('set_time_limit', 0);
        ini_set('max_execution_time', 0);
        $tiempo = microtime(true);
        $migrateUsers = new MigrateAdministrators();
        $result = $migrateUsers->migrateAdminInformation($groupId);
        echo round($tiempo, 2);
        dd('finalizado', $result);
    }
}
