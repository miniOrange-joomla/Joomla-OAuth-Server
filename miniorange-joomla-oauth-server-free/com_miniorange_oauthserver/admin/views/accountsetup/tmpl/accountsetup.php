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
defined('_JEXEC') or die('Restricted Access');
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
Use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Version;
use Joomla\CMS\Router\Route;
jimport('miniorangeoauthserver.utility.MoOAuthServerUtility');
HTMLHelper::_('jquery.framework');

$document = Factory::getApplication()->getDocument();
$document->addScript(Uri::base() . 'components/com_miniorange_oauthserver/assets/js/OAuthServerScript.js');
$document->addStyleSheet(Uri::base() . 'components/com_miniorange_oauthserver/assets/css/miniorange_oauth.css');
$document->addStyleSheet(Uri::base() . 'components/com_miniorange_oauthserver/assets/css/miniorange_boot.css');
$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css');

if(MoOAuthServerUtility::is_curl_installed()==0)
{ ?>
	<p class="mo_oauth_red">(Warning: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP CURL extension</a> is not installed or disabled) Please go to Troubleshooting for steps to enable curl.</p>
  <?php
}
$oauth_active_tab = 'configuration';
$app = Factory::getApplication();
$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
$active_tab = $input->get->getArray();

if(isset($active_tab['tab-panel']) && !empty($active_tab['tab-panel'])) {
    $oauth_active_tab = $active_tab['tab-panel'];
}
$isUserEnabled = PluginHelper::isEnabled('user', 'miniorangeoauthserver');
$isSystemEnabled = PluginHelper::isEnabled('system', 'mooauthserver');
$isWebserviceEnabled = PluginHelper::isEnabled('webservices', 'mooauthserver');
if(!$isSystemEnabled || !$isUserEnabled || !$isWebserviceEnabled) { 
    ?>
    <div id="system-message-container">
        <button type="button" class="mo_boot_close" data-dismiss="alert">×</button>
        <div class="alert alert-error">
            <h4 class="mo_boot_alert-heading">Warning!</h4>
            <div class="alert-message">     
                <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_PLUGIN_REQUIREMENTS'); ?>
            </div>
        </div>
    </div>
	<?php
}


$tabs = [
    [
        'id'     => 'oauthserver_overview',
        'href'   => '#overview',
        'icon'   => 'fa-home',
        'label'  => 'COM_MINIORANGE_OAUTHSERVER_TAB1_OVERVIEW',
        'active' => 'overview',
    ],
    [
        'id'     => 'configu_id',
        'href'   => '#configuration',
        'icon'   => 'fa-cogs',
        'label'  => 'COM_MINIORANGE_OAUTHSERVER_TAB1_CONFIGURE_OAUTH',
        'active' => 'configuration',
    ],
    [
        'id'     => 'advance_settings_id',
        'href'   => '#advancesettings',
        'icon'   => 'fa-cog',
        'label'  => 'COM_MINIORANGE_OAUTHSERVER_TAB2_SETTINGS',
        'active' => 'advancesettings',
        'premium'=> true,
    ],
    [
        'id'     => 'advanceMapping',
        'href'   => '#advancemappinng',
        'icon'   => 'fa-map',
        'label'  => 'COM_MINIORANGE_OAUTHSERVER_TAB3_ADVANCED_MAPPING',
        'active' => 'advancemapping',
        'premium'=> true, // Example premium tab
    ],
    [
        'id'     => 'licensing_planid',
        'href'   => '#licensing-plans',
        'icon'   => 'fa-credit-card',
        'label'  => 'COM_MINIORANGE_OAUTHSERVER_TAB5_LICENSING_PLANS',
        'active' => 'license',
    ]
];

?>    
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>

<div class="mo_boot_row mo_oauth_navbar">
    <div class="mo_boot_col-sm-12">
        <a class="mo_boot_px-4 mo_boot_py-1 oauth_blue_button btn_oauth_custom_top" href="index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=exportConfiguration"> <?php echo Text::_('COM_MINIORANGE_EXPORT_IMPORT');?></a>
        <a class="mo_boot_px-4 mo_boot_py-1 oauth_blue_button btn_oauth_custom_top" href="<?php echo Uri::base()?>index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=requestdemo">
            <i class="fa-solid fa-envelope"></i>
            <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SUPPORT');?>
        </a>
        <a class="mo_boot_px-4 mo_boot_py-1 oauth_blue_button btn_oauth_custom_top"
           href="<?php echo Uri::base(); ?>index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=requestdemo&sub-tab=mo_request_demo">
           <i class="fa-solid fa-globe"></i>
           <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_FREE_TRIAL'); ?>
        </a>
    </div>
</div>

<div class="mo_boot_container-fluid mo_oauth_plugin">
    <div class="mo_boot_row mo_oauth_navbar">
        <?php foreach ($tabs as $key => $tab): ?>
            <a id="<?php echo $tab['id']; ?>"
               class="mo_boot_col mo_oauth_nav-tab mo_nav_tab_<?php echo ($oauth_active_tab === $tab['active']) ? 'active' : ''; ?>"
               href="<?php echo $tab['href']; ?>"
                onclick="add_css_tab('#<?php echo $tab['id']; ?>');"
                data-toggle="tab">

                <span><i class="fa fa-solid <?php echo $tab['icon']; ?>"></i></span>
                <span class="tab-label"><?php echo Text::_($tab['label']); ?></span>

                <?php if (!empty($tab['premium']) && $tab['premium'] === true) : ?>
                    <span title="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_AVAILABLE_IN_PAID_PLANS_ONLY'); ?>">
                        <sup>
                            <img class="crown_img_small"
                                 src="<?php echo Uri::base(); ?>/components/com_miniorange_oauthserver/assets/images/crown.webp">
                        </sup>
                    </span>
                <?php else: ?>
                    <span class="premium-icon-placeholder"></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>


<div class="mo_boot_container-fluid mo_oauth_tab-content">
    <div class="tab-content" id="myTabContent">
        <div id="overview" class="tab-pane <?php echo $oauth_active_tab == 'overview' ? 'active' : ''; ?>" >
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <?php echo mo_oauth_server_overview(); ?>
                </div>
            </div>
        </div>

        <div id="configuration" class="tab-pane <?php echo $oauth_active_tab == 'configuration' ? 'active' : ''; ?>" >
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <?php
                    $app = Factory::getApplication();
                    $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
                    $get = $input->get->getArray();
                    if(isset($get['pa'])&&($get['pa']==1)) {
                        mo_oauth_server_add_client();
                    }
                    else if(isset($get['pa'])&&($get['pa']==2)) {
                        mo_oauth_client_list();
                    }
                    else if(isset($get['pa'])&&($get['pa']==3)) {
                        mo_oauth_update();
                    }
                    elseif(isset($get['endpoints']) && ($get['endpoints'] =='true')) {
                        mo_oauth_server_client_config();
                    }
                    else
                    {
                        mo_oauth_client_list();
                    }
                    ?>
                </div>
            </div>
        </div>

        <div id="clientconfig" class="tab-pane <?php echo $oauth_active_tab == 'clientconfig' ? 'active' : ''; ?>" >
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <?php echo mo_oauth_server_client_config(); ?>
                </div>
            </div>
        </div>

        <div  id="licensing-plans"  class="tab-pane <?php echo $oauth_active_tab == 'license' ? 'active' : ''; ?>" >
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <?php $customer_details = MoOAuthServerUtility::getCustomerDetails();                        
                    $email = $customer_details['email'];                    
                    $hostName = 'https://www.miniorange.com';
                    $loginUrl = $hostName . '/contact';
                    echo mo_oauth_server_licensing_plan(); ?>
                </div>
            </div>    
        </div>

        <div id="requestdemo" class="tab-pane <?php echo $oauth_active_tab == 'requestdemo' ? 'active' : ''; ?>" >
            <div class="mo_boot_row">
                 <div class="mo_boot_col-sm-12">
                    <?php echo mo_oauth_support(); ?>
                 </div>  
            </div>
        </div>
        
        <div id="advancesettings" class="tab-pane <?php echo $oauth_active_tab == 'advancesettings' ? 'active' : ''; ?>" >
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <?php echo mo_oauth_server_advance_settings(); ?>
                </div>
            </div>    
        </div>

        <div id="advancemappinng" class="tab-pane <?php echo $oauth_active_tab == 'advancemapping' ? 'active' : ''; ?>" >
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <?php echo mo_oauth_show_advance_mapping(); ?>
                </div>
            </div>
        </div>
        
        <div id="exportConfiguration" class="tab-pane <?php echo $oauth_active_tab == 'exportConfiguration' ? 'active' : ''; ?>" >
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <?php echo exportConfiguration(); ?>
                </div>
            </div>    
        </div>
    </div>
