<?php
/**
 * @package    Joomla.Plugin
 * @subpackage Webservices.miniOrnage
 *
* @author      miniOrange Security Software Pvt. Ltd.
* @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
* @license     GNU General Public License version 3; see LICENSE.txt
* @contact     joomlasupport@xecurify.com
 */

 defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;

class PlgWebservicesMooauthserver extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var   boolean
     * @since 4.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Registers com_content's API's routes in the application
     *
     * @param ApiRouter &$router The API Routing object
     *
     * @return void
     *
     * @since 4.0.0
     */
    public function onBeforeApiRoute(&$router)
    {

        $router->createCRUDRoutes(
            'v1/moserver/auth',
            'auth',
        );

        $router->createCRUDRoutes(
            'v1/moserver/token',
            'token',
        );

        $router->createCRUDRoutes(
            '/v1/moserver/userinfo',
            'userinfo',
        );
        
        $this->authServerCodeAndTokenRoute($router);
    }


    /**
     * Create contenthistory routes
     *
     * @param ApiRouter &$router The API Routing object
     *
     * @return void
     *
     * @since 4.0.0
     */
    public function authServerCodeAndTokenRoute(&$router)
    {
        jimport('miniorangeoauthserver.utility.MoOAuthServerUtility');
        $app   = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $url =  $input->server->get('PATH_INFO', '', 'STRING');
        if($url === "/v1/moserver/auth") {
            MoOAuthServerUtility::handleOAuthCodeRequest();
        }

        if($url === "/v1/moserver/token") {
            MoOAuthServerUtility::handleOAuthTokenRequest();
        }

        if($url === "/v1/moserver/userinfo") {
            MoOAuthServerUtility::handleOAuthUserInfoRequest();
        }
    }
}
