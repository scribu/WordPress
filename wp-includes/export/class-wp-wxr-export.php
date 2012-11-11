<?php
/**
 * Represents a set of posts and other site data to be exported.
 *
 * An immutable object, which gathers all data needed for the export.
 */
class WP_WXR_Export {
	const QUERY_CHUNK = 100;

	private static $defaults = array(
		'post_ids' => null,
		'post_type' => null,
		'status' => null,
		'author' => null,
		'start_date' => null,
		'end_date' => null,
		'category' => null,
	);

	private $post_ids;
	private $filters;
	private $xml_gen;

	private $wheres = array();
	private $joins = array();

	public function __construct( $filters = array() ) {
		$this->filters = wp_parse_args( $filters, self::$defaults );
		$this->post_ids = $this->calculate_post_ids();
	}

	public function get_xml() {
		return $this->export_using_writer_class( 'WP_WXR_Returner' );
	}

	public function export_to_xml_file( $file_name ) {
		return $this->export_using_writer_class( 'WP_WXR_File_Writer', array( $file_name ) );
	}

	public function post_ids() {
		return $this->post_ids;
	}

	public function charset() {
		return get_bloginfo( 'charset' );
	}

	public function site_metadata() {
		$metadata = array(
			'name' => $this->bloginfo_rss( 'name' ),
			'url' => $this->bloginfo_rss( 'url' ),
			'language' => $this->bloginfo_rss( 'language' ),
			'description' => $this->bloginfo_rss( 'description' ),
			'pubDate' => date( 'D, d M Y H:i:s +0000' ),
			'site_url' => is_multisite()? network_home_url() : $this->bloginfo_rss( 'url' ),
			'blog_url' => $this->bloginfo_rss( 'url' ),
		);
		return $metadata;
	}

	public function wp_generator_tag() {
		return apply_filters( 'the_generator', get_the_generator( 'export' ), 'export' );
	}

	/**
	 * Exports the current data using a specific export writer class
	 *
	 * You should use this method only when you need to export using a
	 * custom writer. For built-in writers, please see the other public
	 * methods like get_xml(), export_to_xml_file(), etc.
	 *
	 * Example:
	 * $export = new WP_WXR_Export(…);
	 * $export->export( 'WP_WXR_CSV_Writer', array( '/home/baba/baba.csv', ';' );
	 *
	 * @param string $writer_class_name The name of the PHP class representing the writer
	 * @param mixed[] $writer_args Optional additional arguments with which to call the writer constructor
	 */
	public function export_using_writer_class( $writer_class_name, $writer_args = array() ) {
		$xml_generator = new WP_WXR_XML_Generator( $this );
		array_unshift( $writer_args, $xml_generator );
		$writer_class = new ReflectionClass( $writer_class_name );
		$writer = $writer_class->newInstanceArgs( $writer_args );
		return $this->export_using_writer( $writer );
	}

