<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\ApiCommonController;
use Illuminate\Support\Facades\Hash;
use App\Models\{User,Template,SiteSetting,UserDevice};
use Illuminate\Http\{Request,Response};
use Auth,Validator,Log,DB,View,Hashids;

class AuthenticateController extends Controller
{
    protected $return_data;
    protected $server_error_code;
    public function __construct(){
        $this->return_data = ApiCommonController::apiCommonResponseData();
        $this->server_error_code = 200;
    }

    // public function initialApp(Request $request): Response {
    //     /******* validation *********/
    //     $validate = Validator::make($request->all(),[
    //         'device_id' => 'required'
    //     ],[
    //         'device_id.required' => 'Device id not given'
    //     ]);

    //     if($validate->fails()){
    //         $message = implode(", ", $validate->messages()->all());
    //         $this->return_data['message'] = $message;
    //         return Response($this->return_data, $this->server_error_code);
    //     }
    //     /******* validation *********/

    //     //variables
    //     print_r(Hashids::decode($request->partner_id));die;
    //     $partner_id = !empty($request->partner_id) ? Hashids::decode($request->partner_id)[0] : 0;
    //     $user_id = !empty($request->user_id) ? Hashids::decode($request->user_id)[0] : NULL;
    //     $redirect_url = config('constants.EXTERNAL_URL').'/'.$lang_code;
    //     $current_date_time = date("Y-m-d H:i:s");

    //     //check for update
    //     $check_for_update['user_id'] = $user_id;
    //     $check_for_update['device_id'] = $device_id;

    //     //record
    //     $record['user_id'] = $user_id;
    //     $record['partner_id'] = $partner_id;
    //     $record['device_type'] = $request->device_type;
    //     $record['device_version'] = $request->device_version;
    //     $record['device_id'] = $device_id;
    //     $record['app_activity_with_login'] = $current_date_time;
    //     $record['app_activity_without_login'] = $current_date_time;
    //     $record['data'] = $request->data;
    //     $data = UserDevice::updateOrCreate($check_for_update, $record);

    //     if(!empty($data)){
    //         $para = [];
    //         $para['base_url'] = $redirect_url;
    //         $para['user_id'] = $user_id;
    //         $redirect_url = ApiCommonController::generateRedirection($para);

    //         $this->return_data['response_code'] = 1;
    //         $this->return_data['message'] = 'Success';
    //         $this->return_data['data']['redirect_url'] = $redirect_url;
    //     }
    //     return Response($this->return_data, $this->server_error_code);
    // }

