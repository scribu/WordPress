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
	'slug' => 'index.php',
) );

	$admin_menu->add_first_submenu( 'dashboard', __( 'Home' ) );

if ( is_multisite() ) {
	$admin_menu->add_submenu( 'dashboard', array(
		'title' => __( 'My Sites' ),
		'cap' => 'read',
		'slug' => 'my-sites.php',
	) );
}

if ( ! is_multisite() || is_super_admin() )
	$update_data = wp_get_update_data();

if ( ! is_multisite() ) {
	$admin_menu->add_submenu( 'dashboard', array(
		'title' => _admin_menu_update_count( $update_data ),
		'cap' => array( 'update_core', 'update_plugins', 'update_themes' ),
		'slug' => 'update-core.php',
	) );
}

$admin_menu->append( array(
	'id' => 'separator1',
	'class' => 'wp-menu-separator',
) );

$admin_menu->append( array(
	'title' => __( 'Posts' ),
	'cap' => 'edit_posts',
	'class' => 'open-if-no-js menu-icon-post',
	'id' => 'posts',
	'slug' => 'edit.php',
) );

	$admin_menu->add_first_submenu( 'posts', __( 'All Posts' ) );

	$admin_menu->add_submenu( 'posts', array(
		/* translators: add new post */
		'title' => _x( 'Add New', 'post' ),
		'cap' => 'edit_posts',
		'slug' => 'post-new.php',
	) );

	_add_tax_submenus( 'posts', 'post' );

$admin_menu->append( array(
	'title' => __( 'Media' ),
	'cap' => 'upload_files',
	'id' => 'media',
	'slug' => 'upload.php',
) );

	$admin_menu->add_first_submenu( 'media', __( 'Library' ) );

	$admin_menu->add_submenu( 'media', array(
		/* translators: add new file */
		'title' => _x( 'Add New', 'file' ),
		'cap' => 'upload_files',
		'slug' => 'media-new.php',
	) );

$admin_menu->append( array(
	'title' => __( 'Links' ),
	'cap' => 'manage_links',
	'id' => 'links',
	'slug' => 'link-manager.php',
) );

	$admin_menu->add_first_submenu( 'links', __( 'All Links' ) );

	$admin_menu->add_submenu( 'links', array(
		/* translators: add new link */
		'title' => _x( 'Add New', 'link' ),
		'cap' => 'manage_links',
		'slug' => 'link-add.php',
	) );

	$admin_menu->add_submenu( 'links', array(
		'title' => __( 'Link Categories' ),
		'cap' => 'manage_categories',
		'slug' => 'edit-tags.php?taxonomy=link_category',
	) );

$admin_menu->append( array(
	'title' => __( 'Pages' ),
	'cap' => 'edit_pages',
	'class' => 'menu-icon-page',
	'id' => 'pages',
	'slug' => 'edit.php?post_type=page',
) );

	$admin_menu->add_first_submenu( 'pages', __( 'All Pages' ) );

	$admin_menu->add_submenu( 'pages', array(
		/* translators: add new link */
		'title' => _x( 'Add New', 'page' ),
		'cap' => 'edit_pages',
		'slug' => 'post-new.php?post_type=page',
	) );

	_add_tax_submenus( 'pages', 'page' );

$admin_menu->append( array(
	'title' => _admin_menu_comment_count( wp_count_comments()->moderated ),
	'cap' => 'edit_posts',
	'id' => 'comments',
	'slug' => 'edit-comments.php',
) );

	$admin_menu->add_first_submenu( 'comments', __( 'All Comments' ) );

$admin_menu->append( array(
	'id' => 'separator2',
	'class' => 'wp-menu-separator',
) );

$admin_menu->append( array(
	'title' => __('Appearance'),
	'cap' => array( 'switch_themes', 'edit_theme_options' ),
	'slug' => 'themes.php',
	'id' => 'appearance',
) );

	$admin_menu->add_first_submenu( 'appearance', __( 'Themes') );

	if ( current_theme_supports( 'menus' ) || current_theme_supports( 'widgets' ) ) {
		$admin_menu->add_submenu( 'appearance', array(
			'title' => __('Menus'),
			'cap' => array( 'switch_themes', 'edit_theme_options' ),
			'slug' => 'nav-menus.php',
		) );
	}

	if ( ! is_multisite() ) {
		$admin_menu->add_submenu( 'appearance', array(
			'title' => _x('Editor', 'theme editor'),
			'cap' => 'edit_themes',
			'slug' => 'theme-editor.php',
		) );
	}

if ( ! is_multisite() && current_user_can( 'update_plugins' ) ) {

	if ( ! isset( $update_data ) )
		$update_data = wp_get_update_data();

	$plugin_title = _admin_menu_plugin_update_count( $update_data );
} else {
	$plugin_title = __( 'Plugins' );
}

