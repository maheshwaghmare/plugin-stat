<?php
/**
 * Plugin Name: Plugin Stat
 */

use WP_CLI\Utils;

/**
 * Registers a new post type
 * @uses $wp_post_types Inserts new post type object into the list
 *
 * @param string  Post type key, must not exceed 20 characters
 * @param array|string  See optional args description above.
 * @return object|WP_Error the registered post type object, or an error object
 */
function prefix_register_name() {

	$labels = array(
		'name'               => __( 'Plugins', 'text-domain' ),
		'singular_name'      => __( 'Plugin', 'text-domain' ),
		'add_new'            => _x( 'Add New Plugin', 'text-domain', 'text-domain' ),
		'add_new_item'       => __( 'Add New Plugin', 'text-domain' ),
		'edit_item'          => __( 'Edit Plugin', 'text-domain' ),
		'new_item'           => __( 'New Plugin', 'text-domain' ),
		'view_item'          => __( 'View Plugin', 'text-domain' ),
		'search_items'       => __( 'Search Plugins', 'text-domain' ),
		'not_found'          => __( 'No Plugins found', 'text-domain' ),
		'not_found_in_trash' => __( 'No Plugins found in Trash', 'text-domain' ),
		'parent_item_colon'  => __( 'Parent Plugin:', 'text-domain' ),
		'menu_name'          => __( 'Plugins', 'text-domain' ),
	);

	$args = array(
		'labels'              => $labels,
		'hierarchical'        => false,
		'description'         => 'description',
		'taxonomies'          => array(),
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => null,
		'menu_icon'           => null,
		'show_in_nav_menus'   => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => false,
		'has_archive'         => true,
		'query_var'           => true,
		'can_export'          => true,
		'rewrite'             => true,
		'capability_type'     => 'post',
		'supports'            => array(
			'title',
			'editor',
			'author',
			'thumbnail',
			'excerpt',
			'custom-fields',
			'trackbacks',
			'comments',
			'revisions',
			'page-attributes',
			'post-formats',
		),
	);

	register_post_type( 'plugin', $args );
}

add_action( 'init', 'prefix_register_name' );


/**
 *
 * 1. Run `wp plugin-stat info`, it will Insert & Update Products
 *
 * @package Plugin Stat
 * @since 1.0.0
 */

