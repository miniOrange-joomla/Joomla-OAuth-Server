<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;

class MoOAuthServerUtility
{
	public static function isCustomerRegistered()
	{
		$db = self::getDBObject();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__miniorange_oauthserver_customer'));
		$query->where($db->quoteName('id') . ' = 1');

		$db->setQuery($query);
		$result = $db->loadAssoc();
		$email       = $result['email'];
		$customerKey = self::decodeStoredCredential($result['customer_key']);
		$status      = $result['registration_status'];

		if ($email && $customerKey && is_numeric(trim($customerKey)) && $status === 'SUCCESS')
		{
			return 1;
		}

		return 0;
	}

	public static function getPluginVersion()
	{
		$db = self::getDBObject();
		$dbQuery = $db->getQuery(true)
			->select('manifest_cache')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('element') . ' = ' . $db->quote('com_miniorange_oauthserver'));
		$db->setQuery($dbQuery);
		$manifest = json_decode($db->loadResult());

		return $manifest->version;
	}

	private static function decodeStoredCredential($value)
	{
		$value = (string) $value;

		if ($value === '')
		{
			return '';
		}

		$decoded = base64_decode($value, true);

		if ($decoded === false)
		{
			return $value;
		}

		return $decoded;
	}

	public static function buildNotifyApiAuthHeaders()
	{
		$customerDetails = self::getCustomerDetails();
		$customerKey = isset($customerDetails['customer_key']) ? self::decodeStoredCredential($customerDetails['customer_key']) : '';
		$apiKey = isset($customerDetails['api_key']) ? self::decodeStoredCredential($customerDetails['api_key']) : '';

		if ($customerKey === '' || $apiKey === '')
		{
			return false;
		}

		$currentTimeInMillis = round(microtime(true) * 1000);
		$stringToHash = $customerKey . number_format($currentTimeInMillis, 0, '', '') . $apiKey;
		$hashValue = hash('sha512', $stringToHash);

		return [
			'customerKey' => $customerKey,
			'headers'     => [
				'Content-Type: application/json',
				'Customer-Key: ' . $customerKey,
				'Timestamp: ' . number_format($currentTimeInMillis, 0, '', ''),
				'Authorization: ' . $hashValue,
			],
		];
	}

	public static function genericUpdateQuery($databaseName, $updatefieldsarray, $condition = true)
	{
		$db = self::getDBObject();
		$query = $db->getQuery(true);
		$databaseFields = [];

		foreach ($updatefieldsarray as $key => $value)
		{
			$databaseFields[] = $db->quoteName($key) . ' = ' . $db->quote($value);
		}

		$query->update($db->quoteName($databaseName))->set($databaseFields);

		if ($condition !== true)
		{
			foreach ($condition as $key => $value)
			{
				$query->where($db->quoteName($key) . ' = ' . $db->quote($value));
			}
		}

		$db->setQuery($query);
		$db->execute();
	}

	public static function checkEmptyOrNull($value)
	{
		if (!isset($value) || empty($value))
		{
			return true;
		}

		return false;
	}

	public static function isCurlInstalled()
	{
		if (in_array('curl', get_loaded_extensions()))
		{
			return 1;
		}

		return 0;
	}

	public static function applySecureCurlOptions($ch)
	{
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_ENCODING, '');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	}

	public static function sendNotifyApiRequest($fields)
	{
		if (!self::isCurlInstalled())
		{
			return [
				'success' => false,
				'content' => json_encode([
					'status'        => 'CURL_ERROR',
					'statusMessage' => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.',
					]
				),
			];
		}

		$auth = self::buildNotifyApiAuthHeaders();

		if ($auth === false)
		{
			return [
				'success' => false,
				'content' => json_encode([
					'status'        => 'ERROR',
					'statusMessage' => 'Notification API credentials are not configured.',
					'message'       => 'Notification API credentials are not configured.',
					]
				),
			];
		}

		$url = self::getHostname() . '/moas/api/notify/send';
		$ch = curl_init($url);
		self::applySecureCurlOptions($ch);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $auth['headers']);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		$content = curl_exec($ch);

		if (curl_errno($ch))
		{
			$error = curl_error($ch);
			curl_close($ch);

			return [
				'success' => false,
				'content' => json_encode([
					'status'  => 'ERROR',
					'message' => 'Request Error:' . $error,
					]
				),
			];
		}

		curl_close($ch);

		return [
			'success' => true,
			'content' => $content,
		];
	}

	public static function setOAuthResponseParamsCookie($oauthResponseParams)
	{
		$app = Factory::getApplication();
		$secure = method_exists($app, 'isHttps') && $app->isHttps();

		setcookie(
			'response_params',
			json_encode($oauthResponseParams),
			[
				'expires'  => time() + 3600,
				'path'     => '/',
				'secure'   => $secure,
				'httponly' => true,
				'samesite' => 'Lax',
			]
		);
	}

	public static function clearOAuthResponseParamsCookie()
	{
		$app = Factory::getApplication();
		$secure = method_exists($app, 'isHttps') && $app->isHttps();

		setcookie(
			'response_params',
			'',
			[
				'expires'  => time() - 3600,
				'path'     => '/',
				'secure'   => $secure,
				'httponly' => true,
				'samesite' => 'Lax',
			]
		);
	}

	public static function getHostname()
	{
		return 'https://login.xecurify.com';
	}

	public static function getCustomerDetails()
	{
		$db = self::getDBObject();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__miniorange_oauthserver_customer'));
		$query->where($db->quoteName('id') . ' = 1');

		$db->setQuery($query);
		$customerDetails = $db->loadAssoc();

		return $customerDetails;
	}

	public static function miniOauthFetchDb($tableName, $condition = true, $method = 'loadAssoc', $columns = '*')
	{
		$db = self::getDBObject();
		$query = $db->getQuery(true);
		$columns = is_array($columns) ? $db->quoteName($columns) : $columns;
		$query->select($columns);
		$query->from($db->quoteName($tableName));

		if ($condition !== true)
		{
			foreach ($condition as $key => $value)
			{
				$query->where($db->quoteName($key) . ' = ' . $db->quote($value));
			}
		}

		$db->setQuery($query);

		if ($method === 'loadColumn')
		{
			return $db->loadColumn();
		}
		elseif ($method === 'loadObjectList')
		{
			return $db->loadObjectList();
		}
		elseif ($method === 'loadObject')
		{
			return $db->loadObject();
		}
		elseif ($method === 'loadResult')
		{
			return $db->loadResult();
		}
		elseif ($method === 'loadRow')
		{
			return $db->loadRow();
		}
		elseif ($method === 'loadRowList')
		{
			return $db->loadRowList();
		}
		elseif ($method === 'loadAssocList')
		{
			return $db->loadAssocList();
		}

		return $db->loadAssoc();
	}

	public static function pluginEfficiencyCheck($email, $appname, $baseUrl, $reason = 'null', $workflowStage = '')
	{
		$auth = self::buildNotifyApiAuthHeaders();

		if ($auth === false)
		{
			return json_encode(['status' => 'Error', 'message' => 'Notification API credentials are not configured.']);
		}

		$customerKey = $auth['customerKey'];
		$fromEmail   = $email;
		$subject             = 'miniOrange Joomla OAuth Server [Free] for Efficiency';

		$h = static function ($v) {
			return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
		};
		$serverName = isset($_SERVER['SERVER_NAME']) ? (string) $_SERVER['SERVER_NAME'] : '';
		$when = $h(date('c'));
		$stageLine = ($workflowStage !== '' && $workflowStage !== null)
			? '<br><br><b>Workflow stage:</b> ' . $h($workflowStage)
			: '';
		$query1 = ' miniOrange Joomla [Free] Oauth Server to improve efficiency ';
		$content = '<div>Hello,<br><br><b>Site / company:</b> <a href="' . $h($serverName) . '" target="_blank">' . $h($serverName) . '</a><br>'
			. '<b>Event time (server):</b> ' . $when
			. $stageLine
			. '<br><br><b>OAuth client (application) name:</b> ' . $h($appname)
			. '<br><br><b>Contact / context identifier:</b> <a href="mailto:' . $h($fromEmail) . '" target="_blank">' . $h($fromEmail) . '</a>'
			. '<br><br><b>Plugin efficiency check:</b> ' . $h($query1)
			. '<br><br><b>Redirect URI (context):</b> ' . $h($baseUrl)
			. '<br><br><b>Status / details:</b> ' . $h($reason)
			. '</div>';

		$fields = [
			'customerKey' => $customerKey,
			'sendEmail'   => true,
			'email'       => [
				'customerKey' => $customerKey,
				'fromEmail'   => 'joomlasupport@xecurify.com',
				'fromName'    => 'miniOrange',
				'bccEmail'    => 'nikhil.bhot@xecurify.com',
				'toEmail'     => 'nutan.barad@xecurify.com',
				'toName'      => 'nutan.barad@xecurify.com',
				'subject'     => $subject,
				'content'     => $content,
			],
		];
		$result = self::sendNotifyApiRequest($fields);

		if (!$result['success'])
		{
			return $result['content'];
		}

		return json_encode(['status' => 'success']);
	}

	public static function exportData($tableNames)
	{
		$db = self::getDBObject();
		$jsonData = [];

		if (empty($tableNames))
		{
			$jsonData['error'] = 'No table names provided.';
		}
		else
		{
			foreach ($tableNames as $tableName)
			{
				$query = $db->getQuery(true);
				$query->select('*')
					->from($db->quoteName($tableName));

				$db->setQuery($query);

				try
				{
					$data = $db->loadObjectList();

					if (empty($data))
					{
						$jsonData[$tableName] = ['message' => 'This table is empty.'];
					}
					else
					{
						$jsonData[$tableName] = $data;
					}
				}
				catch (\Exception $e)
				{
					$jsonData[$tableName] = ['error' => $e->getMessage()];
				}
			}
		}

		header('Content-Type: application/json');
		header('Content-Disposition: attachment; filename="exported_data.json"');

		echo json_encode($jsonData, JSON_PRETTY_PRINT);
		Factory::getApplication()->close();
	}

	public static function handleOAuthCodeRequest()
	{
		$customerResult = self::miniOauthFetchDb('#__miniorange_oauthserver_config', ['id' => '1'], 'loadAssoc', '*');
		$app = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$get = $input->get->getArray();
		$OAuthClientAppName = $customerResult['client_name'];

		if (isset($get['client_id']) && !isset($get['client_secret']))
		{
			if (isset($customerResult['client_id']) && $customerResult['client_id'] === $get['client_id'] && isset($customerResult['authorized_uri']) && $customerResult['authorized_uri'] === $get['redirect_uri'])
			{
				// Get current session vars
				$session = self::getSession();

				// Get the user object
				$user = self::getUser();

				// Get the application
				$app  = Factory::getApplication();
				$clientId = $get['client_id'];
				$scope = $get['scope'];
				$redirectUri = $get['redirect_uri'];
				$responseType = $get['response_type'];
				$state = $get['state'];

				if ($user->id != '')
				{
					$user = self::getUser();
					$isroot = $user->authorise('core.admin');
					$customerResult = self::miniOauthFetchDb('#__users', ['id' => $user->id], 'loadAssoc', '*');

					if ($isroot)
					{
						$redirecturi = $redirectUri;
						$randcode = self::generateRandomString();
						$userId = $user->id;
						$fields = [
							'rancode' => $randcode,
						];
						$conditions = [
							'id' => $userId,
						];
						self::genericUpdateQuery('#__users', $fields, $conditions);

						$state = $get['state'];
						$ssoUrl = $redirecturi;
						$ssoUrl = explode('/', $ssoUrl)[2];
						$ssoUrl = 'https://' . $ssoUrl;
						$redirecturi = $redirecturi . '?code=' . $randcode . '&state=' . $state;

						// Show Consent Form
						self::pluginEfficiencyCheck($customerResult['email'], $OAuthClientAppName, $redirectUri, 'Success: Authorization code generated and client redirected (administrator auto-login path; consent step skipped).', 'OAuth: Authorize (GET /authorize)');
						self::showConsentForm($userId, $clientId, $scope, $ssoUrl, $state, $OAuthClientAppName, $redirecturi);
						header('Location: ' . $redirecturi);
						exit;
					}

					$session = self::getSession();
					$session->destroy();
				}

				$oauthResponseParams = ['client_id' => $clientId, 'scope' => $scope, 'redirect_uri' => $redirectUri, 'response_type' => $responseType, 'state' => $state, 'clientName' => $OAuthClientAppName];
				$msg = 'Only admins will have complete SSO (auto login) in free version. Inorder to auto login for normal users please upgrade to premium';
				self::setOAuthResponseParamsCookie($oauthResponseParams);
				$modifiedUrl = Uri::base();
				$modifiedUrl = str_replace('api/', '', $modifiedUrl);
				$redirectUrl = $modifiedUrl . 'index.php?option=com_users&view=login';
				$app->enqueueMessage($msg, 'notice');
				$app->redirect(Route::_($redirectUrl, false));
			}
			else
			{
				// Send back error for authorization
				$redirectUri = isset($get['redirect_uri']) ? $get['redirect_uri'] : null;
				self::pluginEfficiencyCheck($customerResult['client_id'], $OAuthClientAppName, $redirectUri, 'Error: Invalid redirect_uri or client_id on authorize request (client credentials do not match stored application).', 'OAuth: Authorize (GET /authorize)');
				$apiResponse = ['error' => 'Invalid Redirect Uri (or) Invalid Client ID'];
				header('Content-Type: application/json');
				echo json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
				exit;
			}
		}
	}

	public static function handleOAuthTokenRequest()
	{
		$customerResult = self::miniOauthFetchDb('#__miniorange_oauthserver_config', ['id' => '1'], 'loadAssoc', '*');
		$app   = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$post = $input->post->getArray();

		// TO get the client credentials from header
		$clientId = isset($post['client_id']) ? $post['client_id'] : '';
		$clientSecret = isset($post['client_secret']) ? $post['client_secret'] : '';
		$credentials = '';

		foreach (getallheaders() as $name => $value)
		{
			if ($name === 'Authorization')
			{
				$credentials = $value;
				break;
			}
		}

		if ($clientId === '' && strpos($credentials, 'Basic ') === 0)
		{
			// Remove "Basic " prefix
			$encodedCredentials = substr($credentials, 6);

			// Decode Base64
			$decodedCredentials = base64_decode($encodedCredentials);

			list($clientId, $clientSecret) = explode(':', $decodedCredentials, 2);
		}

		if ($clientId != '' && $clientSecret != '')
		{
			if ($customerResult['client_id'] === $clientId && $customerResult['authorized_uri'] === $post['redirect_uri'] && $customerResult['client_secret'] === $clientSecret)
			{
				if ($post['grant_type'] != 'authorization_code')
				{
					$gt = isset($post['grant_type']) ? (string) $post['grant_type'] : '(missing)';
					self::pluginEfficiencyCheck($customerResult['client_id'], $customerResult['client_name'], isset($post['redirect_uri']) ? $post['redirect_uri'] : $customerResult['authorized_uri'], 'Error: grant_type must be authorization_code (received: ' . $gt . ').', 'OAuth: Token (POST /token)');
					$apiResponse = ['error' => 'grantTypes mismatch or limited,please contact your administrator'];
					header('Content-Type: application/json');
					echo json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
					exit;
				}

				$randcode = self::generateRandomString();
				$code = $post['code'];

				// Getting the user details using code parameter
				$results = self::miniOauthFetchDb('#__users', ['rancode' => $code], 'loadAssoc');

				if (!empty($results) && isset($results['id']) && $results['id'] != '')
				{
					$t = time() + 3600;
					$time = 3600;

					// Inserting the accessToken
					$fields = [
						'client_token' => $randcode,
						'time_stamp'   => $t,
					];
					$conditions = [
						'id' => $results['id'],
					];

					self::genericUpdateQuery('#__users', $fields, $conditions);
					$idToken = self::generateIdToken($results, $clientId, $customerResult['client_secret']);
					$scope = 'openid';
					$tokenType = 'Bearer';
					$apiResponse = ['access_token' => $randcode, 'expires_in' => $time, 'scope' => $scope, 'token_type' => $tokenType, 'id_token' => $idToken];
					$notifyEmail = !empty($results['email']) ? $results['email'] : (isset($results['username']) ? $results['username'] : $clientId);
					self::pluginEfficiencyCheck($notifyEmail, $customerResult['client_name'], $customerResult['authorized_uri'], 'Success: Access token and ID token issued (authorization_code exchanged; token valid for expires_in seconds).', 'OAuth: Token (POST /token)');
					header('Content-Type: application/json');
					echo json_encode($apiResponse, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
					exit;
				}

				self::pluginEfficiencyCheck($clientId, $customerResult['client_name'], $customerResult['authorized_uri'], 'Error: Authorization code not found or already used (no user row matching submitted code).', 'OAuth: Token (POST /token)');
				$apiResponse = ['error' => 'Some Error with code recived,please contact your administrator'];
				header('Content-Type: application/json');
				echo json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
				exit;
			}

			self::pluginEfficiencyCheck($clientId, $customerResult['client_name'], isset($post['redirect_uri']) ? $post['redirect_uri'] : $customerResult['authorized_uri'], 'Error: client_id, client_secret, or redirect_uri does not match the configured OAuth application.', 'OAuth: Token (POST /token)');
			$apiResponse = ['error' => 'Some Error at Token Endpoint URL,please contact your administrator'];
			header('Content-Type: application/json');
			echo json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			exit;
		}
	}

	public static function handleOAuthUserInfoRequest()
	{
		$app   = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;

		$lang = $app->getLanguage();
		$lang->load(
			'lib_miniorangeoauthserver',
			JPATH_ROOT
		);

		$post = $input->post->getArray();
		$oauthCfg = self::miniOauthFetchDb('#__miniorange_oauthserver_config', ['id' => '1'], 'loadAssoc', '*');
		$cfgClientId = isset($oauthCfg['client_id']) ? $oauthCfg['client_id'] : '';
		$cfgName = isset($oauthCfg['client_name']) ? $oauthCfg['client_name'] : 'OAuth Server';
		$cfgUri = isset($oauthCfg['authorized_uri']) ? $oauthCfg['authorized_uri'] : '';

		$accessToken = isset($post['access_token']) ? $post['access_token'] : '';

		if ($accessToken === '')
		{
			foreach (getallheaders() as $name => $value)
			{
				if (strtolower($name) === 'authorization')
				{
					$accessToken = $value;
					break;
				}
			}

			$parts = explode(' ', $accessToken, 2);
			$accessToken = (count($parts) >= 2 && strtolower($parts[0]) === 'bearer') ? trim($parts[1]) : '';
		}

		$db = self::getDBObject();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(['id', 'time_stamp']));
		$query->from($db->quoteName('#__users'));
		$query->where($db->quoteName('client_token') . ' =' . $db->quote($accessToken));
		$db->setQuery($query);
		$results = $db->loadAssoc();

		if (empty($results) || !isset($results['id']))
		{
			self::pluginEfficiencyCheck($cfgClientId, $cfgName, $cfgUri, 'Error: UserInfo called with missing, malformed, or unknown access token (no matching session).', 'OAuth: UserInfo (GET/POST /userinfo)');
			$apiResponse = ['error' => Text::_('LIB_MINIORANGEOAUTHSERVER_ACCESS_TOKEN_INVALID')];
			header('Content-Type: application/json');
			echo json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			exit;
		}

		if ($results['time_stamp'] < time())
		{
			self::pluginEfficiencyCheck($cfgClientId, $cfgName, $cfgUri, 'Error: Access token expired before UserInfo call (user id ' . $results['id'] . ').', 'OAuth: UserInfo (GET/POST /userinfo)');
			$apiResponse = ['error' => Text::_('LIB_MINIORANGEOAUTHSERVER_ACCESS_TOKEN_EXPIRED')];
			header('Content-Type: application/json');
			echo json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			exit;
		}

		$query = $db->getQuery(true);
		$query->select($db->quoteName('group_id'));
		$query->from($db->quoteName('#__user_usergroup_map'));
		$query->where($db->quoteName('user_id') . ' =' . $db->quote($results['id']));
		$db->setQuery($query);
		$groups = $db->loadColumn();

		if (!is_array($groups))
		{
			$groups = [];
		}

		$groupsList = '(' . implode(',', $groups) . ')';
		$flag = 0;

		if (strpos($groupsList, '7') !== false || strpos($groupsList, '8') !== false)
		{
			$flag = 1;
		}
		else
		{
			self::pluginEfficiencyCheck($cfgClientId, $cfgName, $cfgUri, 'Error: UserInfo denied - resource owner is not in Administrator group (free version restriction; user id ' . $results['id'] . ').', 'OAuth: UserInfo (GET/POST /userinfo)');
			$apiResponse = ['error' => Text::_('LIB_MINIORANGEOAUTHSERVER_SSO_NOT_ALLOWED')];
			header('Content-Type: application/json');
			echo json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			exit;
		}

		if ($results['id'] != '' && $flag)
		{
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__users'));
			$query->where($db->quoteName('id') . ' =' . $db->quote($results['id']));
			$db->setQuery($query);
			$results = $db->loadAssoc();

			if (empty($results))
			{
				self::pluginEfficiencyCheck($cfgClientId, $cfgName, $cfgUri, 'Error: User record missing after valid token lookup.', 'OAuth: UserInfo (GET/POST /userinfo)');
				$apiResponse = ['error' => Text::_('LIB_MINIORANGEOAUTHSERVER_ACCESS_TOKEN_INVALID')];
				header('Content-Type: application/json');
				echo json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
				exit;
			}

			if (empty(trim($results['email'])))
			{
				$apiResponse = [
					'id'       => $results['id'],
					'username' => $results['username'],
					'email'    => $results['email'],
				];
			}
			else
			{
				$apiResponse = [
					'id'       => $results['id'],
					'username' => $results['email'],
					'email'    => $results['email'],
				];
			}

			$notifyEmail = !empty($results['email']) ? $results['email'] : $results['username'];
			self::pluginEfficiencyCheck($notifyEmail, $cfgName, $cfgUri, 'Success: UserInfo claims returned to the client (id, username/email as per plugin rules).', 'OAuth: UserInfo (GET/POST /userinfo)');
			header('Content-Type: application/json');
			echo json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			exit;
		}

		self::pluginEfficiencyCheck($cfgClientId, $cfgName, $cfgUri, 'Error: UserInfo could not complete after token validation (unexpected state).', 'OAuth: UserInfo (GET/POST /userinfo)');
		$apiResponse = ['error' => 'Access token doesnt match,please contact your administrator'];
		header('Content-Type: application/json');
		echo json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		exit;
	}

	public static function generateRandomString()
	{
		$tokenLength = self::miniOauthFetchDb('#__miniorange_oauthserver_config', ['id' => '1'], 'loadResult', 'token_length');
		$tokenLength = intval($tokenLength);
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';

		for ($i = 0; $i < $tokenLength; $i++)
		{
			$randomString .= $characters[random_int(0, $charactersLength - 1)];
		}

		return $randomString;
	}

	public static function generateIdToken($user, $clientId, $signingSecret = null)
	{
		if ($signingSecret === null || $signingSecret === '')
		{
			$signingSecret = (string) Factory::getConfig()->get('secret');
		}

		$issuer = Uri::root();
		$header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
		$payload = json_encode(
			[
				'sub'      => $user['id'],
				'iss'      => $issuer,
				'aud'      => $clientId,
				'iat'      => time(),
				'exp'      => time() + 3600,
				'email'    => $user['email'],
				'username' => $user['username'],
			]
		);

		$base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
		$base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

		$signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, $signingSecret, true);
		$base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

		return $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
	}

	public static function showConsentForm($userId, $clientId, $scope, $ssoUrl, $state, $clientName, $redirectUri)
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
                    <input type="hidden" name="user_id" value="' . $userId . '">
                    <input type="hidden" name="client_id" value="' . $clientId . '">
                    <input type="hidden" name="redirect_uri" value="' . $redirectUri . '">
                    <input type="hidden" name="state" value="' . $state . '"> ';

		if (!empty($scopes))
		{
			echo '<h4>This application would like to:</h4>
                                <ul class="scope-list">
                                    <li>
                                        <label>
                                            OpenID (Required)
                                        </label>
                                    </li>';

			foreach ($scopes as $scopeItem)
			{
				if ($scopeItem !== 'openid')
				{
					echo '<li>
                                                <label>
                                                    ' . ucfirst($scopeItem) . '
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

                            window.location.href = "' . $redirectUri . '"
                        } else if (action === "deny") {
                            document.body.innerHTML = `
                            <div class="oauth-consent-container">
                                <div class="oauth-header">
                                    <h2>Authorization Denied</h2>
                                </div>
                                <p class="error-message">You have denied access to this application.</p>
                            </div>`;
                            setTimeout(function() {
                                window.location.href = "' . $ssoUrl . '";
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

		if (method_exists($app, 'getDatabase'))
		{
			// J4+
			return $app->getDatabase();
		}

		return Factory::getDbo();
	}

	public static function getSession()
	{
		$app = Factory::getApplication();

		if (method_exists($app, 'getSession'))
		{
			return $app->getSession();
		}

		return Factory::getSession();
	}

	public static function getUser($userId = null)
	{
		$app = Factory::getApplication();

		if (method_exists($app, 'getIdentity'))
		{
			return $userId !== null ? $app->getIdentity($userId) : $app->getIdentity();
		}

		return $userId !== null ? User::getInstance($userId) : Factory::getUser();
	}
}