</div>

	
<!-- 
	*End Of Tabs for accountsetup view. 
	*Below are the UI for various sections of Account Creation.
-->

<?php
function mo_oauth_server_overview()
{    
    ?>

    <div class="mo_boot_col-sm-12 mo_oauth_dark_bg">
        <div class="mo_boot_row">
            <div class="mo_boot_col-sm-12 mo_boot_mt-4">
                <h3>
                    <em><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_JOOMLA_SSO');?>
                    </em>
                </h3>
                <hr class="mo_boot_bg-dark">
            </div>
        </div>

        <div class="mo_boot_row mt-2">
            <div class="mo_boot_col-lg-7 mo_boot_col-sm-11 mo_boot_text-justify mo_boot_py-4">
                <strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_JOOMLA_SSO_DESC');?>
                </strong>
                <br><br>
                <a class="oauth_blue_button mo_boot_px-3 mo_boot_mx-1" target="_blank" href="https://plugins.miniorange.com/joomla-oauth-server"><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_VISIT_SITE');?></a>
                <a class="oauth_blue_button mo_boot_px-3 mo_boot_mx-1" href="<?php echo Uri::root().'administrator/index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=license';?>"><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_LICE_PLANS');?></a>
                <a class="oauth_blue_button mo_boot_px-3 mo_boot_mx-1" target="_blank" href="https://plugins.miniorange.com/joomla-sso-ldap-mfa-solutions?section=oauth-server"> <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_GUIDES');?></a>
                <a class="oauth_blue_button mo_boot_px-3 mo_boot_mx-1" target="_blank" href="https://faq.miniorange.com/kb/joomla/"> <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_FAQ');?></a>
            </div>
            <div class="mo_boot_col-sm-5 mo_oauth_overview_img">
                <img class="" src="<?php echo Uri::root().'administrator\components\com_miniorange_oauthserver\assets\images\joomla-oauth-server-sso.webp'?>" alt="Joomla Single sign on">
            </div>
        </div>
    </div>

	<?php
}

function mo_oauth_client_list() 
{
    $attribute=MoOAuthServerUtility::miniOauthFetchDb('#__miniorange_oauthserver_config', array("id"=>1), 'loadAssoc', '*');
    
    if($attribute['client_count']>0) { 
        ?>
        <div class="mo_boot_col-sm-12 mo_main_oauth_section">
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <div class="mo_boot_row mo_boot_my-0">
                        <div class="mo_boot_col-lg-6 mo_boot_col-sm-8">
                            <h3>
                                <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_LIST_OF_OAUTH_CLIENTS');?>
                            </h3>
                        </div>
                        <div class="mo_boot_col-lg-6 mo_boot_col-sm-4">
                            <div>
                                <form name="oauth_mapping_form" method="post" action="<?php echo Route::_('index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=configuration&pa=1');?>">
                                <button type="submit" id="add_client" name="send_query" 
                                    class="oauth_blue_button btn_oauth_custom_top mo_boot_float-right" disabled><i class="fa-solid fa-plus"></i>
                                    <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ADD_CLIENT'); ?>
                                </button>
                            </form>
                            </div>
                            <a  onclick="add_css_tab('#configu_id');" href="<?php echo Uri::base().'index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=configuration&endpoints=true';?>"  class="mo_boot_float-right mo_boot_mx-1 oauth_blue_button btn_oauth_custom_top" ><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ENDPOINT_URL');?></a>
                        </div>
                    </div>

                    <div class="mo_boot_row mo_boot_mt-2">
                        <div class="mo_boot_col-sm-12">
                            <span color="red"><em><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ONLY_ONE_CLIENT1');?> <a href="index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=license" rel="noopener noreferrer"><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ONLY_ONE_CLIENT2');?></a> <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ONLY_ONE_CLIENT3');?></em></span>    
                        </div>
                        <div class="mo_boot_col-sm-12 mo_boot_table-responsive">
                           <div class="mo_custom_table">
                                <table>
                                    <tr>
                                        <th><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CLIENT_NAME');?></th>
                                        <th><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CLIENT_ID');?></th>
                                        <th><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CLIENT_SECRET_KEY');?></th>
                                        <th colspan="2" id="li_client_options"><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_OPTIONS');?></th>
                                    </tr>
                                    <tr>
                                        <td><strong><?php echo $attribute['client_name']; ?></strong></td>
                                        <td><span id="client_idkey"><?php echo $attribute['client_id']; ?></span></td>
                                        <td><span id="client_secretkey"><?php echo $attribute['client_secret']; ?></span></td>
                                        <td>
                                            <form method="post" action="<?php echo Route::_('index.php?option=com_miniorange_oauthserver&view=accountsetup&task=accountsetup.deleteclient');?>">
                                                <button type="submit" id="li_delete" title="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_DELETE_CLIENT');?>"><i class="fa-regular fa-trash-can"></i></button>
                                            </form>
                                        </td>
                                        <td>
                                            <form method="post" action="<?php echo Route::_('index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=configuration&pa=3');?>">
                                                <button type="submit" id="li_update" title="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_UPDATE_CLIENT');?>"><i class="fa-regular fa-pen-to-square"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="mo_boot_col-sm-12">
                            <p class='mo_oauth_alert mo_boot_mx-0'>
                                    <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ONLY_ONE_CLIENT_NOTE1');?>
                                <a href="index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=license" rel="noopener noreferrer" class="mo_oauth_guide_link mo_boot_mx-0"><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ONLY_ONE_CLIENT_NOTE2');?> </a>
                                    <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ONLY_ONE_CLIENT_NOTE3');?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php	
    }
    else
    { 
        ?>
        <div class="mo_boot_col-sm-12 mo_main_oauth_section">
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <div class="mo_boot_row mo_boot_my-0">
                        <div class="mo_boot_col-lg-6 mo_boot_col-sm-8">
                            <h3>
                                <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_LIST_OF_OAUTH_CLIENTS');?>
                            </h3>
                        </div>
                        <div class="mo_boot_col-lg-6 mo_boot_col-sm-4">
                            <div>
                                <form name="oauth_mapping_form" method="post" action="<?php echo Route::_('index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=configuration&pa=1');?>">
                                    <button type="submit" id="add_client" name="send_query" 
                                        class="oauth_blue_button btn_oauth_custom_top mo_boot_float-right"><i class="fa-solid fa-plus"></i>
                                        <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ADD_CLIENT'); ?>
                                    </button>
                                </form>
                            </div>
                            <a  onclick="add_css_tab('#configu_id');" href="<?php echo Uri::base().'index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=configuration&endpoints=true';?>"  class="mo_boot_float-right mo_boot_mx-1 oauth_blue_button btn_oauth_custom_top" ><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ENDPOINT_URL');?></a>
                        </div>
                    </div>

                    <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4">
                        <div class="mo_boot_row mo_oauth_overview_img mo_boot_d-flex mo_oauth_justify-center mo_oauth_align-items-center ">
                            <img style="height: 20rem;" src="components/com_miniorange_oauthserver/assets/images/no_app_configured.png"></img>
                        </div>
                        <div class="mo_boot_text-center mo_boot_mt-3">
                            <h4><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_NO_OAUTH_APPS_CONFIGURED_YET'); ?></h4>
                        </div>
                        <div class="mo_boot_row mo_boot_d-flex mo_oauth_justify-center mo_oauth_align-items-center mo_boot_mt-3">
                            <span class="mo_boot_m-1"><h4><?php echo TExt::_('COM_MINIORANGE_OAUTHSERVER_NEED_HELP'); ?></h4></span>
                            <span>
                                <a href="https://plugins.miniorange.com/joomla-oauth-server-guides" target="_blank" class="mo_boot_mx-1 mo_oauth_guide_link"><i class="fa-solid fa-book"></i> <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SETUP_GUIDE'); ?></a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php 
    }
}

