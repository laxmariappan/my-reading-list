<?php
/**
 * Plugin Name:       My Reading List
 * Description:       Create a list of books to be rendered in a dynamic block.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.0.1
 * Author:            Your Name
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       my-reading-list
 *
 * @package           my-reading-list
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
add_action( 'init', 'my_reading_list_reading_list_block_init' );
function my_reading_list_reading_list_block_init() {
	register_block_type( __DIR__ . '/build',
		['render_callback' => 'render_my_reading_list_block']
	);
}


/**
 * Register a book custom post type
 */
add_action( 'init', 'my_reading_list_register_book_post_type' );
function my_reading_list_register_book_post_type() {
	register_post_type(
		'book',
		array(
			'labels'       => array(
				'name'          => 'Books',
				'singular_name' => 'Book',
			),
			'public'       => true,
			'has_archive'  => true,
			'supports'     => array( 'title', 'editor', 'thumbnail' ),
			'show_in_rest' => true
		)
	);
}

// Adding a new (custom) block category and show that category at the top
add_filter( 'block_categories_all', 'learn_wp_block_category', 10, 2);
function learn_wp_block_category( $categories, $post ) {

	array_unshift( $categories, array(
		'slug'	=> 'learn-wp',
		'title' => 'Learn WP'
	) );

	return $categories;
}

/**
 * Add featured image to the book post type
 */
add_action( 'rest_api_init', 'my_reading_list_register_book_featured_image' );
function my_reading_list_register_book_featured_image() {
	register_rest_field(
		'book',
		'featured_image_src',
		array(
			'get_callback' => 'my_reading_list_get_book_featured_image_src',
			'schema'       => null,
		)
	);
}
function my_reading_list_get_book_featured_image_src( $object ) {
	if ( $object['featured_media'] ) {
		$img = wp_get_attachment_image_src( $object['featured_media'], 'medium' );
		return $img[0];
	}
	return false;
}
function render_my_reading_list_block($attributes, $content) {

	$args = array(
		'post_type' => $attributes['postType'] ?: 'book',
		'posts_per_page' => 5,
	);

	$query = new WP_Query($args);

	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			$content .= '<div>';
			$content .= '<h2>' . get_the_title() . '</h2>';
			$content .= '<div><img src="' . get_the_post_thumbnail_url(get_post(), [150,150]) . '" /></div>';
			$content .= '<div>' . get_the_content() . '</div>';
			$content .= '</div>';
		}
	}

	wp_reset_postdata();

	return $content;
}

add_action( 'enqueue_block_editor_assets', 'my_reading_list_enqueue_scripts' );
function my_reading_list_enqueue_scripts() {


	// Get all custom post types
	$post_types = get_post_types( array( '_builtin' => false ), 'objects' );

	// Prepare post types for localization
	$post_types_localize = array();
	foreach ( $post_types as $post_type ) {
		$post_types_localize[] = array(
			'label' => $post_type->labels->singular_name,
			'value' => $post_type->name,
		);
	}

	// Localize the script with the post types data
	wp_localize_script(
		'jquery',
		'myReadingListData',
		array( 'postTypes' => $post_types_localize )
	);

	// Enqueue the script

}
