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
	'slug' => 'index.php',
) );

$admin_menu->append( array(
	'id' => 'separator1',
	'class' => 'wp-menu-separator',
) );

$admin_menu->append( array(
	/* translators: Sites menu item */
	'title' => __( 'Sites' ),
	'cap' => 'manage_sites',
	'id' => 'site',
	'slug' => 'sites.php',
) );

	$admin_menu->add_first_submenu( 'site', __( 'All Sites' ) );

	$admin_menu->add_submenu( 'site', array(
		/* translators: add new site */
		'title' => _x( 'Add New', 'site' ),
		'cap' => 'create_sites',
		'slug' => 'site-new.php',
	) );

$admin_menu->append( array(
	'title' => __( 'Users' ),
	'cap' => 'manage_network_users',
	'slug' => 'users.php',
	'id' => 'users',
) );

	$admin_menu->add_first_submenu( 'users', __( 'All Users') );

	$admin_menu->add_submenu( 'users', array(
		'title' => _x( 'Add New', 'user' ),
		'cap' => 'create_users',
		'slug' => 'user-new.php',
	) );

$update_data = wp_get_update_data();

$admin_menu->append( array(
	'title' => _admin_menu_theme_update_count( $update_data ),
	'cap' => 'manage_network_themes',
	'slug' => 'themes.php',
	'id' => 'appearance',
) );

	$admin_menu->add_first_submenu( 'appearance', __( 'Installed Themes' ) );

	$admin_menu->add_submenu( 'appearance', array(
		'title' => _x( 'Add New', 'theme' ),
		'cap' => 'install_themes',
		'slug' => 'theme-install.php',
	) );

	$admin_menu->add_submenu( 'appearance', array(
		'title' => _x( 'Editor', 'theme editor' ),
		'cap' => 'edit_themes',
		'slug' => 'theme-editor.php',
	) );

$admin_menu->append( array(
	'title' => _admin_menu_plugin_update_count( $update_data ),
	'cap' => 'manage_network_plugins',
	'slug' => 'plugins.php',
	'id' => 'plugins',
) );

	$admin_menu->add_first_submenu( 'plugins', __( 'Installed Plugins' ) );

	$admin_menu->add_submenu( 'plugins', array(
		'title' => _x( 'Add New', 'plugin' ),
		'cap' => 'install_plugins',
		'slug' => 'plugin-install.php',
	) );

	$admin_menu->add_submenu( 'plugins', array(
		'title' => _x( 'Editor', 'plugin editor' ),
		'cap' => 'edit_plugins',
		'slug' => 'plugin-editor.php',
	) );

$admin_menu->append( array(
	'title' => __('Settings'),
	'cap' => 'manage_network_options',
	'slug' => 'settings.php',
	'id' => 'settings',
) );

	$admin_menu->add_first_submenu( 'settings', __('Network Settings') );

	$admin_menu->add_submenu( 'settings', array(
		'title' => __('Writing'),
		'cap' => 'manage_network_options',
		'slug' => 'setup.php',
	) );

$admin_menu->append( array(
	'title' => _admin_menu_update_count( $update_data ),
	'cap' => 'manage_network',
	'slug' => 'upgrade.php',
	'class' => 'menu-icon-tools',
	'id' => 'update',
) );

	$admin_menu->add_first_submenu( 'update', __('Update Network') );

	$admin_menu->add_submenu( 'update', array(
		'title' => __('Available Updates'),
		'cap' => 'update_core',
		'slug' => 'update-core.php',
	) );

unset($update_data);

$admin_menu->append( array(
	'id' => 'separator-last',
	'class' => 'wp-menu-separator',
) );

require_once(ABSPATH . 'wp-admin/includes/menu.php');
