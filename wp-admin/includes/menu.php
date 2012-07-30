<?php

/**
 * Build Administration Menu.
 *
 * @package WordPress
 * @subpackage Administration
 */

if ( is_network_admin() )
	do_action('_network_admin_menu');
elseif ( is_user_admin() )
	do_action('_user_admin_menu');
else
	do_action('_admin_menu');

$_wp_submenu_nopriv = array();
$_wp_menu_nopriv = array();

$admin_menu->_loop( '_generate_admin_page_hooks' );
$admin_menu->_loop( '_check_admin_submenu_privs' );

// Create list of page plugin hook names.
function _generate_admin_page_hooks( $menu_item, $admin_menu ) {
	global $admin_page_hooks;

	if ( false !== $pos = strpos($menu_item->slug, '?') ) {
		// Handle post_type=post|page|foo pages.
		$hook_name = substr($menu_item->slug, 0, $pos);
		$hook_args = substr($menu_item->slug, $pos + 1);
		wp_parse_str($hook_args, $hook_args);
		// Set the hook name to be the post type.
		if ( isset($hook_args['post_type']) )
			$hook_name = $hook_args['post_type'];
		else
			$hook_name = basename($hook_name, '.php');
		unset($hook_args);
	} else {
		$hook_name = basename($menu_item->slug, '.php');
	}
	$hook_name = sanitize_title($hook_name);

	if ( isset($compat[$hook_name]) )
		$hook_name = $compat[$hook_name];
	elseif ( !$hook_name )
		continue;

	$admin_page_hooks[$menu_item->slug] = $hook_name;
}

function _check_admin_submenu_privs( $menu_item, $admin_menu ) {
	global $_wp_submenu_nopriv;

	// Loop over submenus and remove items for which the user does not have privs.
	foreach ( $menu_item->get_children() as $submenu ) {
		if ( !current_user_can( $submenu->cap ) ) {
			$menu_item->remove( $submenu->id );
			$_wp_submenu_nopriv[$menu_item->slug][$submenu->slug] = true;
		}
	}

	// Menus for which the original parent is not accessible due to lack of privs
	// will have the next submenu in line be assigned as the new menu parent.
	$subs = $menu_item->get_children();

	if ( empty( $subs ) )
		return;

	$first_sub = array_shift( $subs );

	$old_parent = $menu_item->slug;
	$new_parent = $first_sub->slug;

	if ( $new_parent != $old_parent ) {
		foreach ( $subs as $sub ) {
			$first_sub->append( $sub );
		}

		$admin_menu->replace( $menu_item->id, $first_sub );

		$_wp_real_parent_file[$old_parent] = $new_parent;

		if ( isset($_wp_submenu_nopriv[$old_parent]) )
			$_wp_submenu_nopriv[$new_parent] = $_wp_submenu_nopriv[$old_parent];
	}
}

if ( is_network_admin() )
	do_action('network_admin_menu', '');
elseif ( is_user_admin() )
	do_action('user_admin_menu', '');
else
	do_action('admin_menu', '');

$admin_menu->_loop( '_check_admin_menu_privs' );

// Remove menus that have no accessible submenus
// and require privs that the user does not have.
function _check_admin_menu_privs( $menu_item, $admin_menu ) {
	global $_wp_menu_nopriv;

	if ( ! current_user_can( $menu_item->cap ) )
		$_wp_menu_nopriv[$menu_item->slug] = true;

	$subs = $menu_item->get_children();

	// If there is only one submenu and it is has same destination as the parent,
	// remove the submenu.
	if ( ! empty( $subs ) && 1 == count( $subs ) ) {
		$first_sub = array_shift( $subs );
		if ( $menu_item->slug == $first_sub->slug )
			$menu_item->remove( $first_sub->id );
	}

	// If submenu is empty...
	if ( !$menu_item->has_children() ) {
		// And user doesn't have privs, remove menu.
		if ( isset( $_wp_menu_nopriv[$menu_item->slug] ) ) {
			$admin_menu->remove( $menu_item->id );
		}
	}
}

// Remove any duplicated separators
$separator_found = false;
foreach ( $admin_menu->get_children() as $menu_item ) {
	if ( 'wp-menu-separator' == $menu_item->class ) {
		if ( !$separator_found ) {
			$separator_found = true;
		} else {
			$admin_menu->remove( $menu_item->id );
			$separator_found = false;
		}
	} else {
		$separator_found = false;
	}
}
unset($separator_found, $menu_item);

if ( !user_can_access_admin_page() ) {
	do_action('admin_page_access_denied');
	wp_die( __('You do not have sufficient permissions to access this page.') );
}

