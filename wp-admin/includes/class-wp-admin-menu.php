<?php

/**
 * A single menu item, with children
 */
class WP_Admin_Menu_Item {

	protected $children;

	function __construct( $payload ) {

		if ( !isset( $payload['id'] ) )
			$payload['id'] = $payload['slug'];

		if ( isset( $payload['cap'] ) )
			$payload['cap'] = $this->convert_caps( $payload['cap'] );

		foreach ( $payload as $key => $value )
			$this->$key = $value;

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
	function add_first_submenu( $parent_id, $title ) {
		$parent = $this->get( $parent_id );

		if ( ! $parent )
			return false;

		return $parent->append( array(
			'title' => $title,
			'cap' => $parent->cap,
			'slug' => $parent->slug,
		) );
	}
}

