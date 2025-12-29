<?php
/**
 * @package    miniOrange
 * @subpackage Plugins
* @author      miniOrange Security Software Pvt. Ltd.
* @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
* @license     GNU General Public License version 3; see LICENSE.txt
* @contact     joomlasupport@xecurify.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
jimport('joomla.plugin.plugin');
jimport('miniorangeoauthserver.utility.MoOAuthServerUtility');
if(!defined('DS')) { define('DS', DIRECTORY_SEPARATOR);
}

class plgUserMiniorangeoauthserver extends CMSPlugin
{

 
    /**
     * This method should handle any authentication and report back to the subject
     *
     * @access public
     * @param  array  $credentials Array holding the user credentials ('username' and 'password')
     * @param  array  $options     Array of extra options
     * @param  object $response    Authentication response object
     * @return boolean
     */
    public function onUserAfterLogin($options)
    {
        $app   = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $cookie = $input->cookie->getArray();
        if(isset($cookie['response_params'])) {
            $response_params =  json_decode(stripslashes($cookie['response_params']), true);
        
            $user = Factory::getUser();
            
            $user_id = $user->get('id');
            
            $randcode = $this->generateRandomString();
            
            $db = Factory::getDbo();
            $query = $db->getQuery(true);
            // Fields to update.
            $fields = array(
              //  $db->quoteName('clientstate') . ' = ' . $db->quote($response_params['state']),
            //$db->quoteName('randcodetok') . ' = ' . $db->quote(),
            $db->quoteName('rancode'). ' = ' . $db->quote($randcode)
            );

            // Conditions for which records should be updated.
            $conditions = array(
            $db->quoteName('id') . ' = '. $db->quote($user_id)
            );

            $query->update($db->quoteName('#__users'))->set($fields)->where($conditions);
            $db->setQuery($query);
            $result = $db->execute(); 
            setcookie("response_params", "", time() - 3600, "/");
            $redirecturi = $response_params['redirect_uri'];
            $state = $response_params['state'];
            $customerResult = MoOAuthServerUtility::miniOauthFetchDb('#__miniorange_oauthserver_config', array("id"=>'1'), 'loadAssoc', '*');
            $scope = $response_params['scope'];
            $sso_url = $redirecturi;
            // $sso_url = explode("/", $sso_url)[2];
            // $sso_url = "https://" . $sso_url;
            MoOAuthServerUtility::plugin_efficiency_check($user->get('email'), $response_params['clientName'], $redirecturi, "Sending Authorization Code");
            $redirecturi = $redirecturi . "?code=" . $randcode . "&state=" . $state;

            if(isset($response_params['redirect_uri']) ) {
                $this->showConsentForm($user_id, $customerResult['client_id'],  $scope, $sso_url, $state, $customerResult['client_name'], $redirecturi);
            }
            
            header('Location: ' . $redirecturi);
            exit;
        }
    }
    
    
    function generateRandomString() 
    {
         
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 30; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    function showConsentForm($user_id, $client_id, $scope, $sso_url, $state, $clientName, $redirect_uri)
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
}
