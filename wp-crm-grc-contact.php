<?php
/*
Plugin Name: CRM GRC Contact API Form
Plugin URI: https://gendt.fr/
Description: A plugin that connect Sendinblue form with CRM GRC Contact API
Author: GENDT Sébastien
Author URI: https://gendt.fr/
Text Domain: crm-grc-contact
Version: 1.0.3
*/

/**
 * Pour ajouter contact form 7 voir hook : http://hookr.io/plugins/contact-form-7/4.7/hooks/#index=f
 */

namespace crmGrcContact;
require __DIR__ . '/vendor/autoload.php';

use SGPLUGIN\GrcContact;

/**
 * Define Constants
 * ================
 */
define( 'WP_GRC_VERSION', '1.0.3' );
define( 'WP_GRC_REQUIRED_WP_VERSION', '4.9' );
define( 'WP_GRC_PLUGIN', __FILE__ );
define( 'WP_GRC_PLUGIN_BASENAME', plugin_basename( WP_GRC_PLUGIN ) );
define( 'WP_GRC_PLUGIN_NAME', trim( dirname( WP_GRC_PLUGIN_BASENAME ), '/' ) );
define( 'WP_GRC_PLUGIN_DIR', str_replace(ABSPATH, '/',__DIR__) );
define( 'WP_GRC_PLUGIN_DIR_URL', plugin_dir_url(WP_GRC_PLUGIN) );

// Create an instance of plugin
$grcContact = GrcContact::getPlugin(WP_GRC_PLUGIN_NAME);
// Set main javascript file plugin
$grcContact->setScript('/assets/js/main.js');

// Create main menu and page for the administration
$grcContact->createAdminPage([
    'page_title' => __('CRM GRC Contact configuration', WP_GRC_PLUGIN_NAME),
    'menu_title' => __('GRC Contact', WP_GRC_PLUGIN_NAME),
    'menu_slug' => WP_GRC_PLUGIN_NAME.'-index',
    'icon_url' => WP_GRC_PLUGIN_DIR.'/assets/img/grc-sigle-color.ico'
]);
// Create submenu and sub page
$grcContact->createAdminSubpage([
    'parent_slug' => WP_GRC_PLUGIN_NAME.'-index',
    'page_title' => __('CRM GRC Contact configuration', WP_GRC_PLUGIN_NAME),
    'menu_title' => __('Configuration', WP_GRC_PLUGIN_NAME),
    'menu_slug' => WP_GRC_PLUGIN_NAME.'-index'
]);
$grcContact->createAdminSubpage([
    'parent_slug' => WP_GRC_PLUGIN_NAME.'-index',
    'page_title' => __('CRM GRC Contact logs API', WP_GRC_PLUGIN_NAME),
    'menu_title' => __('Logs API error', WP_GRC_PLUGIN_NAME),
    'menu_slug' => WP_GRC_PLUGIN_NAME.'-error-logs',
]);
$grcContact->createAdminSubpage([
    'parent_slug' => WP_GRC_PLUGIN_NAME.'-index',
    'page_title' => __('CRM GRC Contact logs API', WP_GRC_PLUGIN_NAME),
    'menu_title' => __('Logs API succès', WP_GRC_PLUGIN_NAME),
    'menu_slug' => WP_GRC_PLUGIN_NAME.'-logs',
]);
// Set local javascript file of plugin
$grcContact->setScript('/assets/js/index.js', WP_GRC_PLUGIN_NAME.'-index');
$grcContact->createAdminSubpage([
    'parent_slug' => WP_GRC_PLUGIN_NAME.'-index',
    'page_title' => __('CRM GRC Contact nouvelle API', WP_GRC_PLUGIN_NAME),
    'menu_title' => __('Nouvelle API', WP_GRC_PLUGIN_NAME),
    'menu_slug' => 'post-new.php?post_type='.$grcContact::post_type,
    'function' => false
]);
$grcContact->createAdminSubpage([
    'parent_slug' => WP_GRC_PLUGIN_NAME.'-index',
    'page_title' => __('CRM GRC Contact API', WP_GRC_PLUGIN_NAME),
    'menu_title' => __('Les APIs', WP_GRC_PLUGIN_NAME),
    'menu_slug' => 'edit.php?post_type='.$grcContact::post_type,
    'function' => false
]);
