<?php

/**
 * Generic container for easily manipulating an ordered list of items
 */
class WP_Admin_Menu_Items {

	protected $items = array();

	// Return the first cap that the user has or last cap
	protected function convert_caps( $caps ) {
		foreach ( (array) $caps as $cap ) {
			if ( current_user_can( $cap ) )
				break;
		}

		return $cap;
	}

	function append( $payload ) {
		// TODO: allow overwrite or have a replace() method ?

		$item = $this->prepare_item( $payload );

		$this->items[ $item->id ] = $item;
	}

	protected function prepare_item( $payload ) {
		$item = (object) $payload;

		if ( !isset( $item->id ) ) {
			$item->id = $item->url;
		}

		if ( isset( $item->cap ) )
			$item->cap = $this->convert_caps( $item->cap );

		return $item;
	}

	function add_before( $ref_id, $payload ) {
		$new_array = array();

		$item = $this->prepare_item( $payload );

		$found = false;
		foreach ( $this->items as $key => $value ) {
			if ( $key == $ref_id ) {
				$new_array[ $item->id ] = $item;
				$found = true;
			}

			$new_array[ $key ] = $value;
		}

		// TODO: just fail instead?
		if ( !$found )
			$new_array[ $item->id ] = $item;

		$this->items = $new_array;
	}

	function add_after( $ref_id, $payload ) {
		$new_array = array();

		$item = $this->prepare_item( $payload );

		$found = false;
		foreach ( $this->items as $key => $value ) {
			$new_array[ $key ] = $value;

			if ( $key == $ref_id ) {
				$new_array[ $item->id ] = $item;
				$found = true;
			}
		}

		// TODO: just fail instead?
		if ( !$found )
			$new_array[ $item->id ] = $item;

		$this->items = $new_array;
	}

	function contains( $id ) {
		return isset( $this->items[ $id ] );
	}

	function get( $id ) {
		return $this->items[ $id ];
	}

	function get_all() {
		return $this->items;
	}

	function remove( $id ) {
		unset( $this->items[ $id ] );
	}
}


class WP_Admin_Menu extends WP_Admin_Menu_Items {

	protected $submenus = array();

	function append( $payload ) {
		$payload = (object) wp_parse_args( $payload, array(
			'icon' => 'div'
		) );

		if ( !isset( $payload->class ) ) {
			$payload->class = 'menu-top menu-icon-' . $payload->id;
		}

		parent::append( $payload );
	}

	function add_submenu( $parent_id, $payload ) {
		if ( !isset( $this->submenus[ $parent_id ] ) )
			$this->submenus[ $parent_id ] = new WP_Admin_Menu_Items;

		$this->submenus[ $parent_id ]->append( $payload );
	}

	function add_first_submenu( $parent_id, $title, $_index = 5 ) {
		$parent = $this->get( $parent_id );

		$this->add_submenu( $parent_id, array(
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

