<?php
namespace ProcessMaker\Adoa\classes;

use Illuminate\Support\Facades\Hash;
use GuzzleHttp\Client;
use Exception;
use DB;

class AdoaCallApi
{
    public function api($url, $token)
    {
        try {
            $pmHeaders = $this->getApiHeaders($token);

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $pmHeaders);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $resp = curl_exec($curl);
            curl_close($curl);
            $jsonResponse = json_decode($resp);

            if (is_null($jsonResponse)) {
                $jsonResponse = '';
            }

            return $jsonResponse;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function getApiHeaders($token)
    {
        try {
            return [
                'Authorization: Bearer ' . $token,
                'Accept: application/json',
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