    public function login(Request $request): Response {
        /******* validation *********/
        $validate = Validator::make($request->all(),[
            'partner_id' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
        ],[
            'partner_id.required' => 'Partner ID is required',
            'email.required' => 'Please enter email address',
            'email.email' => 'Please enter valid email address',
            'password.required' => 'Please enter password',
            'password.regex' => 'Password should have one uppercase, one number and one special character.'
        ]);

        if($validate->fails()){
            $message = implode(", ", $validate->messages()->all());
            $this->return_data['message'] = $message;
            return Response($this->return_data, $this->server_error_code);
        }
        /******* validation *********/

        //variables
        $partner_id = !empty($request->partner_id) ? Hashids::decode($request->partner_id)[0] : 0;
        $lang_code = $request->lang_code ? $request->lang_code : 'en';
        $type = $request->type ? $request->type : 'consultation';
        $hid = $request->hid ? $request->hid : '';
        $redirect_url = config('constants.EXTERNAL_URL').'/'.$lang_code;
        $date_time = date("Y-m-d H:i:s");
        $date_time_mdy = date('m/d/y H:i:s');
        $this->return_data['data']['twofactor'] = 0;
        $this->return_data['data']['twofactor_type'] = '';

        $user_data = User::select('id','is_active','allow_2factor','token_2fa','token_2fa_expiry','factor2_type','mobile','firstname','lastname','email','password')
                    ->where(['email' => $request->email, 'partner_id' => $partner_id, 'role_id' => '3'])
                    ->first();
        if(!empty($user_data)){

            //check user active
            if($user_data->is_active == 0){
                $this->return_data['message'] = 'User is inactive.';
                return Response($this->return_data, $this->server_error_code);
            }

            //check password
            if(!Hash::check($request->password, $user_data->password)){
                $this->return_data['message'] = 'Please enter correct password.';
                return Response($this->return_data, $this->server_error_code);
            }

            if($user_data->allow_2factor == 1){ //2FA

                $user_data->token_2fa = $token_2fa = mt_rand(100000, 999999);
                $user_data->token_2fa_expiry = strtotime('+ 180 seconds');
                $user_data->save();
                $site_settings = SiteSetting::firstOrNew(['id' => 1]);
                
                if($user_data->factor2_type == 2){ //SMS

                    $template = Template::where('template_type', 2)->where('partner_id', $partner_id)->where('is_active', 1)->where('email_type', '2fa_verification')->first();
                    if(empty($template)){
                        $template = Template::where('template_type', 2)->where('partner_id', 0)->where('is_active', 1)->where('email_type', '2fa_verification')->first();
                    }

                    $to_replace = ['[CODE]'];
                    $with_replace = [$user_data->token_2fa];

                    if(!empty($template)){
                        $content = $template->content;
                    }else{
                        $content = "Two Factor Code for CallonDoc is : [CODE] \n\nReply STOP to unsubscribe.";
                    }
                    $html_body = str_replace($to_replace, $with_replace, $content);

                    if($site_settings->allow_sms_login == 1){
                        $para = [];
                        $para['mobile'] = $user_data->mobile;
                        $para['text'] = $html_body;
                        ApiCommonController::sendSMS($para);

                        $user_info = [
                            'id' => Hashids::encode($user_data->id),
                            'firstname' => $user_data->firstname,
                            'lastname' => $user_data->lastname,
                            'email' => $user_data->email
                        ];
                        $this->return_data['response_code'] = 1;
                        $this->return_data['message'] = 'Success';
                        $this->return_data['data']['twofactor'] = 1;
                        $this->return_data['data']['twofactor_type'] = 'SMS';
                        $this->return_data['data']['user_data'] = $user_info;
                    }

                }else if($user_data->factor2_type == 1){  //email

                    $template = Template::where('template_type', 1)->where('partner_id', $partner_id)->where('is_active', 1)->where('email_type', '2fa_token_email')->first();
                    if(empty($template)){
                        $template = Template::where('template_type', 1)->where('partner_id', 0)->where('is_active', 1)->where('email_type', '2fa_token_email')->first();
                    }

                    if(!empty($template)){
                        $subject = $template->subject;
                        $to_replace = ['[NAME]', '[CODE]'];
                        $with_replace = [$user_data->firstname.' '.$user_data->lastname, $user_data->token_2fa];
                        $emailData = str_replace($to_replace, $with_replace, $template->content);
                        $html_body = View::make('email_templete.template', ["data" => $emailData])->render();

                        $para = [];
                        $para['to_email'] = $user_data->email;
                        $para['sender_email'] = 'info@callondoc.com';
                        $para['subject'] = $subject;
                        $para['body_html'] = $html_body;
                        ApiCommonController::sendEmail($para);

                        $user_info = [
                            'id' => Hashids::encode($user_data->id),
                            'firstname' => $user_data->firstname,
                            'lastname' => $user_data->lastname,
                            'email' => $user_data->email
                        ];
                        $this->return_data['response_code'] = 1;
                        $this->return_data['message'] = 'Success';
                        $this->return_data['data']['twofactor'] = 1;
                        $this->return_data['data']['twofactor_type'] = 'EMAIL';
                        $this->return_data['data']['user_data'] = $user_info;
                    }
                }
            }else{
                $loginAuth = [];
                $loginAuth['email'] = $request->email;
                $loginAuth['password'] = $request->password;
                $loginAuth['partner_id'] = $partner_id;
                $loginAuth['is_active'] = 1;
                if (Auth::attempt($loginAuth)) {
                    $auth = Auth::user();
                    $auth_token = $auth->createToken('LoginAppToken')->accessToken;
                    User::where('id', $auth->id)->update(['last_login_on' => $date_time]);
    
                    //set log
                    $msg = $auth->firstname . ' was logged in at ' . $date_time_mdy . '. (IP Address: ' . $_SERVER['REMOTE_ADDR'] . ')';
                    ApiCommonController::createUserLog($auth->id, "Login", $msg);
    
                    if(!empty($request->is_condition_selected) && $request->is_condition_selected == 1){
                        if(!empty($request->app_redirect_url)){
                            $redirect_url = $request->app_redirect_url;
                        }
                    }

                    $para = [];
                    $para['base_url'] = $redirect_url;
                    $para['user_id'] = $auth->id;
                    $redirect_url = ApiCommonController::generateRedirection($para);

                    $user_data = [
                        'id' => Hashids::encode($auth->id),
                        'firstname' => $auth->firstname,
                        'lastname' => $auth->lastname,
                        'email' => $auth->email,
                        'auth_token' => $auth_token
                    ];
                    $this->return_data['response_code'] = 1;
                    $this->return_data['message'] = 'Successfully logged in.';
                    $this->return_data['data']['redirect_url'] = $redirect_url;
                    $this->return_data['data']['user_data'] = $user_data;
                }else{
                    $this->return_data['message'] = 'Please enter correct password.';
                }
            }
        }else{
            $this->return_data['message'] = 'User does not exist.';
        }
        return Response($this->return_data, $this->server_error_code);
    }

