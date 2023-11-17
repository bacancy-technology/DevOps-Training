<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\{User,Log};
use Illuminate\Http\{Request,Response};
use Auth,Validator,Hashids;

class ApiCommonController extends Controller
{
    public static function apiCommonResponseData() {
        $api_response_data = [];
        $api_response_data['response_code'] = 0;
        $api_response_data['message'] = 'Something went wrong please try again later.';
        $api_response_data['data'] = [];
        return $api_response_data;
    }

    public static function createUserLog($user_id, $module, $msg, $remote_addr = '') {
        if(isset($_SERVER['REMOTE_ADDR'])){
            $remote_addr = $_SERVER['REMOTE_ADDR'];
        }
        $data_insert = array(
            'user_id' => $user_id,
            'module' => $module,
            'message' => $msg,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'ip_address' => ($remote_addr) ? $remote_addr : '1'
        );
        Log::insert($data_insert);
        return true;
    }

    public static function sendSMS($para) {
        $return_data['response_code'] = 0;
        $return_data['message'] = "Something went wrong please try again later";

        try {
            $url = config('constants.EXTERNAL_API_URL').'/send-sms';
            $curlHandle = curl_init($url);
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $para);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
            $curlResponse = curl_exec($curlHandle);
            $curlData = json_decode($curlResponse);
            curl_close($curlHandle);

            if(!empty($curlData->response_code)){
                $return_data['response_code'] = 1;
                $return_data['message'] = 'Sent';
            }
        } catch (\Exception $e) {
            $return_data['message'] = $e->getMessage();
        }
        return $return_data;
    }

    public static function sendEmail($para) {
        $return_data['response_code'] = 0;
        $return_data['message'] = "Something went wrong please try again later";

        try {
            $url = config('constants.EXTERNAL_API_URL').'/send-email';
            $curlHandle = curl_init($url);
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $para);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
            $curlResponse = curl_exec($curlHandle);
            $curlData = json_decode($curlResponse);
            curl_close($curlHandle);
            
            if(!empty($curlData->response_code)){
                $return_data['response_code'] = 1;
                $return_data['message'] = 'Sent';
            }
        } catch (\Exception $e) {
            $return_data['message'] = $e->getMessage();
        }
        return $return_data;
    }

    //get date difference
    public static function getDateDifference($datetime1, $datetime2) {
        $to = \Carbon\Carbon::parse($datetime1);
        $from = \Carbon\Carbon::parse($datetime2);
        return [
            "years" => $to->diffInYears($from),
            "months" => $to->diffInMonths($from),
            "weeks" => $to->diffInWeeks($from),
            "days" => $to->diffInDays($from),
            "hours" => $to->diffInHours($from),
            "minutes" => $to->diffInMinutes($from),
            "seconds" => $to->diffInSeconds($from)
        ];
    }

    //get date difference
    public static function getDateDuration($datetime1, $datetime2) {
        $date1 = strtotime($datetime1);
        $date2 = strtotime($datetime2);
        
        $diff = abs($date2 - $date1);
        $years = floor($diff / (365*60*60*24));
        $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
        $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
        $hours = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24) / (60*60));
        $minutes = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60)/ 60);
        $seconds = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60 - $minutes*60));
        return ["years" => $years,"months" => $months,"days" => $days,"hours" => $hours,"minutes" => $minutes,"seconds" => $seconds];
    }

    //get random number
    public static function generateAphaNumericString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    //generate redirect url
    public static function generateRedirection($para) {
        $return = (!empty($para['base_url'])) ? $para['base_url'] : '';
        if(!empty($para['base_url']) && !empty($para['user_id'])){
            $hash_user_id = Hashids::encode($para['user_id']);
            $return = $para['base_url'].'?web_authorization='.$hash_user_id;
        }
        return $return;
    }
}
