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
            $radio=isset($post['deactivate_plugin']) ? $post['deactivate_plugin'] : '' ;
            $data=isset($post['miniorange_skip_feedback']) ? $post['query_feedback'] . '  ...Skipped' : $post['query_feedback'];
            $feedback_email = isset($post['feedback_email']) ? $post['feedback_email'] : '';
            $db = Factory::getDbo();
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
            $current_user =  Factory::getUser();
            //$result = Utilities::getCustomerDetails();
            $db = Factory::getDbo();
            $query = $db->getQuery(true);
            $query->select(array('*'));
            $query->from($db->quoteName('#__miniorange_oauthserver_customer'));
            $query->where($db->quoteName('id')." = 1");
            $db->setQuery($query);
            $customerResult = $db->loadAssoc();
            $admin_email = (isset($customerResult['email']) && !empty($customerResult['email'])) ? $customerResult['email'] : $feedback_email;
            $admin_phone = $customerResult['admin_phone'];
            $data1 = $radio.' : '.$data;
            require_once JPATH_BASE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_oauthserver' . DIRECTORY_SEPARATOR . 'helpers' .DIRECTORY_SEPARATOR . 'mo_customer_setup.php';
            MoOauthServerCustomer::submit_feedback_form($admin_email,$admin_phone,$data1);
            require_once JPATH_SITE . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Installer' .DIRECTORY_SEPARATOR . 'Installer.php';
			foreach ($post['result'] as $fbkey) 
            {
                $db = Factory::getDbo();
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
                    $installer->uninstall ($type,$identifier,$cid);
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
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('extension_id');
        $query->from('#__extensions');
        $query->where($db->quoteName('name') . " = " . $db->quote('COM_MINIORANGE_OAUTHSERVER' ));
        $db->setQuery($query);
        $result = $db->loadColumn();
        $tables = Factory::getDbo()->getTableList();
        $tab=0;
        foreach ($tables as $table) 
        {
            if(strpos($table,"miniorange_oauthserver_customer"))
                $tab=$table;
        }
        if($tab) 
        {
            $db = Factory::getDbo();
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
                                <h1>Feedback Form for OAuth Server</h1>
                                <h3>What Happened?</h3>

                                <form name="f" method="post" action="" id="mojsp_feedback">
                                    <input type="hidden" name="mojsp_feedback" value="mojsp_feedback"/>

                                    <?php
                                    $deactivate_reasons = array(
                                        "Facing issues during configuration",
                                        "Does not have the features I'm looking for",
                                        "Not able to Configure",
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
                                    <textarea id="query_feedback" name="query_feedback" rows="4" style="margin-left:2%"
                                              cols="50" placeholder="Write your query here"></textarea>
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
                                        <input type="submit" id="skipBtn" name="miniorange_skip_feedback"
                                               class="button button-primary button-large" value="Skip"/>
                                    </div>
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
                                
                                    // Initially remove all required attributes
                                    emailField.removeAttribute("required");
                                    radioButtons.forEach(r => r.removeAttribute("required"));
                                
                                    // When user clicks Skip → no required validation
                                    skipBtn.addEventListener("click", function () {
                                        emailField.removeAttribute("required");
                                        radioButtons.forEach(r => r.removeAttribute("required"));
                                        document.getElementById("query_feedback").removeAttribute("required");
                                    });
                                
                                    // If user clicks Submit → email required
                                    submitBtn.addEventListener("click", function () {
                                        emailField.setAttribute("required", "required");
                                        radioButtons.forEach(r => r.setAttribute("required", "required"));
                                    });
                                
                                    // Handle reason selection for placeholder updates
                                    $('input:radio[name="deactivate_plugin"]').click(function () {
                                        var reason = $(this).val();
                                        var feedbackBox = $('#query_feedback');
                                        feedbackBox.removeAttr('required');
                                    
                                        if (reason === "Facing issues during configuration") {
                                            feedbackBox.attr("placeholder", "Can you please describe the issue in detail?");
                                        } else if (reason === "Does not have the features I'm looking for") {
                                            feedbackBox.attr("placeholder", "Let us know what feature you are looking for");
                                        } else if (reason === "Not able to Configure") {
                                            feedbackBox.attr("placeholder", "Not able to configure? Let us know so that we can improve the interface");
                                        } else if (reason === "Other Reasons:") {
                                            feedbackBox.attr("placeholder", "Can you let us know the reason for deactivation");
                                            feedbackBox.prop('required', true);
                                        }
                                    });
                                });
                            </script>
                            <style type="text/css">
                                .form-style-6{
                                    font: 95% Arial, Helvetica, sans-serif;
                                    max-width: 400px;
                                    margin: 10px auto;
                                    padding: 16px;
                                    background: #F7F7F7;
                                }
                                .form-style-6 h1{
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
		
}

