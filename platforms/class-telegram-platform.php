<?php
/**
 * Telegram Platform Implementation
 * 
 * @package Social_Auto_Share
 * @since 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Social_Platform_Telegram extends Social_Platform {
	/**
	 * Get platform identifier
	 *
	 * @return string
	 */
	public function get_id() {
		return 'telegram';
	}

	/**
	 * Get platform display name
	 *
	 * @return string
	 */
	public function get_name() {
		return __( 'Telegram', 'social-auto-share' );
	}

	/**
	 * Check if platform is properly configured
	 *
	 * @return bool
	 */
	public function is_configured() {
		return $this->is_enabled() &&
			! empty( $this->get_setting( 'bot_token' ) ) &&
			! empty( $this->get_setting( 'channel_id' ) );
	}

	/**
	 * Get settings fields
	 *
	 * @return array
	 */
	public function get_settings_fields() {
		return [ 
			[ 
				'id' => 'bot_token',
				'label' => __( 'Bot Token', 'social-auto-share' ),
				'type' => 'text',
				'required' => true,
				'description' => __( 'Enter your Telegram Bot Token (get it from @BotFather)', 'social-auto-share' ),
			],
			[ 
				'id' => 'channel_id',
				'label' => __( 'Channel ID', 'social-auto-share' ),
				'type' => 'text',
				'required' => true,
				'description' => __( 'Enter your Telegram Channel ID (e.g., @channelname)', 'social-auto-share' ),
			],
			[ 
				'id' => 'message_template',
				'label' => __( 'Message Template', 'social-auto-share' ),
				'type' => 'textarea',
				'description' => __( 'Custom message template. Available variables: {title}, {excerpt}, {author}, {date}', 'social-auto-share' ),
				'default' => "*{title}*\n\n{excerpt}"
			],
			[ 
				'id' => 'parse_mode',
				'label' => __( 'Parse Mode', 'social-auto-share' ),
				'type' => 'select',
				'options' => [ 
					'Markdown' => __( 'Markdown', 'social-auto-share' ),
					'HTML' => __( 'HTML', 'social-auto-share' ),
				],
				'description' => __( 'Choose how to format your messages', 'social-auto-share' ),
			]
		];
	}

	/**
	 * Register platform settings
	 */
	public function register_settings() {
		add_settings_section(
			'wp_social_auto_share_telegram_section',
			__( 'Telegram Settings', 'social-auto-share' ),
			null,
			'social-auto-share'
		);

		foreach ( $this->get_settings_fields() as $field ) {
			add_settings_field(
				'telegram_' . $field['id'],
				$field['label'],
				[ $this, 'render_setting_field' ],
				'social-auto-share',
				'wp_social_auto_share_telegram_section',
				$field
			);
		}
	}

	/**
	 * Render platform specific fields
	 */
	protected function render_platform_fields() {
		?>
		<div class="sps-flex">
			<div>
				<?php parent::render_platform_fields(); ?>
			</div><?php
			if ( $this->is_enabled() ) {
				$this->render_setup_tutorial();
			} ?>
		</div>
		<?php
	}

	/**
	 * Render platform specific settings
	 */
	public function render_settings() {
		?>
		<div class="social-platform-settings <?php echo esc_attr( $this->get_id() ); ?>-settings">
			<h2><?php echo esc_html( $this->get_name() ); ?></h2>
			<?php
			$this->render_enabled_field();
			if ( $this->is_enabled() ) {
				$this->render_platform_fields();
			}
			?>
		</div>
		<?php
	}


	/**
	 * Render setup tutorial
	 */
	protected function render_setup_tutorial() {
		?>
		<div class="telegram-setup-tutorial"
			style="margin: 20px 0; padding: 20px; background: #fff; border-inline-start: 2px solid #00a0d2; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
			<h3><?php _e( 'Telegram Setup Guide', 'social-auto-share' ); ?></h3>

			<div class="setup-steps">
				<div class="step" style="margin-bottom: 15px;">
					<h4><?php _e( 'Step 1: Create Telegram Bot', 'social-auto-share' ); ?></h4>
					<ol>
						<li><?php _e( 'Message @BotFather on Telegram', 'social-auto-share' ); ?></li>
						<li><?php _e( 'Send the /newbot command', 'social-auto-share' ); ?></li>
						<li><?php _e( 'Enter a display name for your bot', 'social-auto-share' ); ?></li>
						<li><?php _e( 'Choose a unique username for your bot (must end in "bot")', 'social-auto-share' ); ?></li>
						<li>
							<?php _e( 'BotFather will send you a token - copy it and paste it in the "Bot Token" field', 'social-auto-share' ); ?>
						</li>
					</ol>
				</div>

				<div class="step" style="margin-bottom: 15px;">
					<h4><?php _e( 'Step 2: Add Bot to Channel', 'social-auto-share' ); ?></h4>
					<ol>
						<li><?php _e( 'Go to your Telegram channel', 'social-auto-share' ); ?></li>
						<li><?php _e( 'Click on the channel name to open the menu', 'social-auto-share' ); ?></li>
						<li><?php _e( 'Select "Administrators"', 'social-auto-share' ); ?></li>
						<li><?php _e( 'Click "Add Administrator"', 'social-auto-share' ); ?></li>
						<li><?php _e( 'Search for your bot username and select it', 'social-auto-share' ); ?></li>
						<li><?php _e( 'Enable "Post Messages" and "Edit Messages" permissions for the bot', 'social-auto-share' ); ?>
						</li>
						<li><?php _e( 'Save the changes', 'social-auto-share' ); ?></li>
					</ol>
				</div>

				<div class="step" style="margin-bottom: 15px;">
					<h4><?php _e( 'Step 3: Configure Plugin', 'social-auto-share' ); ?></h4>
					<ol>
						<li><?php _e( 'Enter your bot token in the "Bot Token" field', 'social-auto-share' ); ?></li>
						<li>
							<?php _e( 'Enter your channel ID in the format @channelname in the "Channel ID" field', 'social-auto-share' ); ?>
						</li>
						<li><?php _e( 'Configure your message template (optional)', 'social-auto-share' ); ?></li>
						<li><?php _e( 'Save the settings', 'social-auto-share' ); ?></li>
					</ol>
				</div>

				<div class="step">
					<h4><?php _e( 'Important Notes:', 'social-auto-share' ); ?></h4>
					<ul style="list-style-type: disc; margin-left: 20px;">
						<li><?php _e( 'Make sure the bot is added as an administrator to the channel', 'social-auto-share' ); ?></li>
						<li><?php _e( 'The bot must have posting and editing permissions in the channel', 'social-auto-share' ); ?>
						</li>
						<li><?php _e( 'You can publish a test post to verify the setup', 'social-auto-share' ); ?></li>
					</ul>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Share content to Telegram
	 *
	 * @param array $content_data Content data
	 * @return bool
	 */
	public function share_content( $content_data ) {
		if ( ! $this->is_configured() ) {
			$this->log_error( 'Platform not properly configured' );
			return false;
		}

		try {
			$message = $this->format_message( $content_data );

			// Prepare inline keyboard for "Read More" button
			$inline_keyboard = [ 
				[ 
					[ 
						'text' => __( 'Read More', 'social-auto-share' ),
						'url' => $content_data['url']
					]
				]
			];

			// If we have a featured image, send with image
			if ( ! empty( $content_data['image_url'] ) ) {
				return $this->send_telegram_photo_with_caption(
					$content_data['image_url'],
					$message,
					$inline_keyboard
				);
			}

			// Otherwise send just the text message
			return $this->send_telegram_message( $message, $inline_keyboard );
		} catch (Exception $e) {
			$this->log_error( 'Error sharing content: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Send photo with caption to Telegram
	 *
	 * @param string $image_url Image URL
	 * @param string $caption Message caption
	 * @param array $inline_keyboard Optional inline keyboard buttons
	 * @return bool
	 */
	protected function send_telegram_photo_with_caption( $image_url, $caption, $inline_keyboard = null ) {
		$bot_token = $this->get_setting( 'bot_token' );
		$channel_id = $this->get_setting( 'channel_id' );
		$parse_mode = $this->get_setting( 'parse_mode', 'Markdown' );

		$args = [ 
			'body' => [ 
				'chat_id' => $channel_id,
				'photo' => $image_url,
				'caption' => $caption,
				'parse_mode' => $parse_mode
			]
		];

		// Add inline keyboard if provided
		if ( $inline_keyboard ) {
			$args['body']['reply_markup'] = json_encode( [ 'inline_keyboard' => $inline_keyboard ] );
		}

		$response = wp_remote_post(
			"https://api.telegram.org/bot{$bot_token}/sendPhoto",
			$args
		);

		if ( is_wp_error( $response ) ) {
			$this->log_error( 'Error sending photo: ' . $response->get_error_message() );
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! isset( $data['ok'] ) || ! $data['ok'] ) {
			$this->log_error( 'Telegram API error when sending photo', $data );
			return false;
		}

		return true;
	}

	/**
	 * Format message using template
	 *
	 * @param array $content_data Content data
	 * @return string
	 */
	protected function format_message( $content_data ) {
		$template = $this->get_setting( 'message_template' ) ?: "*{title}*\n\n{excerpt}";

		$replacements = [ 
			'{title}' => $content_data['title'],
			'{excerpt}' => $content_data['excerpt'],
			'{author}' => $content_data['author'] ?? '',
			'{date}' => isset( $content_data['date'] ) ? date_i18n( get_option( 'date_format' ), strtotime( $content_data['date'] ) ) : '',
		];

		return str_replace( array_keys( $replacements ), array_values( $replacements ), $template );
	}

	/**
	 * Send message to Telegram
	 *
	 * @param string $message Message text
	 * @param array $inline_keyboard Optional inline keyboard buttons
	 * @return bool
	 */
	protected function send_telegram_message( $message, $inline_keyboard = null ) {
		$bot_token = $this->get_setting( 'bot_token' );
		$channel_id = $this->get_setting( 'channel_id' );
		$parse_mode = $this->get_setting( 'parse_mode', 'Markdown' );

		$args = [ 
			'body' => [ 
				'chat_id' => $channel_id,
				'text' => $message,
				'parse_mode' => $parse_mode,
				'disable_web_page_preview' => true
			]
		];

		// Add inline keyboard if provided
		if ( $inline_keyboard ) {
			$args['body']['reply_markup'] = json_encode( [ 'inline_keyboard' => $inline_keyboard ] );
		}

		$response = wp_remote_post(
			"https://api.telegram.org/bot{$bot_token}/sendMessage",
			$args
		);

		if ( is_wp_error( $response ) ) {
			$this->log_error( 'Error sending message: ' . $response->get_error_message() );
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! isset( $data['ok'] ) || ! $data['ok'] ) {
			$this->log_error( 'Telegram API error', $data );
			return false;
		}

		return true;
	}

	/**
	 * Send photo to Telegram
	 *
	 * @param string $image_url Image URL
	 * @return bool
	 */
	protected function send_telegram_photo( $image_url ) {
		$bot_token = $this->get_setting( 'bot_token' );
		$channel_id = $this->get_setting( 'channel_id' );

		$args = [ 
			'body' => [ 
				'chat_id' => $channel_id,
				'photo' => $image_url
			]
		];

		$response = wp_remote_post(
			"https://api.telegram.org/bot{$bot_token}/sendPhoto",
			$args
		);

		if ( is_wp_error( $response ) ) {
			$this->log_error( 'Error sending photo: ' . $response->get_error_message() );
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! isset( $data['ok'] ) || ! $data['ok'] ) {
			$this->log_error( 'Telegram API error when sending photo', $data );
			return false;
		}

		return true;
	}

	/**
	 * Validate settings before save
	 *
	 * @param array $settings Settings array
	 * @return array
	 */
	public function validate_settings( $settings ) {
		// Validate Bot Token format
		if ( ! empty( $settings[ $this->get_id() . '_bot_token' ] ) ) {
			$bot_token = sanitize_text_field( $settings[ $this->get_id() . '_bot_token' ] );
			if ( ! preg_match( '/^\d+:[\w-]+$/', $bot_token ) ) {
				add_settings_error(
					'wp_social_auto_share_settings',
					'invalid_bot_token',
					__( 'Invalid Telegram Bot Token format', 'social-auto-share' )
				);
			}
		}

		// Validate Channel ID format
		if ( ! empty( $settings[ $this->get_id() . '_channel_id' ] ) ) {
			$channel_id = sanitize_text_field( $settings[ $this->get_id() . '_channel_id' ] );
			if ( ! preg_match( '/^@?[\w-]+$/', $channel_id ) ) {
				add_settings_error(
					'wp_social_auto_share_settings',
					'invalid_channel_id',
					__( 'Invalid Telegram Channel ID format', 'social-auto-share' )
				);
			}
		}

		return $settings;
	}
}