<?php

namespace ProcessMaker\Package\PackageZjAdoa\Models;

use Illuminate\Database\Eloquent\Model;
use Exception;
use \DB;

class AdoaUsers extends Model
{
    protected $table = 'users';

    protected $casts = [
        'meta' => 'array',
    ];

    protected $fillable = [
        'id',
        'username',
        'email',
        'firstname',
        'lastname',
        'status',
        'cell'
    ];

    public function getFullName()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function getAllUsers()
    {
        try {
            $response = static::all()
                ->toArray();
            return $response;
        } catch (Exception $error) {
            return $response['error'] = 'There are errors in the Function: getAllUsers ' . $error->getMessage();
        }
    }

    public function getAllUsersByEin()
    {
        try {
            $users = static::select('users.id', 'users.username')
                ->get()
                ->toArray();
            return $users;
        } catch (Exception $error) {
            return $response['error'] = 'There are errors in the Function: getAllUsersByEIN ' . $error->getMessage();
        }
    }

    public function getUserIdByEin($ein)
    {
        try {
            $response = static::select('users.id', 'users.*')
                ->where('username', $ein)
                ->first()
                ->toArray();
            return $response;
        } catch(Exception $error) {
            return $response['error'] = 'There are errors in the Function: deleteAllUsers ' . $error->getMessage();
        }
    }

    public function getUserIdById($id)
    {
        try {
            $response = static::select('users.id', 'users.*')
                ->where('id', $id)
                ->first()
                ->toArray();
            return $response;
        } catch(Exception $error) {
            return $response['error'] = 'There are errors in the Function: deleteAllUsers ' . $error->getMessage();
        }
    }

    public function getUserInformationByEin($ein)
    {
        try {
            return static::select('users.id', 'users.*')
                ->where('users.meta->ein', $ein)
                ->first()
                ->toArray();
        } catch (Exception $exception) {
            return $response['error'] = 'There are errors in the Function: getUserInformationByEin ' . $exception->getMessage();
        }
    }

    public function getAllUserInformationByEin(String $ein)
    {
        try {
            $response = static::select('users.id as id',  'adoa_user_information.ein as title', 'users.firstname as firstname', 'users.lastname as lastname',
                'users.email as email', 'users.username as username', 'users.status as status')
                ->join('adoa_user_information', 'users.id', '=', 'adoa_user_information.user_id')
                ->where('adoa_user_information.ein', $ein)
                ->first()
                ->toArray();
            return $response;
        } catch(Exception $error) {
            return $response['error'] = 'There are errors in the Function: getAllUserInformationByEin ' . $error->getMessage();
        }
    }

    public function deleteAllUsers()
    {
        try {
            $response = static::where('id', '!=', 1)->delete();
            return $response;
        } catch(Exception $error) {
            return $response['error'] = 'There are errors in the Function: deleteAllUsers ' . $error->getMessage();
        }
    }

    public function adoaUserInformation()
    {
        return $this->hasOne('ProcessMaker\Package\PackageZjAdoa\Models\AdoaUserInformation', 'user_id', 'id');
    }

    public function insertUser($userData) {
        try {
            $response = static::insertGetId($userData);
            return $response;
        } catch (Exception $error) {
            return $response['error'] = 'There are errors in the Function: insertUser ' . $error->getMessage();
        }
    }

    public function updateUser($userData) {
        try {

            $response = static::where('id', '=', $userData['id'])->update($userData);
            return $response;
        } catch (Exception $error) {
            return $response['error'] = 'There are errors in the Function: updateUser ' . $error->getMessage();
        }
    }

    public function isAdoaManager($userId)
    {
        try {
            $userData      = static::find($userId);
            if (!empty($userData)) {
                if ($userData['meta']['manager'] == 'Y') {
                    return true;
                } else {
                    return false;
                }
            }
        } catch (Exception $exception) {
            return false;
        }
    }

