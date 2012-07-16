<?php
/**
 * Build Administration Menu.
 *
 * @package WordPress
 * @subpackage Administration
 */

$admin_menu = new WP_Admin_Menu;

$admin_menu->append( array(
	'title' => __( 'Dashboard' ),
	'cap' => 'read',
	'id' => 'dashboard',
	'url' => 'index.php',
	'_index' => 2
) );

	$admin_menu->add_first_submenu( 'dashboard', __( 'Home' ), 0 );

if ( is_multisite() ) {
	$admin_menu->add_submenu( 'dashboard', array(
		'title' => __( 'My Sites' ),
		'cap' => 'read',
		'url' => 'my-sites.php',
		'_index' => 5
	) );
}

if ( ! is_multisite() || is_super_admin() )
	$update_data = wp_get_update_data();

if ( ! is_multisite() ) {
	$admin_menu->add_submenu( 'dashboard', array(
		'title' => sprintf( __('Updates %s'), "<span class='update-plugins count-{$update_data['counts']['total']}' title='{$update_data['title']}'><span class='update-count'>" . number_format_i18n($update_data['counts']['total']) . "</span></span>" ),
		'cap' => array( 'update_core', 'update_plugins', 'update_themes' ),
		'url' => 'update-core.php',
		'_index' => 10
	) );
}

$admin_menu->append( array(
	'id' => 'separator1',
	'class' => 'wp-menu-separator',
	'_index' => 4
) );

$admin_menu->append( array(
	'title' => __( 'Posts' ),
	'cap' => 'edit_posts',
	'class' => 'open-if-no-js menu-icon-post',
	'id' => 'posts',
	'url' => 'edit.php',
	'_index' => 5
) );

	$admin_menu->add_first_submenu( 'posts', __( 'All Posts' ) );

	$admin_menu->add_submenu( 'posts', array(
		/* translators: add new post */
		'title' => _x( 'Add New', 'post' ),
		'cap' => 'edit_posts',
		'url' => 'post-new.php',
		'_index' => 10
	) );

	$admin_menu->_add_tax_submenus( 'posts', 'post' );

$admin_menu->append( array(
	'title' => __( 'Media' ),
	'cap' => 'upload_files',
	'id' => 'media',
	'url' => 'upload.php',
	'_index' => 10
) );

	$admin_menu->add_first_submenu( 'media', __( 'Library' ) );

	$admin_menu->add_submenu( 'media', array(
		/* translators: add new file */
		'title' => _x( 'Add New', 'file' ),
		'cap' => 'upload_files',
		'url' => 'media-new.php',
		'_index' => 10
	) );

$admin_menu->append( array(
	'title' => __( 'Links' ),
	'cap' => 'manage_links',
	'id' => 'links',
	'url' => 'link-manager.php',
	'_index' => 15
) );

	$admin_menu->add_first_submenu( 'links', __( 'All Links' ) );

	$admin_menu->add_submenu( 'links', array(
		/* translators: add new link */
		'title' => _x( 'Add New', 'link' ),
		'cap' => 'manage_links',
		'url' => 'link-add.php',
		'_index' => 10
	) );

	$admin_menu->add_submenu( 'links', array(
		'title' => __( 'Link Categories' ),
		'cap' => 'manage_categories',
		'url' => 'edit-tags.php?taxonomy=link_category',
		'_index' => 15
	) );

$admin_menu->append( array(
	'title' => __( 'Pages' ),
	'cap' => 'edit_pages',
	'class' => 'menu-icon-page',
	'id' => 'pages',
	'url' => 'edit.php?post_type=page',
	'_index' => 20
) );

	$admin_menu->add_first_submenu( 'pages', __( 'All Pages' ) );

	$admin_menu->add_submenu( 'pages', array(
		/* translators: add new link */
		'title' => _x( 'Add New', 'page' ),
		'cap' => 'edit_pages',
		'url' => 'post-new.php?post_type=page',
		'_index' => 10
	) );

	$admin_menu->_add_tax_submenus( 'pages', 'page' );

