<?php

/**
 * @package    Joomla.Plugin
 * @subpackage System.mooauthserver
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Session\Session;

jimport('joomla.plugin.plugin');
jimport('miniorangeoauthserver.utility.MoOAuthServerUtility');

class PlgSystemMooauthserver extends CMSPlugin
{
	// User state key holding the extension ids queued for uninstall by the administrator uninstall flow.
	const UNINSTALL_STATE_KEY = 'plg_system_mooauthserver.uninstall.ids';

	public function onAfterInitialise()
	{
		$app   = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;

		if (strtoupper((string) $input->getMethod()) !== 'POST')
		{
			return;
		}

		$post = $input->post->getArray();

		if (!array_key_exists('mojsp_feedback', $post))
		{
			return;
		}

		// The feedback form is only ever rendered inside the administrator uninstall flow.
		if (!self::isAdministrator($app))
		{
			return;
		}

		if (!Session::checkToken('post'))
		{
			self::denyRequest($app, 'The security token did not match. Please retry the uninstall from the Extensions manager.');
		}

		if (!self::canUninstallExtensions($app))
		{
			self::denyRequest($app, 'You are not authorised to uninstall extensions.');
		}

		$extensionIds = self::consumeQueuedExtensionIds($app, $post);
		$skipped      = array_key_exists('miniorange_skip_feedback', $post);
		$reason       = (string) $input->post->getString('deactivate_plugin', '');
		$comment      = (string) $input->post->getString('query_feedback', '');
		$email        = (string) $input->post->getString('feedback_email', '');

		self::markFeedbackSubmitted();

		// A skipped form carries no usable feedback, so nothing is reported back to miniOrange.
		if (!$skipped)
		{
			self::sendFeedback($email, $reason, $comment);
		}

		foreach ($extensionIds as $extensionId)
		{
			self::uninstallExtension($extensionId);
		}
	}

	private static function isAdministrator($app)
	{
		if (method_exists($app, 'isClient'))
		{
			return (bool) $app->isClient('administrator');
		}

		return method_exists($app, 'isAdmin') ? (bool) $app->isAdmin() : false;
	}

	private static function canUninstallExtensions($app)
	{
		$user = method_exists($app, 'getIdentity') ? $app->getIdentity() : Factory::getUser();

		if (empty($user) || !empty($user->guest))
		{
			return false;
		}

		return (bool) $user->authorise('core.manage', 'com_installer')
			&& (bool) $user->authorise('core.delete', 'com_installer');
	}

	private static function denyRequest($app, $message)
	{
		$app->setUserState(self::UNINSTALL_STATE_KEY, null);
		$app->enqueueMessage($message, 'error');
		$app->redirect('index.php');
	}

	// Only extensions that the administrator actually queued for uninstall may be removed.
	private static function consumeQueuedExtensionIds($app, array $post)
	{
		$queued = $app->getUserState(self::UNINSTALL_STATE_KEY, []);
		$app->setUserState(self::UNINSTALL_STATE_KEY, null);

		if (!is_array($queued) || empty($queued))
		{
			return [];
		}

		$queued    = array_map('intval', $queued);
		$submitted = [];

		if (!empty($post['result']) && is_array($post['result']))
		{
			foreach ($post['result'] as $value)
			{
				$submitted[] = (int) $value;
			}
		}

		return array_values(array_unique(array_intersect($submitted, $queued)));
	}

	private static function markFeedbackSubmitted()
	{
		$db    = self::getDBObject();
		$query = $db->getQuery(true);
		$query->update($db->quoteName('#__miniorange_oauthserver_customer'))
			->set($db->quoteName('uninstall_feedback') . ' = ' . $db->quote(1))
			->where($db->quoteName('id') . ' = 1');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			// The uninstall must continue even when the plugin tables are already gone.
		}
	}

	private static function sendFeedback($email, $reason, $comment)
	{
		$email = trim($email);

		if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false)
		{
			return;
		}

		$helper = JPATH_BASE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_oauthserver'
			. DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_customer_setup.php';

		if (!is_file($helper))
		{
			return;
		}

		require_once $helper;

		if (!class_exists('MoOauthServerCustomer'))
		{
			return;
		}

		try
		{
			MoOauthServerCustomer::submitFeedbackForm($email, $reason . ' : ' . $comment);
		}
		catch (Exception $e)
		{
			// Never let a failed feedback call block the uninstall.
		}
	}

	private function resolveOAuthPath($input)
	{
		$oauthPaths = ['/v1/moserver/auth', '/v1/moserver/token', '/v1/moserver/userinfo'];
		$pathInfo = $input->server->get('PATH_INFO', '', 'STRING');

		if (in_array($pathInfo, $oauthPaths, true))
		{
			return $pathInfo;
		}

		$requestUri = $input->server->get('REQUEST_URI', '', 'STRING');

		if (preg_match('#/index\.php(/v1/moserver/(?:auth|token|userinfo))#', $requestUri, $matches))
		{
			return $matches[1];
		}

		return '';
	}

	public function onAfterRoute()
	{
		$app   = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$url = $this->resolveOAuthPath($input);

		if ($url === '/v1/moserver/auth')
		{
			MoOAuthServerUtility::handleOAuthCodeRequest();
		}
		elseif ($url === '/v1/moserver/token')
		{
			MoOAuthServerUtility::handleOAuthTokenRequest();
		}
		elseif ($url === '/v1/moserver/userinfo')
		{
			MoOAuthServerUtility::handleOAuthUserInfoRequest();
		}
	}

	public function onExtensionBeforeUninstall($id)
	{
		$app = Factory::getApplication();

		// The feedback form is part of the administrator uninstall screen only.
		if (!self::isAdministrator($app) || !self::canUninstallExtensions($app))
		{
			return;
		}

		if (!self::isOwnComponent($id) || !self::isFeedbackPending())
		{
			return;
		}

		$input     = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$post      = $input->post->getArray();
		$queuedIds = [(int) $id];

		if (!empty($post['cid']) && is_array($post['cid']))
		{
			foreach ($post['cid'] as $cid)
			{
				$queuedIds[] = (int) $cid;
			}
		}

		$queuedIds = array_values(array_unique(array_filter($queuedIds)));

		// Remember what may be uninstalled once the feedback form comes back.
		$app->setUserState(self::UNINSTALL_STATE_KEY, $queuedIds);

		$this->renderFeedbackForm($queuedIds);

		$app->close();
	}

	private static function isOwnComponent($id)
	{
		$db    = self::getDBObject();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('name') . ' = ' . $db->quote('COM_MINIORANGE_OAUTHSERVER'));
		$db->setQuery($query);

		try
		{
			$componentIds = $db->loadColumn();
		}
		catch (Exception $e)
		{
			return false;
		}

		if (empty($componentIds))
		{
			return false;
		}

		return in_array((int) $id, array_map('intval', $componentIds), true);
	}

	private static function isFeedbackPending()
	{
		$db          = self::getDBObject();
		$tableExists = false;

		foreach ($db->getTableList() as $table)
		{
			if (strpos($table, 'miniorange_oauthserver_customer') !== false)
			{
				$tableExists = true;
				break;
			}
		}

		if (!$tableExists)
		{
			return false;
		}

		$query = $db->getQuery(true);
		$query->select($db->quoteName('uninstall_feedback'))
			->from($db->quoteName('#__miniorange_oauthserver_customer'))
			->where($db->quoteName('id') . ' = 1');
		$db->setQuery($query);

		try
		{
			$feedback = $db->loadResult();
		}
		catch (Exception $e)
		{
			return false;
		}

		return $feedback !== null && (int) $feedback === 0;
	}

	private function renderFeedbackForm(array $queuedIds)
	{
		$deactivateReasons = [
			"Does not have the features I'm looking for",
			'Confusing Interface',
			'Not able to Configure',
			'Redirecting back to login page after Authentication',
			'Not Working',
			'Bugs in the plugin',
			'Other Reasons:',
		];
		$reasonMarkup = '';

		foreach ($deactivateReasons as $reason)
		{
			$escapedReason = htmlspecialchars($reason, ENT_QUOTES, 'UTF-8');
			$reasonMarkup .= '<div class="radio" style="padding:1px;margin-left:2%">'
				. '<label style="font-weight:normal;font-size:14.6px">'
				. '<input type="radio" name="deactivate_plugin" value="' . $escapedReason . '">'
				. $escapedReason
				. '</label></div>';
		}

		$hiddenFieldsMarkup = '';

		foreach ($queuedIds as $queuedId)
		{
			$hiddenFieldsMarkup .= '<input type="hidden" name="result[]" value="' . (int) $queuedId . '">';
		}

		$tokenMarkup = '<input type="hidden" name="' . Session::getFormToken() . '" value="1">';

		?>
		<div class="form-style-6">
			<h1 class="feedback-title">
				Feedback Form for OAuth Server

				<button type="submit"
						name="miniorange_skip_feedback"
						value="1"
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

				<?php echo $tokenMarkup; ?>

				<?php echo $reasonMarkup; ?>

				<br>
				<textarea id="query_feedback" name="query_feedback" rows="4" style="margin-left:2%" cols="50" placeholder="Write your query here" minlength="10"></textarea>
				<br><br>

				<label style="margin-left:2%;"><strong>Email<span style="color:red;">*</span>:</strong></label>
				<input type="email" id="feedback_email" name="feedback_email" placeholder="Enter email to contact."
					   style="width:96%; margin-left:2%;"/>

				<br><br>

				<?php echo $hiddenFieldsMarkup; ?>

				<br>
				<div class="mojsp_modal-footer">
					<input type="submit" id="submitBtn" name="miniorange_feedback_submit"
						   class="button button-primary button-large" value="Submit"/>
				</div>
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
	}

	public function generateRandomString()
	{
		$tokenLength = MoOAuthServerUtility::miniOauthFetchDb('#__miniorange_oauthserver_config', ['id' => '1'], 'loadResult', 'token_length');
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

	public static function getDBObject()
	{
		$app = Factory::getApplication();

		if (method_exists($app, 'getDatabase'))
		{
			return $app->getDatabase();
		}

		return Factory::getDbo();
	}

	// Resolves the extension type itself and refuses core, protected or locked extensions.
	private static function uninstallExtension($extensionId)
	{
		$extension = self::getUninstallableExtension($extensionId);

		if ($extension === null)
		{
			return false;
		}

		$installer = Installer::getInstance();

		if (method_exists($installer, 'setDatabase'))
		{
			$installer->setDatabase(self::getDBObject());
		}

		return (bool) $installer->uninstall($extension['type'], (int) $extension['extension_id'], 0);
	}

	private static function getUninstallableExtension($extensionId)
	{
		$extensionId = (int) $extensionId;

		if ($extensionId <= 0)
		{
			return null;
		}

		$db    = self::getDBObject();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('extension_id') . ' = ' . $extensionId);
		$db->setQuery($query);

		try
		{
			$extension = $db->loadAssoc();
		}
		catch (Exception $e)
		{
			return null;
		}

		if (empty($extension) || empty($extension['type']))
		{
			return null;
		}

		// Joomla 3 only ships the protected flag, Joomla 4 and newer add the locked flag.
		if (!empty($extension['protected']) || !empty($extension['locked']))
		{
			return null;
		}

		return $extension;
	}

}
