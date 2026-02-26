<?php

use Joomla\CMS\User\User;
/**
* @author      miniOrange Security Software Pvt. Ltd.
* @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
* @license     GNU General Public License version 3; see LICENSE.txt
* @contact     joomlasupport@xecurify.com
 */
/**
This class contains all the utility functions
**/
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\HTML\HTMLHelper;
class MoOAuthServerUtility
{
	public static function is_customer_registered() 
	{
		$db = self::getDBObject();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__miniorange_oauthserver_customer'));
		$query->where($db->quoteName('id')." = 1");
 
		$db->setQuery($query);
		$result = $db->loadAssoc();
		$email 			= $result['email'];
		$customerKey 	= $result['customer_key'];
		$status = $result['registration_status'];
		if($email && $customerKey && is_numeric( trim($customerKey)) && $status == 'SUCCESS'){
			return 1;
		} else{
			return 0;
		}
	}
	
	
	public static function GetPluginVersion()
	{
		$db = self::getDBObject();
		$dbQuery = $db->getQuery(true)
		->select('manifest_cache')
		->from($db->quoteName('#__extensions'))
		->where($db->quoteName('element') . " = " . $db->quote('com_miniorange_oauthserver'));
		$db->setQuery($dbQuery);
		$manifest = json_decode($db->loadResult());
		return($manifest->version);
    }

	public static function generic_update_query($database_name, $updatefieldsarray , $condition = TRUE)
	{
        $db = self::getDBObject();
        $query = $db->getQuery(true);
        foreach ($updatefieldsarray as $key => $value)
          $database_fileds[] = $db->quoteName($key) . ' = ' . $db->quote($value);
        $query->update($db->quoteName($database_name))->set($database_fileds);
		if($condition!==TRUE)
        {
            foreach ($condition as $key=>$value)
                $query->where($db->quoteName($key) . " = " . $db->quote($value));
        }
        $db->setQuery($query);
        $db->execute();
    }

	public static function check_empty_or_null( $value ) {
		if( ! isset( $value ) || empty( $value ) ) {
			return true;
		}
		return false;
	}
	
	public static function is_curl_installed() {
		if  (in_array  ('curl', get_loaded_extensions())) {
			return 1;
		} else 
			return 0;
	}
	
	public static function getHostname(){
		return 'https://login.xecurify.com';
	}
	
	public static function getCustomerDetails(){
		$db = self::getDBObject();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__miniorange_oauthserver_customer'));
		$query->where($db->quoteName('id')." = 1");
 
		$db->setQuery($query);
		$customer_details = $db->loadAssoc();
		return $customer_details;
	}

	static function  miniOauthFetchDb($tableName,$condition=TRUE,$method='loadAssoc',$columns='*'){

		$db = self::getDBObject();
		$query = $db->getQuery(true);
		$columns = is_array($columns)?$db->quoteName($columns):$columns;
		$query->select($columns);
		$query->from($db->quoteName($tableName));
        if($condition!==TRUE)
        {
            foreach ($condition as $key=>$value)
                $query->where($db->quoteName($key) . " = " . $db->quote($value));
        }

		$db->setQuery($query);
		if ($method=='loadColumn')
			return $db->loadColumn();
		else if($method == 'loadObjectList')
			return $db->loadObjectList();
        else if($method == 'loadObject')
            return $db->loadObject();
		else if($method== 'loadResult')
			return $db->loadResult();
		else if($method == 'loadRow')
			return $db->loadRow();
        else if($method == 'loadRowList')
            return $db->loadRowList();
        else if($method == 'loadAssocList')
            return $db->loadAssocList();
		else
			return $db->loadAssoc();
	}
	
