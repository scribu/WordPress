<?php

/**
 * A single menu item, with children
 */
class WP_Admin_Menu_Item {

	protected $children;

	function __construct( $payload ) {

		if ( !isset( $payload['id'] ) ) {
			$payload['id'] = $payload['slug'];
		}

		if ( isset( $payload['cap'] ) )
			$payload['cap'] = $this->convert_caps( $payload['cap'] );

		foreach ( $payload as $key => $value ) {
			$this->$key = $value;
		}

		$this->children = array();
	}

	protected function prepare_item( $payload ) {
		if ( is_a( $payload, __CLASS__ ) )
			return $payload;

		return new WP_Admin_Menu_Item( $payload );
	}

	// Return the first cap that the user has or last cap
	protected function convert_caps( $caps ) {
		foreach ( (array) $caps as $cap ) {
			if ( current_user_can( $cap ) )
				break;
		}

		return $cap;
	}

	function append( $payload ) {
		$item = $this->prepare_item( $payload );

		if ( isset( $this->children[ $item->id ] ) )
			return false;

		$this->children[ $item->id ] = $item;

		return true;
	}

	function insert_before( $ref_id, $payload ) {
		if ( !isset( $this->children[ $ref_id ] ) )
			return false;

		$new_array = array();

		$item = $this->prepare_item( $payload );

		foreach ( $this->children as $key => $value ) {
			if ( $key == $ref_id ) {
				$new_array[ $item->id ] = $item;
			}

			$new_array[ $key ] = $value;
		}

		$this->children = $new_array;

		return true;
	}

	function insert_after( $ref_id, $payload ) {
		if ( !isset( $this->children[ $ref_id ] ) )
			return false;

		$new_array = array();

		$item = $this->prepare_item( $payload );

		foreach ( $this->children as $key => $value ) {
			$new_array[ $key ] = $value;

			if ( $key == $ref_id ) {
				$new_array[ $item->id ] = $item;
			}
		}

		$this->children = $new_array;

		return true;
	}

	function replace( $ref_id, $payload ) {
		if ( !$this->insert_after( $ref_id, $payload ) )
			return false;

		$this->remove( $ref_id );
	}

	function contains( $id ) {
		return isset( $this->children[ $id ] );
	}

	function get( $id, $field = 'id' ) {
		if ( 'id' != $field ) {
			$items = $this->get_children( array( $field => $id ) );
			if ( empty( $items ) )
				return false;

			return reset( $items );
		}

		if ( !isset( $this->children[ $id ] ) )
			return false;

		return $this->children[ $id ];
	}

	function has_children() {
		return !empty( $this->children );
	}

	function get_children( $args = array() ) {
		return wp_list_filter( $this->children, $args );
	}

	function remove( $id ) {
		if ( !isset( $this->children[ $id ] ) )
			return false;

		unset( $this->children[ $id ] );

		return true;
	}
}


/**
 * The root menu item, with some convenience methods
 */
class WP_Admin_Menu extends WP_Admin_Menu_Item {

	function __construct() {
		$this->children = array();
	}

	function append( $payload ) {
		$payload = wp_parse_args( $payload, array(
			'icon' => 'div'
		) );

		if ( !isset( $payload['class'] ) ) {
			$payload['class'] = 'menu-icon-' . $payload['id'];
		}

		parent::append( $payload );
	}

	// Convenience method
	function add_submenu( $parent_id, $payload ) {
		$parent = $this->get( $parent_id );

		if ( ! $parent )
			return false;

		return $parent->append( $payload );
	}

	// Super-convenience method
	function add_first_submenu( $parent_id, $title, $_index = 5 ) {
		$parent = $this->get( $parent_id );

		if ( ! $parent )
			return false;

		return $parent->append( array(
			'title' => $title,
			'cap' => $parent->cap,
			'slug' => $parent->slug,
			'_index' => $_index
		) );
	}

