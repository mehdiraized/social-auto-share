<?php
/**
 * Post Type Content Implementation
 * 
 * @package Social_Auto_Share
 * @since 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Content_Type_Post extends Content_Type {
	/**
	 * Get content type identifier
	 *
	 * @return string
	 */
	public function get_id() {
		return 'post';
	}

	/**
	 * Get content type display name
	 *
	 * @return string
	 */
	public function get_name() {
		return __( 'Posts', 'social-auto-share' );
	}

	/**
	 * Get WordPress hooks that should trigger sharing
	 *
	 * @return array
	 */
	public function get_hooks() {
		return [ 
			'publish_post' => 'handle_post_publish',
			'future_to_publish' => 'handle_scheduled_post',
			'pending_to_publish' => 'handle_post_publish',
			'draft_to_publish' => 'handle_post_publish'
		];
	}

	/**
	 * Check if sharing is enabled for posts
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return (bool) $this->get_setting( 'enabled', false );
	}

	/**
	 * Get settings fields
	 *
	 * @return array
	 */
	public function get_settings_fields() {
		return [ 
			[ 
				'id' => 'post_types',
				'label' => __( 'Post Types', 'social-auto-share' ),
				'type' => 'multiselect',
				'options' => $this->get_post_type_labels(),
				'description' => __( 'Select which post types to share', 'social-auto-share' ),
				'default' => [ 'post' ]
			],
			[ 
				'id' => 'categories',
				'label' => __( 'Categories', 'social-auto-share' ),
				'type' => 'multiselect',
				'options' => $this->get_category_labels(),
				'description' => __( 'Select specific categories to share (leave empty for all)', 'social-auto-share' )
			],
			[ 
				'id' => 'exclude_categories',
				'label' => __( 'Exclude Categories', 'social-auto-share' ),
				'type' => 'multiselect',
				'options' => $this->get_category_labels(),
				'description' => __( 'Select categories to exclude from sharing', 'social-auto-share' )
			],
			[ 
				'id' => 'share_updates',
				'label' => __( 'Share Updates', 'social-auto-share' ),
				'type' => 'checkbox',
				'description' => __( 'Share post updates when post is modified', 'social-auto-share' ),
				'default' => false
			],
			[ 
				'id' => 'min_word_count',
				'label' => __( 'Minimum Word Count', 'social-auto-share' ),
				'type' => 'text',
				'description' => __( 'Minimum number of words required to share (leave empty to disable)', 'social-auto-share' )
			],
			[ 
				'id' => 'excerpt_length',
				'label' => __( 'Excerpt Length', 'social-auto-share' ),
				'type' => 'text',
				'description' => __( 'Number of words in the excerpt (default: 55)', 'social-auto-share' ),
				'default' => 55
			]
		];
	}

	/**
	 * Check if post should be shared
	 *
	 * @param int $post_id Post ID
	 * @return bool
	 */
	public function should_share( $post_id ) {
		// Get post object
		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}

		// Check if post type is enabled
		$post_types = (array) $this->get_setting( 'post_types', [ 'post' ] );
		if ( ! in_array( $post->post_type, $post_types ) ) {
			return false;
		}

		// Skip if post is a revision
		if ( wp_is_post_revision( $post_id ) ) {
			return false;
		}

		// Skip if post is auto-draft
		if ( $post->post_status === 'auto-draft' ) {
			return false;
		}

		// Check minimum word count if set
		$min_words = (int) $this->get_setting( 'min_word_count' );
		if ( $min_words > 0 ) {
			$word_count = str_word_count( strip_tags( $post->post_content ) );
			if ( $word_count < $min_words ) {
				return false;
			}
		}

		// Check categories if post type supports them
		if ( $post->post_type === 'post' || is_object_in_taxonomy( $post->post_type, 'category' ) ) {
			$post_categories = wp_get_post_categories( $post_id );

			// Check excluded categories
			$excluded_categories = (array) $this->get_setting( 'exclude_categories', [] );
			if ( ! empty( $excluded_categories ) && array_intersect( $post_categories, $excluded_categories ) ) {
				return false;
			}

			// Check included categories if specified
			$included_categories = (array) $this->get_setting( 'categories', [] );
			if ( ! empty( $included_categories ) && ! array_intersect( $post_categories, $included_categories ) ) {
				return false;
			}
		}

		// Check if we should share updates
		if ( $post->post_modified !== $post->post_date ) {
			$share_updates = (bool) $this->get_setting( 'share_updates', false );
			if ( ! $share_updates ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Prepare post data for sharing
	 *
	 * @param int $post_id Post ID
	 * @return array|false
	 */
	public function prepare_content_data( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || ! $this->should_share( $post_id ) ) {
			return false;
		}

		// Get post excerpt
		$excerpt = has_excerpt( $post_id ) ?
			get_the_excerpt( $post ) :
			wp_trim_words(
				strip_shortcodes( $post->post_content ),
				$this->get_setting( 'excerpt_length', 55 ),
				'...'
			);

		// Prepare content data
		$content_data = [ 
			'title' => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
			'excerpt' => html_entity_decode( $excerpt, ENT_QUOTES, 'UTF-8' ),
			'url' => get_permalink( $post ),
			'author' => get_the_author_meta( 'display_name', $post->post_author ),
			'date' => $post->post_date,
			'modified' => $post->post_modified,
			'categories' => wp_get_post_categories( $post_id, [ 'fields' => 'names' ] ),
			'tags' => wp_get_post_tags( $post_id, [ 'fields' => 'names' ] )
		];

		// Add featured image if exists
		if ( has_post_thumbnail( $post_id ) ) {
			$content_data['image_url'] = get_the_post_thumbnail_url( $post_id, 'full' );
		}

		return apply_filters( 'wp_social_auto_share_post_content_data', $content_data, $post );
	}

	/**
	 * Handle post publication
	 *
	 * @param WP_Post $post Post object
	 * @return void
	 */
	public function handle_post_publish( $post ) {
		if ( is_int( $post ) ) {
			$post = get_post( $post );
		}

		if ( ! $post || ! $this->is_enabled() ) {
			error_log( 'Social Auto Share: Post invalid or sharing disabled' );
			return;
		}

		// Add debug information
		$this->debug_sharing_process( $post->ID );

		$content_data = $this->prepare_content_data( $post->ID );
		if ( ! $content_data ) {
			error_log( 'Social Auto Share: Failed to prepare content data for post ' . $post->ID );
			return;
		}

		// Get enabled platforms
		$platforms = apply_filters( 'wp_social_auto_share_enabled_platforms', [] );

		if ( empty( $platforms ) ) {
			error_log( 'Social Auto Share: No enabled platforms found' );
			return;
		}

		foreach ( $platforms as $platform ) {
			if ( $platform->is_configured() ) {
				$result = $platform->share_content( $content_data );
				error_log( 'Social Auto Share: Sharing to ' . get_class( $platform ) . ' - Result: ' . ( $result ? 'Success' : 'Failed' ) );
			} else {
				error_log( 'Social Auto Share: Platform ' . get_class( $platform ) . ' not properly configured' );
			}
		}
	}

	/**
	 * Handle scheduled post publication
	 *
	 * @param WP_Post $post Post object
	 * @return void
	 */
	public function handle_scheduled_post( $post ) {
		$this->handle_post_publish( $post );
	}

	/**
	 * Get category labels
	 *
	 * @return array
	 */
	protected function get_category_labels() {
		$categories = get_categories( [ 
			'hide_empty' => false,
			'orderby' => 'name',
			'order' => 'ASC'
		] );

		$labels = [];
		foreach ( $categories as $category ) {
			$labels[ $category->term_id ] = $category->name;
		}

		return $labels;
	}

	/**
	 * Debug helper for post sharing process
	 *
	 * @param int $post_id Post ID
	 * @return void
	 */
	private function debug_sharing_process( $post_id ) {
		$post = get_post( $post_id );

		error_log( '=== Social Auto Share Debug ===' );
		error_log( 'Post ID: ' . $post_id );
		error_log( 'Post Status: ' . $post->post_status );
		error_log( 'Post Type: ' . $post->post_type );

		// Check if sharing is enabled
		error_log( 'Sharing Enabled: ' . ( $this->is_enabled() ? 'Yes' : 'No' ) );

		// Check post type settings
		$post_types = (array) $this->get_setting( 'post_types', [ 'post' ] );
		error_log( 'Enabled Post Types: ' . print_r( $post_types, true ) );
		error_log( 'Current Post Type Enabled: ' . ( in_array( $post->post_type, $post_types ) ? 'Yes' : 'No' ) );

		// Check categories if applicable
		if ( $post->post_type === 'post' ) {
			$post_categories = wp_get_post_categories( $post_id );
			$excluded_categories = (array) $this->get_setting( 'exclude_categories', [] );
			$included_categories = (array) $this->get_setting( 'categories', [] );

			error_log( 'Post Categories: ' . print_r( $post_categories, true ) );
			error_log( 'Excluded Categories: ' . print_r( $excluded_categories, true ) );
			error_log( 'Included Categories: ' . print_r( $included_categories, true ) );
		}

		// Check if should share
		error_log( 'Should Share: ' . ( $this->should_share( $post_id ) ? 'Yes' : 'No' ) );

		// Get enabled platforms
		$platforms = apply_filters( 'wp_social_auto_share_enabled_platforms', [] );
		foreach ( $platforms as $platform ) {
			error_log( 'Platform: ' . get_class( $platform ) );
			error_log( 'Platform Enabled: ' . ( $platform->is_enabled() ? 'Yes' : 'No' ) );
			error_log( 'Platform Configured: ' . ( $platform->is_configured() ? 'Yes' : 'No' ) );
		}

		error_log( '=== End Debug ===' );
	}
}