<?php
namespace ProcessMaker\Package\Adoa\Http\Controllers;

use ProcessMaker\Http\Controllers\Controller;
use ProcessMaker\Http\Resources\ApiCollection;
use ProcessMaker\Package\Adoa\Models\AdoaUsers;
// use ProcessMaker\Package\Adoa\Models\AdoaUserInformation;

use RBAC;
use \Exception;
use Illuminate\Http\Request;
use URL;
use \DateTime;
use \DB;
use \Auth;

class AdoaUsersController extends Controller
{
    public function index()
    {
        // return view('adoa::index');
    }

    public function getUsersIdFullname(Request $request, int $id)
    {
        try {
            $searchTerm  = request('searchTerm');
            $userLogged  = auth()->user();
            $userCurrent = array(
                'text' => strtoupper($userLogged['firstname'] . ' ' . $userLogged['lastname']),
                'id'   => $userLogged['id']
            );

            $adoaUser = new AdoaUsers();

            $employeeList = array();

            if($userLogged->is_administrator){
                $query  = AdoaUsers::select(DB::raw("CONCAT(firstname,' ',lastname) AS text"), 'id')
                    ->where('status', 'ACTIVE')
                    ->when($searchTerm, function ($query, $searchTerm) {
                        return $query->where(DB::raw('CONCAT_WS(" ", firstname, lastname)'), 'like', '%' . $searchTerm . '%');
                    })
                    ->limit(200)
                    ->orderBy('text', 'ASC')
                    ->get()
                    ->toArray();

                $employeeList = empty($query) ? [] : $query;
                array_unshift($employeeList, $userCurrent);

                return $employeeList;

            } else if ($adoaUser->isAdoaManager($id)){

                $employees = $this->getEmployesByManagerId($id);
                $employeeList = empty($employees) ? [] : $employees;

                $manager   = AdoaUsers::select(DB::raw("CONCAT(firstname,' ',lastname) AS text"), 'id', 'users.*')
                ->where('id', $id)
                ->where('status', 'ACTIVE')
                ->first()
                ->toArray();

                array_unshift($employeeList, $manager);

                return $employeeList;

            } else {
                $query = AdoaUsers::select(DB::raw("CONCAT(firstname,' ',lastname) AS text"), 'id')
                ->where('status', 'ACTIVE')
                ->where('id', $id)
                ->get()
                ->toArray();

                return $query;
            }
        } catch (Exception $exception) {
            throw new Exception('Error on Function getUserIdFullname: ' . $exception->getMessage());
        }
    }

    public function getUser(Int $id)
    {
        $query = AdoaUsers::select('id',  'title', 'firstname', 'lastname', 'email', 'username', 'status', 'meta')
            ->findOrfail($id);
        return $query;
    }

    public function getUserByEin(String $ein)
    {
        $adoaUsers = new AdoaUsers();
        $query = $adoaUsers->getAllUserInformationByEin($ein);
        return $query;
    }

    public function getEmployesByManagerId(int $managerId, Array $response = [])
    {
        try {

            $manager = AdoaUsers::select(DB::raw("CONCAT(firstname,' ',lastname) AS text"), 'id', 'users.meta', 'status')
            ->where('users.id', $managerId)
            ->first()
            ->toArray();

            $userList = AdoaUsers::select(DB::raw("CONCAT(firstname,' ',lastname) AS text"), 'id', 'users.meta', 'status')
            ->whereJsonContains('meta->super_position', $manager['meta']['position'])
            ->whereJsonContains('meta->agency', $manager['meta']['agency'])
            ->get()
            ->toArray();

            $amountManagers = 0;
            foreach ($userList as $userEmployee) {
                if($userEmployee['meta']['manager'] == 'Y') {
                    $amountManagers++;
                }
            }

            $employee = array();

            foreach ($userList as $value) {
                if ($value['status'] == 'ACTIVE') {
                    array_push($response, $value);
                }
                if ($amountManagers > 0) {
                    if ($value['meta']['manager'] == 'Y') {
                        $employees = $this->getEmployesByManagerId($value['id'], $response);

                        foreach ($employees as $employee) {
                            if ($employee['status'] == 'ACTIVE') {
                                array_push($response, $employee);
                            }
                        }
                    }
                }
            }

            $unique   = array_unique(array_column($response, 'id'));
            $response = array_intersect_key($response, $unique);

            return $response;

        } catch (Exception $exception) {
            throw new Exception('Error function getEmployesByManagerId: ' . $exception->getMessage());
        }
    }

    /**
     * Get manager's employees by user ID
     * @param String $userId
     *
     * @return array
     */
    public function getManagerEmployees(String $userId)
    {
        $manager = (new AdoaUsers)->getUserIdById($userId);
        $employees = (new AdoaUsers)->getManagerEmployees($manager['meta']['position']);
        return $employees;
    }
}

