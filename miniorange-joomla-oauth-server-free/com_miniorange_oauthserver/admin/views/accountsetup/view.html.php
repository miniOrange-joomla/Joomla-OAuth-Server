<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_miniorange_oauthserver
 *
* @author      miniOrange Security Software Pvt. Ltd.
* @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
* @license     GNU General Public License version 3; see LICENSE.txt
* @contact     joomlasupport@xecurify.com
 */
 
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
jimport('miniorangeoauthserver.utility.MoOAuthServerUtility');

$document = Factory::getApplication()->getDocument();
$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');

/**
 * Account Setup View
 *
 * @since  0.0.1
 */
class miniorangeoauthserverViewAccountSetup extends HtmlView
{
	function display($tpl = null)
	{
		// Get data from the model
		$this->lists		= $this->get('List');
		//$this->pagination	= $this->get('Pagination');
 
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			Factory::getApplication()->enqueueMessage(500, implode('<br />', $errors));
 
			return false;
		}
		$this->setLayout('accountsetup');
		// Set the toolbar
		$this->addToolBar();
 
		// Display the template
		parent::display($tpl);
	}
 
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolBar()
	{
		ToolbarHelper::title(Text::_('COM_MINIORANGE_OAUTHSERVER_PLUGIN_TITLE'),'mo_oauth_logo mo_oauth_logo');

	}
}