function mo_oauth_server_add_client()
{
    ?>
    <div class="mo_boot_col-sm-12 mo_main_oauth_section">
        <div class="mo_boot_row">
            <div class="mo_boot_col-sm-12">
                <div class="mo_boot_row mo_boot_my-0">
                    <div class="mo_boot_col-lg-6 mo_boot_col-sm-8">
                        <h2><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CONFIGURE_OAUTH_CLIENT');?></h2>
                    </div>
                </div>

                <!-- Configure -->
                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-2 mo_oauth_mini_section">
                   <!-- Header -->
                    <div class="mo_oauth_tab_header" onclick="toggleCollapse('mo_oauth_tab_content_basic', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_boot_col-sm-11 mo_oauth_tab_title">
                            <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CONFIGURATION'); ?>
                        </div>
                        <div class="mo_boot_col-sm-1 mo_toggle_icon mo_boot_text-right"> - </div>
                    </div>

                    <!-- Content -->
                    <div id="mo_oauth_tab_content_basic" class="mo_boot_col-sm-12 mo_boot_mt-3" style="display:block">
                       <div class="mo_boot_row mo_boot_p-3">
                            <div class="mo_boot_col-sm-12 mo_boot_px-0 mo_boot_my-4">
                                <form  method="post" action="<?php echo Route::_('index.php?option=com_miniorange_oauthserver&view=accountsetup&task=accountsetup.addclient');?>">
                                    <div class="mo_boot_row mo_boot_p-0 mo_boot_m-1" > 
                                        <div class="mo_boot_col-sm-5">
                                            <strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CLIENT_NAME');?><sup><span class="mo_oauth_highlight">*</span></sup>:</strong>
                                        </div>
                                        <div class="mo_boot_col-sm-7">
                                            <input class="mo_boot_form-control" required="" type="text" id="mo_oauth_custom_client_name" name="mo_oauth_custom_client_name" value="" placeholder= "<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CLIENT_NAME_PLACEHOLDER');?>">
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_p-0 mo_boot_m-1 mo_boot_mt-3" >
                                        <div class="mo_boot_col-sm-5">
                                            <strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_AUTHORIZED_REDIRECT_URI');?><sup><span class="mo_oauth_highlight">*</span></sup>:</strong>
                                        </div>
                                        <div class="mo_boot_col-sm-7">
                                            <input class="mo_boot_form-control" required="" type="text" name="mo_oauth_client_redirect_url" value="" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_AUTHORIZED_REDIRECT_URI_PLACEHOLDER');?>">
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_p-0 mo_boot_mt-2">
                                        <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_text-center">
                                            <input type="submit" name="submit" value="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SAVE_CLIENT');?>" class="oauth_blue_button mo_boot_mx-2" />
                                            <a href="<?php echo Route::_('index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=configuration'); ?>" class="mo_oauth_white_button mo_boot_py-2" ><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_GO_BACK');?></a>
                                        </div>
                                    </div> 
                                </form>
                            </div>
                       </div>
                    </div>
                </div>

                <!-- Premium Feature -->
                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_oauth_mini_section">
                   <!-- Header -->
                    <div class="mo_oauth_tab_header" onclick="toggleCollapse('mo_oauth_premium_feature', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_boot_col-sm-11 mo_oauth_tab_title">
                            <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_PREMIUM_FEATURES'); ?><sup> <small class="mo_oauth_highlight"><a href="index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=license" title="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_AVAILABLE_IN_PAID_PLANS_ONLY'); ?>"> <sup><img class="crown_img_small" src="<?php echo Uri::base(); ?>/components/com_miniorange_oauthserver/assets/images/crown.webp" alt="Premium"></sup></a></small></sup>
                        </div>
                        <div class="mo_boot_col-sm-1 mo_toggle_icon mo_boot_text-right"> + </div>
                    </div>

                    <!-- Content -->
                    <div id="mo_oauth_premium_feature" class="mo_boot_col-sm-12 mo_boot_mt-3" style="display:none">
                       <div class="mo_boot_row mo_boot_p-3">
                            <div class="mo_boot_col-sm-12 mo_boot_my-4">
                                <div class="mo_boot_row mo_boot_mt-3" >
                                    <div class="mo_boot_col-sm-5">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_GRANT_TYPE');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-7">
                                        <select name="mo_oauth_grant_type" readonly class="mo_boot_form-control" id="mo_oauth_grant_type">
                                            <option value="" selected> <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_AUTHORIZATION_GRANT_TYPE');?></option>
                                            <option value="" disabled> <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_IMPLICIT_GRANT_TYPE');?></option>
                                            <option value="" disabled> <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_PASSWORD_GRANT_TYPE');?></option>
                                            <option value="" disabled> <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_REFRESH_TOKEN_GRANT_TYPE');?></option>
                                            <option value="" disabled> <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CLIENT_CREDENTIALS_GRANT_TYPE');?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-3" >
                                    <div class="mo_boot_col-sm-5">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ENABLE_PKCE');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-7 mo_boot_d-flex mo_oauth_align-items-center mo_ouath_flex-gap-10 ">
                                        <div><input type="radio" class="mo_oauth_cursor" name="mo_oauth_enable_pkce" disabled value="1"> <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ENABLE_PKCE_YES');?></div>
                                        <div><input type="radio" class="mo_oauth_cursor" name="mo_oauth_enable_pkce" disabled value="0"> <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ENABLE_PKCE_NO');?></div>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-3" >
                                    <div class="mo_boot_col-sm-5">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_TOKEN_EXPIRY');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-7">
                                        <input class="mo_boot_form-control mo_oauth_cursor" required="" type="text" disabled name="mo_oauth_token_expiry" value="3600">
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-3" >
                                    <div class="mo_boot_col-sm-5">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_TOKEN_LENGTH');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-7">
                                        <input class="mo_boot_form-control mo_oauth_cursor" required="" type="text" disabled name="mo_oauth_token_length" value="64">
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_p-0 mo_boot_mt-2">
                                    <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_text-center">
                                        <input type="submit" name="submit" value="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SAVE_CLIENT');?>" class="oauth_blue_button mo_boot_mx-2 mo_oauth_cursor" disabled/>
                                        <input type="submit" name="back" value="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_GO_BACK');?>" class="mo_oauth_white_button mo_boot_mx-2 mo_oauth_cursor" disabled/>
                                    </div>
                                </div> 
                            </div>
                       </div>
                    </div>
                </div>

                <!-- JWT Feature -->
                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_oauth_mini_section">
                   <!-- Header -->
                    <div class="mo_oauth_tab_header" onclick="toggleCollapse('mo_oauth_jwt_feature', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_boot_col-sm-11 mo_oauth_tab_title">
                            <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ENABLE_JWT_PREMIUM_FEATURE'); ?><sup> <small class="mo_oauth_highlight"><a href="index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=license" title="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_AVAILABLE_IN_PAID_PLANS_ONLY'); ?>"> <sup><img class="crown_img_small" src="<?php echo Uri::base(); ?>/components/com_miniorange_oauthserver/assets/images/crown.webp" alt="Premium"></sup></a></small></sup>
                        </div>
                        <div class="mo_boot_col-sm-1 mo_toggle_icon mo_boot_text-right"> + </div>
                    </div>

                    <!-- Content -->
                    <div id="mo_oauth_jwt_feature" class="mo_boot_col-sm-12 mo_boot_mt-3" style="display:none">
                        <div class="mo_boot_row mo_boot_px-3">
                            <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_d-flex mo_oauth_align-items-center mo_ouath_flex-gap-10">
                                <input type="checkbox" class="mo_oauth_cursor" disabled id="enablejwt" value="1" name="enablejwt" /> 
                                <span class="mo_oauth_black"><strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ENABLE_JWT');?></strong><sup><a target="_blank" href="https://developers.miniorange.com/docs/oauth-joomla/jwt-verification" title="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_KNOW_MORE'); ?>"> <i class="fa-regular fa-circle-question"></i> </a></sup></span>
                                <small><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ENABLE_JWT_DESCRIPTION');?></small>
                            </div>
                            <div class="mo_boot_col-sm-12 mo_boot_mt-3">          
                                <p class="mo_oauth_alert mo_boot_mx-0 mo_boot_my-3">
                                    <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ENABLE_JWT_NOTE');?>
                                </p>
                            </div>
                            <div class="mo_boot_col-sm-12">
                                <h4 class="mo_oauth_black"><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SIGNING_ALGORITHMS');?></h4> 
                            </div>
                            <div class="mo_boot_col-sm-12 mo_boot_my-3">
                                <table>
                                    <tr>
                                        <td>
                                            <input type="radio" disabled id="hsa" name="mo_server_jwt_encryption" class="mo_oauth_cursor" value="HSA" />&nbsp;HSA&nbsp;&nbsp;
                                            <input disabled id="rsa" type="radio" name="mo_server_jwt_encryption" class="mo_oauth_cursor" value="RSA"  /> RSA&nbsp;&nbsp;<br><br>
                                            <input type="button" disabled class="mo_boot_btn mo_boot_btn-primary mo_oauth_btns mo_oauth_cursor" value="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_DOWNLOAD_CERTIFICAE');?>"> <br><br>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="mo_boot_row mo_boot_p-0 mo_boot_mt-2">
                            <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_text-center">
                                <input type="submit" name="submit" value="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SAVE_CLIENT');?>" class="oauth_blue_button mo_boot_mx-2 mo_oauth_cursor" disabled/>
                                <input type="submit" name="back" value="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_GO_BACK');?>" class="mo_oauth_white_button mo_boot_mx-2 mo_oauth_cursor" disabled/>
                            </div>
                        </div> 
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php 
}

