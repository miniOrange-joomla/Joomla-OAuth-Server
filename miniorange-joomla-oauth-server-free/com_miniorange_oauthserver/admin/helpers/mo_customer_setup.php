<?php
/** Copyright (C) 2015  miniOrange

 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Version;
jimport('miniorangeoauthserver.utility.MoOAuthServerUtility');

class MoOauthServerCustomer
{
	/**
	 * Customer email address.
	 *
	 * @var string
	 */
	public $email;

	/**
	 * Customer phone number.
	 *
	 * @var string
	 */
	public $phone;

	/**
	 * Customer key.
	 *
	 * @var string
	 */
	public $customerKey;

	/**
	 * Transaction identifier.
	 *
	 * @var string
	 */
	public $transactionId;

	public function moOauthRequestForDemo($email, $plan, $description)
	{
		$auth = MoOAuthServerUtility::buildNotifyApiAuthHeaders();

		if ($auth === false)
		{
			return json_encode(array("status" => 'ERROR', 'message' => 'Notification API credentials are not configured.'));
		}

		$customerKey = $auth['customerKey'];
		$phpVersion 		= phpversion();
		$adminEmail         = self::getApplicationUserEmail($email);
		$currentUserEmail   = $adminEmail;
		$fromEmail 			= $email;
		$jVersion 			= new Version;
		$jCmsVersion 		= $jVersion->getShortVersion();
		$moPluginVersion 	= MoOAuthServerUtility::getPluginVersion();
		$timezone           = self::getUserTimezone();
		$subject            = "Joomla OAuth Server Request for Demo | PHP:" . $phpVersion . " | Joomla: " . $jCmsVersion . " | Plugin: " . $moPluginVersion . " - " . $email;

		$content = '<div>Hello, <br><br>
        <strong>Company : </strong><a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" >' . $_SERVER['SERVER_NAME'] . '</a><br><br>
        <strong>Email : </strong><a href="mailto:' . $fromEmail . '" target="_blank">' . $fromEmail . '</a><br><br>
        <strong>Admin Email : </strong><a href="mailto:' . $adminEmail . '" target="_blank">' . $adminEmail . '</a><br><br>
        <strong>Demo for plugin : </strong>' . $plan . '<br><br>
        <strong>Description: </strong>' . $description . '<br><br>
        <strong>Timezone: </strong>' . $timezone . '</div>';
		$fields = array(
			'customerKey'	=> $customerKey,
			'sendEmail' 	=> true,
			'email' 		=> array(
			'customerKey' 	=> $customerKey,
			'fromEmail' 	=> $fromEmail,
			'fromName' 		=> 'miniOrange',
			'toEmail' 		=> 'joomlasupport@xecurify.com',
			'toName' 		=> 'joomlasupport@xecurify.com',
			'subject' 		=> $subject,
			'content' 		=> $content
			),
		);
		$result = MoOAuthServerUtility::sendNotifyApiRequest($fields);

		return $result['content'];
	}

	public static function submitFeedbackForm($email, $query)
	{
		$auth = MoOAuthServerUtility::buildNotifyApiAuthHeaders();

		if ($auth === false)
		{
			return json_encode(array("status" => 'ERROR', 'message' => 'Notification API credentials are not configured.'));
		}

		$customerKey = $auth['customerKey'];
		$app = Factory::getApplication();

		$adminEmail         = self::getApplicationUserEmail($email);
		$fromEmail          = $email;
		$jVersion           = new Version;
		$phpVersion         = phpversion();
		$jCmsVersion        = $jVersion->getShortVersion();
		$osName = php_uname('s');
		$osRelease = php_uname('r');
		$osArch = php_uname('m');
		$timezone = self::getUserTimezone();

		if (class_exists("MoOAuthServerUtility"))
		{
			$moPluginVersion     = MoOAuthServerUtility::getPluginVersion();
		}
		else
		{
			$moPluginVersion = "NA";
		}

		$subject = "Joomla OAuth Server Free Feedback - " . $email;

		$query1 = "miniOrange Joomla Server [Free] Oauth ";
		$content = '<div>Hello, <br><br>
                        <strong>Company :</strong> <a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" >' . $_SERVER['SERVER_NAME'] . '</a><br><br>
                        <strong>Email : </strong> <a href="mailto:' . $fromEmail . '" target="_blank">' . $fromEmail . '</a><br><br>
                        <strong>Admin Email : </strong><a href="mailto:' . $adminEmail . '" target="_blank">' . $adminEmail . '</a><br><br>
                        <strong>Plugin Deactivated:</strong> ' . $query1 . '<br><br>
                        <strong>Reason:</strong> ' . $query . ' <br><br>
                        <strong>System Information:</strong> Joomla: ' . $jCmsVersion . ' | PHP: ' . $phpVersion . ' | Plugin: ' . $moPluginVersion . ' | OS: ' . $osName . ' ' . $osRelease . ' ' . $osArch . ' | Timezone: ' . $timezone . '</div>';
		;

		$fields = array(
				'customerKey'	=> $customerKey,
				'sendEmail' 	=> true,
				'email' 		=> array(
				'customerKey' 	=> $customerKey,
				'fromEmail' 	=> $fromEmail,
				'fromName' 		=> 'miniOrange',
				'toEmail' 		=> 'joomlasupport@xecurify.com',
				'toName' 		=> 'joomlasupport@xecurify.com',
				'subject' 		=> $subject,
				'content' 		=> $content
			),
		);
		$result = MoOAuthServerUtility::sendNotifyApiRequest($fields);

		return $result['content'];
	}

	public static function sendInstallationNotification()
	{
		$auth = MoOAuthServerUtility::buildNotifyApiAuthHeaders();

		if ($auth === false)
		{
			return json_encode(array("status" => 'CURL_ERROR', 'statusMessage' => 'Notification API credentials are not configured.'));
		}

		$customerKey = $auth['customerKey'];
		$app = Factory::getApplication();
		$adminEmail         = self::getApplicationUserEmail('');

		$jVersion = new Version;
		$phpVersion = phpversion();
		$osName = php_uname('s');
		$osRelease = php_uname('r');
		$osArch = php_uname('m');
		$timezone = self::getUserTimezone();
		$jCmsVersion = $jVersion->getShortVersion();
		$moPluginVersion = MoOAuthServerUtility::getPluginVersion();
		$subject = "Installation of Joomla OAuth Server [Free]";

		$content = '<div> Hello, <br><br>
        <strong>Company: </strong><a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" >' . $_SERVER['SERVER_NAME'] . '</a><br>
        <strong>Admin Email: </strong><a href="mailto:' . $adminEmail . '" target="_blank">' . $adminEmail . '</a><br>
        <strong>System Information: </strong> Joomla ' . $jCmsVersion . ' | PHP ' . $phpVersion . ' | OS ' . $osName . ' ' . $osRelease . ' | Plugin Version ' . $moPluginVersion . ' | Timezone ' . $timezone . '</div>';

		$fields = array(
			'customerKey'    => $customerKey,
			'sendEmail'      => true,
			'email'          => array(
			'customerKey'    => $customerKey,
			'fromEmail'      => 'joomlasupport@xecurify.com',
			'fromName'       => 'miniOrange',
			'toEmail'        => 'nutan.barad@xecurify.com',
			'toName'         => 'nutan.barad@xecurify.com',
			'bccEmail'      => 'nikhil.bhot@xecurify.com',
			'subject'        => $subject,
			'content'        => $content
			),
		);

		$result = MoOAuthServerUtility::sendNotifyApiRequest($fields);

		if (!$result['success'])
		{
			return $result['content'];
		}

		return;
	}

	public function submitContactUs($qEmail, $qPhone, $query)
	{
		$auth = MoOAuthServerUtility::buildNotifyApiAuthHeaders();

		if ($auth === false)
		{
			return json_encode(array("status" => 'ERROR', 'message' => 'Notification API credentials are not configured.'));
		}

		$customerKey = $auth['customerKey'];
		$app = Factory::getApplication();
		$adminEmail         = self::getApplicationUserEmail($qEmail);
		$currentUserEmail   = $adminEmail;
		$jVersion = new Version;
		$phpVersion = phpversion();
		$osName = php_uname('s');
		$osRelease = php_uname('r');
		$osArch = php_uname('m');
		$timezone = self::getUserTimezone();
		$jCmsVersion = $jVersion->getShortVersion();
		$moPluginVersion = MoOAuthServerUtility::getPluginVersion();
		$subject = "Query for Joomla OAuth Server Free - " . $qEmail;
		$query = '[Joomla OAuth Server Free Plugin]: <br> ' . $query;
		$content = '<div >Hello, <br><br>
        <strong>Company</strong> :<a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" >' . $_SERVER['SERVER_NAME'] . '</a><br><br>
        <strong>Phone Number</strong> :' . $qPhone . '<br><br>
        <strong>Admin Email : </strong><a href="mailto:' . $adminEmail . '" target="_blank">' . $adminEmail . '</a><br><br>
        <strong>Email :</strong><a href="mailto:' . $qEmail . '" target="_blank">' . $qEmail . '</a><br><br>
        <strong>Query</strong>: ' . $query . '<br><br>
        <strong>System Information:</strong> Joomla ' . $jCmsVersion . ' | PHP ' . $phpVersion . ' | OS ' . $osName . ' ' . $osRelease . ' ' . $osArch . ' | Plugin Version ' . $moPluginVersion . ' | Timezone ' . $timezone . '</div>';

		$fields = array(
			'customerKey'    => $customerKey,
			'sendEmail'      => true,
			'email'          => array(
			'customerKey'    => $customerKey,
			'fromEmail'      => $qEmail,
			'fromName'       => 'miniOrange',
			'toEmail'        => 'joomlasupport@xecurify.com',
			'toName'         => 'joomlasupport@xecurify.com',
			'subject'        => $subject,
			'content'        => $content
			),
		);

		$result = MoOAuthServerUtility::sendNotifyApiRequest($fields);

		if (!$result['success'])
		{
			return $result['content'];
		}

		return true;
	}

	private static function getApplicationUser()
	{
		$app = Factory::getApplication();
		$user = null;

		if (method_exists($app, 'getIdentity'))
		{
			$user = $app->getIdentity();
		}

		if ($user === null && method_exists($app, 'getUser'))
		{
			$user = $app->getUser();
		}

		if ($user === null)
		{
			$user = Factory::getUser();
		}

		return $user;
	}

	private static function getApplicationUserEmail($fallback = '')
	{
		$user = self::getApplicationUser();

		if ($user !== null && !empty($user->email))
		{
			return $user->email;
		}

		return $fallback;
	}

	public static function getUserTimezone()
	{
		$app = Factory::getApplication();

		try
		{
			if (!empty($_COOKIE['user_tz']))
			{
				$tz = new DateTimeZone($_COOKIE['user_tz']);
				$dt = new DateTime('now', $tz);

				return $dt->format('P') . ' (' . $_COOKIE['user_tz'] . ')';
			}

			$user = self::getApplicationUser();

			if ($user && $user->id)
			{
				$userTz = $user->getParam('timezone');

				if (!empty($userTz))
				{
					$tz = new DateTimeZone($userTz);
					$dt = new DateTime('now', $tz);

					return $dt->format('P');
				}
			}

			$siteTz = method_exists($app, 'getConfig') ? $app->getConfig()->get('offset') : Factory::getConfig()->get('offset');

			if (!empty($siteTz))
			{
				$tz = new DateTimeZone($siteTz);
				$dt = new DateTime('now', $tz);

				return $dt->format('P');
			}
		}
		catch (Exception $e)
		{
			return '+00:00';
		}

		return '+00:00';
	}

	public static function isPluginEnabled($pluginType, $pluginName)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('enabled'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			->where($db->quoteName('folder') . ' = ' . $db->quote($pluginType))
			->where($db->quoteName('element') . ' = ' . $db->quote($pluginName));
		$db->setQuery($query);

		return (bool) $db->loadResult();
	}

	public static function getPhoneCountryCode()
	{
		$countryCodes = [
			["name" => "Afghanistan", "code" => "+93"],
			["name" => "Albania", "code" => "+355"],
			["name" => "Algeria", "code" => "+213"],
			["name" => "Andorra", "code" => "+376"],
			["name" => "Angola", "code" => "+244"],
			["name" => "Argentina", "code" => "+54"],
			["name" => "Armenia", "code" => "+374"],
			["name" => "Australia", "code" => "+61"],
			["name" => "Austria", "code" => "+43"],
			["name" => "Azerbaijan", "code" => "+994"],
			["name" => "Bahamas", "code" => "+1-242"],
			["name" => "Bahrain", "code" => "+973"],
			["name" => "Bangladesh", "code" => "+880"],
			["name" => "Barbados", "code" => "+1-246"],
			["name" => "Belarus", "code" => "+375"],
			["name" => "Belgium", "code" => "+32"],
			["name" => "Belize", "code" => "+501"],
			["name" => "Benin", "code" => "+229"],
			["name" => "Bhutan", "code" => "+975"],
			["name" => "Bolivia", "code" => "+591"],
			["name" => "Bosnia and Herzegovina", "code" => "+387"],
			["name" => "Botswana", "code" => "+267"],
			["name" => "Brazil", "code" => "+55"],
			["name" => "Brunei", "code" => "+673"],
			["name" => "Bulgaria", "code" => "+359"],
			["name" => "Burkina Faso", "code" => "+226"],
			["name" => "Burundi", "code" => "+257"],
			["name" => "Cambodia", "code" => "+855"],
			["name" => "Cameroon", "code" => "+237"],
			["name" => "Canada", "code" => "+1"],
			["name" => "Cape Verde", "code" => "+238"],
			["name" => "Central African Republic", "code" => "+236"],
			["name" => "Chad", "code" => "+235"],
			["name" => "Chile", "code" => "+56"],
			["name" => "China", "code" => "+86"],
			["name" => "Colombia", "code" => "+57"],
			["name" => "Comoros", "code" => "+269"],
			["name" => "Congo", "code" => "+242"],
			["name" => "Costa Rica", "code" => "+506"],
			["name" => "Croatia", "code" => "+385"],
			["name" => "Cuba", "code" => "+53"],
			["name" => "Cyprus", "code" => "+357"],
			["name" => "Czech Republic", "code" => "+420"],
			["name" => "Denmark", "code" => "+45"],
			["name" => "Djibouti", "code" => "+253"],
			["name" => "Dominica", "code" => "+1-767"],
			["name" => "Dominican Republic", "code" => "+1-809"],
			["name" => "Ecuador", "code" => "+593"],
			["name" => "Egypt", "code" => "+20"],
			["name" => "El Salvador", "code" => "+503"],
			["name" => "Equatorial Guinea", "code" => "+240"],
			["name" => "Eritrea", "code" => "+291"],
			["name" => "Estonia", "code" => "+372"],
			["name" => "Eswatini", "code" => "+268"],
			["name" => "Ethiopia", "code" => "+251"],
			["name" => "Fiji", "code" => "+679"],
			["name" => "Finland", "code" => "+358"],
			["name" => "France", "code" => "+33"],
			["name" => "Gabon", "code" => "+241"],
			["name" => "Gambia", "code" => "+220"],
			["name" => "Georgia", "code" => "+995"],
			["name" => "Germany", "code" => "+49"],
			["name" => "Ghana", "code" => "+233"],
			["name" => "Greece", "code" => "+30"],
			["name" => "Grenada", "code" => "+1-473"],
			["name" => "Guatemala", "code" => "+502"],
			["name" => "Guinea", "code" => "+224"],
			["name" => "Guyana", "code" => "+592"],
			["name" => "Haiti", "code" => "+509"],
			["name" => "Honduras", "code" => "+504"],
			["name" => "Hungary", "code" => "+36"],
			["name" => "Iceland", "code" => "+354"],
			["name" => "India", "code" => "+91"],
			["name" => "Indonesia", "code" => "+62"],
			["name" => "Iran", "code" => "+98"],
			["name" => "Iraq", "code" => "+964"],
			["name" => "Ireland", "code" => "+353"],
			["name" => "Israel", "code" => "+972"],
			["name" => "Italy", "code" => "+39"],
			["name" => "Jamaica", "code" => "+1-876"],
			["name" => "Japan", "code" => "+81"],
			["name" => "Jordan", "code" => "+962"],
			["name" => "Kazakhstan", "code" => "+7"],
			["name" => "Kenya", "code" => "+254"],
			["name" => "Kiribati", "code" => "+686"],
			["name" => "Kuwait", "code" => "+965"],
			["name" => "Kyrgyzstan", "code" => "+996"],
			["name" => "Laos", "code" => "+856"],
			["name" => "Latvia", "code" => "+371"],
			["name" => "Lebanon", "code" => "+961"],
			["name" => "Lesotho", "code" => "+266"],
			["name" => "Liberia", "code" => "+231"],
			["name" => "Libya", "code" => "+218"],
			["name" => "Liechtenstein", "code" => "+423"],
			["name" => "Lithuania", "code" => "+370"],
			["name" => "Luxembourg", "code" => "+352"],
			["name" => "Madagascar", "code" => "+261"],
			["name" => "Malawi", "code" => "+265"],
			["name" => "Malaysia", "code" => "+60"],
			["name" => "Maldives", "code" => "+960"],
			["name" => "Mali", "code" => "+223"],
			["name" => "Malta", "code" => "+356"],
			["name" => "Mauritania", "code" => "+222"],
			["name" => "Mauritius", "code" => "+230"],
			["name" => "Mexico", "code" => "+52"],
			["name" => "Moldova", "code" => "+373"],
			["name" => "Monaco", "code" => "+377"],
			["name" => "Mongolia", "code" => "+976"],
			["name" => "Montenegro", "code" => "+382"],
			["name" => "Morocco", "code" => "+212"],
			["name" => "Mozambique", "code" => "+258"],
			["name" => "Myanmar", "code" => "+95"],
			["name" => "Namibia", "code" => "+264"],
			["name" => "Nepal", "code" => "+977"],
			["name" => "Netherlands", "code" => "+31"],
			["name" => "New Zealand", "code" => "+64"],
			["name" => "Nicaragua", "code" => "+505"],
			["name" => "Niger", "code" => "+227"],
			["name" => "Nigeria", "code" => "+234"],
			["name" => "North Korea", "code" => "+850"],
			["name" => "North Macedonia", "code" => "+389"],
			["name" => "Norway", "code" => "+47"],
			["name" => "Oman", "code" => "+968"],
			["name" => "Pakistan", "code" => "+92"],
			["name" => "Palestine", "code" => "+970"],
			["name" => "Panama", "code" => "+507"],
			["name" => "Papua New Guinea", "code" => "+675"],
			["name" => "Paraguay", "code" => "+595"],
			["name" => "Peru", "code" => "+51"],
			["name" => "Philippines", "code" => "+63"],
			["name" => "Poland", "code" => "+48"],
			["name" => "Portugal", "code" => "+351"],
			["name" => "Qatar", "code" => "+974"],
			["name" => "Romania", "code" => "+40"],
			["name" => "Russia", "code" => "+7"],
			["name" => "Rwanda", "code" => "+250"],
			["name" => "Saudi Arabia", "code" => "+966"],
			["name" => "Senegal", "code" => "+221"],
			["name" => "Serbia", "code" => "+381"],
			["name" => "Seychelles", "code" => "+248"],
			["name" => "Sierra Leone", "code" => "+232"],
			["name" => "Singapore", "code" => "+65"],
			["name" => "Slovakia", "code" => "+421"],
			["name" => "Slovenia", "code" => "+386"],
			["name" => "Somalia", "code" => "+252"],
			["name" => "South Africa", "code" => "+27"],
			["name" => "South Korea", "code" => "+82"],
			["name" => "Spain", "code" => "+34"],
			["name" => "Sri Lanka", "code" => "+94"],
			["name" => "Sudan", "code" => "+249"],
			["name" => "Suriname", "code" => "+597"],
			["name" => "Sweden", "code" => "+46"],
			["name" => "Switzerland", "code" => "+41"],
			["name" => "Syria", "code" => "+963"],
			["name" => "Taiwan", "code" => "+886"],
			["name" => "Tajikistan", "code" => "+992"],
			["name" => "Tanzania", "code" => "+255"],
			["name" => "Thailand", "code" => "+66"],
			["name" => "Togo", "code" => "+228"],
			["name" => "Trinidad and Tobago", "code" => "+1-868"],
			["name" => "Tunisia", "code" => "+216"],
			["name" => "Turkey", "code" => "+90"],
			["name" => "Turkmenistan", "code" => "+993"],
			["name" => "Uganda", "code" => "+256"],
			["name" => "Ukraine", "code" => "+380"],
			["name" => "United Arab Emirates", "code" => "+971"],
			["name" => "United Kingdom", "code" => "+44"],
			["name" => "United States", "code" => "+1"],
			["name" => "Uruguay", "code" => "+598"],
			["name" => "Uzbekistan", "code" => "+998"],
			["name" => "Vanuatu", "code" => "+678"],
			["name" => "Vatican City", "code" => "+379"],
			["name" => "Venezuela", "code" => "+58"],
			["name" => "Vietnam", "code" => "+84"],
			["name" => "Yemen", "code" => "+967"],
			["name" => "Zambia", "code" => "+260"],
			["name" => "Zimbabwe", "code" => "+263"]
		];

		return $countryCodes;
	}
}
