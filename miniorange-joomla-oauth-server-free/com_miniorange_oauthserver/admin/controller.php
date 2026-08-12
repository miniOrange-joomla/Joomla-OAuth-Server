<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_miniorange_oauthserver
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * General Controller of miniorange_oauth component
 *
 * @package     Joomla.Administrator
 * @subpackage  com_miniorange_oauthserver
 * @since       0.0.7
 */
class MiniorangeOAuthserverController extends BaseController
{
	public function __construct($config = array(), $factory = null, $app = null, $input = null)
	{
		$config['default_view'] = 'accountsetup';

		parent::__construct($config, $factory, $app, $input);
	}
}
