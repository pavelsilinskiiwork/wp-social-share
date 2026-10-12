<?php
/**
 * Admin settings page.
 *
 * @package PavelSilinskii\SocialShare
 */

namespace PavelSilinskii\SocialShare;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Settings > Social Share page and its fields.
 */
class Admin {

	/**
	 * Settings page slug.
	 */
	private const PAGE = 'psss-settings';

	/**
	 * Option name.
	 */
	private const OPTION = 'psss_settings';

	/**
	 * Supported share networks.
	 */
	private const NETWORKS = array( 'facebook', 'twitter', 'linkedin', 'whatsapp', 'pinterest' );

	/**
	 * Register hooks.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Add the settings page under Settings.
	 *
	 * @return void
	 */
	public function add_menu() {
		add_options_page(
			__( 'Social Share Settings', 'pavel-silinskii-social-share' ),
			__( 'Social Share', 'pavel-silinskii-social-share' ),
			'manage_options',
			self::PAGE,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register the setting, sections and fields.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'psss_settings_group',
			self::OPTION,
			array( 'sanitize_callback' => array( $this, 'sanitize_settings' ) )
		);

		add_settings_section( 'psss_networks', __( 'Networks', 'pavel-silinskii-social-share' ), '__return_false', self::PAGE );
		add_settings_section( 'psss_placement', __( 'Placement', 'pavel-silinskii-social-share' ), '__return_false', self::PAGE );
		add_settings_section( 'psss_post_types', __( 'Post Types', 'pavel-silinskii-social-share' ), '__return_false', self::PAGE );
		add_settings_section( 'psss_appearance', __( 'Appearance', 'pavel-silinskii-social-share' ), '__return_false', self::PAGE );

		$fields = array(
			'networks'     => array( __( 'Share buttons', 'pavel-silinskii-social-share' ), 'psss_networks' ),
			'position'     => array( __( 'Position', 'pavel-silinskii-social-share' ), 'psss_placement' ),
			'open_in'      => array( __( 'Open links', 'pavel-silinskii-social-share' ), 'psss_placement' ),
			'post_types'   => array( __( 'Show on', 'pavel-silinskii-social-share' ), 'psss_post_types' ),
			'button_style' => array( __( 'Button style', 'pavel-silinskii-social-share' ), 'psss_appearance' ),
			'shape'        => array( __( 'Shape', 'pavel-silinskii-social-share' ), 'psss_appearance' ),
			'size'         => array( __( 'Size', 'pavel-silinskii-social-share' ), 'psss_appearance' ),
			'label'        => array( __( 'Label', 'pavel-silinskii-social-share' ), 'psss_appearance' ),
		);

		foreach ( $fields as $key => list( $title, $section ) ) {
			$args = 'label' === $key ? array( 'label_for' => 'psss-label' ) : array();

			add_settings_field( 'psss_' . $key, $title, array( $this, "render_{$key}_field" ), self::PAGE, $section, $args );
		}
	}

	/**
	 * Sanitize and whitelist submitted settings.
	 *
	 * @param mixed $input Submitted values.
	 * @return array<string, mixed>
	 */
	public function sanitize_settings( $input ): array {
		$input    = is_array( $input ) ? $input : array();
		$defaults = Frontend::DEFAULTS;

		$networks = isset( $input['networks'] ) ? array_map( 'strval', (array) $input['networks'] ) : array();

		$post_types = isset( $input['post_types'] ) ? array_map( 'sanitize_key', (array) $input['post_types'] ) : array();

		return array(
			'networks'     => array_values( array_intersect( $networks, self::NETWORKS ) ),
			'position'     => $this->whitelist( $input, 'position', array( 'before', 'after', 'both', 'none' ) ),
			'post_types'   => array_values( array_unique( array_filter( $post_types ) ) ),
			'button_style' => $this->whitelist( $input, 'button_style', array( 'icon_text', 'icon_only', 'text_only' ) ),
			'shape'        => $this->whitelist( $input, 'shape', array( 'rounded', 'square', 'circle' ) ),
			'size'         => $this->whitelist( $input, 'size', array( 'small', 'medium', 'large' ) ),
			'label'        => isset( $input['label'] ) ? sanitize_text_field( $input['label'] ) : $defaults['label'],
			'open_in'      => $this->whitelist( $input, 'open_in', array( 'popup', 'new_tab' ) ),
		);
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap psss-admin">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'psss_settings_group' );
				do_settings_sections( self::PAGE );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the networks checkboxes.
	 *
	 * @return void
	 */
	public function render_networks_field() {
		$current = (array) $this->get_settings()['networks'];

		echo '<ul class="psss-networks-list">';

		foreach ( self::NETWORKS as $network ) {
			printf(
				'<li><label><input type="checkbox" name="%1$s[networks][]" value="%2$s"%3$s /> %4$s</label></li>',
				esc_attr( self::OPTION ),
				esc_attr( $network ),
				checked( in_array( $network, $current, true ), true, false ),
				esc_html( ShareUrl::get_label( $network ) )
			);
		}

		echo '</ul>';
	}

	/**
	 * Render the position radios.
	 *
	 * @return void
	 */
	public function render_position_field() {
		$this->render_radios(
			'position',
			array(
				'before' => __( 'Before content', 'pavel-silinskii-social-share' ),
				'after'  => __( 'After content', 'pavel-silinskii-social-share' ),
				'both'   => __( 'Both', 'pavel-silinskii-social-share' ),
				'none'   => __( 'None - use shortcode', 'pavel-silinskii-social-share' ),
			)
		);
		echo '<p class="description">' . esc_html__( 'Shortcode:', 'pavel-silinskii-social-share' ) . ' <code>[psss_share]</code></p>';
	}

	/**
	 * Render the post type checkboxes.
	 *
	 * @return void
	 */
	public function render_post_types_field() {
		$choices = array();

		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $post_type ) {
			if ( 'attachment' === $post_type->name ) {
				continue;
			}

			$choices[ $post_type->name ] = $post_type->labels->name;
		}

		$this->render_checkboxes( 'post_types', $choices );
	}