$awaiting_mod = wp_count_comments()->moderated;

$admin_menu->append( array(
	'title' => sprintf( __('Comments %s'), "<span class='awaiting-mod count-$awaiting_mod'><span class='pending-count'>" . number_format_i18n($awaiting_mod) . "</span></span>" ),
	'cap' => 'edit_posts',
	'id' => 'comments',
	'url' => 'edit-comments.php',
	'_index' => 25
) );

	$admin_menu->add_first_submenu( 'comments', __( 'All Comments' ), 0 );

unset($awaiting_mod);

$admin_menu->append( array(
	'id' => 'separator2',
	'class' => 'wp-menu-separator',
	'_index' => 59
) );

$admin_menu->append( array(
	'title' => __('Appearance'),
	'cap' => array( 'switch_themes', 'edit_theme_options' ),
	'url' => 'themes.php',
	'id' => 'appearance',
	'_index' => 60
) );

	$admin_menu->add_first_submenu( 'appearance', __( 'Themes') );

	if ( current_theme_supports( 'menus' ) || current_theme_supports( 'widgets' ) ) {
		$admin_menu->add_submenu( 'appearance', array(
			'title' => __('Menus'),
			'cap' => array( 'switch_themes', 'edit_theme_options' ),
			'url' => 'nav-menus.php',
			'_index' => 10
		) );
	}

	if ( ! is_multisite() ) {
		$admin_menu->add_submenu( 'appearance', array(
			'title' => _x('Editor', 'theme editor'),
			'cap' => 'edit_themes',
			'url' => 'theme-editor.php',
			'_index' => 15
		) );
	}

$count = '';
if ( ! is_multisite() && current_user_can( 'update_plugins' ) ) {
	if ( ! isset( $update_data ) )
		$update_data = wp_get_update_data();
	$count = "<span class='update-plugins count-{$update_data['counts']['plugins']}'><span class='plugin-count'>" . number_format_i18n($update_data['counts']['plugins']) . "</span></span>";
}

$admin_menu->append( array(
	'title' => sprintf( __('Plugins %s'), $count ),
	'cap' => 'activate_plugins',
	'url' => 'plugins.php',
	'id' => 'plugins',
	'_index' => 65
) );

	$admin_menu->add_first_submenu( 'plugins', __( 'Installed Plugins') );

	if ( ! is_multisite() ) {
		$admin_menu->add_submenu( 'plugins', array(
			/* translators: add new plugin */
			'title' => _x('Add New', 'plugin'),
			'cap' => 'install_plugins',
			'url' => 'plugin-install.php',
			'_index' => 10
		) );

		$admin_menu->add_submenu( 'plugins', array(
			'title' => _x('Editor', 'plugin editor'),
			'cap' => 'edit_plugins',
			'url' => 'plugin-editor.php',
			'_index' => 15
		) );
	}

unset( $update_data, $count );

if ( current_user_can('list_users') ) {
	$admin_menu->append( array(
		'title' => __('Users'),
		'cap' => 'list_users',
		'url' => 'users.php',
		'id' => 'users',
		'_index' => 70
	) );
} else {
	$admin_menu->append( array(
		'title' => __('Profile'),
		'cap' => 'read',
		'url' => 'profile.php',
		'id' => 'users',
		'_index' => 70
	) );
}

if ( current_user_can('list_users') ) {
	$_wp_real_parent_file['profile.php'] = 'users.php'; // Back-compat for plugins adding submenus to profile.php.

	$admin_menu->add_first_submenu( 'users', __( 'All Users') );

	$admin_menu->add_submenu( 'users', array(
		'title' => _x('Add New', 'user'),
		'cap' => array( 'create_users', 'promote_users' ),
		'url' => 'user-new.php',
		'_index' => 10
	) );

	$admin_menu->add_submenu( 'users', array(
		'title' => __('Your Profile'),
		'cap' => 'read',
		'url' => 'profile.php',
		'_index' => 15
	) );
} else {
	$_wp_real_parent_file['users.php'] = 'profile.php';

	$admin_menu->add_first_submenu( 'users', __( 'Your Profile') );

	$admin_menu->add_submenu( 'users', array(
		'title' => _x('Add New', 'user'),
		'cap' => array( 'create_users', 'promote_users' ),
		'url' => 'user-new.php',
		'_index' => 10
	) );
}