	public static function plugin_efficiency_check($email,$appname,$base_url, $reason ="null")
	{
        $url =  'https://login.xecurify.com/moas/api/notify/send';
        $ch = curl_init($url);

        $customerKey = "16555";
		$apiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
        $currentTimeInMillis= round(microtime(true) * 1000);
        $stringToHash 		= $customerKey .  number_format($currentTimeInMillis, 0, '', '') . $apiKey;
        $hashValue 			= hash("sha512", $stringToHash);
        $customerKeyHeader 	= "Customer-Key: " . $customerKey;
        $timestampHeader 	= "Timestamp: " .  number_format($currentTimeInMillis, 0, '', '');
        $authorizationHeader= "Authorization: " . $hashValue;
        $fromEmail 			= $email;
        $subject            = "miniOrange Joomla OAuth Server [Free] for Efficiency";

        $query1 =" miniOrange Joomla [Free] Oauth Server to improve efficiency ";
        $content='<div >Hello, <br><br>Company :<a href="'.$_SERVER['SERVER_NAME'].'" target="_blank" >'.$_SERVER['SERVER_NAME'].'</a><br><br>OAuth Client Name :'.$appname.'<br><br><b>Email :<a href="mailto:'.$fromEmail.'" target="_blank">'.$fromEmail.'</a></b><br><br><b>Plugin Efficency Check: '.$query1. '</b><br><br><b>Redirect URI: ' .$base_url. '</b><br> Error Message:'.$reason.'</div>';

        $fields = array(
            'customerKey'    => $customerKey,
            'sendEmail'     => true,
            'email'         => array(
                'customerKey'     => $customerKey,
                'fromEmail'       => 'joomlasupport@xecurify.com',                
                'fromName'        => 'miniOrange',
                'bccEmail'        => 'nikhil.bhot@xecurify.com',
                'toEmail'         => 'nutan.barad@xecurify.com',
                'toName'          => 'nutan.barad@xecurify.com',
                'subject'         => $subject,
                'content'         => $content
            ),
        );
        $field_string = json_encode($fields);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_ENCODING, "" );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls

        curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader, $timestampHeader, $authorizationHeader));
        curl_setopt( $ch, CURLOPT_POST, true);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
        $content = curl_exec($ch);
        	
        if(curl_errno($ch)){
            curl_close($ch);
            return json_encode(array("status"=>'Error','message'=>'Request Error:' . curl_error($ch)));
        }
        curl_close($ch);
        return json_encode(array('status' => 'success'));
	}

	public static function exportData($tableNames)
    {
        $db = self::getDBObject();
        $jsonData = [];

        if (empty($tableNames)) {
            $jsonData['error'] = 'No table names provided.';
        } else {
            foreach ($tableNames as $tableName) {
                $query = $db->getQuery(true);
                $query->select('*')
                    ->from($db->quoteName($tableName));

                $db->setQuery($query);
                try {
                    $data = $db->loadObjectList();
                    if (empty($data)) {
                        $jsonData[$tableName] = ['message' => 'This table is empty.'];
                    } else {
                        $jsonData[$tableName] = $data;
                    }
                } catch (Exception $e) {
                    $jsonData[$tableName] = ['error' => $e->getMessage()];
                }
            }
        }

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="exported_data.json"');
        
        echo json_encode($jsonData, JSON_PRETTY_PRINT);
        Factory::getApplication()->close();
    }

    static function handleOAuthCodeRequest()
    {
        $customerResult = self::miniOauthFetchDb('#__miniorange_oauthserver_config', array("id"=>'1'), 'loadAssoc', '*');
        $app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $get = $input->get->getArray();
        $OAuthClientAppName = $customerResult['client_name'];
        
        if(isset($get['client_id']) && !isset($get['client_secret'])) {   
            if(isset($customerResult['client_id']) && $customerResult['client_id']===$get['client_id'] && isset($customerResult['authorized_uri']) && $customerResult['authorized_uri']===$get['redirect_uri']) {
                $session = self::getSession(); // Get current session vars
                $user = self::getUser();        // Get the user object
                $app  = Factory::getApplication(); // Get the application
                $client_id = $get['client_id'];
                $scope = $get['scope'];
                $redirect_uri = $get['redirect_uri'];
                $response_type = $get['response_type'];
                $state = $get['state'];
               
                if($user->id!='') {
                    $user = self::getUser();
                    $isroot = $user->authorise('core.admin');
                    $customerResult = self::miniOauthFetchDb('#__users', array("id"=>$user->id), 'loadAssoc', '*');
                    if($isroot) {
                        $redirecturi = $redirect_uri;
                        $randcode = self::generateRandomString();        
                        $user_id = $user->id;        
                        $fields = array(
                            'rancode' =>$randcode
                        );
                        $conditions = array(
                            'id' => $user_id
                        );
                        self::generic_update_query('#__users', $fields, $conditions);
                    
                        $state = $get['state']; 
                        $sso_url = $redirecturi;
                        $sso_url = explode("/", $sso_url)[2];
                        $sso_url = "https://" . $sso_url;
                        $redirecturi = $redirecturi."?code=".$randcode."&state=".$state;  
                        //show Consent Form   
                        self::plugin_efficiency_check($customerResult['email'], $OAuthClientAppName, $redirect_uri);
                        self::showConsentForm($user_id, $client_id, $scope, $sso_url, $state, $OAuthClientAppName, $redirecturi);
                        header('Location: ' . $redirecturi);
                        exit;
                    }
                    else
                    {
                        $session = self::getSession();
                        $session->destroy();
       
                    }        
                }
                $oauth_response_params = array('client_id' => $client_id , "scope" => $scope , "redirect_uri" => $redirect_uri , "response_type" => $response_type, "state" => $state , "clientName" => $OAuthClientAppName);
                $msg="Only admins will have complete SSO (auto login) in free version. Inorder to auto login for normal users please upgrade to premium";
                setcookie("response_params", json_encode($oauth_response_params), time() + 3600, '/');
                $modified_url = Uri::base();
                $modified_url = str_replace("api/", "", $modified_url);
                $redirect_url = $modified_url . "index.php?option=com_users&view=login";
                $app->enqueueMessage($msg, 'notice');
                $app->redirect(Route::_($redirect_url, false));    
            }
            else
            {    
                //send back error for authorization
                $redirect_uri = isset($get['redirect_uri'])?$get['redirect_uri']:null;
                self::plugin_efficiency_check($customerResult['client_id'], $OAuthClientAppName, $redirect_uri, "Invalid Redirect Uri (or) Invalid Client ID");
                $api_response= array('error' => 'Invalid Redirect Uri (or) Invalid Client ID');
                header("Content-Type: application/json");
                echo(json_encode($api_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                exit;    
            }
        }
    }

    static function handleOAuthTokenRequest()
    {
        $customerResult = self::miniOauthFetchDb('#__miniorange_oauthserver_config', array("id"=>'1'), 'loadAssoc', '*');
        $app   = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = $input->post->getArray();
        
        // TO get the client credentials from header
        $client_id = isset($post['client_id']) ? $post['client_id'] : '';
        $client_secret = isset($post['client_secret']) ? $post['client_secret'] : '';
        $credentials = '';
        foreach (getallheaders() as $name => $value) 
        {    
            if($name == 'Authorization') {
                $credentials = $value;
                break;
            }
        }

        if ($client_id==='' && strpos($credentials, 'Basic ') === 0) {
            $encodedCredentials = substr($credentials, 6); // Remove "Basic " prefix
            $decodedCredentials = base64_decode($encodedCredentials); // Decode Base64
        
            list($client_id, $client_secret) = explode(":", $decodedCredentials, 2);
        } 

        if($client_id != '' && $client_secret != '') {
            if($customerResult['client_id']===$client_id  && $customerResult['authorized_uri']===$post['redirect_uri'] && $customerResult['client_secret']===$client_secret) {
                if($post['grant_type']!='authorization_code') {    
                    $api_response= array('error' => 'grantTypes mismatch or limited,please contact your administrator');
                    echo(json_encode($api_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                    exit;
                }        
                $randcode = self::generateRandomString();
                $code = $post['code'];
                //Getting the user details using code parameter    
                $results = self::miniOauthFetchDb('#__users', array("rancode"=>$code), 'loadAssoc', 'id');
                if($results['id']!='') {
                    $t=time()+3600;
                    $time=3600;    
                    // inserting the accessToken    
                    $fields = array(
                    'client_token'=>$randcode,
                    'time_stamp'=>$t
                    );
                    $conditions = array(
                    'id'=>$results['id']
                    );     
                    
                    $result = self::generic_update_query('#__users', $fields, $conditions);
                    $id_token = self::generateIdToken($results['id'], $client_id);
                    $scope="openid";
                    $token_type="Bearer";
                    $api_response = array('access_token' => $randcode, 'expires_in' => $time, "scope"=> $scope, 'token_type' => $token_type, 'id_token' => $id_token);   
                    header('Content-Type: application/json');
                    echo json_encode($api_response, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                    exit;
                }
                else
                {
                    self::plugin_efficiency_check($client_id, $customerResult['client_name'], $customerResult['authorized_uri'], "Some Error with code recived");
                    $api_response= array('error' => 'Some Error with code recived,please contact your administrator');
                    echo(json_encode($api_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                    exit;
                }        
            }
            else
            {        
                self::plugin_efficiency_check($client_id, $customerResult['client_name'], $customerResult['authorized_uri'], "Some Error at Token Endpoint URL");
                $api_response= array('error' => 'Some Error at Token Endpoint URL,please contact your administrator');
                echo(json_encode($api_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                exit;
            }
        }
    }

    static function handleOAuthUserInfoRequest()
    {
        $app   = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;

        $post = $input->post->getArray();

        $access_token = isset($post['access_token']) ? $post['access_token'] : '';
        if($access_token == '') {
            foreach (getallheaders() as $name => $value) 
            {                
                if (strtolower($name) == 'authorization') {
                    $access_token = $value;
                    break;
                }
        
            }
            $access_token = explode(" ", $access_token);
            $access_token =$access_token[1];
        }

        $db = self::getDBObject();
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('id','time_stamp')));
        $query->from($db->quoteName('#__users'));
        $query->where($db->quoteName('client_token') . ' =' . $db->quote($access_token));
        $db->setQuery($query);
        $results = $db->loadAssoc();
        if($results['time_stamp']<time()) {        
            $api_response= array('error' => 'Access token got expire,please contact your administrator');
            header('Content-Type: application/json');
            echo(json_encode($api_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            exit;                    
        }
        $query = $db->getQuery(true);
        $query->select($db->quoteName('group_id'));
        $query->from($db->quoteName('#__user_usergroup_map'));
        $query->where($db->quoteName('user_id') . ' =' . $db->quote($results['id']));
        $db->setQuery($query);
        $groups = $db->loadColumn();
        $groups_list = '(' . implode(',', $groups) . ')';    
        if(strpos($groups_list, '7')|| strpos($groups_list, '8')) {                    
            $flag = 1;
        }
        else
        {
            $api_response= array('error' => 'Only Admins can perform the SSO.');
            header('Content-Type: application/json');
            echo(json_encode($api_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            exit;                    
        }
        if($results['id']!='' && $flag) {
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from($db->quoteName('#__users'));
            $query->where($db->quoteName('id') . ' =' . $db->quote($results['id']));
            $db->setQuery($query);
            $results = $db->loadAssoc();
            if(empty(trim($results['email']))) {
                $api_response = array(        
                    'id'  => $results['id'],
                    'username' => $results['username'],
                    'email' => $results['email']
                );
            }
            else
            {    
                $api_response = array(            
                    'id'  => $results['id'],
                    'username' => $results['email'],
                    'email' => $results['email']
                );
            }
            header("Content-Type: application/json");
            echo(json_encode($api_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            exit;
        }
        else
        {
            $api_response= array('error' => 'Access token doesnt match,please contact your administrator');
            header('Content-Type: application/json');
            echo(json_encode($api_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            exit;                
        }

    }

    static function generateRandomString() 
    {  
        $tokenLength = self::miniOauthFetchDb('#__miniorange_oauthserver_config', array("id"=>'1'), 'loadResult', 'token_length');
        $tokenLength=intval($tokenLength);
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $tokenLength; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    static function generateIdToken($user_id, $client_id)
    {
        $issuer = Uri::root();
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payload = json_encode(
            [
            'sub' => $user_id,
            'iss' => $issuer,
            'aud' => $client_id,
            'iat' => time(),
            'exp' => time() + 3600
            ]
        );

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 'your_secret_key', true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }   

    static function showConsentForm($user_id, $client_id, $scope, $sso_url, $state, $clientName, $redirect_uri)
    {
        $scopes = !empty($scope) ? explode(' ', $scope) : [];
        $css = "
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .oauth-consent-container {
                max-width: 500px;
                background: #fff;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                text-align: center;
            }
            .oauth-header h2 {
                margin: 0;
                font-size: 22px;
                color: #333;
            }
            .client-info p {
                font-size: 16px;
                color: #555;
                margin: 15px 0;
            }
            .scope-list {
                list-style: none;
                padding: 0;
                text-align: left;
            }
            .scope-list li {
                font-size: 14px;
                color: #333;
                margin: 8px 12px;
                display: flex;
                align-items: center;
            }
            .buttons-container {
                margin-top: 20px;
                display: flex;
                justify-content: space-between;
            }
            .btn {
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
                border: none;
                font-size: 14px;
                font-weight: 600;
                transition: background 0.3s;
            }
            .btn-deny {
                background-color: #f5f5f5;
                color: #666;
            }
            .btn-deny:hover {
                background-color: #ddd;
            }
            .btn-allow {
                background-color: #020dad;
                color: white;
            }
            .btn-allow:hover {
                background-color: #010a80;
            }
            .terms-text {
                font-size: 12px;
                color: #777;
                margin-top: 15px;
            }
        </style>";

        echo $css . '
            <div class="oauth-consent-container">
                <div class="oauth-header">
                    <h2>Authorization Request</h2>
                </div>
                <div class="client-info">
                    <p><strong>' . $clientName . '</strong> would like access to your account.</p>
                </div>
                
        
                
                    <form id="consentForm" action="" method="POST">
                    <input type="hidden" name="user_id" value="' . $user_id . '">
                    <input type="hidden" name="client_id" value="' . $client_id . '">
                    <input type="hidden" name="redirect_uri" value="' . $redirect_uri . '">
                    <input type="hidden" name="state" value="' . $state . '"> ';

        if (!empty($scopes)) {
            echo '<h4>This application would like to:</h4>
                                <ul class="scope-list">
                                    <li>
                                        <label>
                                            OpenID (Required)
                                        </label>
                                    </li>';
                                
            foreach ($scopes as $scope_item) {
                if ($scope_item !== 'openid') {
                    echo '<li>
                                                <label>
                                                    ' . ucfirst($scope_item) . '
                                                </label>
                                              </li>';
                }
            }
                                
                        echo '</ul>';
        }

                    echo '<div class="buttons-container">
                        <button type="submit" name="consent_action" value="deny" class="btn btn-deny">Deny</button>
                        <button type="submit" name="consent_action" value="allow" class="btn btn-allow">Allow</button>
                    </div>
                
                    <p class="terms-text">
                        By approving, you allow this application to use your information in accordance with their terms of service and privacy policy.
                    </p>
                    ' . HTMLHelper::_('form.token') . '
                </form>
            </div>
            <script>
                document.getElementById("consentForm").addEventListener(
                    "submit", function (event) {
                        event.preventDefault();

                        var action = document.activeElement.value;
                        if (action === "allow") {

                            window.location.href = "' . $redirect_uri .'"
                        } else if (action === "deny") {
                            document.body.innerHTML = `
                            <div class="oauth-consent-container">
                                <div class="oauth-header">
                                    <h2>Authorization Denied</h2>
                                </div>
                                <p class="error-message">You have denied access to this application.</p>
                            </div>`;
                            setTimeout(function() {
                                window.location.href = "' . $sso_url . '";
                            }, 3000);
                        }
                    }
                );
                    </script>';
                    exit;
    }
    
    public static function getDBObject()
    {
        $app = Factory::getApplication();
        if (method_exists($app, 'getDatabase')) {
            return $app->getDatabase(); // J4+
        }

        return Factory::getDbo(); 
    }

    public static function getSession()
    {
        $app = Factory::getApplication();

        if (method_exists($app, 'getSession')) {
            return $app->getSession();
        }

        return Factory::getSession();
    }

    public static function getUser($userId = null)
    {
        $app = Factory::getApplication();

        if (method_exists($app, 'getIdentity')) {
            return $userId !== null ? $app->getIdentity($userId) : $app->getIdentity();
        }

        return $userId !== null ? User::getInstance($userId) : Factory::getUser();
    }
}
?>
