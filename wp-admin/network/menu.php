<?php
/**
 * Build Network Administration Menu.
 *
 * @package WordPress
 * @subpackage Multisite
 * @since 3.1.0
 */

$admin_menu = new WP_Admin_Menu;

$admin_menu->append( array(
	/* translators: Network menu item */
	'title' => __( 'Dashboard' ),
	'cap' => 'manage_network',
	'id' => 'dashboard',
	'url' => 'index.php',
	'_index' => 2
) );

$admin_menu->append( array(
	'id' => 'separator1',
	'class' => 'wp-menu-separator',
	'_index' => 4
) );

$admin_menu->append( array(
	/* translators: Sites menu item */
	'title' => __( 'Sites' ),
	'cap' => 'manage_sites',
	'id' => 'site',
	'url' => 'sites.php',
	'_index' => 5
) );

	$admin_menu->add_first_submenu( 'site', __( 'All Sites' ) );

	$admin_menu->add_submenu( 'site', array(
		/* translators: add new site */
		'title' => _x( 'Add New', 'site' ),
		'cap' => 'create_sites',
		'url' => 'site-new.php',
		'_index' => 10
	) );

$admin_menu->append( array(
	'title' => __( 'Users' ),
	'cap' => 'manage_network_users',
	'url' => 'users.php',
	'id' => 'users',
	'_index' => 10
) );

	$admin_menu->add_first_submenu( 'users', __( 'All Users') );

	$admin_menu->add_submenu( 'users', array(
		'title' => _x( 'Add New', 'user' ),
		'cap' => 'create_users',
		'url' => 'user-new.php',
		'_index' => 10
	) );

$update_data = wp_get_update_data();

$admin_menu->append( array(
	'title' => _admin_menu_theme_update_count( $update_data ),
	'cap' => 'manage_network_themes',
	'url' => 'themes.php',
	'id' => 'appearance',
	'_index' => 15
) );

	$admin_menu->add_first_submenu( 'appearance', __( 'Installed Themes' ) );

	$admin_menu->add_submenu( 'appearance', array(
		'title' => _x( 'Add New', 'theme' ),
		'cap' => 'install_themes',
		'url' => 'theme-install.php',
		'_index' => 10
	) );

	$admin_menu->add_submenu( 'appearance', array(
		'title' => _x( 'Editor', 'theme editor' ),
		'cap' => 'edit_themes',
		'url' => 'theme-editor.php',
		'_index' => 15
	) );

$admin_menu->append( array(
	'title' => _admin_menu_plugin_update_count( $update_data ),
	'cap' => 'manage_network_plugins',
	'url' => 'plugins.php',
	'id' => 'plugins',
	'_index' => 20
) );

	$admin_menu->add_first_submenu( 'plugins', __( 'Installed Plugins' ) );

	$admin_menu->add_submenu( 'plugins', array(
		'title' => _x( 'Add New', 'plugin' ),
		'cap' => 'install_plugins',
		'url' => 'plugin-install.php',
		'_index' => 10
	) );

	$admin_menu->add_submenu( 'plugins', array(
		'title' => _x( 'Editor', 'plugin editor' ),
		'cap' => 'edit_plugins',
		'url' => 'plugin-editor.php',
		'_index' => 15
	) );

$admin_menu->append( array(
	'title' => __('Settings'),
	'cap' => 'manage_network_options',
	'url' => 'settings.php',
	'id' => 'settings',
	'_index' => 25
) );

	$admin_menu->add_first_submenu( 'settings', __('Network Settings') );

	$admin_menu->add_submenu( 'settings', array(
		'title' => __('Writing'),
		'cap' => 'manage_network_options',
		'url' => 'setup.php',
		'_index' => 10
	) );

$admin_menu->append( array(
	'title' => _admin_menu_update_count( $update_data ),
	'cap' => 'manage_network',
	'url' => 'upgrade.php',
	'class' => 'menu-icon-tools',
	'id' => 'update',
	'_index' => 30
) );

	$admin_menu->add_first_submenu( 'update', __('Update Network'), 10 );

	$admin_menu->add_submenu( 'update', array(
		'title' => __('Available Updates'),
		'cap' => 'update_core',
		'url' => 'update-core.php',
		'_index' => 15
	) );

unset($update_data);

$admin_menu->append( array(
	'id' => 'separator-last',
	'class' => 'wp-menu-separator',
	'_index' => 99
) );

require_once(ABSPATH . 'wp-admin/includes/menu.php');