$admin_menu->append( array(
	'title' => $plugin_title,
	'cap' => 'activate_plugins',
	'slug' => 'plugins.php',
	'id' => 'plugins',
) );

	$admin_menu->add_first_submenu( 'plugins', __( 'Installed Plugins') );

	if ( ! is_multisite() ) {
		$admin_menu->add_submenu( 'plugins', array(
			/* translators: add new plugin */
			'title' => _x('Add New', 'plugin'),
			'cap' => 'install_plugins',
			'slug' => 'plugin-install.php',
		) );

		$admin_menu->add_submenu( 'plugins', array(
			'title' => _x('Editor', 'plugin editor'),
			'cap' => 'edit_plugins',
			'slug' => 'plugin-editor.php',
		) );
	}

unset( $update_data, $plugin_title );

if ( current_user_can('list_users') ) {
	$admin_menu->append( array(
		'title' => __('Users'),
		'cap' => 'list_users',
		'slug' => 'users.php',
		'id' => 'users',
	) );
} else {
	$admin_menu->append( array(
		'title' => __('Profile'),
		'cap' => 'read',
		'slug' => 'profile.php',
		'id' => 'users',
	) );
}

if ( current_user_can('list_users') ) {
	$_wp_real_parent_file['profile.php'] = 'users.php'; // Back-compat for plugins adding submenus to profile.php.

	$admin_menu->add_first_submenu( 'users', __( 'All Users') );

	$admin_menu->add_submenu( 'users', array(
		'title' => _x('Add New', 'user'),
		'cap' => array( 'create_users', 'promote_users' ),
		'slug' => 'user-new.php',
	) );

	$admin_menu->add_submenu( 'users', array(
		'title' => __('Your Profile'),
		'cap' => 'read',
		'slug' => 'profile.php',
	) );
} else {
	$_wp_real_parent_file['users.php'] = 'profile.php';

	$admin_menu->add_first_submenu( 'users', __( 'Your Profile') );

	$admin_menu->add_submenu( 'users', array(
		'title' => _x('Add New', 'user'),
		'cap' => array( 'create_users', 'promote_users' ),
		'slug' => 'user-new.php',
	) );
}

$admin_menu->append( array(
	'title' => __('Tools'),
	'cap' => 'edit_posts',
	'slug' => 'tools.php',
	'id' => 'tools',
) );

	$admin_menu->add_first_submenu( 'tools', __('Available Tools') );

	$admin_menu->add_submenu( 'tools', array(
		'title' => __('Import'),
		'cap' => 'import',
		'slug' => 'import.php',
	) );

	$admin_menu->add_submenu( 'tools', array(
		'title' => __('Export'),
		'cap' => 'export',
		'slug' => 'export.php',
	) );

	if ( is_multisite() && !is_main_site() ) {
		$admin_menu->add_submenu( 'tools', array(
			'title' => __('Delete Site'),
			'cap' => 'manage_options',
			'slug' => 'ms-delete-site.php',
		) );
	}

	if ( ! is_multisite() && defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE ) {
		$admin_menu->add_submenu( 'tools', array(
			'title' => __('Network Setup'),
			'cap' => 'manage_options',
			'slug' => 'network.php',
		) );
	}

$admin_menu->append( array(
	'title' => __('Settings'),
	'cap' => 'manage_options',
	'slug' => 'options-general.php',
	'id' => 'settings',
) );

	$admin_menu->add_first_submenu( 'settings', _x('General', 'settings screen') );

	$admin_menu->add_submenu( 'settings', array(
		'title' => __('Writing'),
		'cap' => 'manage_options',
		'slug' => 'options-writing.php',
	) );

	$admin_menu->add_submenu( 'settings', array(
		'title' => __('Reading'),
		'cap' => 'manage_options',
		'slug' => 'options-reading.php',
	) );

	$admin_menu->add_submenu( 'settings', array(
		'title' => __('Discussion'),
		'cap' => 'manage_options',
		'slug' => 'options-writing.php',
	) );

	$admin_menu->add_submenu( 'settings', array(
		'title' => __('Media'),
		'cap' => 'manage_options',
		'slug' => 'options-media.php',
	) );

	$admin_menu->add_submenu( 'settings', array(
		'title' => __('Privacy'),
		'cap' => 'manage_options',
		'slug' => 'options-privacy.php',
	) );

	$admin_menu->add_submenu( 'settings', array(
		'title' => __('Permalinks'),
		'cap' => 'manage_options',
		'slug' => 'options-permalink.php',
	) );

$admin_menu->append( array(
	'id' => 'separator-last',
	'class' => 'wp-menu-separator',
) );

// CPT menus need to be added later due to 'menu_position'
_add_post_type_menus();
add_action( 'admin_menu', '_add_post_type_submenus', 9 );

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
