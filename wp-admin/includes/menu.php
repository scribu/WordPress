<?php

/**
 * Build Plugin Administration Menus.
 *
 * @package WordPress
 * @subpackage Administration
 */

if ( is_network_admin() )
	do_action( '_network_admin_menu' );
elseif ( is_user_admin() )
	do_action( '_user_admin_menu' );
else
	do_action( '_admin_menu' );

$_wp_submenu_nopriv = array();
$_wp_menu_nopriv = array();

$admin_menu->_loop( '_generate_admin_page_hooks' );
$admin_menu->_loop( '_check_admin_submenu_privs' );

if ( is_network_admin() )
	do_action( 'network_admin_menu', '' );
elseif ( is_user_admin() )
	do_action( 'user_admin_menu', '' );
else
	do_action( 'admin_menu', '' );

$admin_menu->_loop( '_check_admin_menu_privs' );

if ( !user_can_access_admin_page() ) {
	do_action( 'admin_page_access_denied' );
	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
}