$admin_menu->append( array(
	'title' => __('Tools'),
	'cap' => 'edit_posts',
	'url' => 'tools.php',
	'id' => 'tools',
	'_index' => 75
) );

	$admin_menu->add_first_submenu( 'tools', __('Available Tools') );

	$admin_menu->add_submenu( 'tools', array(
		'title' => __('Import'),
		'cap' => 'import',
		'url' => 'import.php',
		'_index' => 10
	) );

	$admin_menu->add_submenu( 'tools', array(
		'title' => __('Export'),
		'cap' => 'export',
		'url' => 'export.php',
		'_index' => 15
	) );

	if ( is_multisite() && !is_main_site() ) {
		$admin_menu->add_submenu( 'tools', array(
			'title' => __('Delete Site'),
			'cap' => 'manage_options',
			'url' => 'ms-delete-site.php',
			'_index' => 25
		) );
	}

	if ( ! is_multisite() && defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE ) {
		$admin_menu->add_submenu( 'tools', array(
			'title' => __('Network Setup'),
			'cap' => 'manage_options',
			'url' => 'network.php',
			'_index' => 50
		) );
	}

$admin_menu->append( array(
	'title' => __('Settings'),
	'cap' => 'manage_options',
	'url' => 'options-general.php',
	'id' => 'settings',
	'_index' => 80
) );

	$admin_menu->add_first_submenu( 'settings', _x('General', 'settings screen'), 10 );

	$admin_menu->add_submenu( 'settings', array(
		'title' => __('Writing'),
		'cap' => 'manage_options',
		'url' => 'options-writing.php',
		'_index' => 15
	) );

	$admin_menu->add_submenu( 'settings', array(
		'title' => __('Reading'),
		'cap' => 'manage_options',
		'url' => 'options-reading.php',
		'_index' => 20
	) );

	$admin_menu->add_submenu( 'settings', array(
		'title' => __('Discussion'),
		'cap' => 'manage_options',
		'url' => 'options-writing.php',
		'_index' => 25
	) );

	$admin_menu->add_submenu( 'settings', array(
		'title' => __('Media'),
		'cap' => 'manage_options',
		'url' => 'options-media.php',
		'_index' => 30
	) );

	$admin_menu->add_submenu( 'settings', array(
		'title' => __('Privacy'),
		'cap' => 'manage_options',
		'url' => 'options-privacy.php',
		'_index' => 35
	) );

	$admin_menu->add_submenu( 'settings', array(
		'title' => __('Permalinks'),
		'cap' => 'manage_options',
		'url' => 'options-permalink.php',
		'_index' => 40
	) );

$admin_menu->append( array(
	'id' => 'separator-last',
	'class' => 'wp-menu-separator',
	'_index' => 99
) );

// CPT menus need to be added later due to 'menu_position'
$admin_menu->_add_cpt_menus();

// Back-compat for old top-levels
$_wp_real_parent_file['post.php'] = 'edit.php';
$_wp_real_parent_file['post-new.php'] = 'edit.php';
$_wp_real_parent_file['edit-pages.php'] = 'edit.php?post_type=page';
$_wp_real_parent_file['page-new.php'] = 'edit.php?post_type=page';
$_wp_real_parent_file['wpmu-admin.php'] = 'tools.php';
$_wp_real_parent_file['ms-admin.php'] = 'tools.php';

// ensure we're backwards compatible
$compat = array(
	'index' => 'dashboard',
	'edit' => 'posts',
	'post' => 'posts',
	'upload' => 'media',
	'link-manager' => 'links',
	'edit-pages' => 'pages',
	'page' => 'pages',
	'edit-comments' => 'comments',
	'options-general' => 'settings',
	'themes' => 'appearance',
	);

require_once(ABSPATH . 'wp-admin/includes/menu.php');