    public function register(Request $request): Response {
        /******* validation *********/
        $validate = Validator::make($request->all(),[
            'partner_id' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
        ],[
            'partner_id.required' => 'Partner ID is required',
            'email.required' => 'Please enter email address',
            'email.email' => 'Please enter valid email address',
            'password.required' => 'Please enter password',
            'password.regex' => 'Password should have one uppercase, one number and one special character.'
        ]);

        if($validate->fails()){
            $message = implode(", ", $validate->messages()->all());
            $this->return_data['message'] = $message;
            return Response($this->return_data, $this->server_error_code);
        }
        /******* validation *********/

        //variables
        $partner_id = !empty($request->partner_id) ? Hashids::decode($request->partner_id)[0] : 0;
        $lang_code = $request->lang_code ? $request->lang_code : 'en';
        $date_time = date("Y-m-d H:i:s");
        $date_time_mdy = date('m/d/y H:i:s');
        $redirect_url = config('constants.EXTERNAL_URL').'/'.$lang_code;

        //check existing email
        $user_info = User::where('email', $request->email)->first();
        if(!empty($user_info)){
            $this->return_data['message'] = 'Email address already exists.';
            return Response($this->return_data, $this->server_error_code);
        }

        //start transaction
        DB::beginTransaction();

        try {
            $gender = 0;
            if(!empty($request->is_condition_selected) && $request->is_condition_selected == 1){
                $gender = (!empty($request->who_is_for) && $request->who_is_for == 1) ? $request->gender : $gender;
            }

            //store user
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'original_password' => $request->password,
                'partner_id' => $partner_id,
                'is_active' => 1,
                'gender' => $gender,
                'doctor_role_id' => 0,
                'invited_for' => 0,
                'is_notify' => 0,
                'role_id' => 3,
                'created_at' => $date_time,
                'updated_at' => $date_time,
                'patient_status' => 'New'
            ]);

            if(!empty($user)){
                //set log
                $msg = $user->firstname . ' was registered as a patient at ' . $date_time_mdy . '. (IP Address: ' . $_SERVER['REMOTE_ADDR'] . ')';
                $logs = ApiCommonController::createUserLog($user->id, "New Patient", $msg);

                //log in array
                $loginAuth = [];
                $loginAuth['email'] = $request->email;
                $loginAuth['password'] = $request->password;
                $loginAuth['partner_id'] = $partner_id;
                $loginAuth['is_active'] = 1;

                if (Auth::attempt($loginAuth)) {
                    $auth = Auth::user();
                    $auth_token = $auth->createToken('LoginAppToken')->accessToken;
                    User::where('id', $user->id)->update(['last_login_on' => $date_time]);

                    //set log
                    $msg = $auth->firstname . ' was logged in at ' . $date_time_mdy . '. (IP Address: ' . $_SERVER['REMOTE_ADDR'] . ')';
                    ApiCommonController::createUserLog($user->id, "Login", $msg);

                    if(!empty($request->is_condition_selected) && $request->is_condition_selected == 1){
                        if(!empty($request->app_redirect_url)){
                            $redirect_url = $request->app_redirect_url;
                        }
                    }

                    $para = [];
                    $para['base_url'] = $redirect_url;
                    $para['user_id'] = $auth->id;
                    $redirect_url = ApiCommonController::generateRedirection($para);
                    
                    $user_data = [
                        'id' => Hashids::encode($auth->id),
                        'firstname' => $auth->firstname,
                        'lastname' => $auth->lastname,
                        'email' => $auth->email,
                        'auth_token' => $auth_token
                    ];
                    $this->return_data['response_code'] = 1;
                    $this->return_data['message'] = 'Registration has been done successfully';
                    $this->return_data['data']['redirect_url'] = $redirect_url;
                    $this->return_data['data']['user_data'] = $user_data;

                    //save commit
                    DB::commit();
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::channel('appLogs')->error($e);
            $this->server_error_code = 500;
            $this->return_data['message'] = $e->getMessage();
        }
        return Response($this->return_data, $this->server_error_code);
    }

