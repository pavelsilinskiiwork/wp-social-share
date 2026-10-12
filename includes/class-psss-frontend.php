<?php
/**
 * Front-end output of the share buttons.
 *
 * @package PavelSilinskii\SocialShare
 */

namespace PavelSilinskii\SocialShare;

defined( 'ABSPATH' ) || exit;

/**
 * Adds share buttons to post content and registers the [psss_share] shortcode.
 */
class Frontend {

	/**
	 * Default settings, used for any key missing from the saved option.
	 *
	 * @var array<string, mixed>
	 */
	public const DEFAULTS = array(
		'networks'     => array( 'facebook', 'twitter', 'linkedin', 'whatsapp', 'pinterest' ),
		'position'     => 'after',
		'post_types'   => array( 'post' ),
		'button_style' => 'icon_text',
		'shape'        => 'rounded',
		'size'         => 'medium',
		'label'        => 'Share this:',
		'open_in'      => 'popup',
	);

	/**
	 * Plugin settings merged with defaults.
	 *
	 * @var array<string, mixed>
	 */
	private array $settings;

	/**
	 * Load settings and register hooks.
	 */
	public function __construct() {
		$this->settings = wp_parse_args( (array) get_option( 'psss_settings', array() ), self::DEFAULTS );

		add_filter( 'the_content', array( $this, 'filter_content' ), 20 );
		add_shortcode( 'psss_share', array( $this, 'shortcode_handler' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Enqueue front-end assets.
	 *
	 * @return void
	 */
	public function enqueue() {
		wp_enqueue_style( 'psss-frontend', PSSS_PLUGIN_URL . 'assets/css/psss-frontend.css', array(), PSSS_VERSION );
		wp_enqueue_script( 'psss-frontend', PSSS_PLUGIN_URL . 'assets/js/psss-frontend.js', array(), PSSS_VERSION, true );
	}

	/**
	 * Add share buttons to singular post content.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function filter_content( string $content ): string {
		if ( is_admin() || ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		if ( ! in_array( get_post_type(), (array) $this->settings['post_types'], true ) ) {
			return $content;
		}

		if ( 'none' === $this->settings['position'] ) {
			return $content;
		}

		$buttons = $this->get_buttons( (int) get_the_ID() );

		return match ( $this->settings['position'] ) {
			'before' => $buttons . $content,
			'after'  => $content . $buttons,
			'both'   => $buttons . $content . $buttons,
			default  => $content,
		};
	}

	/**
	 * Render the [psss_share] shortcode.
	 *
	 * @param array|string $atts Shortcode attributes (an empty string when none are given).
	 * @return string
	 */
	public function shortcode_handler( array|string $atts = array() ): string {
		$atts = shortcode_atts(
			array(
				'post_id' => get_the_ID(),
			),
			(array) $atts,
			'psss_share'
		);

		return $this->get_buttons( absint( $atts['post_id'] ) );
	}

	/**
	 * Build the share buttons HTML for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public function get_buttons( int $post_id ): string {
		if ( ! $post_id ) {
			return '';
		}

		$classes = array(
			'psss-share',
			'psss-style-' . sanitize_html_class( $this->settings['button_style'] ),
			'psss-shape-' . sanitize_html_class( $this->settings['shape'] ),
			'psss-size-' . sanitize_html_class( $this->settings['size'] ),
		);

		if ( 'popup' === $this->settings['open_in'] ) {
			$classes[] = 'psss-open-popup';
		}

		$html = '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">';

		if ( ! empty( $this->settings['label'] ) ) {
			$html .= '<span class="psss-label">' . esc_html( $this->settings['label'] ) . '</span>';
		}

		$html .= '<div class="psss-buttons">';

		foreach ( (array) $this->settings['networks'] as $network ) {
			$url = ShareUrl::get( $network, $post_id );

			if ( '' === $url ) {
				continue;
			}

			$label = ShareUrl::get_label( $network );

			$html .= sprintf(
				'<a class="psss-btn psss-%1$s" href="%2$s" target="_blank" rel="noopener noreferrer" aria-label="%3$s"><span class="psss-icon">%4$s</span><span class="psss-text">%5$s</span></a>',
				esc_attr( $network ),
				esc_url( $url ),
				/* translators: %s: social network name. */
				esc_attr( sprintf( __( 'Share on %s', 'pavel-silinskii-social-share' ), $label ) ),
				ShareUrl::get_svg( $network ),
				esc_html( $label )
			);
		}

		$html .= sprintf(
			'<button type="button" class="psss-btn psss-copy" data-url="%1$s" data-copied="%2$s" aria-label="%3$s"><span class="psss-icon">%4$s</span><span class="psss-text">%3$s</span></button>',
			esc_url( get_permalink( $post_id ) ),
			esc_attr__( 'Copied!', 'pavel-silinskii-social-share' ),
			esc_html__( 'Copy link', 'pavel-silinskii-social-share' ),
			ShareUrl::get_svg( 'copy' )
		);

		$html .= '</div></div>';

		return $html;
	}
}