	/**
	 * Render the button style radios.
	 *
	 * @return void
	 */
	public function render_button_style_field() {
		$this->render_radios(
			'button_style',
			array(
				'icon_text' => __( 'Icon + Text', 'pavel-silinskii-social-share' ),
				'icon_only' => __( 'Icon only', 'pavel-silinskii-social-share' ),
				'text_only' => __( 'Text only', 'pavel-silinskii-social-share' ),
			)
		);
	}

	/**
	 * Render the shape radios.
	 *
	 * @return void
	 */
	public function render_shape_field() {
		$this->render_radios(
			'shape',
			array(
				'rounded' => __( 'Rounded', 'pavel-silinskii-social-share' ),
				'square'  => __( 'Square', 'pavel-silinskii-social-share' ),
				'circle'  => __( 'Circle', 'pavel-silinskii-social-share' ),
			)
		);
	}

	/**
	 * Render the size radios.
	 *
	 * @return void
	 */
	public function render_size_field() {
		$this->render_radios(
			'size',
			array(
				'small'  => __( 'Small', 'pavel-silinskii-social-share' ),
				'medium' => __( 'Medium', 'pavel-silinskii-social-share' ),
				'large'  => __( 'Large', 'pavel-silinskii-social-share' ),
			)
		);
	}

	/**
	 * Render the label text input.
	 *
	 * @return void
	 */
	public function render_label_field() {
		printf(
			'<input type="text" id="psss-label" name="%1$s[label]" value="%2$s" class="regular-text" /><p class="description">%3$s</p>',
			esc_attr( self::OPTION ),
			esc_attr( $this->get_settings()['label'] ),
			esc_html__( 'Heading above buttons. Leave empty to hide.', 'pavel-silinskii-social-share' )
		);
	}

	/**
	 * Render the open-in radios.
	 *
	 * @return void
	 */
	public function render_open_in_field() {
		$this->render_radios(
			'open_in',
			array(
				'popup'   => __( 'Open in popup', 'pavel-silinskii-social-share' ),
				'new_tab' => __( 'Open in new tab', 'pavel-silinskii-social-share' ),
			)
		);
	}

	/**
	 * Enqueue admin styles on the settings page only.
	 *
	 * @param string $hook Current admin page hook suffix.
	 * @return void
	 */
	public function enqueue( $hook ) {
		if ( 'settings_page_' . self::PAGE !== $hook ) {
			return;
		}

		wp_enqueue_style( 'psss-admin', PSSS_PLUGIN_URL . 'assets/css/psss-admin.css', array(), PSSS_VERSION );
	}

	/**
	 * Get saved settings merged with defaults.
	 *
	 * @return array<string, mixed>
	 */
	private function get_settings(): array {
		return wp_parse_args( (array) get_option( self::OPTION, array() ), Frontend::DEFAULTS );
	}

	/**
	 * Return a submitted value if it is allowed, otherwise the default.
	 *
	 * @param array    $input   Submitted values.
	 * @param string   $key     Setting key.
	 * @param string[] $allowed Allowed values.
	 * @return string
	 */
	private function whitelist( array $input, string $key, array $allowed ): string {
		$value = isset( $input[ $key ] ) ? (string) $input[ $key ] : '';

		return in_array( $value, $allowed, true ) ? $value : Frontend::DEFAULTS[ $key ];
	}

	/**
	 * Render a group of radio buttons.
	 *
	 * @param string                $key     Setting key.
	 * @param array<string, string> $choices Value => label.
	 * @return void
	 */
	private function render_radios( string $key, array $choices ) {
		$current = $this->get_settings()[ $key ];

		echo '<fieldset class="psss-radio-group">';

		foreach ( $choices as $value => $label ) {
			printf(
				'<label><input type="radio" name="%1$s[%2$s]" value="%3$s"%4$s /> %5$s</label>',
				esc_attr( self::OPTION ),
				esc_attr( $key ),
				esc_attr( $value ),
				checked( $current, $value, false ),
				esc_html( $label )
			);
		}

		echo '</fieldset>';
	}

	/**
	 * Render a group of checkboxes.
	 *
	 * @param string                $key     Setting key.
	 * @param array<string, string> $choices Value => label.
	 * @return void
	 */
	private function render_checkboxes( string $key, array $choices ) {
		$current = (array) $this->get_settings()[ $key ];

		echo '<fieldset class="psss-checkbox-group">';

		foreach ( $choices as $value => $label ) {
			printf(
				'<label><input type="checkbox" name="%1$s[%2$s][]" value="%3$s"%4$s /> %5$s</label>',
				esc_attr( self::OPTION ),
				esc_attr( $key ),
				esc_attr( $value ),
				checked( in_array( $value, $current, true ), true, false ),
				esc_html( $label )
			);
		}

		echo '</fieldset>';
	}
}