function mo_oauth_update()
{
    $attribute=MoOAuthServerUtility::miniOauthFetchDb('#__miniorange_oauthserver_config', array("id"=>1), 'loadAssoc', '*');
    ?>
    
    <div class="mo_boot_col-sm-12 mo_main_oauth_section">
        <div class="mo_boot_row">
            <div class="mo_boot_col-sm-12">
                <div class="mo_boot_row mo_boot_my-0">
                    <div class="mo_boot_col-lg-6 mo_boot_col-sm-8">
                        <h2><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CONFIGURE_OAUTH_CLIENT');?></h2>
                    </div>
                </div>

                <!-- Update Configuration -->
                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-2 mo_oauth_mini_section">
                     <!-- Header -->
                    <div class="mo_oauth_tab_header" onclick="toggleCollapse('mo_oauth_tab_update_config', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_boot_col-sm-11 mo_oauth_tab_title">
                            <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_UPDATE_OAUTH_CLIENT'); ?>
                        </div>
                        <div class="mo_boot_col-sm-1 mo_toggle_icon mo_boot_text-right"> - </div>
                    </div>

                    <div id="mo_oauth_tab_update_config" class="mo_boot_col-sm-12 mo_boot_mt-3" style="display:block">
                        <div class="mo_boot_row mo_boot_p-3">
                            <div class="mo_boot_col-sm-12 mo_boot_px-0 mo_boot_my-4">
                                <form  name="f" method="post" action=" <?php echo Route::_('index.php?option=com_miniorange_oauthserver&view=accountsetup&task=accountsetup.updateclient');?> ">
                                    <div class="mo_boot_row mo_boot_p-0 mo_boot_m-1" > 
                                        <div class="mo_boot_col-sm-5">
                                            <strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CLIENT_NAME');?><sup><span class="mo_oauth_highlight">*</span></sup>:</strong>
                                        </div>
                                        <div class="mo_boot_col-sm-7">
                                            <input class="mo_boot_form-control mo_oauth_cursor" type="text" id="mo_oauth_custom_client_name" name="mo_oauth_custom_client_name" value="<?php echo $attribute['client_name'];?>" disabled="disable">
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_p-0 mo_boot_m-1 mo_boot_mt-3" >
                                        <div class="mo_boot_col-sm-5">
                                            <strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_AUTHORIZED_REDIRECT_URI');?><sup><span class="mo_oauth_highlight">*</span></sup>:</strong>
                                        </div>
                                        <div class="mo_boot_col-sm-7">
                                            <input class="mo_boot_form-control" required="" type="text" name="mo_oauth_client_redirect_url" value="<?php echo $attribute['authorized_uri'];?>" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_AUTHORIZED_REDIRECT_URI_PLACEHOLDER');?>">
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_p-0 mo_boot_mt-2">
                                        <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_text-center">
                                            <input type="submit" name="submit" value="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_UPDATE_CLIENT');?>" class="oauth_blue_button mo_boot_mx-2" />
                                            <a href="<?php echo Route::_('index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=configuration'); ?>" class="mo_oauth_white_button mo_boot_py-2" ><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_GO_BACK');?></a>
                                        </div>
                                    </div> 
                                </form>
                            </div>
                       </div>
                    </div>
                </div>

                <!-- Premium Feature -->
                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_oauth_mini_section">
                   <!-- Header -->
                    <div class="mo_oauth_tab_header" onclick="toggleCollapse('mo_oauth_premium_feature', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_boot_col-sm-11 mo_oauth_tab_title">
                            <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_PREMIUM_FEATURES'); ?><sup> <small class="mo_oauth_highlight"><a href="index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=license" title="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_AVAILABLE_IN_PAID_PLANS_ONLY'); ?>"> <sup><img class="crown_img_small" src="<?php echo Uri::base(); ?>/components/com_miniorange_oauthserver/assets/images/crown.webp" alt="Premium"></sup></a></small></sup>
                        </div>
                        <div class="mo_boot_col-sm-1 mo_toggle_icon mo_boot_text-right"> + </div>
                    </div>

                    <!-- Content -->
                    <div id="mo_oauth_premium_feature" class="mo_boot_col-sm-12 mo_boot_mt-3" style="display:none">
                       <div class="mo_boot_row mo_boot_p-3">
                            <div class="mo_boot_col-sm-12 mo_boot_my-4">
                                <div class="mo_boot_row mo_boot_mt-3" >
                                    <div class="mo_boot_col-sm-3">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_GRANT_TYPE');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <select name="mo_oauth_grant_type" readonly class="mo_boot_form-control" id="mo_oauth_grant_type">
                                            <option value="" selected> <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_AUTHORIZATION_GRANT_TYPE');?></option>
                                            <option value="" disabled> <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_IMPLICIT_GRANT_TYPE');?></option>
                                            <option value="" disabled> <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_PASSWORD_GRANT_TYPE');?></option>
                                            <option value="" disabled> <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_REFRESH_TOKEN_GRANT_TYPE');?></option>
                                            <option value="" disabled> <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CLIENT_CREDENTIALS_GRANT_TYPE');?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-3" >
                                    <div class="mo_boot_col-sm-3">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ENABLE_PKCE');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <input type="radio" class="mo_oauth_cursor" name="mo_oauth_enable_pkce" disabled value="1"> <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ENABLE_PKCE_YES');?>
                                        <input type="radio" class="mo_oauth_cursor" name="mo_oauth_enable_pkce" disabled value="0"> <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ENABLE_PKCE_NO');?>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-3" >
                                    <div class="mo_boot_col-sm-3">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_TOKEN_EXPIRY');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <input class="mo_boot_form-control mo_oauth_cursor" required="" type="text" disabled name="mo_oauth_token_expiry" value="3600">
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-3" >
                                    <div class="mo_boot_col-sm-3">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_TOKEN_LENGTH');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <input class="mo_boot_form-control mo_oauth_cursor" required="" type="text" disabled name="mo_oauth_token_length" value="64">
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_p-0 mo_boot_mt-2">
                                    <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_text-center">
                                        <input type="submit" name="submit" value="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SAVE_CLIENT');?>" class="oauth_blue_button mo_boot_mx-2 mo_oauth_cursor" disabled/>
                                        <input type="submit" name="back" value="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_GO_BACK');?>" class="mo_oauth_white_button mo_boot_mx-2 mo_oauth_cursor" disabled/>
                                    </div>
                                </div> 
                            </div>
                       </div>
                    </div>
                </div>

                <!-- JWT Feature -->
                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_oauth_mini_section">
                   <!-- Header -->
                    <div class="mo_oauth_tab_header" onclick="toggleCollapse('mo_oauth_jwt_feature', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_boot_col-sm-11 mo_oauth_tab_title">
                            <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ENABLE_JWT_PREMIUM_FEATURE'); ?><sup> <small class="mo_oauth_highlight"><a href="index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=license" title="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_AVAILABLE_IN_PAID_PLANS_ONLY'); ?>"> <sup><img class="crown_img_small" src="<?php echo Uri::base(); ?>/components/com_miniorange_oauthserver/assets/images/crown.webp" alt="Premium"></sup></a></small></sup>
                        </div>
                        <div class="mo_boot_col-sm-1 mo_toggle_icon mo_boot_text-right"> + </div>
                    </div>

                    <!-- Content -->
                    <div id="mo_oauth_jwt_feature" class="mo_boot_col-sm-12 mo_boot_mt-3" style="display:none">
                        <div class="mo_boot_row mo_boot_px-3">
                            <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_d-flex mo_oauth_align-items-center mo_ouath_flex-gap-10">
                                <input type="checkbox" class="mo_oauth_cursor" disabled id="enablejwt" value="1" name="enablejwt" /> 
                                <span class="mo_oauth_black"><strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ENABLE_JWT');?></strong><sup><a target="_blank" href="https://developers.miniorange.com/docs/oauth-joomla/jwt-verification" title="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_KNOW_MORE'); ?>"> <i class="fa-regular fa-circle-question"></i> </a></sup></span>
                                <small><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ENABLE_JWT_DESCRIPTION');?></small>
                            </div>
                            <div class="mo_boot_col-sm-12 mo_boot_mt-3">          
                                <p class="mo_oauth_alert mo_boot_mx-0 mo_boot_my-3">
                                    <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ENABLE_JWT_NOTE');?>
                                </p>
                            </div>
                            <div class="mo_boot_col-sm-12">
                                <h4 class="mo_oauth_black"><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SIGNING_ALGORITHMS');?></h4> 
                            </div>
                            <div class="mo_boot_col-sm-12 mo_boot_my-3">
                                <table>
                                    <tr>
                                        <td>
                                            <input type="radio" disabled id="hsa" name="mo_server_jwt_encryption" class="mo_oauth_cursor" value="HSA" />&nbsp;HSA&nbsp;&nbsp;
                                            <input disabled id="rsa" type="radio" name="mo_server_jwt_encryption" class="mo_oauth_cursor" value="RSA"  /> RSA&nbsp;&nbsp;<br><br>
                                            <input type="button" disabled class="mo_boot_btn mo_boot_btn-primary mo_oauth_btns mo_oauth_cursor" value="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_DOWNLOAD_CERTIFICAE');?>"> <br><br>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="mo_boot_row mo_boot_p-0 mo_boot_mt-2">
                            <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_text-center">
                                <input type="submit" name="submit" value="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SAVE_CLIENT');?>" class="oauth_blue_button mo_boot_mx-2 mo_oauth_cursor" disabled/>
                                <input type="submit" name="back" value="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_GO_BACK');?>" class="mo_oauth_white_button mo_boot_mx-2 mo_oauth_cursor" disabled/>
                            </div>
                        </div> 
                    </div>
                </div>
            </div>
        </div>
    </div>    
    <?php
}


