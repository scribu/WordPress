<?php
/**
 * Build User Administration Menu.
 *
 * @package WordPress
 * @subpackage Administration
 * @since 3.1.0
 */

$admin_menu = new WP_Admin_Menu;

$admin_menu->append( array(
	'title' => __( 'Dashboard' ),
	'cap' => 'exist',
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
	'title' => __( 'Profile' ),
	'cap' => 'exist',
	'id' => 'users',
	'url' => 'profile.php',
	'_index' => 70
) );

$admin_menu->append( array(
	'id' => 'separator-last',
	'class' => 'wp-menu-separator',
	'_index' => 99
) );

$_wp_real_parent_file['users.php'] = 'profile.php';
$compat = array();

require_once(ABSPATH . 'wp-admin/includes/menu.php');