if( ! class_exists( 'PluginStat' ) && class_exists( 'WP_CLI_Command' ) ) :

	/**
	 * PluginStat
	 *
	 * @since 1.0.0
	 */
	class PluginStat extends WP_CLI_Command {

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			
		}

		/**
		 * Info
		 *
		 * wp plugin-stat info
		 * 
		 * @since 1.0.0
		 * @return void
		 */
		public function info() {

			WP_CLI::line( '***** Silence is Golden. *****' );

		}
		/**
		 * Info
		 *
		 * wp plugin-stat import
		 * 
		 * @since 1.0.0
		 * @return void
		 */
		public function import() {

			WP_CLI::line( '***** Importing.. *****' );

			// 

			// // global $wpdb;

			// // // This table must exist with the correct schema.
			// // $wpdb->select( 'gutenberg_plugins', $wpdb->dbh );

			$original_request_url = 'https://wordpress.org/plugins/wp-json/plugins/v1/query-plugins?s=&posts_per_page=100';

			$paged = 1;
			$count = 1;

			do {
				$request_url = $original_request_url . '&paged=' . $paged;

				WP_CLI::log( 'Requesting: ' . $request_url );
				$response = Utils\http_request( 'GET', $request_url );
				$list_body = json_decode( $response->body, true );
				if ( ! empty( $list_body['plugins'] ) ) {
					foreach ( $list_body['plugins'] as $plugin_name ) {
						// WP_CLI::log( ' -> ' . $plugin_name );
						$plugin_url = 'https://wordpress.org/plugins/wp-json/plugins/v1/plugin/' . $plugin_name;
						$response = Utils\http_request( 'GET', $plugin_url );
						$plugin = json_decode( $response->body, true );

						if( isset( $plugin['name'] ) && ! empty( $plugin['name'] ) ) {
							$plugin_title    = $plugin['name'] . ' [' . $plugin['slug'] . ']';
							$active_installs = isset( $plugin['active_installs'] ) ? $plugin['active_installs'] : 0;

							/**
							 * NOTE: Insert ONLY 50k + active install plugins.
							 */
							if( $active_installs >= 50000 ) {
								$post_args = array(
									'post_type'    => 'plugin',
									'post_title'   => $plugin_title,
									'post_content' => isset( $plugin['description'] ) ? $plugin['description'] : '',
									'meta_input'   => array(
										'name'                     => isset( $plugin['name'] ) ? $plugin['name'] : '',
										'slug'                     => isset( $plugin['slug'] ) ? $plugin['slug'] : '',
										'version'                  => isset( $plugin['version'] ) ? $plugin['version'] : '',
										'author'                   => isset( $plugin['author'] ) ? $plugin['author'] : '',
										'author_profile'           => isset( $plugin['author_profile'] ) ? $plugin['author_profile'] : '',
										'contributors'             => isset( $plugin['contributors'] ) ? $plugin['contributors'] : array(),
										'requires'                 => isset( $plugin['requires'] ) ? $plugin['requires'] : '',
										'tested'                   => isset( $plugin['tested'] ) ? $plugin['tested'] : '',
										'requires_php'             => isset( $plugin['requires_php'] ) ? $plugin['requires_php'] : '',
										'compatibility'            => isset( $plugin['compatibility'] ) ? $plugin['compatibility'] : '',
										'rating'                   => isset( $plugin['rating'] ) ? $plugin['rating'] : '',
										'ratings_5'                => isset( $plugin['ratings']['5'] ) ? $plugin['ratings']['5'] : '',
										'ratings_4'                => isset( $plugin['ratings']['4'] ) ? $plugin['ratings']['4'] : '',
										'ratings_3'                => isset( $plugin['ratings']['3'] ) ? $plugin['ratings']['3'] : '',
										'ratings_2'                => isset( $plugin['ratings']['2'] ) ? $plugin['ratings']['2'] : '',
										'ratings_1'                => isset( $plugin['ratings']['1'] ) ? $plugin['ratings']['1'] : '',
										'num_ratings'              => isset( $plugin['num_ratings'] ) ? $plugin['num_ratings'] : '',
										'support_threads'          => isset( $plugin['support_threads'] ) ? $plugin['support_threads'] : '',
										'support_threads_resolved' => isset( $plugin['support_threads_resolved'] ) ? $plugin['support_threads_resolved'] : '',
										'active_installs'          => isset( $plugin['active_installs'] ) ? $plugin['active_installs'] : '',
										'downloaded'               => isset( $plugin['downloaded'] ) ? $plugin['downloaded'] : '',
										'last_updated'             => isset( $plugin['last_updated'] ) ? $plugin['last_updated'] : '',
										'added'                    => isset( $plugin['added'] ) ? $plugin['added'] : '',
										'homepage'                 => isset( $plugin['homepage'] ) ? $plugin['homepage'] : '',
										'sections'                 => isset( $plugin['sections'] ) ? $plugin['sections'] : '',
										'short_description'        => isset( $plugin['short_description'] ) ? $plugin['short_description'] : '',
										'download_link'            => isset( $plugin['download_link'] ) ? $plugin['download_link'] : '',
										'screenshots'              => isset( $plugin['screenshots'] ) ? $plugin['screenshots'] : '',
										'tags'                     => isset( $plugin['tags'] ) ? $plugin['tags'] : '',
										'stable_tag'               => isset( $plugin['stable_tag'] ) ? $plugin['stable_tag'] : '',
										'versions'                 => isset( $plugin['versions'] ) ? $plugin['versions'] : '',
										'donate_link'              => isset( $plugin['donate_link'] ) ? $plugin['donate_link'] : '',
										'banners'                  => isset( $plugin['banners'] ) ? $plugin['banners'] : '',
										'icons'                    => isset( $plugin['icons'] ) ? $plugin['icons'] : '',
								    ),
								);

								// json_encode( $plugin['sections'] );
								// json_encode( $plugin['screenshots'] );
								// json_encode( $plugin['tags'] );
								// json_encode( $plugin['versions'] );
								// json_encode( $plugin['banners'] );
								// json_encode( $plugin['icons'] );

								$post_id = post_exists( $plugin_title );
								if( $post_id ) {
									$post_id = wp_update_post( $post_args );
									WP_CLI::line( $count . ' Updated ' . $plugin_title );
								} else {
									$post_id = wp_insert_post( $post_args );
									WP_CLI::line( $count . ' Created ' . $plugin_title );
								}
							}
							
						} else {
							WP_CLI::line( $count . ' Avoided ' . $plugin_url );
						}

						$count++;

					}
				} else {
					$request_url = false;
				}
				$paged++;
			} while( $request_url );
			WP_CLI::success( 'All done' );

		}

	}

	/**
	 * Add Command
	 */
	WP_CLI::add_command( 'plugin-stat', 'PluginStat' );

endif;

// /**
//  * Download Settings In JSON file.
//  *
//  * @since 1.0.0
//  */
// add_action( 'init', function(  ) {

// 	$original_request_url = 'https://wordpress.org/plugins/wp-json/plugins/v1/plugin/contact-form-7';

// 	$response = wp_remote_get( $original_request_url );
// 	$plugin = $response['body'];
// 	header( 'X-Robots-Tag: noindex, nofollow', true );
// 	header( 'Content-Type: application/octet-stream' );
// 	header( 'Content-Description: File Transfer' );
// 	header( 'Content-Disposition: attachment; filename="astra-options.json";' );
// 	header( 'Content-Transfer-Encoding: binary' );
// 	echo $plugin;
// 	die();
// } );