function mo_oauth_server_advance_settings()
{
    ?>

    <div class="mo_boot_col-sm-12 mo_main_oauth_section">
        <div class="mo_boot_row">
            
            <div class="mo_boot_col-lg-6 mo_boot_col-sm-8 mo_boot_px-0">
                <h3><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ADVANCE_SETTINGS');?></h3>
            </div>
                
            <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-2 mo_oauth_mini_section">
                <!-- Header -->
                <div class="mo_oauth_tab_header" onclick="toggleCollapse('mo_oauth_state_parameter', this.querySelector('.mo_toggle_icon'))">
                    <div class="mo_boot_col-sm-11 mo_oauth_tab_title">
                        <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_STATE_PARAMETER'); ?> <sup><a target="_blank" href="https://developers.miniorange.com/docs/oauth/wordpress/server/enforce-state-parameters" title="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_KNOW_MORE'); ?>"> <i class="fa-regular fa-circle-question"></i> </a></sup> <sup> <small class="mo_oauth_highlight"><a href="index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=license" title="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_AVAILABLE_IN_PAID_PLANS_ONLY'); ?>"> <sup><img class="crown_img_small" src="<?php echo Uri::base(); ?>/components/com_miniorange_oauthserver/assets/images/crown.webp" alt="Premium"></sup></a></small></sup></h3>
                    </div>
                    <div class="mo_boot_col-sm-1 mo_toggle_icon mo_boot_text-right"> + </div>
                </div>
                <!-- Content -->
                <div id="mo_oauth_state_parameter" class="mo_oauth_tab_content mo_boot_pt-0" style="display:none">
                    <div class="mo_boot_row mo_boot_px-3">
                        <div class="mo_boot_col-sm-12">
                            <input type="checkbox" name="mo_oauth_auto_redirect"  id="mo_oauth_auto_redirect" value="1" class="mo_boot_float-start mo_boot_mt-1" disabled /><label class="mo_boot_float-start mo_boot_mx-2" for="mo_oauth_auto_redirect">&nbsp;<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ENABLE_AUTHORIZE');?></label>
                            <p class="mo_boot_pt-2"> <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_STATE_PARAMTER_DESCRIPTION');?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_oauth_mini_section">
                 <!-- Header -->
                <div class="mo_oauth_tab_header" onclick="toggleCollapse('mo_oauth_protect_admin_login', this.querySelector('.mo_toggle_icon'))">
                    <div class="mo_boot_col-sm-11 mo_oauth_tab_title">
                        <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_PROTECT_ADMIN_LOGIN_PAGE_URL'); ?> <sup> <small class="mo_oauth_highlight"><a href="index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=license" title="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_AVAILABLE_IN_PAID_PLANS_ONLY'); ?>"><img class="crown_img_small" src="<?php echo Uri::base(); ?>/components/com_miniorange_oauthserver/assets/images/crown.webp" alt="Premium"></a></small></sup> <small>( <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_PROTECT_ADMIN_LOGIN_PAGE_URL_DETAILS');?> ) </small></h3>
                    </div>
                    <div class="mo_boot_col-sm-1 mo_toggle_icon mo_boot_text-right"> + </div>
                </div>
                <!-- Content -->
                <div id="mo_oauth_protect_admin_login" class="mo_oauth_tab_content mo_boot_pt-0" style="display:none">
                    <div class="mo_boot_col-sm-12">
                        <div class="mo_boot_row mo_boot_my-4">
                            <div class="mo_boot_col-sm-12">
                                <input type="checkbox" class="mo_boot_float-start mo_boot_mt-1" disabled />&nbsp;<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ENABLE_CUSTOM_LOGIN_PAGE_URL');?></label>
                            </div>
                        </div>
                        <div class="mo_boot_row  mo_boot_mt-3">
                            <div class="mo_boot_col-sm-4">
                                <p><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ACCESS_KEY_FOR_YOUR_ADMIN_LOGIN_URL');?></p>
                            </div>
                            <div class="mo_boot_col-sm-8">
                                <input class="mo_boot_form-control" type="text" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ACCESS_KEY_FOR_YOUR_ADMIN_LOGIN_URL_PLACEHOLDER');?>" disabled="disable"/>
                            </div>
                        </div>
                        <div class="mo_boot_row  mo_boot_mt-3">
                            <div class="mo_boot_col-sm-4">
                                <p><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CURRENT_ADMIN_LOGIN_URL');?></p>
                            </div>
                            <div class="mo_boot_col-sm-8 mo_boot_text-wrap">
                                <input type="text" class="mo_boot_form-control" name="" disabled  placeholder="<?php echo Uri::base();?>">
                            </div>
                        </div>
                        <div class="mo_boot_row  mo_boot_mt-3">
                            <div class="mo_boot_col-sm-4">
                                <p><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CUSTOM_ADMIN_LOGIN_URL');?></p>
                            </div>
                            <div class="mo_boot_col-sm-8 mo_boot_text-wrap">
                                <input type="text" class="mo_boot_form-control" name="" disabled  placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CUSTOM_ADMIN_LOGIN_URL_PLACEHOLDER');?>">
                            </div>
                        </div>
                        <div class="mo_boot_row mo_boot_mt-3">
                            <div class="mo_boot_col-sm-4">
                                <p><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_REDIRECT_AFTER_FAILED_RESPONSE');?></p>
                            </div>
                            <div class="mo_boot_col-sm-8">
                                <select class="mo_boot_form-control" id="failure_response">
                                    <option><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_REDIRECT_TO_HOMEPAGE');?></option>
                                    <option><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_REDIRECT_TO_CUSTOM_404_MESSAGE');?></option>
                                    <option><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_REDIRECT_TO_CUSTOM_REDIRECT_URL');?></option>
                                </select>
                            </div>
                        </div>
                        <div class="mo_boot_row mt-3">
                            <div class="mo_boot_col-sm-4">
                                <p><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CUSTOM_REDIRECT_URL_AFTER_FAILURE');?></p>
                            </div>
                            <div class="mo_boot_col-sm-8">
                                <input class="mo_boot_form-control" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CUSTOM_REDIRECT_URL_AFTER_FAILURE_PLACEHOLDER');?>" disabled type="text"/>
                            </div>
                        </div>
                        <div class="mo_boot_row  mo_boot_mt-3" id="custom_message">
                            <div class="mo_boot_col-sm-4">
                                <p><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CUSTOM_ERROR_MESSAGE_AFTER_FAILURE');?></p>
                            </div>
                            <div class="mo_boot_col-sm-8">
                                <textarea class="mo_OAuth_textbox_border mo_oauth_width mo_boot_px-2" disabled placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CUSTOM_ERROR_MESSAGE_AFTER_FAILURE_PLACEHOLDER');?>"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="mo_boot_col-sm-12  mo_boot_mt-4  mo_boot_text-center">
                        <input type="submit" class="oauth_blue_button" value="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SAVE_ADMIN_LOGIN_PAGE_URL_SETTINGS');?>" disabled/>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
}