    public function twoFactorVerification(Request $request): Response {
        /******* validation *********/
        $validate = Validator::make($request->all(),[
            'code' => 'required',
            'user_id' => 'required'
        ],[
            'code.required' => 'Verification code is required',
            'user_id.required' => 'User not given'
        ]);

        if($validate->fails()){
            $message = implode(", ", $validate->messages()->all());
            $this->return_data['message'] = $message;
            return Response($this->return_data, $this->server_error_code);
        }
        /******* validation *********/

        //variables
        $code = !empty($request->code) ? $request->code : '';
        $user_id = !empty($request->user_id) ? Hashids::decode($request->user_id)[0] : '';
        $lang_code = $request->lang_code ? $request->lang_code : 'en';
        $redirect_url = config('constants.EXTERNAL_URL').'/'.$lang_code;
        $this->return_data['message'] = 'Incorrect verification code';
        $date_time_mdy = date('m/d/y H:i:s');
        $current_date_time = date("Y-m-d H:i:s");

        //check code
        $user_info = User::select('id','token_2fa_expiry')->where('id', $user_id)->where('token_2fa', $code)->first();
        if(!empty($user_info)){
  
            /******* code expiration validation *******/
            $givenTimestamp = date("Y-m-d H:i:s", $user_info->token_2fa_expiry);
            $date_diff = ApiCommonController::getDateDifference($current_date_time, $givenTimestamp);
            $check_time_period = 180; //seconds

            if(isset($date_diff['seconds']) && ($date_diff['seconds'] >= $check_time_period)){
                $this->return_data['message'] = 'Verification code has expired';
                return Response($this->return_data, $this->server_error_code);
            }
            /******* /code expiration validation *******/

            $auth = Auth::loginUsingId($user_id);
            $auth_token = $auth->createToken('LoginAppToken')->accessToken;

            //set log
            $msg = $auth->firstname . ' was logged in at ' . $date_time_mdy . '. (IP Address: ' . $_SERVER['REMOTE_ADDR'] . ')';
            ApiCommonController::createUserLog($auth->id, "Login", $msg);
            
            if(!empty($request->is_condition_selected) && $request->is_condition_selected == 1){
                if(!empty($request->app_redirect_url)){
                    $redirect_url = $request->app_redirect_url;
                }
            }

            $para = [];
            $para['base_url'] = $redirect_url;
            $para['user_id'] = $auth->id;
            $redirect_url = ApiCommonController::generateRedirection($para);
            
            $user_data = [
                'id' => Hashids::encode($auth->id),
                'firstname' => $auth->firstname,
                'lastname' => $auth->lastname,
                'email' => $auth->email,
                'auth_token' => $auth_token
            ];
            $this->return_data['response_code'] = 1;
            $this->return_data['message'] = 'Successfully verified';
            $this->return_data['data']['redirect_url'] = $redirect_url;
            $this->return_data['data']['user_data'] = $user_data;
        }
        return Response($this->return_data, $this->server_error_code);
    }

