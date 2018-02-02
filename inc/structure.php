<?php
/**
 *
 * Setup and configuration of post types and taxonomies
 *
 * @package dm-twilio
 */

// Disallow direct access.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define a custom post type to track messages.
 *
 * @return void
 */
function dmtwilio_register_cpt_dmtwilio_message() {

	$labels = [
		'name'                       => _x( 'Twilio Messages', 'Twilio Messages General Name', 'dm-twilio' ),
		'singular_name'              => _x( 'Twilio Message', 'Twilio Message Singular Name', 'dm-twilio' ),
		'menu_name'                  => __( 'Twilio Messages', 'dm-twilio' ),
		'name_admin_bar'             => __( 'Twilio Messages', 'dm-twilio' ),
		'archives'                   => __( 'Item Archives', 'dm-twilio' ),
		'attributes'                 => __( 'Item Attributes', 'dm-twilio' ),
		'parent_item_colon'          => __( 'Parent Item:', 'dm-twilio' ),
		'all_items'                  => __( 'All Items', 'dm-twilio' ),
		'add_new_item'               => __( 'Add New Item', 'dm-twilio' ),
		'add_new'                    => __( 'Add New', 'dm-twilio' ),
		'new_item'                   => __( 'New Item', 'dm-twilio' ),
		'edit_item'                  => __( 'Edit Item', 'dm-twilio' ),
		'update_item'                => __( 'Update Item', 'dm-twilio' ),
		'view_item'                  => __( 'View Item', 'dm-twilio' ),
		'view_items'                 => __( 'View Items', 'dm-twilio' ),
		'search_items'               => __( 'Search Item', 'dm-twilio' ),
		'not_found'                  => __( 'Not found', 'dm-twilio' ),
		'not_found_in_trash'         => __( 'Not found in Trash', 'dm-twilio' ),
		'featured_image'             => __( 'Featured Image', 'dm-twilio' ),
		'set_featured_image'         => __( 'Set featured image', 'dm-twilio' ),
		'remove_featured_image'      => __( 'Remove featured image', 'dm-twilio' ),
		'use_featured_image'         => __( 'Use as featured image', 'dm-twilio' ),
		'insert_into_item'           => __( 'Insert into item', 'dm-twilio' ),
		'uploaded_to_this_item'      => __( 'Uploaded to this item', 'dm-twilio' ),
		'items_list'                 => __( 'Items list', 'dm-twilio' ),
		'items_list_navigation'      => __( 'Items list navigation', 'dm-twilio' ),
		'filter_items_list'          => __( 'Filter items list', 'dm-twilio' ),
	];
	$args = [
		'label'                      => __( 'Twilio Messages', 'dm-twilio' ),
		'description'                => __( 'Twilio Messages (hidden except in development)', 'dm-twilio' ),
		'labels'                     => $labels,
		'supports'                   => [ 'title', 'custom-fields' ],
		'hierarchical'               => false,
		'public'                     => false,
		'show_ui'                    => dm_is_dev(), // Only show in the admin area if we're in development.
		'show_in_menu'               => dm_is_dev(),
		'menu_position'              => 99,
		'show_in_admin_bar'          => false,
		'show_in_nav_menus'          => dm_is_dev(),
		'can_export'                 => false,
		'has_archive'                => false,
		'exclude_from_search'        => false,
		'publicly_queryable'         => false,
		'rewrite'                    => false,
		'capability_type'            => 'page',
		'show_in_rest'               => false,
	];
	register_post_type( 'dmtwilio_message', $args );

}
add_action( 'init', 'dmtwilio_register_cpt_dmtwilio_message', 0 );

/**
 * Define a custom taxonomy to track message sending status
 *
 * @return void
 */
function dmtwilio_register_tax_dmtwilio_message_status() {

	$labels = [
		'name'                       => _x( 'Message Status', 'Taxonomy General Name', 'dmtwilio' ),
		'singular_name'              => _x( 'Message Status', 'Taxonomy Singular Name', 'dmtwilio' ),
		'menu_name'                  => __( 'Statuses', 'dmtwilio' ),
		'all_items'                  => __( 'All Items', 'dmtwilio' ),
		'parent_item'                => __( 'Parent Item', 'dmtwilio' ),
		'parent_item_colon'          => __( 'Parent Item:', 'dmtwilio' ),
		'new_item_name'              => __( 'New Item Name', 'dmtwilio' ),
		'add_new_item'               => __( 'Add New Item', 'dmtwilio' ),
		'edit_item'                  => __( 'Edit Item', 'dmtwilio' ),
		'update_item'                => __( 'Update Item', 'dmtwilio' ),
		'view_item'                  => __( 'View Item', 'dmtwilio' ),
		'separate_items_with_commas' => __( 'Separate items with commas', 'dmtwilio' ),
		'add_or_remove_items'        => __( 'Add or remove items', 'dmtwilio' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'dmtwilio' ),
		'popular_items'              => __( 'Popular Items', 'dmtwilio' ),
		'search_items'               => __( 'Search Items', 'dmtwilio' ),
		'not_found'                  => __( 'Not Found', 'dmtwilio' ),
		'no_terms'                   => __( 'No items', 'dmtwilio' ),
		'items_list'                 => __( 'Items list', 'dmtwilio' ),
		'items_list_navigation'      => __( 'Items list navigation', 'dmtwilio' ),
	];

	$args = [
		'labels'                     => $labels,
		'hierarchical'               => false,
		'public'                     => false,
		'show_ui'                    => dm_is_dev(),
		'show_admin_column'          => dm_is_dev(),
		'show_in_nav_menus'          => dm_is_dev(),
		'show_tagcloud'              => false,
		'rewrite'                    => false,
		'show_in_rest'               => false,
		// 'capabilities'               => $capabilities,
	];
	register_taxonomy( 'dmtwilio_message_status', [ 'dmtwilio_message' ], $args );

}
add_action( 'init', 'dmtwilio_register_tax_dmtwilio_message_status', 0 );


/**
 * Create taxonomy terms within the message status taxonomy
 *
 * @return void
 */
function dmtwilio_populate_dmtwilio_message_status_tax() {

	// Need to call this here, as we're calling this function from an activation hook.
	dmtwilio_register_tax_dmtwilio_message_status();

	// Possible statues from Twilio, these are stored with the prefix dmtwilio_ to avoid conflicts.
	$terms = [ 'accepted', 'queued', 'sending', 'sent', 'delivered', 'received', 'failed', 'undelivered' ];

	foreach ( $terms as $term ) {
		if ( ! term_exists( $term, 'dmtwilio_message_status' ) ) {
			$inserted_term = wp_insert_term(
				$term, 'dmtwilio_message_status', [
					'slug' => 'dmtwilio_' . $term,
				]
			);
			update_term_meta( $inserted_term['term_id'], 'dmtwilio_immutable', true );
		}
	}
}

/**
 * Don't allow editing or deletion of our 'system' taxonomy terms.
 *
 * @param  string $required_cap Required capability.
 * @param  string $cap          Capability requested.
 * @param  int    $user_id      User ID of user being checked.
 * @param  array  $args         Additional data.
 * @return string               Required capability.
 */
function dmtwilio_prevent_term_deletion( $required_cap, $cap, $user_id, $args ) {
	if ( 'delete_term' == $cap || 'edit_term' == $cap ) {
		if ( get_term_meta( $args[0], 'dmtwilio_immutable', false ) ) {
			$required_cap[] = 'do_not_allow';
		}
	}
	return $required_cap;
}
add_filter( 'map_meta_cap', 'dmtwilio_prevent_term_deletion', 10, 4 );
