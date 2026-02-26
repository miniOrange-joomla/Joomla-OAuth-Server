<?php

/**
 * @package    Joomla.Plugin
 * @subpackage System.mooauthserver
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     joomlasupport@xecurify.com
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;

jimport( 'joomla.plugin.plugin' );
jimport('miniorangeoauthserver.utility.MoOAuthServerUtility');

class plgSystemMooauthserver extends CMSPlugin	
{

    public function onAfterInitialise()
    { 
        $app   = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;

        $post = $input->post->getArray();

        if(isset($post['mojsp_feedback'])) {
            $radio = isset($post['deactivate_plugin']) ? $post['deactivate_plugin'] : '' ;
            $data = isset($post['miniorange_skip_feedback']) ? $post['query_feedback'] . '  ...Skipped' : $post['query_feedback'];
            $feedback_email = isset($post['feedback_email']) ? $post['feedback_email'] : '';
            $db = self::getDBObject();
            $query = $db->getQuery(true);
            // Fields to update.
            $fields = array(
                $db->quoteName('uninstall_feedback') . ' = ' . $db->quote(1)
            );
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('id') . ' = 1'
            );
            $query->update($db->quoteName('#__miniorange_oauthserver_customer'))->set($fields)->where($conditions);
            $db->setQuery($query);
            $result = $db->execute();

            $current_user = (method_exists($app, 'getIdentity')) ? $app->getIdentity() :  Factory::getUser();

            $db = self::getDBObject();
            $query = $db->getQuery(true);
            $query->select(array('*'));
            $query->from($db->quoteName('#__miniorange_oauthserver_customer'));
            $query->where($db->quoteName('id')." = 1");
            $db->setQuery($query);
            $customerResult = $db->loadAssoc();

            $admin_phone = $customerResult['admin_phone'];
            $data1 = $radio.' : '.$data;
            require_once JPATH_BASE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_oauthserver' . DIRECTORY_SEPARATOR . 'helpers' .DIRECTORY_SEPARATOR . 'mo_customer_setup.php';
            $response = MoOauthServerCustomer::submit_feedback_form($feedback_email, $admin_phone, $data1);
            require_once JPATH_SITE . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Installer' .DIRECTORY_SEPARATOR . 'Installer.php';
			foreach ($post['result'] as $fbkey) 
            {
                $db = self::getDBObject();
                $query = $db->getQuery(true);
                $query->select('type');
                $query->from('#__extensions');
                $query->where($db->quoteName('extension_id') . " = " . $db->quote($fbkey));
                $db->setQuery($query);
                $result = $db->loadColumn();
                $identifier=$fbkey;
			    $type=0;
                foreach ($result as $results)
                {        
                    $type=$results;
                }
                if($type)
                {
                    $cid=0;
                    $installer = new Installer();
                    $installer->setDatabase(self::getDBObject());
                    $installer->uninstall($type, $identifier, $cid);
                }
    		}
        }
	}

    public function onAfterRoute()
    {
        $app   = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $url =  $input->server->get('PATH_INFO', '', 'STRING');

        if($url === "/v1/moserver/auth") {
            MoOAuthServerUtility::handleOAuthCodeRequest();
        }
        else if($url === "/v1/moserver/token") {
            MoOAuthServerUtility::handleOAuthTokenRequest();
        }
        else if($url === "/v1/moserver/userinfo") {
            MoOAuthServerUtility::handleOAuthUserInfoRequest();
        }
    }

	function onExtensionBeforeUninstall($id)
    {
	    $post = Factory::getApplication()->input->post->getArray();
        $db = self::getDBObject();
        $query = $db->getQuery(true);
        $query->select('extension_id');
        $query->from('#__extensions');
        $query->where($db->quoteName('name') . " = " . $db->quote('COM_MINIORANGE_OAUTHSERVER' ));
        $db->setQuery($query);
        $result = $db->loadColumn();
        $tables = self::getDBObject()->getTableList();
        $tab=0;
        foreach ($tables as $table) 
        {
            if(strpos($table,"miniorange_oauthserver_customer"))
                $tab=$table;
        }
        if($tab) 
        {
            $db = self::getDBObject();
            $query = $db->getQuery(true);
            $query->select('uninstall_feedback');
            $query->from('#__miniorange_oauthserver_customer');
            $query->where($db->quoteName('id') . " = " . $db->quote(1));
            $db->setQuery($query);
            $fid = $db->loadColumn();
            $tpostData = $post;
            foreach ($fid as $value) 
            {
                if ($value == 0) 
                {
                    foreach ($result as $results) 
                    {
                        if ($results == $id) 
                        {
                            ?>
                            <div class="form-style-6">
                                <h1 class="feedback-title">
                                    Feedback Form for OAuth Server

                                    <button type="submit"
                                            name="miniorange_skip_feedback"
                                            class="close-x"
                                            form="mojsp_feedback"
                                            id="skipBtn"
                                            formnovalidate
                                            title="Skip Feedback">
                                        ✕
                                    </button>
                                </h1>
                                
                                <h3>What Happened?</h3>

                                <form name="f" method="post" action="" id="mojsp_feedback">
                                    <input type="hidden" name="mojsp_feedback" value="mojsp_feedback"/>

                                    <?php
                                    $deactivate_reasons = array(
                                        "Does not have the features I'm looking for",
                                        "Confusing Interface",
                                        "Not able to Configure",
                                        "Redirecting back to login page after Authentication",
                                        "Not Working",
                                        "Bugs in the plugin",
                                        "Other Reasons:"
                                    );
                                
                                    foreach ($deactivate_reasons as $reason) { ?>
                                        <div class="radio" style="padding:1px;margin-left:2%">
                                            <label style="font-weight:normal;font-size:14.6px" for="<?php echo $reason; ?>">
                                                <input type="radio" name="deactivate_plugin"
                                                       value="<?php echo $reason; ?>">
                                                <?php echo $reason; ?>
                                            </label>
                                        </div>
                                    <?php } ?>
                                    
                                    <br>
                                    <textarea id="query_feedback" name="query_feedback" rows="4" style="margin-left:2%" cols="50" placeholder="Write your query here" minlength="10"></textarea>
                                    <br><br>
                                    
                                    <label style="margin-left:2%;"><strong>Email<span style="color:red;">*</span>:</strong></label>
                                    <input type="email" id="feedback_email" name="feedback_email" placeholder="Enter email to contact."
                                           style="width:96%; margin-left:2%;"/>
                                    
                                    <br><br>
                                    
                                    <?php
                                    if (isset($tpostData['cid'])) {
                                        foreach ($tpostData['cid'] as $key) { ?>
                                            <input type="hidden" name="result[]" value="<?php echo $key; ?>">
                                        <?php }
                                    } ?>

                                    <br>
                                    <div class="mojsp_modal-footer">
                                        <input type="submit" id="submitBtn" name="miniorange_feedback_submit"
                                               class="button button-primary button-large" value="Submit"/>
                                    </div>
                                </form>
                                
                                <form name="f" method="post" action="" id="mojsp_feedback_form_close">
                                    <input type="hidden" name="option" value="mojsp_skip_feedback"/>
                                </form>
                            </div>
                                
                            <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
                            <script>
                                document.addEventListener("DOMContentLoaded", function () {
                                const emailField = document.getElementById("feedback_email");
                                const skipBtn = document.getElementById("skipBtn");
                                const submitBtn = document.getElementById("submitBtn");
                                const radioButtons = document.querySelectorAll('input[name="deactivate_plugin"]');
                                const feedbackBox = document.getElementById("query_feedback");

                                emailField.removeAttribute("required");
                                radioButtons.forEach(r => r.removeAttribute("required"));

                                skipBtn.addEventListener("click", function () {
                                    emailField.removeAttribute("required");
                                    radioButtons.forEach(r => r.removeAttribute("required"));
                                    feedbackBox.removeAttribute("required");
                                });
                            
                                submitBtn.addEventListener("click", function () {
                                    emailField.setAttribute("required", "true");
                                    radioButtons.forEach(r => r.setAttribute("required", "true"));
                                    feedbackBox.setAttribute("required", "true");
                                });
                            
                                $('input:radio[name="deactivate_plugin"]').click(function () {
                                    var reason = $(this).val();
                                    var feedbackBox = $('#query_feedback');
                                
                                    feedbackBox.removeAttr('required');
                                
                                    if (reason === "Facing issues during configuration" || reason === "Redirecting back to login page after Authentication" || reason === "Bugs in the plugin" ) {
                                        feedbackBox.attr("placeholder", "Please describe the issue you faced and when it occurs.");
                                    } else if (reason === "Does not have the features I'm looking for") {
                                        feedbackBox.attr("placeholder", "Which feature were you expecting but couldn't find?");
                                    } else if (reason === "Not able to Configure" || reason === "Confusing Interface" ) {
                                        feedbackBox.attr("placeholder", "What part was confusing or difficult to set up?");
                                    } else if(reason === "Not Working"){
                                        feedbackBox.attr("placeholder", "Which feature is not working and what happens instead?");
                                    } else if (reason === "Other Reasons:") {
                                        feedbackBox.attr("placeholder", "Please tell us why you decided to deactivate the plugin.");
                                        feedbackBox.prop('required', true);
                                    }
                                });
                            });

                            </script>

                            <style type="text/css">
                                .form-style-6 {
                                    font: 95% Arial, Helvetica, sans-serif;
                                    max-width: 400px;
                                    margin: 10px auto;
                                    padding: 16px;
                                    background: #F7F7F7;
                                }
                            
                                .form-style-6 h1 {
                                    background: #1F3047;
                                    padding: 20px 0;
                                    font-size: 140%;
                                    font-weight: 300;
                                    text-align: center;
                                    color: #fff;
                                    margin: -16px -16px 16px -16px;
                                }
                            
                                .form-style-6 input[type="text"],
                                .form-style-6 input[type="email"],
                                .form-style-6 textarea,
                                .form-style-6 select {
                                    transition: all 0.3s ease-in-out;
                                    box-sizing: border-box;
                                    width: 100%;
                                    background: #fff;
                                    margin-bottom: 4%;
                                    border: 1px solid #ccc;
                                    padding: 3%;
                                    color: #555;
                                    font: 95% Arial, Helvetica, sans-serif;
                                }
                            
                                .form-style-6 input[type="text"]:focus,
                                .form-style-6 input[type="email"]:focus,
                                .form-style-6 textarea:focus,
                                .form-style-6 select:focus {
                                    box-shadow: 0 0 5px #4D79B3;
                                    border: 1px solid #1F3047;
                                }
                            
                                .form-style-6 input[type="submit"],
                                .form-style-6 input[type="button"] {
                                    width: 100%;
                                    padding: 3%;
                                    background: #1F3047;
                                    color: #fff;
                                    border: none;
                                    cursor: pointer;
                                }
                            
                                .form-style-6 input[type="submit"]:hover,
                                .form-style-6 input[type="button"]:hover {
                                    background: #4D79B3;
                                }
                                .feedback-title {
                                    position: relative;
                                }
                                .close-x {
                                    position: absolute;
                                    top: 50%;
                                    right: 15px;
                                    transform: translateY(-50%);
                                    background: transparent;
                                    border: none;
                                    color: #fff;
                                    font-size: 22px;
                                    font-weight: bold;
                                    cursor: pointer;
                                    padding: 0;
                                }
                                .close-x:hover {
                                    color: #ffdddd;
                                }
                            </style>

                            <?php
                            exit;
                        }
                    }
                }
            }
        }
    }
	
    function generateRandomString() 
    {  
        $tokenLength = MoOAuthServerUtility::miniOauthFetchDb('#__miniorange_oauthserver_config',array("id"=>'1'),'loadResult','token_length');
        $tokenLength=intval($tokenLength);
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $tokenLength; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

    public static function getDBObject()
    {
        $app = Factory::getApplication();
        if (method_exists($app, 'getDatabase')) {
            return $app->getDatabase(); // J4+
        }

        return Factory::getDbo(); 
    }
	
}

