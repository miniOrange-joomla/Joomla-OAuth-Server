<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Script file of miniorange_dirsync_system_plugin.
 *
* @author      miniOrange Security Software Pvt. Ltd.
* @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
* @license     GNU General Public License version 3; see LICENSE.txt
* @contact     joomlasupport@xecurify.com
 */
class pkg_OAUTHSERVERInstallerScript
{
    /**
     * This method is called after a component is installed.
     *
     * @param  \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    public function install($parent) 
    {

            
    }

    /**
     * This method is called after a component is uninstalled.
     *
     * @param  \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    public function uninstall($parent) 
    {

    }

    /**
     * This method is called after a component is updated.
     *
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function update($parent) 
    {

    }

    /**
     * Runs just before any installation action is performed on the component.
     * Verifications and pre-requisites should run in this function.
     *
     * @param  string    $type   - Type of PreFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function preflight($type, $parent) 
    {
    }

    /**
     * Runs right after any installation action is performed on the component.
     *
     * @param  string    $type   - Type of PostFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    function postflight($type, $parent) 
    {
  
        if ($type == 'uninstall') {
            return true;
        }

        $helperPath = JPATH_ADMINISTRATOR . '/components/com_miniorange_oauthserver/helpers/mo_customer_setup.php';
        
        if (file_exists($helperPath)) {
            require_once $helperPath;
            
            if (class_exists('MoOauthServerCustomer')) {
                MoOauthServerCustomer::sendInstallationNotification(); 
            }
        }

        $this->showInstallMessage('');
        
    }

    protected function showInstallMessage($messages=array()) {
        ?>
        <style>
            .mo-row{
                width: 100%;
                display: block;
                margin-bottom: 2%;
            }
            .mo-row:after{
                clear: both;
                display: block;
                content: "";
            }
            .mo-button-style {
                background-color: #007DB0;
                color: #ffffff;
                border: 1px solid #007DB0;
                padding: 8px 16px;
                border-radius: 4px;
                font-size: 14px;
                cursor: pointer;
                display: inline-block;
                text-decoration: none;
            }

            .mo-button-style:hover,
            .mo-button-style:focus {
                background-color: #00597D;
                border-color: #00597D;
                color: #ffffff;
                text-decoration: none;
            }
        </style>
        <div>
            <h2>miniOrange Joomla OAuth Server Free Plugin</h2>
            <hr>
            <p>
                <strong>
                    Joomla OAuth Server plugin allows you to perform Single Sign-On with any OAuth 2.0 compliant client application . 
                    It enables users to authenticate into your client application using their Joomla credentials, allowing Joomla to 
                    act as an OAuth Provider. You can also access all OAuth APIs using the Joomla OAuth Server SSO plugin.
                </strong>
                <h4>Steps to use the OAuth Server plugin.</h4>
                <ul>
                    <li>Click on <b>Components</b></li>
                    <li>Click on <b>miniOrange OAuth Server</b> and select <b>Configure OAuth</b> tab</li>
                    <li>You can start configuring.</li>
                </ul>
            </p>

            <div class="mo-row">
                <a class="mo-button-style"  href="index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=configuration">Start Using miniOrange OAuth Server plugin</a>
                <a class="mo-button-style"  href="https://plugins.miniorange.com/joomla-sso-ldap-mfa-solutions?section=oauth-server" target="_blank">Setup guides</a>
		        <a class="mo-button-style"  href="https://www.miniorange.com/contact" target="_blank">Free Trial / Need assistance</a>
            </div>
            
        </div>
        <?php
    }
  
}