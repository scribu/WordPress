<?php

/**
 * Generic container for easily manipulating an ordered list of items
 */
class WP_Admin_Menu_Item {

	protected $children;

	function __construct( $payload ) {

		if ( !isset( $payload['id'] ) ) {
			$payload['id'] = $payload['url'];
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
		$new_array = array();

		$item = $this->prepare_item( $payload );

		$found = false;
		foreach ( $this->children as $key => $value ) {
			if ( $key == $ref_id ) {
				$new_array[ $item->id ] = $item;
				$found = true;
			}

			$new_array[ $key ] = $value;
		}

		if ( !$found )
			return false;

		$this->children = $new_array;

		return true;
	}

	function insert_after( $ref_id, $payload ) {
		$new_array = array();

		$item = $this->prepare_item( $payload );

		$found = false;
		foreach ( $this->children as $key => $value ) {
			$new_array[ $key ] = $value;

			if ( $key == $ref_id ) {
				$new_array[ $item->id ] = $item;
				$found = true;
			}
		}

		if ( !$found )
			return false;

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

	function get( $id ) {
		if ( !isset( $this->children[ $id ] ) )
			return false;

		return $this->children[ $id ];
	}

	function get_children() {
		return $this->children;
	}

	function remove( $id ) {
		if ( !isset( $this->children[ $id ] ) )
			return false;

		unset( $this->children[ $id ] );

		return true;
	}
}


class WP_Admin_Menu extends WP_Admin_Menu_Item {

	function __construct() {
		$this->children = array();
	}

	function append( $payload ) {
		$payload = wp_parse_args( $payload, array(
			'icon' => 'div'
		) );

		if ( !isset( $payload['class'] ) ) {
			$payload['class'] = 'menu-top menu-icon-' . $payload['id'];
		}

		parent::append( $payload );
	}

	function add_submenu( $parent_id, $payload ) {
		$parent = $this->get( $parent_id );

		if ( ! $parent )
			return false;

		return $parent->append( $payload );
	}

	function add_first_submenu( $parent_id, $title, $_index = 5 ) {
		$parent = $this->get( $parent_id );

		if ( ! $parent )
			return false;

		return $parent->append( array(
			'title' => $title,
			'cap' => $parent->cap,
			'url' => $parent->url,
			'_index' => $_index
		) );
	}

	function _add_tax_submenus( $parent_id, $ptype ) {
		$i = 15;
		foreach ( get_taxonomies( array(), 'objects' ) as $tax ) {
			if ( ! $tax->show_ui || ! in_array($ptype, (array) $tax->object_type, true) )
				continue;

			$this->add_submenu( $parent_id, array(
				'title' => esc_attr( $tax->labels->menu_name ),
				'cap' => $tax->cap->manage_terms,
				'url' => "edit-tags.php?taxonomy=$tax->name&amp;post_type=$ptype",
				'_index' => $i++
			) );
		}
	}
}

