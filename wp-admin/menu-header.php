<?php
/**
 * Displays Administration Menu.
 *
 * @package WordPress
 * @subpackage Administration
 */

/**
 * The current page.
 *
 * @global string $self
 * @name $self
 * @var string
 */
$self = preg_replace('|^.*/wp-admin/network/|i', '', $_SERVER['PHP_SELF']);
$self = preg_replace('|^.*/wp-admin/|i', '', $self);
$self = preg_replace('|^.*/plugins/|i', '', $self);
$self = preg_replace('|^.*/mu-plugins/|i', '', $self);

global $admin_menu, $parent_file; //For when admin-header is included from within a function.
$parent_file = apply_filters("parent_file", $parent_file); // For plugins to move submenu tabs around.

get_admin_page_parent();
?>

<div id="adminmenuback"></div>
<div id="adminmenuwrap">
	<div id="adminmenushadow"></div>

	<ul id="adminmenu" role="navigation">
<?php
	_add_admin_menu_classes( $admin_menu );
	_wp_menu_output( $admin_menu );
	do_action( 'adminmenu' );
?>
	</ul>
</div>