function mo_oauth_server_client_config() 
{
    $versionObj = new Version();
    $version = $versionObj->getShortVersion();

    $redirectUrlByVersion = "";

    if(version_compare($version, '4.0.0', '>=')) {
        $redirectUrlByVersion = "api/";
    }
    ?>
    <div class="mo_boot_col-sm-12 mo_main_oauth_section">
        <div class="mo_boot_row">
            <div class="mo_boot_col-sm-12">
                <div class="mo_boot_row mo_boot_my-0">
                    <div class="mo_boot_col-lg-6 mo_boot_col-sm-8">
                        <h3><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ENDPOINT_URI');?></h3>
                        <span><em><p><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ENDPOINT_DESCRIPTION');?><p></em></span>
                    </div>
                    <div class="mo_boot_col-lg-6 mo_boot_col-sm-4">
                        <a href="<?php echo Route::_('index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=configuration'); ?>" class="mo_boot_float-right mo_boot_mx-1 oauth_blue_button" ><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ENDPOINT_BACK');?></a>
                    </div>
                </div>

                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4">
                    <table class="mo_boot_table mo_boot_table-bordered">
                        <tr>
                            <th>
                                <strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_AUTHORIZE_ENDPOINT');?> </strong> :
                            </th>
                            <td>    
                                <span id="auth_endpoint" ><?php echo Uri::root(). $redirectUrlByVersion ."index.php/v1/moserver/auth" ?></span> 
                                <em class="fa fa-pull-right fa-lg fa-copy mo_copy copytooltip mo_oauth_pointer mo_oauth_red"; onclick="copyToClipboard('#auth_endpoint');"  >
                                <span class="copytooltiptext"><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_COPIED'); ?></span> </em> 
                            </td>

                        </tr>
                        <tr>
                            <th>
                                <strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_ACCESS_TOKEN_ENDPOINT');?> </strong> :
                            </th>
                            <td>
                                <span id="acc_token_enpoint"><?php echo Uri::root(). $redirectUrlByVersion ."index.php/v1/moserver/token" ?></span>
                                <em class="fa fa-pull-right fa-lg fa-copy mo_copy copytooltip mo_oauth_pointer mo_oauth_red"; onclick="copyToClipboard('#acc_token_enpoint');" >
                                <span class="copytooltiptext"><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_COPIED'); ?></span> </em>

                            </td>            
                        </tr>
                        <tr>
                            <th >
                                <strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_GET_USER_INFO_ENDPOINT');?> </strong> :
                            </th>
                            <td>
                                <span id="user_info_endpoint"><?php echo Uri::root(). $redirectUrlByVersion ."index.php/v1/moserver/userinfo"; ?></span>
                                <em class="fa fa-pull-right fa-lg fa-copy mo_copy copytooltip mo_oauth_pointer mo_oauth_red"; onclick="copyToClipboard('#user_info_endpoint');">
                                <span class="copytooltiptext"><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_COPIED'); ?></span> </em>
                            </td>        
                        </tr>
                        <tr>
                            <th>
                                <strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SCOPE');?> </strong> : 
                            </th>
                            <td>
                                <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SCOPE_EMAIL');?>
                            </td>        
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function mo_oauth_server_support()
{
    $result = MoOAuthServerUtility::getCustomerDetails();    
    $current_user =  Factory::getUser();
    $admin_email = $result['email'];
    $admin_phone = $result['admin_phone'];
    if($admin_email == '') {
        $admin_email = $current_user->email;
    }
    
    $contry_code = MoOauthServerCustomer::get_phone_country_code();

    ?>

        <div class="mo_boot_col-sm-12">
            <form name="f" method="post" action="<?php echo Route::_('index.php?option=com_miniorange_oauthserver&view=accountsetup&task=accountsetup.moOAuthContactUs');?>">
                <div class="mo_boot_row mo_boot_d-flex mo_oauth_justify-center">

                    <!-- Email -->
                    <div class="mo_boot_col-sm-7 mo_boot_mt-2 mo_boot_d-flex mo_oauth_align-items-center mo_ouath_flex-gap-10">
                        <label class="mo_boot_col-sm-3" for="query_email"><strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SUPPORT_EMAIL'); ?> </span><sup><span class="mo_oauth_highlight">*</span></sup> :</strong></label>
                        <input type="email" id="query_email" class="mo_boot_form-control mo_OAuth_textbox_border mo_boot_col-sm-9"  
                            name="query_email" value="<?php echo $admin_email; ?>" 
                            placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SUPPORT_EMAIL_PLACEHOLDER');?>" required />
                    </div>

                    <!-- Phone -->
                    <div class="mo_boot_col-sm-7 mo_boot_mt-2 mo_boot_d-flex mo_oauth_align-items-center mo_ouath_flex-gap-10 mo_boot_mt-3">
                        <label class="mo_boot_col-sm-3" for="query_phone"><strong>
                            <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_PHONE_NUMBER'); ?> :
                        </strong></label>

                        <div class="mo_boot_col-sm-9 mo_boot_d-flex mo_ouath_flex-gap-10 mo_boot_p-0">
                            <select id="country_code" name="country_code" class="mo_boot_form-control mo_OAuth_textbox_border" style="width: 40%;">
                                <?php foreach ($contry_code as $country): ?>
                                    <option value="<?php echo $country['code']; ?>" 
                                        <?php echo ($country['code'] == '+91') ? 'selected' : ''; ?>>
                                        <?php echo $country['name'] . ' (' . $country['code'] . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                                
                            <input type="number" id="query_phone" class="mo_boot_form-control mo_OAuth_textbox_border"
                                name="query_phone" value="<?php echo $admin_phone; ?>"
                                placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SUPPORT_PHONE_PLACEHOLDER'); ?>" style="width: 60%;" />
                        </div>
                    </div>

                    <!-- Query -->
                    <div class="mo_boot_col-sm-7 mo_boot_mt-2 mo_boot_d-flex  mo_ouath_flex-gap-10 mo_boot_mt-3">
                        <label class="mo_boot_col-sm-3" for="query"><strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_QUERY'); ?> </span><sup><span class="mo_oauth_highlight">*</span></sup> :</strong></label>
                        <textarea id="query" name="query" class="mo_OAuth_textbox_border mo_boot_px-2 mo_oauth_width mo_boot_col-sm-9" 
                            rows="6" 
                            placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SUPPORT_QUERY_PLACEHOLDER');?>"
                            required></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="mo_boot_col-sm-12 mo_boot_my-3 mo_boot_text-center">
                        <input type="submit" name="send_query"  
                            value="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SUPPORT_SUBMIT_QUERY');?>"
                            class="oauth_blue_button" />
                    </div>

                </div>
            </form>
        </div>
    <?php
}

function mo_oauth_server_request_demo()
{
    ?>
    <div class="mo_boot_col-sm-12">
        <form id="demo_request" name="demo_request" method="post" action='<?php echo Route::_("index.php?option=com_miniorange_oauthserver&view=accountsetup&task=accountsetup.moOAuthRequestForDemoPlan");?>' >
            <div class="mo_boot_row mo_boot_d-flex mo_oauth_justify-center">

                <div class="mo_boot_col-sm-7 mo_boot_mt-2 mo_boot_d-flex mo_oauth_align-items-center mo_ouath_flex-gap-10">
                    <div class="mo_boot_col-sm-3">
                        <strong><span><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SUPPORT_EMAIL');?>:</span><sup><span class="mo_oauth_highlight">*</span></sup></strong>
                    </div>
                    <div class="mo_boot_col-sm-9">
                        <input type="email" class="mo_boot_form-control mo_OAuth_textbox_border" onblur="validateEmail(this)" name="email" placeholder="person@example.com" value='<?php echo Factory::getUser()->email ;?>' />
                        <p class="mo_oauth_disp_no mo_oauth_red"id="email_error">Invalid Email</p>
                    </div>
                </div>

                <div class="mo_boot_col-sm-7 mo_boot_mt-2 mo_boot_d-flex mo_oauth_align-items-center mo_ouath_flex-gap-10">
                    <div class="mo_boot_col-sm-3">
                        <strong><span><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_REQUEST_DEMO_TRIAL');?></span></strong>
                    </div>
                    <div class="mo_boot_col-sm-9">
                        <input type="text" class="mo_boot_form-control mo_OAuth_textbox_border" name="plan" value="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_JOOMLA_OAUTH_SERVER_PREMIUM_PLUGIN');?>" readonly >
                    </div>
                </div>

                <div class="mo_boot_col-sm-7 mo_boot_mt-2 mo_boot_d-flex mo_oauth_align-items-center mo_ouath_flex-gap-10">
                    <div class="mo_boot_col-sm-3">
                        <strong><span><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SUPPORT_DESCRIPTION');?>:</span><sup><span class="mo_oauth_highlight">*</span></sup></strong>
                    </div>
                    <div class="mo_boot_col-sm-9">
                        <textarea class="mo_OAuth_textbox_border mo_oauth_width mo_boot_px-2"
                                  required type="text" name="description"
                                  rows="6"
                                  placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SUPPORT_DESCRIPTION_PLACEHOLDER');?>"
                                  value=""></textarea>
                    </div>
                </div>

                <div class="mo_boot_col-sm-12 mo_boot_my-3 mo_boot_text-center">
                    <input type="submit" name="submit" value="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SUPPORT_SUBMIT');?>" class="oauth_blue_button" />
                </div>

            </div>       
        </form>
    </div>
    <?php
}

function mo_oauth_support()
{
    ?>
        <div class="mo_boot_row mo_boot_px-5 mo_boot_mt-4 mo_boot_d-flex mo_ouath_flex-gap-3 ">
            <div onclick = "changeSubMenu('#requestdemo',this , '#mo_general_support')" class="mo_boot_col mo_oauth_sub_menu mo_oauth_sub_menu_active">
                <span><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SUPPORT');?></span>
            </div>
            <div onclick = "changeSubMenu('#requestdemo',this,'#mo_request_demo')" class=" mo_boot_col mo_oauth_sub_menu">
                <span><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_REQUEST_DEMO');?></span>
            </div>
        </div>


        <div class="mo_boot_row mo_boot_m-0 mo_boot_p-1">
            <div class="mo_boot_col-sm-12">
                
                <div class="mo_boot_row mo_boot_m-1 mo_boot_my-3" id="mo_general_support">
                    <div class="mo_boot_col-sm-12 mo_boot_mt-2">
                        <div class="mo_boot_row mo_boot_mt-2">
                            <?php echo mo_oauth_server_support();  ?>
                        </div>
                    </div>
                </div>

                <div class="mo_boot_row mo_boot_m-1 mo_boot_my-3" style="display: none;" id="mo_request_demo">
                    <div class="mo_boot_col-sm-12 mo_boot_mt-2">
                        <div class="mo_boot_row mo_boot_mt-2">
                            <?php echo mo_oauth_server_request_demo();  ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
}

function mo_oauth_server_licensing_plan()
{
    ?>

    <div class="mo_boot_row">
        <div class="mo_boot_col-sm-12">
            <!-- Plans -->
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12 mo_boot_my-4">
                    <div class="mo_oauth_pricing_wrapper">
                        <!-- Free -->
                        <div class="mo_oauth_pricing_table">
                            <div class="mo_oauth_license_plan_name"><?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_FREE_PLAN'); ?></div>
                            <div class="mo_oauth_license_price mo_boot_my-2"><?php echo Text::_('COM_MINIORANGE_FREE'); ?><small><small></small></small></div>
                            <div class="mo_oauth_license_btn">
                                <a href="index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=license"><?php echo Text::_('COM_MINIORANGE_OAUTH_CURRENT_PLAN'); ?></a>
                            </div>

                            <div class="mo_boot_my-4">
                                <div class="mo_boot_d-flex mo_oauth_justify-content-between" onclick="toggleFeatureList('mo_free_feature_include')">
                                    <div ><span class="mo_oauth_square_check"><i class="fa-solid fa-square-check"></i></span></div>
                                    <div class="mo_oauth_feature_title"><?php echo Text::_('COM_MINIORANGE_OAUTH_INCLUDED_FEATURES'); ?></div>
                                    <div><span class="mo_oauth_feature_arrow"> <i class="fa-solid fa-chevron-down"></i> </span></div>
                                </div>

                                <ul id="mo_free_feature_include" class="mo_feature_list" style="display: none;">
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_1'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_2'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_3'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_4'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_5'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_15'); ?> </li>
                                </ul>
                                </ul>
                            </div>

                            <div class="mo_boot_my-4">
                                <div class="mo_boot_d-flex mo_oauth_justify-content-between" onclick="toggleFeatureList('mo_free_feature_exclude')">
                                    <div ><span class="mo_oauth_square_xmark"><i class="fa-solid fa-square-xmark"></i></span></div>
                                    <div class="mo_oauth_feature_title"> <?php echo Text::_('COM_MINIORANGE_OAUTH_EXCLUDED_FEATURES'); ?> </div>
                                    <div><span class="mo_oauth_feature_arrow"> <i class="fa-solid fa-chevron-down"></i> </span></div>
                                </div>

                                <ul id="mo_free_feature_exclude" class="mo_feature_list" style="display: none;">
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_6'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_7'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_8'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_9'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_10'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_11'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_12'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_13'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_14'); ?> </li>
                            </div>
                        </div>


                        <!-- Premium -->
                         <div class="mo_oauth_pricing_table">
                            <div class="mo_oauth_license_plan_name"><?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN'); ?><small style="font-weight: 500;"><small> (monthly and yearly plans available)</small></small></div>
                            <div class="mo_oauth_license_price mo_boot_py-3"><p></p></div>
                            <div class="mo_oauth_license_btn mo_boot_mt-4">
                                <a href="https://plugins.miniorange.com/joomla-oauth-server#pricing" target="_blank"><?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_BASIC_PLAN_COST'); ?></a>
                            </div>

                            <div class="mo_boot_my-4">
                                <div class="mo_boot_d-flex mo_oauth_justify-content-between" onclick="toggleFeatureList('mo_premium_feature_include')">
                                    <div ><span class="mo_oauth_square_check"><i class="fa-solid fa-square-check"></i></span></div>
                                    <div class="mo_oauth_feature_title"><?php echo Text::_('COM_MINIORANGE_OAUTH_INCLUDED_FEATURES'); ?></div>
                                    <div><span class="mo_oauth_feature_arrow"> <i class="fa-solid fa-chevron-down"></i> </span></div>
                                </div>

                                <ul id="mo_premium_feature_include" class="mo_feature_list" style="display: none;">
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_1'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_2'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_3'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_4'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_5'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_6'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_7'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_8'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_9'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_10'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_11'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_12'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_13'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_14'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN_FEATURE_15'); ?> </li>
                                </ul>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mo_boot_px-5">
                <div class="mo_boot_col-sm-12 mo_boot_mt-4 mo_oauth_mini_section">
                    <div class="mo_oauth_tab_header mo_boot_d-flex mo_oauth_justify-content-between mo_oauth_align-items-center mo_boot_p-3"
                         onclick="toggleCollapse('mo_oauth_how_to_upgrade', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_oauth_tab_title">
                            <?php echo Text::_('COM_MINIORANGE_UPGRADE_PLAN'); ?>
                        </div>
                        <div class="mo_toggle_icon"> + </div>
                    </div>

                    <div id="mo_oauth_how_to_upgrade" class="mo_boot_col-sm-12 mo_boot_mt-3" style="display: none;">
                        <div class="mo_boot_row  ">
                            <div class="  mo_boot_col-sm-6  mo_oauth_works-step ">
                                <div><strong>1</strong></div>
                                <p><?php echo Text::_('COM_MINIORANGE_UPGRADE_STEP1');?></p>
                            </div>
                            <div class=" mo_boot_col-sm-6  mo_oauth_works-step ">
                                <div ><strong>4</strong></div>
                                <p><?php echo Text::_('COM_MINIORANGE_UPGRADE_STEP4');?></p>
                            </div>            
                        </div>
                        <div class=" mo_boot_row  ">
                            <div class="  mo_boot_col-sm-6  mo_oauth_works-step">
                                <div ><strong>2</strong></div>
                                <p> <?php echo Text::_('COM_MINIORANGE_UPGRADE_STEP2');?></p>
                            </div>
                            <div class=" mo_boot_col-sm-6  mo_oauth_works-step">
                                <div ><strong>5</strong></div>
                                <p><?php echo Text::_('COM_MINIORANGE_UPGRADE_STEP5');?> </p>
                            </div>         
                        </div>
                        <div class=" mo_boot_row  ">
                            <div class=" mo_boot_col-sm-6  mo_oauth_works-step">
                                <div ><strong>3</strong></div>
                                <p><?php echo Text::_('COM_MINIORANGE_UPGRADE_STEP3');?></p>
                            </div>
                            <div class="  mo_boot_col-sm-6  mo_oauth_works-step">
                                <div ><strong>6</strong></div>
                                <p><?php echo Text::_('COM_MINIORANGE_UPGRADE_STEP6');?></p>
                            </div>       
                        </div> 
                    </div>
                </div>


                <div class="mo_boot_col-sm-12 mo_boot_mt-4 mo_oauth_mini_section">
                    <div class="mo_oauth_tab_header mo_boot_d-flex mo_oauth_justify-content-between mo_oauth_align-items-center mo_boot_p-3"
                         onclick="toggleCollapse('mo_oauth_return_policy', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_oauth_tab_title">
                            <?php echo Text::_('COM_MINIORANGE_RETURN_POLICY'); ?>
                        </div>
                        <div class="mo_toggle_icon"> + </div>
                    </div>

                    <div id="mo_oauth_return_policy" class="mo_boot_col-sm-12 mo_boot_mt-3" style="display: none;">
                        <div class="mo_boot_row">
                            <div class="mo_boot_col-sm-12 mo_boot_pb-3">
                                <p style="font-size:16px;"> <?php echo Text::_('COM_MINIORANGE_RETURN_POLICY_DETAILS');?></p><br>
                                <div class="mo_oauth_return_policy_work">
                                    <h4><?php echo Text::_('COM_MINIORANGE_RETURN_POLICY_HOW_IT_WORKS');?> : </h4>
                                    <ol>
                                        <li><?php echo Text::_('COM_MINIORANGE_RETURN_POLICY_HOW_IT_WORKS_1'); ?></li>
                                        <li><?php echo Text::_('COM_MINIORANGE_RETURN_POLICY_HOW_IT_WORKS_2'); ?></li>
                                        <li><?php echo Text::_('COM_MINIORANGE_RETURN_POLICY_HOW_IT_WORKS_3'); ?></li>
                                    </ol>
                                </div>
                                <div class="mo_oauth_return_policy_work">
                                    <h4><?php echo Text::_('COM_MINIORANGE_RETURN_POLICY_NOT_ISSUED');?> : </h4>
                                    <ul>
                                        <li><?php echo Text::_('COM_MINIORANGE_RETURN_POLICY_NOT_ISSUED_1'); ?></li>
                                        <li><?php echo Text::_('COM_MINIORANGE_RETURN_POLICY_NOT_ISSUED_2'); ?></li>
                                        <li><?php echo Text::_('COM_MINIORANGE_RETURN_POLICY_NOT_ISSUED_3'); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <br>
                <div class="mo_boot_col-sm-12 mo_boot_px-0">
                    <p> 📧 <?php echo Text::_('COM_MINIORANGE_RETURN_POLICY_NEED_HELP'); ?></p>
                </div>
            </div>
        </div>
    </div> 
    
    <?php
}