    public function twoFactorResend(Request $request): Response {
        /******* validation *********/
        $validate = Validator::make($request->all(),[
            'user_id' => 'required'
        ],[
            'user_id.required' => 'User is required'
        ]);

        if($validate->fails()){
            $message = implode(", ", $validate->messages()->all());
            $this->return_data['message'] = $message;
            return Response($this->return_data, $this->server_error_code);
        }
        /******* validation *********/

        //variables
        $user_id = !empty($request->user_id) ? Hashids::decode($request->user_id)[0] : '';

        //check user
        $user_data = User::select('id','is_active','allow_2factor','token_2fa','token_2fa_expiry','factor2_type','mobile','firstname','lastname','email')
                        ->where('id', $user_id)
                        ->first();
                        
        if(!empty($user_data)){
            $partner_id = !empty($user_data->partner_id) ? $user_data->partner_id : 0;

            if($user_data->is_active == 0){
                $this->return_data['message'] = 'User is inactive.';
                return Response($this->return_data, $this->server_error_code);
            }

            if($user_data->allow_2factor == 1){ //2FA

                $user_data->token_2fa = $token_2fa = mt_rand(100000, 999999);
                $user_data->token_2fa_expiry = strtotime('+ 180 seconds');
                $user_data->save();
                $site_settings = SiteSetting::firstOrNew(['id' => 1]);
                
                if($user_data->factor2_type == 2){ //SMS

                    $template = Template::where('template_type', 2)->where('partner_id', $partner_id)->where('is_active', 1)->where('email_type', '2fa_verification')->first();
                    if(empty($template)){
                        $template = Template::where('template_type', 2)->where('partner_id', 0)->where('is_active', 1)->where('email_type', '2fa_verification')->first();
                    }

                    $to_replace = ['[CODE]'];
                    $with_replace = [$user_data->token_2fa];

                    if(!empty($template)){
                        $content = $template->content;
                    }else{
                        $content = "Two Factor Code for CallonDoc is : [CODE] \n\nReply STOP to unsubscribe.";
                    }
                    $html_body = str_replace($to_replace, $with_replace, $content);

                    if($site_settings->allow_sms_login == 1){
                        $para = [];
                        $para['mobile'] = $user_data->mobile;
                        $para['text'] = $html_body;
                        ApiCommonController::sendSMS($para);
 
                        $this->return_data['response_code'] = 1;
                        $this->return_data['message'] = 'Verification code has been successfully sent';
                    }

                }else if($user_data->factor2_type == 1){  //email

                    $template = Template::where('template_type', 1)->where('partner_id', $partner_id)->where('is_active', 1)->where('email_type', '2fa_token_email')->first();
                    if(empty($template)){
                        $template = Template::where('template_type', 1)->where('partner_id', 0)->where('is_active', 1)->where('email_type', '2fa_token_email')->first();
                    }

                    if(!empty($template)){
                        $subject = $template->subject;
                        $to_replace = ['[NAME]', '[CODE]'];
                        $with_replace = [$user_data->firstname.' '.$user_data->lastname, $user_data->token_2fa];
                        $emailData = str_replace($to_replace, $with_replace, $template->content);
                        $html_body = View::make('email_templete.template', ["data" => $emailData])->render();

                        $para = [];
                        $para['to_email'] = $user_data->email;
                        $para['sender_email'] = 'info@callondoc.com';
                        $para['subject'] = $subject;
                        $para['body_html'] = $html_body;
                        ApiCommonController::sendEmail($para);
 
                        $this->return_data['response_code'] = 1;
                        $this->return_data['message'] = 'Verification code has been successfully sent'; 
                    }
                }
            }else{
                $this->return_data['message'] = 'Two factor authentication is not set.';
            }
        }else{
            $this->return_data['message'] = 'User does not exist.';
        }
        return Response($this->return_data, $this->server_error_code);
    }