    public function inactiveAllUsers()
    {
        try {
            return  static::where('status', '=', 'ACTIVE')
                ->where('username', '!=', 'admin')
                ->where('username', '!=', '_pm4_anon_user')
                ->update(array('status' => 'INACTIVE'));
        } catch (Exception $exception) {
            return $response['error'] = 'There are errors in the Function: inactiveAllUsers ' . $exception->getMessage();
        }
    }

    public function getManagerById($id)
    {
        try {
            return static::select(DB::raw("CONCAT(firstname,' ',lastname) AS text"), 'id', 'users.meta', 'status')
                ->where('id', $id)
                ->where('meta->manager', 'Y')->first()
                ->toArray();
        } catch (Exception $exception) {
            return $response['error'] = 'There are errors in the Function: getManagerById ' . $exception->getMessage();
        }
    }

    public function getAllEmployeesBySuperPositionAndAgency($superPosition, $agency)
    {
        try {
            return static::select(DB::raw("CONCAT(firstname,' ',lastname) AS text"), 'id', 'users.meta', 'status')
                ->where('meta->agency', $agency)
                ->where('meta->super_position', $superPosition)
                ->get()
                ->toArray();
        } catch (Exception $exception) {
            return $response['error'] = 'There are errors in the Function: getAllEmployeesBySuperPositionAndAgengy ' . $exception->getMessage();
        }
    }

    /**
     * Get Manager's Employees by manager Position
     * @param String $userId
     *
     * @return array
     */
    public function getManagerEmployees(String $position)
    {
        return static::select('id',  'title',
            'firstname', 'lastname',
            'email', 'username',
            'status', 'meta->ein as ein',
            'meta->position as position',
            'meta->super_position as super_position',
            'meta->agency as agency',
            'meta->agency_name as agency_name',
            'meta->manager as manager'
            )
            ->where(function ($query) use ($position){
                $query->where('meta->super_position', $position)
                    ->orWhere('meta->indirect_super_position', $position);
            })
            ->where('status', 'ACTIVE')
            ->orderBy('firstname', 'asc')
            ->get()
            ->toArray();
    }

   /**
    * @param String $position
    *
    * @return Array
    */
    static function getEmployeesByPosition (String $position) {
        $employeeList = static::select('id',  'title',
            'firstname', 'lastname',
            'email', 'username',
            'status', 'meta->ein as ein',
            'meta->position as position',
            'meta->super_position as super_position',
            'meta->agency as agency',
            'meta->agency_name as agency_name',
            'meta->manager as manager'
            )
            ->where(function ($query) use ($position){
                $query->where('meta->super_position', $position)
                    ->orWhere('meta->indirect_super_position', $position);
            })
            ->where('status', 'ACTIVE')
            ->orderBy('firstname', 'asc')
            ->get()
            ->toArray();

        $positionsList = DB::table('collection_8')
            ->where('data->SUPER_POSITION', $position)
            ->get()
            ->toArray();

        $transformData = array_map(function ($element) {
                return json_decode($element->data, true);
            }, $positionsList);

        $newEmployeeList = [];

        foreach ($transformData as $key => $value) {
            $userFound = array_search($value['POSITION'], array_column($employeeList, 'position'));
            if ($userFound !== false) {
                $newEmployeeList[] = $employeeList[$userFound];
            } else {
                $newEmployeeList[] = [
                    'id' => 'NO_DEFINED',
                    'title' => $value['TITLE'],
                    'firstname' => 'VACANT',
                    'lastname' => '(' . $value['TITLE'] . ')',
                    'email' => '',
                    'username' => '',
                    'status' => '',
                    'ein' => '',
                    'position' => $value['POSITION'],
                    'super_position' => $value['SUPER_POSITION'],
                    'agency' => $value['AGENCY'],
                    'agency_name' => $value['AGENCY_NAME'],
                    'manager' => $value['MANAGER']
                ];
            }
        }
        return $newEmployeeList;
    }
}