	function _add_cpt_menus() {
		$cpt_list = get_post_types( array(
			'show_ui' => true,
			'_builtin' => false,
			'show_in_menu' => true
		) );

		foreach ( $cpt_list as $ptype ) {
			$ptype_obj = get_post_type_object( $ptype );
			// TODO: use in includes/menu.php
			$ptype_for_id = sanitize_html_class( $ptype );

			if ( is_string( $ptype_obj->menu_icon ) ) {
				$admin_menu_icon = esc_url( $ptype_obj->menu_icon );
				$ptype_class = $ptype_for_id;
			} else {
				$admin_menu_icon = 'div';
				$ptype_class = 'post';
			}

			$args =  array(
				'title' => esc_attr( $ptype_obj->labels->menu_name ),
				'cap' => $ptype_obj->cap->edit_posts,
				'class' => 'menu-icon-' . $ptype_class,
				'id' => 'posts-' . $ptype_for_id,
				'slug' => "edit.php?post_type=$ptype",
				'icon' => $admin_menu_icon,
				'_index' => false
			);

			if ( $ptype_obj->menu_position ) {
				$before = $ptype_obj->menu_position;
			} else {
				$before = 'separator2';
			}

			$this->insert_before( $before, $args );

			$this->add_first_submenu( 'posts-' . $ptype_for_id, $ptype_obj->labels->all_items );

			$this->add_submenu( 'posts-' . $ptype_for_id, array(
				'title' => $ptype_obj->labels->add_new,
				'cap' => $ptype_obj->cap->edit_posts,
				'slug' => "post-new.php?post_type=$ptype",
				'_index' => 10
			) );

			$this->_add_tax_submenus( 'posts-' . $ptype_for_id, $ptype );
		}
	}

	function _add_tax_submenus( $parent_id, $ptype ) {
		$i = 15;
		foreach ( get_taxonomies( array(), 'objects' ) as $tax ) {
			if ( ! $tax->show_ui || ! in_array($ptype, (array) $tax->object_type, true) )
				continue;

			$slug = 'edit-tags.php?taxonomy=' . $tax->name;

			if ( 'post' != $ptype )
				$slug .= '&amp;post_type=' . $ptype;

			$this->add_submenu( $parent_id, array(
				'title' => esc_attr( $tax->labels->menu_name ),
				'cap' => $tax->cap->manage_terms,
				'slug' => $slug,
				'_index' => $i++
			) );
		}
	}

	/** @private */
	function _loop( $callback ) {
		foreach ( $this->get_children() as $item ) {
			if ( !isset( $item->slug ) )
				continue;

			call_user_func( $callback, $item, $this );
		}
	}
}


// TODO: use in admin bar?
/** @private */
function _admin_menu_comment_count( $awaiting_mod ) {
	$count = sprintf(
		"<span class='awaiting-mod count-%s'><span class='pending-count'>%s</span></span>",
		$awaiting_mod,
		number_format_i18n( $awaiting_mod )
	);

	return sprintf( __('Comments %s'), $count );
}

/** @private */
function _admin_menu_update_count( $update_data ) {
	$count = sprintf(
		"<span class='update-plugins count-%s' title='%s'><span class='update-count'>%s</span></span>",
		$update_data['counts']['total'],
		$update_data['title'],
		number_format_i18n( $update_data['counts']['total'] )
	);

	return sprintf( __( 'Updates %s' ), $count );
}

function _admin_menu_plugin_update_count( $update_data ) {
	$count = sprintf(
		"<span class='update-plugins count-%s'><span class='plugin-count'>%s</span></span>",
		$update_data['counts']['plugins'],
		number_format_i18n( $update_data['counts']['plugins'] )
	);

	return sprintf( __( 'Plugins %s' ), $count );
}

function _admin_menu_theme_update_count( $update_data ) {
	$count = sprintf(
		"<span class='update-plugins count-%s'><span class='theme-count'>%s</span></span>",
		$update_data['counts']['themes'],
		number_format_i18n( $update_data['counts']['themes'] )
	);

	return sprintf( __( 'Themes %s' ), $count );
}