    public function forgotPassword(Request $request): Response {
        /******* validation *********/
        $validate = Validator::make($request->all(),[
            'partner_id' => 'required',
            'email' => 'required|email'
        ],[
            'partner_id.required' => 'Partner ID is required',
            'email.required' => 'Please enter email address',
            'email.email' => 'Please enter valid email address'
        ]);

        if($validate->fails()){
            $message = implode(", ", $validate->messages()->all());
            $this->return_data['message'] = $message;
            return Response($this->return_data, $this->server_error_code);
        }
        /******* validation *********/

        //variables
        $email = !empty($request->email) ? $request->email : '';
        $partner_id = !empty($request->partner_id) ? Hashids::decode($request->partner_id)[0] : 0;

        //check user
        $user_info = User::select('id','email','firstname','lastname','is_active')
                    ->where(['email' => $email, 'partner_id' => $partner_id, 'role_id' => '3'])
                    ->first();

        if(!empty($user_info)){
            if($user_info->is_active != 1){
                $this->return_data['message'] = 'Your account was not approved or deactivate by Admin';
                return Response($this->return_data, $this->server_error_code);
            }

            $template = Template::select('id','subject','content')->where('template_type', 1)->where('is_active', 1)->where('partner_id', $partner_id)->where('email_type', 'forgot_password')->first();
            if(!empty($template)){ 

                $email_verification_code = ApiCommonController::generateAphaNumericString(10);
                $token_expire = date('Y-m-d H:i:s', strtotime("+24 hours"));

                $link = config('constants.EXTERNAL_URL')."/reset-password/".$email_verification_code."/".Hashids::encode($user_info->id).'?from=1'; //1-app
                $to_replace = ['[NAME]', '[LINK]'];
                $with_replace = [$user_info->firstname.' '.$user_info->lastname, $link];
                $content = str_replace($to_replace, $with_replace, $template->content);
                $html_body = View::make('email_templete.template', ["data" => $content])->render();

                $para = [];
                $para['to_email'] = $user_info->email;
                $para['sender_email'] = 'info@callondoc.com';
                $para['subject'] = $template->subject;
                $para['body_html'] = $html_body;
                $send = ApiCommonController::sendEmail($para);

                if($send['response_code'] == 1){
                    //update
                    User::where('id', $user_info->id)
                        ->update(['email_verification_code' => $email_verification_code, 'token_expire' => $token_expire, 'is_email_verified' => 0]);

                    $this->return_data['response_code'] = 1;
                    $this->return_data['message'] = 'A reset password link has been sent';
                }
            }else{
                $this->return_data['message'] = 'Email template is not specify.';
            }
        }else{
            $this->return_data['message'] = 'This email address is not register';
        }
        return Response($this->return_data, $this->server_error_code);
    }

    public function resetPassword(Request $request): Response {
        /******* validation *********/
        $validate = Validator::make($request->all(),[
            'new_password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
            'email_verification_code' => 'required',
            'user_id' => 'required'
        ],[
            'new_password.regex' => 'Password should have one uppercase, one number and one special character.',
            'email_verification_code.required' => 'Email Verification Code is required',
            'user_id.required' => 'User id is required'
        ]);

        if($validate->fails()){
            $message = implode(", ", $validate->messages()->all());
            $this->return_data['message'] = $message;
            return Response($this->return_data, $this->server_error_code);
        }
        /******* validation *********/

        //variables
        $new_password = !empty($request->new_password) ? $request->new_password : '';
        $email_verification_code = !empty($request->email_verification_code) ? $request->email_verification_code : '';
        $user_id = !empty($request->user_id) ? Hashids::decode($request->user_id)[0] : '';

        //check user
        $user_info = User::select('id','email','firstname','lastname','is_active','email_verification_code')
                    ->where(['id' => $user_id, 'role_id' => '3'])
                    ->first();

        if(!empty($user_info)){
            //check user active
            if($user_info->is_active != 1){
                $this->return_data['message'] = 'Your account was not approved or deactivate by Admin';
                return Response($this->return_data, $this->server_error_code);
            }

            //check verification code
            if($user_info->email_verification_code != $email_verification_code){
                $this->return_data['message'] = 'Invalid verification code';
                return Response($this->return_data, $this->server_error_code);
            }

            //update
            $is_save = User::where('id', $user_info->id)
                ->update(['email_verification_code' => NULL, 'token_expire' => NULL, 'is_email_verified' => 1, 'is_active' => 1, 'password' => Hash::make($new_password), 'original_password' => $new_password]);

            if(!empty($is_save)){
                $this->return_data['response_code'] = 1;
                $this->return_data['message'] = 'A new password has been updated';
            }
        }else{
            $this->return_data['message'] = 'User does not exist.';
        }
        return Response($this->return_data, $this->server_error_code);
    }

    public function logout(Request $request): Response {
        if (Auth::guard('api')->check()) {
            $auth = Auth::guard('api')->user();
            $auth = Auth::guard('api')->user()->token();
            $auth->revoke();
        }
        $this->return_data['response_code'] = 1;
        $this->return_data['message'] = 'Successfully logged out';
        return Response($this->return_data, $this->server_error_code);
    }
    
    public function getUser(Request $request) {
        $user = Auth::guard('api')->user();
        echo "<pre>"; print_r($user->toArray());die;
    }
}
