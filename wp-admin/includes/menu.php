<?php

/**
 * Build Administration Menus.
 *
 * @package WordPress
 * @subpackage Administration
 */

require ABSPATH . 'wp-admin/includes/class-wp-admin-menu.php';
require ABSPATH . 'wp-admin/includes/menu-functions.php';

if ( is_network_admin() ) {
	require ABSPATH . 'wp-admin/network/menu.php';
	do_action( '_network_admin_menu' );
} elseif ( is_user_admin() ) {
	require ABSPATH . 'wp-admin/user/menu.php';
	do_action( '_user_admin_menu' );
} else {
	require ABSPATH . 'wp-admin/menu.php';
	do_action( '_admin_menu' );
}

$_wp_submenu_nopriv = array();
$_wp_menu_nopriv = array();

_each_admin_menu_item( '_generate_admin_page_hooks' );
_each_admin_menu_item( '_check_admin_submenu_privs' );

if ( is_network_admin() )
	do_action( 'network_admin_menu', '' );
elseif ( is_user_admin() )
	do_action( 'user_admin_menu', '' );
else
	do_action( 'admin_menu', '' );

_each_admin_menu_item( '_check_admin_menu_privs' );

if ( !user_can_access_admin_page() ) {
	do_action( 'admin_page_access_denied' );
	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
}

