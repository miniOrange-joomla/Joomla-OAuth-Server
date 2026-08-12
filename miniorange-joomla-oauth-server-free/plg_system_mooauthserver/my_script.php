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
use Joomla\CMS\Factory;

class PlgSystemMooauthserverInstallerScript
{
	/**
	 * This method is called after a component is installed.
	 *
	 * @param \stdClass $parent - Parent object calling this method.
	 *
	 * @return void
	 */
	public function install($parent)
	{
		$app = Factory::getApplication();

		if (method_exists($app, 'getDatabase'))
		{
			// J4+
			$db = $app->getDatabase();
		}
		else
		{
			$db = Factory::getDbo();
		}

		$query = $db->getQuery(true);
		$query->update('#__extensions');
		$query->set($db->quoteName('enabled') . ' = 1');
		$query->where($db->quoteName('element') . ' = ' . $db->quote('mooauthserver'));
		$query->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
		$db->setQuery($query);
		$db->execute();
	}
}
