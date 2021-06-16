<?php
namespace ProcessMaker\Adoa\classes;

use ProcessMaker\Package\Adoa\Models\AdoaUsers;
use Illuminate\Support\Facades\Hash;
use GuzzleHttp\Client;
use Exception;
use DB;

class MigrateUsersDev
{
    public function migrateUserInformation($groupIdEmployee, $groupIdManager)
    {
        try {
            $localUsersList = array();
            $adoaUser = new AdoaUsers();
            $adoaUser->inactiveAllUsers();
            $usersList = $adoaUser->getAllUsersByEin();

            if (!empty($usersList)) {
                foreach ($usersList as $userId) {
                    $localUsersList[$userId['username']] = $userId['id'];
                }
            }

            $result = $this->saveUserInformation($localUsersList, 0, 0, $groupIdEmployee, $groupIdManager);
            return $result;
        } catch (Exception $error) {
            return $response['error'] = 'There are errors in the Function: migrateUserInformation ' . $error->getMessage();
        }
    }

    public function saveUserInformation($userList, $countCreatedUsers, $countUpdatedUsers, $groupIdEmployee, $groupIdManager)
    {
        try {
            $adoaUsers = new AdoaUsers();

            $userInformation = $this->getAdoaExternalUsers();

            if (!empty($userInformation->rows)) {
                foreach ($userInformation->rows as $externalUserInfo) {
                    if (empty($userList[$externalUserInfo[0]])) {

                        $email = $externalUserInfo[0] . '@hris.az.gov';
                        $email = trim($email);

                        $metaEmail = '';
                        if (!empty($externalUserInfo[5])) {
                            $metaEmail = trim($externalUserInfo[5]);
                        }

                        $metaInformationData = array(
                            'ein' => $externalUserInfo[0],
                            'email' => $metaEmail,
                            'position' => $externalUserInfo[6],
                            'manager' => $externalUserInfo[7],
                            'super_position' => $externalUserInfo[8],
                            'title' => $externalUserInfo[9],
                            'agency' => $externalUserInfo[10],
                            'agency_name' => $externalUserInfo[11],
                            'process_level' => $externalUserInfo[12],
                            'department' => $externalUserInfo[13],
                            'term_date' => $externalUserInfo[14],
                            'flsa_status' => $externalUserInfo[15]
                        );
                        $metaInformationData = json_encode($metaInformationData);

                        $password = Hash::make('p^@)YUvVB"j4.J*F');
                        $newUserData = array(
                            'email' => $email,
                            'firstname'=> $externalUserInfo[1],
                            'lastname'=> $externalUserInfo[2],
                            'username'=> $externalUserInfo[0],
                            'password'=> $password,
                            'address'=> $externalUserInfo[3],
                            'phone'=> trim($externalUserInfo[4]),
                            'is_administrator'=> false,
                            'status'=> 'ACTIVE',
                            'meta' => $metaInformationData,
                            'created_at'=> date('Y-m-d H:i:s'),
                        );

                        $userUid = $adoaUsers->insertUser($newUserData);

                        if (!empty($userUid)) {
                            if ($externalUserInfo[7] == 'Y') {
                                $groupMemberManager = array(
                                    'group_id' => $groupIdManager,
                                    'member_type' => 'ProcessMaker\Models\User',
                                    'member_id' => $userUid,
                                    'created_at' => date('Y-m-d H:i:s')
                                );

                                DB::table('group_members')
                                ->insert($groupMemberManager);
                            }

                            $groupMemberEmployee = array(
                                'group_id' => $groupIdEmployee,
                                'member_type' => 'ProcessMaker\Models\User',
                                'member_id' => $userUid,
                                'created_at' => date('Y-m-d H:i:s')
                            );

                            DB::table('group_members')
                            ->insert($groupMemberEmployee);

                            $countCreatedUsers = $countCreatedUsers + 1;
                        }
                    } elseif (!empty($userList[$externalUserInfo[0]])) {

                        $email = $externalUserInfo[0] . '@hris.az.gov';
                        $email = trim($email);

                        $metaEmail = '';
                        if (!empty($externalUserInfo[5])) {
                            $metaEmail = trim($externalUserInfo[5]);
                        }

                        $metaInformationData = array(
                            'ein' => $externalUserInfo[0],
                            'email' => $metaEmail,
                            'position' => $externalUserInfo[6],
                            'manager' => $externalUserInfo[7],
                            'super_position' => $externalUserInfo[8],
                            'title' => $externalUserInfo[9],
                            'agency' => $externalUserInfo[10],
                            'agency_name' => $externalUserInfo[11],
                            'process_level' => $externalUserInfo[12],
                            'department' => $externalUserInfo[13],
                            'term_date' => $externalUserInfo[14],
                            'flsa_status' => $externalUserInfo[15]
                        );
                        $metaInformationData = json_encode($metaInformationData);

                        $updateUserData = array (
                            'id' => $userList[$externalUserInfo[0]],
                            'email' => $email,
                            'firstname'=> $externalUserInfo[1],
                            'lastname'=> $externalUserInfo[2],
                            'address'=> $externalUserInfo[3],
                            'phone'=> trim($externalUserInfo[4]),
                            'is_administrator'=> false,
                            'status'=> 'ACTIVE',
                            'meta' => $metaInformationData,
                            'updated_at'=> date('Y-m-d H:i:s'),
                        );
                        $response = $adoaUsers->updateUser($updateUserData);

                        if ($externalUserInfo[7] == 'Y') {
                            $groupManager = DB::table('group_members')
                                ->where('member_id', $userList[$externalUserInfo[0]])
                                ->where('group_id', $groupIdManager)
                                ->get();

                            if(count($groupManager) == 0) {
                                $groupMemberManager = array(
                                    'group_id' => $groupIdManager,
                                    'member_type' => 'ProcessMaker\Models\User',
                                    'member_id' => $userList[$externalUserInfo[0]],
                                    'created_at' => date('Y-m-d H:i:s')
                                );

                                DB::table('group_members')
                                ->insert($groupMemberManager);
                            }
                        } else {
                            $groupManager = DB::table('group_members')
                                ->where('member_id', $userList[$externalUserInfo[0]])
                                ->where('group_id', $groupIdManager)
                                ->get();

                            if(count($groupManager) > 0) {
                                DB::table('group_members')
                                ->where('member_id', $userList[$externalUserInfo[0]])
                                ->delete();
                            }
                        }

                        $groupEmployee = DB::table('group_members')
                            ->where('member_id', $userList[$externalUserInfo[0]])
                            ->where('group_id', $groupIdEmployee)
                            ->get();

                        if(count($groupEmployee) == 0) {
                            $groupMemberEmployee = array(
                                'group_id' => $groupIdEmployee,
                                'member_type' => 'ProcessMaker\Models\User',
                                'member_id' => $userList[$externalUserInfo[0]],
                                'created_at' => date('Y-m-d H:i:s')
                            );

                            DB::table('group_members')
                            ->insert($groupMemberEmployee);
                        }

                        if ($response == 1) {
                            $countUpdatedUsers = $countUpdatedUsers + 1;
                        }
                    }
                }
            }
            return $result = array(
                'created_users' => $countCreatedUsers,
                'updated_users' => $countUpdatedUsers,
            );
        } catch (Exception $error) {
            return $response['error'] = 'There are errors in the Function: saveUserInformation ' . $error->getMessage();
        }
    }

    public function getAdoaExternalUsers()
    {
        try {
            $adoaHeaders = array(
                "Accept: application/json",
                "Authorization: Bearer 3-5738379ecfaa4e9fb2eda707779732c7",
            );
            $url = 'https://hrsieapitest.azdoa.gov/api/hrorg/PMEmployInfo.json';

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $adoaHeaders);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $resp = curl_exec($curl);
            curl_close($curl);

            $userInformationList = json_decode($resp);
            return $userInformationList;
        } catch (Exception $error) {
            return $response['error'] = 'There are errors in the Function: getAdoaExternalUsers ' . $error->getMessage();
        }
    }
}
