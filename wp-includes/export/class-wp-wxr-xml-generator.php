<?php
/**
 * Responsible for generating the WXR XML from the data in WP_WXR_Export
 */
class WP_WXR_XML_Generator {
	function __construct( $export ) {
		$this->export = $export;
	}

	static function get_parts() {
		return array( 'header', 'site_metadata', 'authors', 'categories', 'tags', 'nav_menu_terms', 'other_terms', 'posts', 'footer', );
	}

	function header() {
		$wxr_version = WXR_VERSION;
		$charset = $this->export->charset();
		$wp_generator_tag = $this->export->wp_generator_tag();
		return <<<XML
<?xml version="1.0" encoding="$charset" ?>
<!-- This is a WordPress eXtended RSS file generated by WordPress as an export of your site. -->
<!-- It contains information about your site's posts, pages, comments, categories, and other content. -->
<!-- You may use this file to transfer that content from one site to another. -->
<!-- This file is not intended to serve as a complete backup of your site. -->

<!-- To import this information into a WordPress site follow these steps: -->
<!-- 1. Log in to that site as an administrator. -->
<!-- 2. Go to Tools: Import in the WordPress admin panel. -->
<!-- 3. Install the "WordPress" importer from the list. -->
<!-- 4. Activate & Run Importer. -->
<!-- 5. Upload this file using the form provided on that page. -->
<!-- 6. You will first be asked to map the authors in this export file to users -->
<!--    on the site. For each author, you may choose to map to an -->
<!--    existing user on the site or to create a new user. -->
<!-- 7. WordPress will then import each of the posts, pages, comments, categories, etc. -->
<!--    contained in this file into your site. -->
$wp_generator_tag
<rss version="2.0"
	xmlns:excerpt="http://wordpress.org/export/$wxr_version/excerpt/"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:wp="http://wordpress.org/export/$wxr_version/"
>
<channel>

XML;
	}

	function site_metadata() {
		$metadata = $this->export->site_metadata();
		return <<<XML
	<title>{$metadata['name']}</title>
	<link><?php bloginfo_rss( 'url' ); ?></link>
	<description><?php bloginfo_rss( 'description' ); ?></description>
	<pubDate><?php echo date( 'D, d M Y H:i:s +0000' ); ?></pubDate>
	<language><?php bloginfo_rss( 'language' ); ?></language>
	<wp:wxr_version><?php echo WXR_VERSION; ?></wp:wxr_version>
	<wp:base_site_url><?php echo wxr_site_url(); ?></wp:base_site_url>
	<wp:base_blog_url><?php bloginfo_rss( 'url' ); ?></wp:base_blog_url>

XML;
	}

	function authors() {
		$authors = $this->export->authors();
		$xml = '';
		foreach ( $authors as $author ) {
			self::make_object_fields_cdata( $author, array( 'display_name', 'user_firstname', 'user_lastname' ) );
			$xml .= <<<XML
	<wp:author>
		<wp:author_id>{$author->ID}</wp:author_id>
		<wp:author_login>{$author->user_login}</wp:author_login>
		<wp:author_email>{$author->user_email}</wp:author_email>
		<wp:author_display_name>{$author->display_name_cdata}</wp:author_display_name>
		<wp:author_first_name>{$author->user_firstname_cdata}</wp:author_first_name>
		<wp:author_last_name>{$author->user_lastname_cdata}</wp:author_last_name>
	</wp:author>

XML;
		}
		return $xml;
	}

	function categories() {
		$categories = $this->export->categories();
		$xml = '';
		foreach( $categories as $term_id => $category ) {
			self::make_object_fields_cdata( $category, array( 'name', 'description' ) );
			$category->parent_slug = $category->parent? $categories[$category->parent]->slug : '';
			$xml .= <<<XML
	<wp:category>
		<wp:term_id>{$category->term_id}</wp:term_id>
		<wp:category_nicename>{$category->slug}</wp:category_nicename>
		<wp:category_parent>{$category->parent_slug}</wp:category_parent>
		<wp:cat_name>{$category->name_cdata}</wp:cat_name>
		<wp:category_description>{$category->description_cdata}</wp:category_description>
	</wp:category>

XML;
		}
		return $xml;
	}

	function tags() {
		$tags = $this->export->tags();
		$xml = '';
		foreach( $tags as $tag ) {
			self::make_object_fields_cdata( $tag, array( 'name', 'description' ) );
			$xml .= <<<XML
	<wp:tag>
		<wp:term_id>{$tag->term_id}</wp:term_id>
		<wp:tag_slug>{$tag->slug}</wp:tag_nicename>
		<wp:tag_name>{$tag->name_cdata}</wp:tag_name>
		<wp:tag_description>{$tag->description_cdata}</wp:tag_description>
	</wp:tag>

XML;
		}
		return $xml;
	}

	function nav_menu_terms() {
	}

	function other_terms() {
	}

	function posts() {
		return array( 'a', 'b' );
	}

	function footer() {
		return <<<XML
</channel>
</rss>
XML;
	}

	private static function make_object_fields_cdata( $object, $fields = array() ) {
		foreach( $fields as $field ) {
			$field_cdata = "{$field}_cdata";
			$object->$field_cdata = self::cdata( $object->$field );
		}
	}

	private static function cdata( $text ) {
		if ( !seems_utf8( $text ) ) {
			$text = utf8_encode( $text );
		}
		return '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $text ) . ']]>';
	}
}
