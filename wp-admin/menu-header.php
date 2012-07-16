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

function _admin_menu_get_menu_file( $item ) {
	$menu_file = $item->url;

	if ( false !== ( $pos = strpos( $menu_file, '?' ) ) )
		$menu_file = substr($menu_file, 0, $pos);

	return $menu_file;
}

function _admin_menu_get_url( $menu_hook, $item, &$admin_is_parent ) {
	$menu_file = _admin_menu_get_menu_file( $item );

	if (
		!empty( $menu_hook ) ||
		( 'index.php' != $item->url && file_exists( WP_PLUGIN_DIR . "/$menu_file" ) )
	) {
		$admin_is_parent = true;
		$url = 'admin.php?page=' . $item->url;
	} else {
		$url = $item->url;
	}

	return $url;
}

function _admin_submenu_get_url( $sub_item, $item, $menu_file, $admin_is_parent ) {
	$menu_hook = get_plugin_page_hook( $sub_item->url, $item->url );

	$sub_file = _admin_menu_get_menu_file( $sub_item );

	if ( !empty( $menu_hook ) || ( 'index.php' != $sub_item->url && file_exists( WP_PLUGIN_DIR . "/$sub_file" ) ) ) {
		if (
			( !$admin_is_parent && file_exists( WP_PLUGIN_DIR . "/$menu_file" ) && !is_dir( WP_PLUGIN_DIR . "/{$item->url}" ) )
			|| file_exists( $menu_file )
		) {
			$base = $item->url;
		} else {
			$base = 'admin.php';
		}

		return add_query_arg( 'page', $sub_item->url, $base );
	}

	return $sub_item->url;
}

/**
 * Display menu.
 *
 * @access private
 * @since 2.7.0
 *
 * @param array $menu
 * @param array $submenu
 * @param bool $submenu_as_parent
 */
function _wp_menu_output( $menu, $submenu_as_parent = true ) {
	global $self, $parent_file, $submenu_file, $plugin_page, $pagenow, $typenow;

	$first = true;
	foreach ( $menu->get_children() as $item ) {

		if ( 'wp-menu-separator' == $item->class ) {
			echo "\n\t<li class='wp-menu-separator' id='menu-$item->id'>";
			echo '<div class="separator"></div>';
			echo "</li>";
			continue;
		}

		$admin_is_parent = false;
		$class = array();
		$aria_attributes = '';

		if ( $first ) {
			$class[] = 'wp-first-item';
			$first = false;
		}

		$submenu_items = $item->get_children();

		if ( ! empty( $submenu_items ) ) {
			$class[] = 'wp-has-submenu';
		}

		if ( ( $parent_file && $item->url == $parent_file ) || ( empty($typenow) && $self == $item->url ) ) {
			$class[] = ! empty( $submenu_items ) ? 'wp-has-current-submenu wp-menu-open' : 'current';
		} else {
			$class[] = 'wp-not-current-submenu';
			if ( ! empty( $submenu_items ) )
				$aria_attributes .= 'aria-haspopup="true"';
		}

		if ( ! empty( $item->class ) )
			$class[] = $item->class;

		$class[] = 'menu-top';

		$class = $class ? ' class="' . join( ' ', $class ) . '"' : '';
		$id = ! empty( $item->id ) ? ' id="menu-' . preg_replace( '|[^a-zA-Z0-9_:.]|', '-', $item->id ) . '"' : '';
		$img = '';
		if ( ! empty( $item->icon ) )
			$img = ( 'div' === $item->icon ) ? '<br />' : '<img src="' . $item->icon . '" alt="" />';
		$arrow = '<div class="wp-menu-arrow"><div></div></div>';

		$title = wptexturize( $item->title );
		$aria_label = esc_attr( strip_tags( $item->title ) ); // strip the comment/plugins/updates bubbles spans but keep the pending number if any

		echo "\n\t<li$class$id>";

		$url = false;
		if ( $submenu_as_parent && ! empty( $submenu_items ) ) {
			$first_submenu = reset( $submenu_items );

			$menu_hook = get_plugin_page_hook( $first_submenu->url, $item->url );
			$url = _admin_menu_get_url( $menu_hook, $first_submenu, $admin_is_parent );
		}
		elseif ( ! empty( $item->url ) && current_user_can( $item->cap ) ) {
			$menu_hook = get_plugin_page_hook( $item->url, 'admin.php' );
			$url = _admin_menu_get_url( $menu_hook, $item, $admin_is_parent );
		}

		if ( $url ) {
			echo "<div class='wp-menu-image'><a href='$url' tabindex='-1' aria-label='$aria_label'>$img</a></div>";
			echo $arrow;
			echo "<a href='$url'$class $aria_attributes>$title</a>";
		}

		if ( ! empty( $submenu_items ) ) {
			echo "\n\t<div class='wp-submenu'><div class='wp-submenu-wrap'>";
			echo "<div class='wp-submenu-head'>{$item->title}</div><ul>";
			$first = true;
			foreach ( $submenu_items as $sub_item ) {
				if ( ! current_user_can( $sub_item->cap ) )
					continue;

				$class = array();
				if ( $first ) {
					$class[] = 'wp-first-item';
					$first = false;
				}

				$menu_file = $item->url;

				if ( false !== ( $pos = strpos( $menu_file, '?' ) ) )
					$menu_file = substr( $menu_file, 0, $pos );

				// Handle current for post_type=post|page|foo pages, which won't match $self.
				$self_type = ! empty( $typenow ) ? $self . '?post_type=' . $typenow : 'nothing';

				if ( isset( $submenu_file ) ) {
					if ( $submenu_file == $sub_item->url )
						$class[] = 'current';
				// If plugin_page is set the parent must either match the current page or not physically exist.
				// This allows plugin pages with the same hook to exist under different parents.
				} else if (
					( ! isset( $plugin_page ) && $self == $sub_item->url ) ||
					( isset( $plugin_page ) && $plugin_page == $sub_item->url && ( $item->url == $self_type || $item->url == $self || file_exists($menu_file) === false ) )
				) {
					$class[] = 'current';
				}

				$class = $class ? ' class="' . join( ' ', $class ) . '"' : '';

				$title = wptexturize( $sub_item->title );

				$sub_item_url = _admin_submenu_get_url( $sub_item, $item, $menu_file, $admin_is_parent );
				$sub_item_url = esc_url( $sub_item_url );

				echo "<li$class><a href='{$sub_item_url}'$class $aria_attributes>$title</a></li>";
			}
			echo "</ul></div></div>";
		}
		echo "</li>";
	}

	echo '<li id="collapse-menu" class="hide-if-no-js"><div id="collapse-button"><div></div></div>';
	echo '<span>' . esc_html__( 'Collapse menu' ) . '</span>';
	echo '</li>';
}

?>

<div id="adminmenuback"></div>
<div id="adminmenuwrap">
<div id="adminmenushadow"></div>
<ul id="adminmenu" role="navigation">

<?php
_wp_menu_output( $admin_menu );
do_action( 'adminmenu' );

?>
</ul>
</div>
