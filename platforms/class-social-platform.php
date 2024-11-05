<?php
/**
 * Base class for social media platforms
 * 
 * @package Social_Auto_Share
 * @since 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

abstract class Social_Platform {
	/**
	 * Platform settings
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
	 * Get platform identifier
	 *
	 * @return string
	 */
	abstract public function get_id();

	/**
	 * Get platform display name
	 *
	 * @return string
	 */
	abstract public function get_name();

	/**
	 * Share content to the platform
	 *
	 * @param array $content_data Content data including:
	 *                           - title: Content title
	 *                           - excerpt: Content excerpt/description
	 *                           - url: Content URL
	 *                           - image_url: Featured image URL (optional)
	 *                           - author: Author name (optional)
	 *                           - date: Publish date (optional)
	 *                           - custom: Array of custom data (optional)
	 * @return bool True on success, false on failure
	 */
	abstract public function share_content( $content_data );

	/**
	 * Register platform specific settings
	 *
	 * @return void
	 */
	abstract public function register_settings();

	/**
	 * Check if platform is properly configured
	 *
	 * @return bool
	 */
	abstract public function is_configured();

	/**
	 * Get platform specific settings fields
	 *
	 * @return array Array of setting field definitions
	 */
	abstract public function get_settings_fields();

	/**
	 * Validate platform specific settings
	 *
	 * @param array $settings Settings to validate
	 * @return array Validated and sanitized settings
	 */
	public function validate_settings( $settings ) {
		return $settings;
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

	/**
	 * Get setting value
	 *
	 * @param string $key Setting key
	 * @param mixed $default Default value
	 * @return mixed
	 */
	protected function get_setting( $key, $default = null ) {
		$platform_key = $this->get_id() . '_' . $key;
		return isset( $this->settings[ $platform_key ] ) ? $this->settings[ $platform_key ] : $default;
	}

	/**
	 * Update setting value
	 *
	 * @param string $key Setting key
	 * @param mixed $value Setting value
	 * @return bool
	 */
	protected function update_setting( $key, $value ) {
		$platform_key = $this->get_id() . '_' . $key;
		$this->settings[ $platform_key ] = $value;
		return update_option( 'wp_social_auto_share_settings', $this->settings );
	}

	/**
	 * Render common settings section
	 *
	 * @return void
	 */
	protected function render_settings_section() {
		?>
		<div class="social-platform-settings <?php echo esc_attr( $this->get_id() ); ?>-settings">
			<h2><?php echo esc_html( $this->get_name() ); ?></h2>

			<div class="platform-settings-content">
				<?php
				$this->render_enabled_field();
				if ( $this->is_enabled() ) {
					$this->render_platform_fields();
				}
				?>
			</div>
		</div>
		<?php
	}


	/**
	 * Check if platform is enabled
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return (bool) $this->get_setting( 'enabled', false );
	}

	/**
	 * Render enabled/disabled field
	 *
	 * @return void
	 */
	protected function render_enabled_field() {
		$enabled = $this->is_enabled();
		?>
		<div class="platform-enabled-field">
			<label>
				<input type="checkbox" name="wp_social_auto_share_settings[<?php echo esc_attr( $this->get_id() ); ?>_enabled]"
					value="1" <?php checked( $enabled ); ?> />
				<?php _e( 'Enable this platform', 'social-auto-share' ); ?>
			</label>
		</div>
		<?php
	}

	/**
	 * Render platform specific fields
	 *
	 * @return void
	 */
	protected function render_platform_fields() {
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
}