function mo_oauth_show_advance_mapping()
{
    ?>
    <div class="mo_boot_col-sm-12 mo_main_oauth_section">
        <div class="mo_boot_row">
            <div class="mo_boot_col-sm-12">
                <!-- Custom Attribute Mapping -->
                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-2 mo_oauth_mini_section">
                    <!-- Header -->
                    <div class="mo_oauth_tab_header" onclick="toggleCollapse('mo_oauth_custom_mapping', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_boot_col-sm-11 mo_oauth_tab_title">
                            <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CUSTOM_ATTRIBUTE_MAPPING'); ?> <sup> <small class="mo_oauth_highlight"><a href="index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=license" title="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_AVAILABLE_IN_PAID_PLANS_ONLY'); ?>"> <sup><img class="crown_img_small" src="<?php echo Uri::base(); ?>/components/com_miniorange_oauthserver/assets/images/crown.webp" alt="Premium"></sup></a></small></sup></h3>
                        </div>
                        <div class="mo_boot_col-sm-1 mo_toggle_icon mo_boot_text-right"> - </div>
                    </div>

                    <div id="mo_oauth_custom_mapping" class="mo_oauth_tab_content mo_boot_pt-0" style="display:block">
                        <div class="mo_boot_row mo_boot_px-3">
                            <div class="mo_boot_col-sm-12 mo_boot_my-2">
                                <div class="mo_boot_row">
                                    <div class="mo_boot_col-sm-6 mo_boot_text_center">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CUSTOM_ATTRIBUTE');?> <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CUSTOM_ATTRIBUTE_NAME');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-6">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CUSTOM_ATTRIBUTE');?> <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CUSTOM_ATTRIBUTE_VALUE');?></strong>
                                    </div>
                                </div>
                                <div class="mo_boot_row">
                                    <?php
                                    for($icnt = 1; $icnt <= 3; $icnt++)
                                        {
                                        ?>
                                            <div class="mo_boot_col-sm-6 mo_boot_mt-2">
                                                <input type="text" class="mo_oauth_server_textfield mo_boot_form-control" disabled="disabled" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CUSTOM_ATTRIBUTE_PLACEHOLDER');?>"/>
                                            </div>
                                            <div class="mo_boot_col-sm-6 mo_boot_mt-2">
                                                <select class="mo_oauth_server_textfield mo_boot_form-control">
                                                    <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SELECT_CUSTOM_ATTRIBUTE');?></option>
                                                    <option value="emailAddress"><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CUSTOM_ATTRIBUTES_EMAIL_ADDRESS');?></option>
                                                    <option value="username"><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CUSTOM_ATTRIBUTES_USERNAME');?></option>
                                                    <option value="name"><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CUSTOM_ATTRIBUTES_NAME');?></option>
                                                    <option value="firstname"><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CUSTOM_ATTRIBUTES_FIRST_NAME');?></option>
                                                    <option value="lastname"><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CUSTOM_ATTRIBUTES_LAST_NAME');?></option>
                                                    <option value="groups"><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CUSTOM_ATTRIBUTES_GROUPS');?></option>
                                                </select>
                                            </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="col-sm-12 mo_boot_mt-5 mo_boot_mb-3 mo_boot_text-center">
                                    <input type="submit" class="oauth_blue_button" value="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_SAVE_ADDITIONAL_USER_ATTRIBUTE_MAPPING');?>" disabled/>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <?php
}
function exportConfiguration()
{
    ?>
    <div class="mo_boot_col-sm-12 mo_main_oauth_section">
        <div class="mo_boot_row">
            <div class="mo_boot_col-sm-12 mo_boot_px-0">
                <h2><?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_IMPORT_EXPORT_CONFIGURATION'); ?></h2>
            </div>
            <div class="mo_boot_col-sm-12 mo_boot_p-3 mo_boot_mt-2 mo_oauth_mini_section">
                <div class="mo_boot_row mo_boot_d-flex mo_oauth_align-items-center mo_oauth_justify-content-between">
                    <div class="mo_boot_col-sm-8">
                        <div><h3><?php echo Text::_('COM_MINIORANGE_EXPORT_CONFIGURATION'); ?></h3></div>
                        <div class="mo_boot_mt-3"><?php echo Text::_('COM_MINIORANGE_EXPORT_CONFIGURATION_TEXT'); ?></div>
                    </div>
                    <div class="mo_boot_col-sm-3">
                        <form action="<?php echo Route::_('index.php?option=com_miniorange_oauthserver&task=accountsetup.exportConfiguration'); ?>" method="post">
                            <button type="submit" class=" oauth_blue_button "> <i class="fa-solid fa-download"></i>  <?php echo Text::_('COM_MINIORANGE_EXPORT_CONFIGURATION'); ?></button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="mo_boot_col-sm-12 mo_boot_p-3 mo_boot_mt-2 mo_oauth_mini_section">
                <div class="mo_boot_row mo_boot_d-flex mo_oauth_align-items-center mo_oauth_justify-content-between">
                    <div class="mo_boot_col-sm-8">
                        <div class="mo_boot_d-flex"><h3><?php echo Text::_('COM_MINIORANGE_IMPORT_CONFIGURATION'); ?></h3> <sup> <small class="mo_oauth_highlight"><a href="index.php?option=com_miniorange_oauthserver&view=accountsetup&tab-panel=license" title="<?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_AVAILABLE_IN_PAID_PLANS_ONLY'); ?>"> <sup><img class="crown_img_small" src="<?php echo Uri::base(); ?>/components/com_miniorange_oauthserver/assets/images/crown.webp" alt="Premium"></sup></a></small></sup></div>
                        <div class="mo_boot_mt-3"><?php echo Text::_('COM_MINIORANGE_IMPORT_CONFIGURATION_TEXT'); ?></div>
                    </div>
                    <div class="mo_boot_col-sm-3">
                        <button type="submit" class=" oauth_blue_button" disabled><i class="fa-solid fa-upload"></i>  <?php echo Text::_('COM_MINIORANGE_IMPORT_CONFIGURATION'); ?></button>
                    </div>
                </div>

                <div class="mo_boot_mt-3 mo_boot_d-flex mo_oauth_align-items-center mo_ouath_flex-gap-3">
                    <input type="file" id="fileInput" name="file" accept=".json" style="display: none;">
                    <button type="button" class="mo_oauth_white_button mo_boot_px-5" disabled onclick="document.getElementById('fileInput').click();">
                        <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_CHOOSE_FILE'); ?>
                    </button>
                    <span id="fileName"> <?php echo Text::_('COM_MINIORANGE_OAUTHSERVER_NO_FILES_UPLOADED'); ?></span>
                </div>

            </div>
        </div>
    </div>
    <?php
}
?>