	private function calculate_post_ids() {
		global $wpdb;
		if ( is_array( $this->filters['post_ids'] ) ) {
			return $this->filters['post_ids'];
		}
		$this->post_type_where();
		$this->status_where();
		$this->author_where();
		$this->start_date_where();
		$this->end_date_where();
		$this->category_where();

		$where = implode( ' AND ', array_filter( $this->wheres ) );
		if ( $where ) $where = "WHERE $where";
		$join = implode( ' ', array_filter( $this->joins ) );

		$post_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} AS p $join $where" );
		$post_ids = array_merge( $post_ids, $this->attachments_for_specific_post_types( $post_ids ) );
		return $post_ids;
	}

	private function post_type_where() {
		global $wpdb;
		$post_types_filters = array( 'can_export' => true );
		if ( $this->filters['post_type'] ) {
			$post_types_filters = array_merge( $post_types_filters, array( 'name' => $this->filters['post_type'] ) );
		}
		$post_types = get_post_types( $post_types_filters );
		if ( !$post_types ) {
			$this->wheres[] = 'p.post_type IS NULL';
			return;
		}
		$this->wheres[] = $this->build_IN_condition( 'p.post_type', $post_types );
	}

	private function status_where() {
		global $wpdb;
		if ( !$this->filters['status'] ) {
			$this->wheres[] = "p.post_status != 'auto-draft'";
			return;
		}
		$this->wheres[] = $wpdb->prepare( 'p.post_status = %s', $this->filters['status'] );
	}

	private function author_where() {
		global $wpdb;
		$user = $this->find_user_from_any_object( $this->filters['author'] );
		if ( !$user || is_wp_error( $user ) ) {
			return;
		}
		$this->wheres[] = $wpdb->prepare( 'p.post_author = %d', $user->ID );
	}

	private function start_date_where() {
		global $wpdb;
		$timestamp = strtotime( $this->filters['start_date'] );
		if ( !$timestamp ) {
			return;
		}
		$this->wheres[] = $wpdb->prepare( 'p.post_date >= %s', date( 'Y-m-d 00:00:00', $timestamp ) );
	}

	private function end_date_where() {
		global $wpdb;
		$timestamp = strtotime( $this->filters['end_date'] );
		if ( !$timestamp ) {
			return;
		}
		$this->wheres[] = $wpdb->prepare( 'p.post_date <= %s', date( 'Y-m-d 23:59:59', $timestamp ) );
	}

	private function category_where() {
		global $wpdb;
		if ( 'post' != $this->filters['post_type'] ) {
			return;
		}
		$category = $this->find_category_from_any_object( $this->filters['category'] );
		if ( !$category ) {
			return;
		}
		$this->joins[] = "INNER JOIN {$wpdb->term_relationships} AS tr ON (p.ID = tr.object_id)";
		$this->wheres[] = $wpdb->prepare( 'tr.term_taxonomy_id = %d', $category->term_taxonomy_id );
	}

	private function attachments_for_specific_post_types( $post_ids ) {
		global $wpdb;
		if ( !$this->filters['post_type'] ) {
			return array();
		}
		$attachment_ids = array();
		while ( $batch_of_post_ids = array_splice( $post_ids, 0, self::QUERY_CHUNK ) ) {
			$post_parent_condition = $this->build_IN_condition( 'post_parent', $batch_of_post_ids );
			$attachment_ids = array_merge( $attachment_ids, (array)$wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND $post_parent_condition" ) );
		}
		return array_map( 'intval', $attachment_ids );
	}

	private function export_using_writer( $writer ) {
		try {
			return $writer->export();
		} catch ( WP_WXR_Exception $e ) {
			return new WP_Error( 'wxr-error', $e->getMessage() );
		}
	}

	private function bloginfo_rss( $section ) {
		return apply_filters( 'bloginfo_rss', get_bloginfo_rss( $section ), $section );
	}

	private function build_IN_condition( $column_name, $values ) {
		global $wpdb;
		if ( !is_array( $values ) || empty( $values ) ) {
			return false;
		}
		$esses = implode( ', ', array_fill( 0, count( $values ), '%s' ) );
		return $wpdb->prepare( "$column_name IN ($esses)", $values );
	}

	private function find_user_from_any_object( $user ) {
		if ( is_numeric( $user ) ) {
			return get_user_by( 'id', $user );
		} elseif ( is_string( $user ) ) {
			return get_user_by( 'login', $user );
		} elseif ( isset( $user->ID ) ) {
			return get_user_by( 'id', $user->ID );
		}
		return false;
	}

	private function find_category_from_any_object( $category ) {
		if ( is_numeric( $category ) ) {
			return get_term( $category, 'category' );
		} elseif ( is_string( $category ) ) {
			$term = term_exists( $category, 'category' );
			return isset( $term['term_id'] )? get_term( $term['term_id'], 'category' ) : false;
		} elseif ( isset( $category->term_id ) ) {
			return get_term( $category->term_id, 'category' );
		}
		return false;
	}
}

class WP_WXR_Exception extends RuntimeException {
}
