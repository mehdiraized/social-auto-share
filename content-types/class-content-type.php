<?php
/**
 * Base class for content types
 * 
 * @package Social_Auto_Share
 * @since 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

abstract class Content_Type {
	/**
	 * Plugin settings
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->settings = get_option( 'wp_social_auto_share_settings' );
	}

	/**
	 * Get content type identifier
	 *
	 * @return string
	 */
	abstract public function get_id();

	/**
	 * Get content type display name
	 *
	 * @return string
	 */
	abstract public function get_name();

	/**
	 * Get WordPress hooks that should trigger sharing
	 *
	 * @return array Associative array of hook => callback
	 */
	abstract public function get_hooks();

	/**
	 * Check if sharing is enabled for this content type
	 *
	 * @return bool
	 */
	abstract public function is_enabled();

	/**
	 * Check if content should be shared based on its properties
	 *
	 * @param mixed $content_id Content identifier
	 * @return bool
	 */
	abstract public function should_share( $content_id );

	/**
	 * Prepare content data for sharing
	 *
	 * @param mixed $content_id Content identifier
	 * @return array|false Content data array or false if preparation fails
	 */
	abstract public function prepare_content_data( $content_id );

	/**
	 * Get settings fields
	 *
	 * @return array Array of setting field definitions
	 */
	abstract public function get_settings_fields();

	/**
	 * Register content type settings
	 *
	 * @return void
	 */
	public function register_settings() {
		add_settings_section(
			'wp_social_auto_share_' . $this->get_id() . '_section',
			sprintf( __( '%s Settings', 'social-auto-share' ), $this->get_name() ),
			null,
			'social-auto-share'
		);

		foreach ( $this->get_settings_fields() as $field ) {
			add_settings_field(
				$this->get_id() . '_' . $field['id'],
				$field['label'],
				[ $this, 'render_setting_field' ],
				'social-auto-share',
				'wp_social_auto_share_' . $this->get_id() . '_section',
				$field
			);
		}
	}

	/**
	 * Render settings section
	 *
	 * @return void
	 */
	public function render_settings() {
		?>
		<div class="content-type-settings <?php echo esc_attr( $this->get_id() ); ?>-settings">
			<h2><?php echo esc_html( $this->get_name() ); ?></h2>

			<div class="content-type-settings-content">
				<?php
				$this->render_enabled_field();
				if ( $this->is_enabled() ) {
					$this->render_content_type_fields();
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render enabled/disabled field
	 *
	 * @return void
	 */
	protected function render_enabled_field() {
		$enabled = $this->is_enabled();
		?>
		<div class="content-type-enabled-field">
			<label>
				<input type="checkbox" name="wp_social_auto_share_settings[<?php echo esc_attr( $this->get_id() ); ?>_enabled]"
					value="1" <?php checked( $enabled ); ?> />
				<?php printf( __( 'Enable sharing for %s', 'social-auto-share' ), $this->get_name() ); ?>
			</label>
		</div>
		<?php
	}

	/**
	 * Render content type specific fields
	 *
	 * @return void
	 */
	protected function render_content_type_fields() {
		foreach ( $this->get_settings_fields() as $field ) {
			$this->render_setting_field( $field );
		}
	}

	/**
	 * Render individual setting field
	 *
	 * @param array $field Field definition
	 * @return void
	 */
	protected function render_setting_field( $field ) {
		$value = $this->get_setting( $field['id'] );
		?>
		<div class="setting-field <?php echo esc_attr( $field['id'] ); ?>-field">
			<label>
				<span class="setting-label"><?php echo esc_html( $field['label'] ); ?></span>
				<?php
				switch ( $field['type'] ) {
					case 'text':
					case 'password':
						$this->render_text_field( $field, $value );
						break;
					case 'textarea':
						$this->render_textarea_field( $field, $value );
						break;
					case 'select':
						$this->render_select_field( $field, $value );
						break;
					case 'checkbox':
						$this->render_checkbox_field( $field, $value );
						break;
					case 'multiselect':
						$this->render_multiselect_field( $field, $value );
						break;
				}
				?>
				<?php if ( ! empty( $field['description'] ) ) : ?>
					<p class="description"><?php echo esc_html( $field['description'] ); ?></p>
				<?php endif; ?>
			</label>
		</div>
		<?php
	}

	/**
	 * Render text input field
	 *
	 * @param array $field Field definition
	 * @param mixed $value Field value
	 * @return void
	 */
	protected function render_text_field( $field, $value ) {
		?>
		<input type="<?php echo esc_attr( $field['type'] ); ?>"
			name="wp_social_auto_share_settings[<?php echo esc_attr( $this->get_id() . '_' . $field['id'] ); ?>]"
			value="<?php echo esc_attr( $value ); ?>" class="regular-text" <?php echo ! empty( $field['required'] ) ? 'required' : ''; ?> />
		<?php
	}

	/**
	 * Render textarea field
	 *
	 * @param array $field Field definition
	 * @param mixed $value Field value
	 * @return void
	 */
	protected function render_textarea_field( $field, $value ) {
		?>
		<textarea name="wp_social_auto_share_settings[<?php echo esc_attr( $this->get_id() . '_' . $field['id'] ); ?>]"
			class="large-text" rows="4" <?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>><?php echo esc_textarea( $value ); ?></textarea>
		<?php
	}

	/**
	 * Render select field
	 *
	 * @param array $field Field definition
	 * @param mixed $value Field value
	 * @return void
	 */
	protected function render_select_field( $field, $value ) {
		?>
		<select name="wp_social_auto_share_settings[<?php echo esc_attr( $this->get_id() . '_' . $field['id'] ); ?>]" <?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>>
			<?php foreach ( $field['options'] as $option_value => $option_label ) : ?>
				<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $value, $option_value ); ?>>
					<?php echo esc_html( $option_label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Render multiselect field
	 *
	 * @param array $field Field definition
	 * @param array $values Selected values
	 * @return void
	 */
	protected function render_multiselect_field( $field, $values ) {
		$values = (array) $values;
		$placeholder = ! empty( $field['placeholder'] ) ? $field['placeholder'] : __( 'Select options...', 'social-auto-share' );
		?>
		<select name="wp_social_auto_share_settings[<?php echo esc_attr( $this->get_id() . '_' . $field['id'] ); ?>][]" multiple
			class="regular-text" data-placeholder="<?php echo esc_attr( $placeholder ); ?>" <?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>>
			<?php foreach ( $field['options'] as $option_value => $option_label ) : ?>
				<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( in_array( $option_value, $values ) ); ?>>
					<?php echo esc_html( $option_label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Render checkbox field
	 *
	 * @param array $field Field definition
	 * @param mixed $value Field value
	 * @return void
	 */
	protected function render_checkbox_field( $field, $value ) {
		?>
		<input type="checkbox"
			name="wp_social_auto_share_settings[<?php echo esc_attr( $this->get_id() . '_' . $field['id'] ); ?>]" value="1" <?php checked( $value ); ?> />
		<?php
	}

	/**
	 * Get setting value
	 *
	 * @param string $key Setting key
	 * @param mixed $default Default value
	 * @return mixed
	 */
	protected function get_setting( $key, $default = null ) {
		$setting_key = $this->get_id() . '_' . $key;
		return isset( $this->settings[ $setting_key ] ) ? $this->settings[ $setting_key ] : $default;
	}

	/**
	 * Update setting value
	 *
	 * @param string $key Setting key
	 * @param mixed $value Setting value
	 * @return bool
	 */
	protected function update_setting( $key, $value ) {
		$setting_key = $this->get_id() . '_' . $key;
		$this->settings[ $setting_key ] = $value;
		return update_option( 'wp_social_auto_share_settings', $this->settings );
	}

	/**
	 * Get post type labels
	 * 
	 * @return array Array of post type labels
	 */
	protected function get_post_type_labels() {
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		$labels = [];

		foreach ( $post_types as $post_type ) {
			$labels[ $post_type->name ] = $post_type->labels->singular_name;
		}

		return $labels;
	}

	/**
	 * Get taxonomies for a post type
	 *
	 * @param string $post_type Post type name
	 * @return array Array of taxonomy labels
	 */
	protected function get_taxonomy_labels( $post_type ) {
		$taxonomies = get_object_taxonomies( $post_type, 'objects' );
		$labels = [];

		foreach ( $taxonomies as $taxonomy ) {
			if ( $taxonomy->public ) {
				$labels[ $taxonomy->name ] = $taxonomy->labels->singular_name;
			}
		}

		return $labels;
	}

	/**
	 * Format error message
	 *
	 * @param string $message Error message
	 * @param mixed $data Additional error data
	 * @return string
	 */
	protected function format_error( $message, $data = null ) {
		$error = sprintf(
			'[Social Auto Share - %s] %s',
			$this->get_name(),
			$message
		);

		if ( $data ) {
			$error .= ' | Data: ' . print_r( $data, true );
		}

		return $error;
	}

	/**
	 * Log error message
	 *
	 * @param string $message Error message
	 * @param mixed $data Additional error data
	 * @return void
	 */
	protected function log_error( $message, $data = null ) {
		error_log( $this->format_error( $message, $data ) );
	}
}