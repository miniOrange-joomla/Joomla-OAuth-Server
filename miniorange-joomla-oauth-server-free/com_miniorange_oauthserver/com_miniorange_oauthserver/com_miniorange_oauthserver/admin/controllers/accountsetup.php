<?php
/**
 * @package    Joomla.Administrator
 * @subpackage com_miniorange_oauthserver
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     joomlasupport@xecurify.com
 */ 

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

jimport('miniorangeoauthserver.utility.MoOAuthServerUtility');

class miniorangeoauthserverControllerAccountSetup extends FormController
{
    function __construct()
    {
        $this->view_list = 'accountsetup';
        parent::__construct();
    }
   
    function moOAuthRequestForDemoPlan()
    {
        $app   = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;

        $post = $input->post->getArray();
        
        if(count($post)==0) {
            $this->setRedirect('index.php?option=com_miniorange_oauthserver&view=accountsetup');
            return;
        }

        $email          = isset($post['email']) ? trim(strip_tags($post['email'])) : '';
        $plan           = isset($post['plan']) ? trim(strip_tags($post['plan'])) : '';
        $description    = isset($post['description']) ? trim(strip_tags($post['description'])) : '';
        $customer       = new MoOauthServerCustomer();
 
        $response = json_decode($customer->mo_oauth_request_for_demo($email, $plan, $description));

        if($response->status != 'ERROR') {
            $this->setRedirect('index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=requestdemo', Text::_('COM_MINIORANGE_OAUTHSERVER_REQUEST_DEMO_SUCCESS'));
        } else {
            $this->setRedirect('index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=requestdemo', Text::_('COM_MINIORANGE_OAUTHSERVER_REQUEST_DEMO_ERROR'), 'error');
        }
    }
    
    
    function addclient()
    {
        $app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = $input->post->getArray();
        $user = Factory::getUser();
        $client_id = miniorangeoauthserverControllerAccountSetup::generateRandomString(30);
        $client_secret= miniorangeoauthserverControllerAccountSetup::generateRandomString(30);
        $authorized_uri=trim($post['mo_oauth_client_redirect_url'], " ");
        // Fields to update.
        $fields = array(
        'client_name' => $post['mo_oauth_custom_client_name'],
        'client_id'=>$client_id,
        'client_secret' => $client_secret,
        'authorized_uri' => $authorized_uri,
        'client_count' =>1
        );
             
        // Conditions for which records should be updated.
        $conditions = array(
        'id' => 1
        );

        MoOAuthServerUtility::generic_update_query("#__miniorange_oauthserver_config", $fields, $conditions);
        MoOAuthServerUtility::plugin_efficiency_check($user->get('email'), $post['mo_oauth_custom_client_name'], $authorized_uri, "Added Client information");
        $this->setRedirect('index.php?option=com_miniorange_oauthserver&tab-panel=configuration&pa=2', Text::_('COM_MINIORANGE_OAUTHSERVER_CLIENT_ADDED_SUCCESSFULLY'));    
    }
    
    function deleteclient()
    {
            
        // Fields to update.
        $fields = array(
        'client_name' =>null,
        'client_id' =>null,
        'client_secret' =>null,
        'authorized_uri' =>null,
        'client_count' =>0,
                
        );
             
        // Conditions for which records should be updated.
        $conditions = array(
        'id'=> 1
        );
        MoOAuthServerUtility::generic_update_query("#__miniorange_oauthserver_config", $fields, $conditions);
            
        $this->setRedirect('index.php?option=com_miniorange_oauthserver&tab-panel=configuration', Text::_('COM_MINIORANGE_OAUTHSERVER_CLIENT_DELETE_SUCCESSFULLY'));
        
    }
        
        
    function generateRandomString($length=30)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    function updateclient()
    {
        $app   = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;

        $post = $input->post->getArray();        
        $authorized_uri=trim($post['mo_oauth_client_redirect_url'], " ");
        // Fields to update.
        $fields = array(
        'authorized_uri'=>$authorized_uri,
        );
        // Conditions for which records should be updated.
        $conditions = array(
        'id' => 1
        );
        MoOAuthServerUtility::generic_update_query("#__miniorange_oauthserver_config", $fields, $conditions);

        $this->setRedirect('index.php?option=com_miniorange_oauthserver&tab-panel=configuration&pa=2', Text::_('COM_MINIORANGE_OAUTHSERVER_CLIENT_UPDATE_SUCCESSFULLY'));
    }
    
    function moOAuthContactUs()
    {
        $app   = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        
        $post = $input->post->getArray();

        $query_email=$post['query_email'];
        $query=$post['query'];
        
        if(MoOAuthServerUtility::check_empty_or_null($query_email) || MoOAuthServerUtility::check_empty_or_null($query) ) {
            $this->setRedirect('index.php?option=com_miniorange_oauthserver&view=accountsetup', Text::_('COM_MINIORANGE_OAUTHSERVER_QUERY_EMAIL_ERROR'), 'error');
            return;
        } 
        else
        {
            if(isset($post['mo_oauthserver_select_plan'])) {
                $query = " <br> [mo_oauthserver_select_plan]:  ".$post['mo_oauthserver_select_plan']." <br> [number_of_users]:  ".$post['number_of_users']." <br> [Query]: ".$post['query'];
            }
            
            $phone =  $post['country_code'] . ' ' . $post['query_phone'];
            $contact_us = new MoOauthServerCustomer();
            $submited = json_decode($contact_us->submit_contact_us($query_email, $phone, $query), true);
            if(json_last_error() == JSON_ERROR_NONE) {
                if(is_array($submited) && array_key_exists('status', $submited) && $submited['status'] == 'ERROR') {
                    $this->setRedirect('index.php?option=com_miniorange_oauthserver&view=accountsetup', $submited['message'], 'error');
                }else{
                    if ($submited == false ) {
                        $this->setRedirect('index.php?option=com_miniorange_oauthserver&view=accountsetup', Text::_('COM_MINIORANGE_OAUTHSERVER_QUERY_ERROR'), 'error');
                    } else {
                        
                        if(isset($post['mo_oauthserver_select_plan'])) {
                        
                            $this->setRedirect('index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=requestdemo', Text::_('COM_MINIORANGE_OAUTHSERVER_QUERY_RESPONSE'));
                        
                        }else{
                            
                            $this->setRedirect('index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=requestdemo', Text::_('COM_MINIORANGE_OAUTHSERVER_QUERY_RESPONSE2'));
                            
                        }
                    
                    }
                }
            }

        }
    }

    public function exportConfiguration()
    {
        // Define single or multiple table names here
        $tableNames = [
            '#__miniorange_oauthserver_customer',
            '#__miniorange_oauthserver_config',
        ];

        MoOAuthServerUtility::exportData($tableNames);
    }
}
