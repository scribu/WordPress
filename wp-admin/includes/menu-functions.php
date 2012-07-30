<?php

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

function _admin_menu_get_menu_file( $item ) {
	$menu_file = $item->slug;

	if ( false !== ( $pos = strpos( $menu_file, '?' ) ) )
		$menu_file = substr($menu_file, 0, $pos);

	return $menu_file;
}

function _admin_menu_get_url( $menu_hook, $item, &$admin_is_parent ) {
	$menu_file = _admin_menu_get_menu_file( $item );

	if (
		!empty( $menu_hook ) ||
		( 'index.php' != $item->slug && file_exists( WP_PLUGIN_DIR . "/$menu_file" ) )
	) {
		$admin_is_parent = true;
		$url = 'admin.php?page=' . $item->slug;
	} else {
		$url = $item->slug;
	}

	return $url;
}

function _admin_menu_is_current( $item ) {
	global $self, $typenow, $parent_file;

	if ( $parent_file && $item->slug == $parent_file )
		return true;

	if ( empty($typenow) && $self == $item->slug )
		return true;

	return false;
}

function _admin_submenu_is_current( $sub_item, $item ) {
	global $self, $typenow, $submenu_file, $plugin_page;

	if ( isset( $submenu_file ) && $submenu_file == $sub_item->slug )
		return true;

	if ( !isset( $plugin_page ) && $self == $sub_item->slug )
		return true;

	// Handle current for post_type=post|page|foo pages, which won't match $self.
	$self_type = ! empty( $typenow ) ? $self . '?post_type=' . $typenow : 'nothing';

	// If plugin_page is set the parent must either match the current page or not physically exist.
	// This allows plugin pages with the same hook to exist under different parents.
	if (
		isset( $plugin_page ) &&
		$plugin_page == $sub_item->slug &&
		(
			$item->slug == $self_type ||
			$item->slug == $self ||
			!file_exists( $menu_file )
		)
	)
		return true;

	return false;
}

function _admin_submenu_get_url( $sub_item, $item, $admin_is_parent ) {
	$menu_file = _admin_menu_get_menu_file( $item );

	$menu_hook = get_plugin_page_hook( $sub_item->slug, $item->slug );

	$sub_file = _admin_menu_get_menu_file( $sub_item );

	if ( !empty( $menu_hook ) || ( 'index.php' != $sub_item->slug && file_exists( WP_PLUGIN_DIR . "/$sub_file" ) ) ) {
		if (
			( !$admin_is_parent && file_exists( WP_PLUGIN_DIR . "/$menu_file" ) && !is_dir( WP_PLUGIN_DIR . "/{$item->slug}" ) )
			|| file_exists( $menu_file )
		) {
			$base = $item->slug;
		} else {
			$base = 'admin.php';
		}

		return add_query_arg( 'page', $sub_item->slug, $base );
	}

	return $sub_item->slug;
}

function add_cssclass($add, $class) {
	$class = empty($class) ? $add : $class .= ' ' . $add;
	return $class;
}

function _add_admin_menu_classes( $admin_menu ) {
	$items = array_values( $admin_menu->get_children() );

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

	// Remove the last menu item if it is a separator.
	$last = end( $items );
	if ( 'wp-menu-separator' == $last->class ) {
		$admin_menu->remove( $last->id );
		array_pop( $items );
	}

	$first = false;

	foreach ( $items as $i => $menu_item ) {
		if ( 'dashboard' == $menu_item->id ) { // dashboard is always shown/single
			$menu_item->class = add_cssclass( 'menu-top-first', $menu_item->class );
			continue;
		}

		if ( 'wp-menu-separator' == $menu_item->class ) {
			$first = true;
			$previous = $items[$i-1];
			$previous->class = add_cssclass( 'menu-top-last', $previous->class );
			continue;
		}

		if ( $first ) {
			$menu_item->class = add_cssclass( 'menu-top-first', $menu_item->class );
			$first = false;
		}
	}

	$last = end( $items );

	$last->class = add_cssclass( 'menu-top-last', $last->class );
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
	$first = true;
	foreach ( $menu->get_children() as $item ) {

		if ( 'wp-menu-separator' == $item->class ) {
			echo "\n\t<li class='wp-menu-separator' id='menu-$item->id'>";
			echo '<div class="separator"></div>';
			echo "</li>";
			continue;
		}

		$admin_is_parent = false;

		$aria_attributes = '';

		$class = array();

		if ( $first ) {
			$class[] = 'wp-first-item';
			$first = false;
		}

		$submenu_items = $item->get_children();

		if ( ! empty( $submenu_items ) ) {
			$class[] = 'wp-has-submenu';
		}

		if ( _admin_menu_is_current( $item ) ) {
			if ( ! empty( $submenu_items ) )
				$class[] = 'wp-has-current-submenu wp-menu-open';
			else
				$class[] = 'current';
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

			$menu_hook = get_plugin_page_hook( $first_submenu->slug, $item->slug );
			$url = _admin_menu_get_url( $menu_hook, $first_submenu, $admin_is_parent );
		}
		elseif ( ! empty( $item->slug ) && current_user_can( $item->cap ) ) {
			$menu_hook = get_plugin_page_hook( $item->slug, 'admin.php' );
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

				if ( _admin_submenu_is_current( $sub_item, $item ) ) {
					$class[] = 'current';
				}

				$class = $class ? ' class="' . join( ' ', $class ) . '"' : '';

				$title = wptexturize( $sub_item->title );

				$sub_item_url = _admin_submenu_get_url( $sub_item, $item, $admin_is_parent );
				$sub_item_url = esc_url( $sub_item_url );

				echo "<li$class><a href='{$sub_item_url}'$class>$title</a></li>";
			}
			echo "</ul></div></div>";
		}
		echo "</li>";
	}

	echo '<li id="collapse-menu" class="hide-if-no-js"><div id="collapse-button"><div></div></div>';
	echo '<span>' . esc_html__( 'Collapse menu' ) . '</span>';
	echo '</li>';
}
