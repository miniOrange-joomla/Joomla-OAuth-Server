<?php
/**
 * @package    Joomla.Administrator
 * @subpackage com_miniorange_oauthserver
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

jimport('miniorangeoauthserver.utility.MoOAuthServerUtility');

class MiniorangeoauthserverControllerAccountSetup extends FormController
{
	public function __construct($config = array(), $factory = null, $app = null, $input = null)
	{
		$config['view_list'] = 'accountsetup';

		parent::__construct($config, $factory, $app, $input);
	}

	public function moOAuthRequestForDemoPlan()
	{
		$app   = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;

		$post = $input->post->getArray();

		if (count($post) == 0)
		{
			$this->setRedirect('index.php?option=com_miniorange_oauthserver&view=accountsetup');

			return;
		}

		$email          = isset($post['email']) ? trim(strip_tags($post['email'])) : '';
		$plan           = isset($post['plan']) ? trim(strip_tags($post['plan'])) : '';
		$description    = isset($post['description']) ? trim(strip_tags($post['description'])) : '';
		$customer       = new MoOauthServerCustomer;

		$response = json_decode($customer->moOauthRequestForDemo($email, $plan, $description));

		if ($response->status != 'ERROR')
		{
			$this->setRedirect('index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=requestdemo', Text::_('COM_MINIORANGE_OAUTHSERVER_REQUEST_DEMO_SUCCESS'));
		}
		else
		{
			$this->setRedirect('index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=requestdemo', Text::_('COM_MINIORANGE_OAUTHSERVER_REQUEST_DEMO_ERROR'), 'error');
		}
	}

	public function addclient()
	{
		$app = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$post = $input->post->getArray();
		$user = method_exists($app, 'getIdentity') ? $app->getIdentity() : Factory::getUser();
		$clientId = self::generateRandomString(30);
		$clientSecret = self::generateRandomString(30);
		$authorizedUri = trim($post['mo_oauth_client_redirect_url'], " ");

		// Fields to update.
		$fields = array(
		'client_name' => $post['mo_oauth_custom_client_name'],
		'client_id' => $clientId,
		'client_secret' => $clientSecret,
		'authorized_uri' => $authorizedUri,
		'client_count' => 1
		);

		// Conditions for which records should be updated.
		$conditions = array(
		'id' => 1
		);

		MoOAuthServerUtility::genericUpdateQuery("#__miniorange_oauthserver_config", $fields, $conditions);
		MoOAuthServerUtility::pluginEfficiencyCheck($user->get('email'), $post['mo_oauth_custom_client_name'], $authorizedUri, 'Success: OAuth client application saved (client_id / secret generated; redirect URI registered).', 'Admin: Client configuration');
		$this->setRedirect('index.php?option=com_miniorange_oauthserver&tab-panel=configuration&pa=2', Text::_('COM_MINIORANGE_OAUTHSERVER_CLIENT_ADDED_SUCCESSFULLY'));
	}

	public function deleteclient()
	{

		// Fields to update.
		$fields = array(
		'client_name' => null,
		'client_id' => null,
		'client_secret' => null,
		'authorized_uri' => null,
		'client_count' => 0,

		);

		// Conditions for which records should be updated.
		$conditions = array(
		'id' => 1
		);
		MoOAuthServerUtility::genericUpdateQuery("#__miniorange_oauthserver_config", $fields, $conditions);
		$app = Factory::getApplication();
		$user = method_exists($app, 'getIdentity') ? $app->getIdentity() : Factory::getUser();
		MoOAuthServerUtility::pluginEfficiencyCheck($user->get('email'), '(deleted client)', '', 'Success: OAuth client application removed from Joomla (client_id, secret, and redirect URI cleared).', 'Admin: Client configuration (delete)');

		$this->setRedirect('index.php?option=com_miniorange_oauthserver&tab-panel=configuration', Text::_('COM_MINIORANGE_OAUTHSERVER_CLIENT_DELETE_SUCCESSFULLY'));

	}


	public function generateRandomString($length=30)
	{
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';

		for ($i = 0; $i < $length; $i++)
		{
			$randomString .= $characters[random_int(0, $charactersLength - 1)];
		}

		return $randomString;
	}

	public function updateclient()
	{
		$app   = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;

		$post = $input->post->getArray();
		$authorizedUri = trim($post['mo_oauth_client_redirect_url'], " ");

		// Fields to update.
		$fields = array(
		'authorized_uri' => $authorizedUri,
		);

		// Conditions for which records should be updated.
		$conditions = array(
		'id' => 1
		);
		MoOAuthServerUtility::genericUpdateQuery("#__miniorange_oauthserver_config", $fields, $conditions);
		$user = method_exists($app, 'getIdentity') ? $app->getIdentity() : Factory::getUser();
		$cfg = MoOAuthServerUtility::miniOauthFetchDb('#__miniorange_oauthserver_config', array('id' => '1'), 'loadAssoc', '*');
		$appLabel = isset($cfg['client_name']) ? $cfg['client_name'] : 'OAuth application';
		MoOAuthServerUtility::pluginEfficiencyCheck($user->get('email'), $appLabel, $authorizedUri, 'Success: OAuth client redirect URI updated in administrator configuration.', 'Admin: Client configuration (update)');

		$this->setRedirect('index.php?option=com_miniorange_oauthserver&tab-panel=configuration&pa=2', Text::_('COM_MINIORANGE_OAUTHSERVER_CLIENT_UPDATE_SUCCESSFULLY'));
	}

	public function moOAuthContactUs()
	{
		$app   = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;

		$post = $input->post->getArray();

		$queryEmail = $post['query_email'];
		$query = $post['query'];

		if (MoOAuthServerUtility::checkEmptyOrNull($queryEmail) || MoOAuthServerUtility::checkEmptyOrNull($query))
		{
			$this->setRedirect('index.php?option=com_miniorange_oauthserver&view=accountsetup', Text::_('COM_MINIORANGE_OAUTHSERVER_QUERY_EMAIL_ERROR'), 'error');

			return;
		}
		else
		{
			if (isset($post['mo_oauthserver_select_plan']))
			{
				$query = " <br> [mo_oauthserver_select_plan]:  " . $post['mo_oauthserver_select_plan'] . " <br> [number_of_users]:  " . $post['number_of_users'] . " <br> [Query]: " . $post['query'];
			}

			$phone = $post['country_code'] . ' ' . $post['query_phone'];
			$contactUs = new MoOauthServerCustomer;
			$submited = json_decode($contactUs->submitContactUs($queryEmail, $phone, $query), true);

			if (json_last_error() == JSON_ERROR_NONE)
			{
				if (is_array($submited) && array_key_exists('status', $submited) && $submited['status'] == 'ERROR')
				{
					$this->setRedirect('index.php?option=com_miniorange_oauthserver&view=accountsetup', $submited['message'], 'error');
				}
				else
				{
					if ($submited == false)
					{
						$this->setRedirect('index.php?option=com_miniorange_oauthserver&view=accountsetup', Text::_('COM_MINIORANGE_OAUTHSERVER_QUERY_ERROR'), 'error');
					}
					else
					{
						if (isset($post['mo_oauthserver_select_plan']))
						{
							$this->setRedirect('index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=requestdemo', Text::_('COM_MINIORANGE_OAUTHSERVER_QUERY_RESPONSE'));
						}
						else
						{
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
