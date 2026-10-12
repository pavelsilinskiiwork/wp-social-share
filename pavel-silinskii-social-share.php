<?php
/**
 * Plugin Name:       Pavel Silinskii Social Share
 * Plugin URI:        https://github.com/pavelsilinskiiwork/pavel-silinskii-social-share
 * Description:       Lightweight social share buttons for WordPress. Add Facebook, Twitter/X, LinkedIn, Pinterest, and WhatsApp share buttons to your posts and pages.
 * Version:           1.0.0
 * Requires at least: 5.9
 * Requires PHP:      8.0
 * Author:            Pavel Silinskii
 * Author URI:        https://profiles.wordpress.org/pavelsilinskii/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pavel-silinskii-social-share
 * Domain Path:       /languages
 *
 * @package PavelSilinskii\SocialShare
 */

namespace PavelSilinskii\SocialShare;

defined( 'ABSPATH' ) || exit;

define( 'PSSS_VERSION', '1.0.0' );
define( 'PSSS_PLUGIN_FILE', __FILE__ );
define( 'PSSS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PSSS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once PSSS_PLUGIN_DIR . 'includes/class-psss-share-url.php';
require_once PSSS_PLUGIN_DIR . 'includes/class-psss-frontend.php';
require_once PSSS_PLUGIN_DIR . 'admin/class-psss-admin.php';

/**
 * Save default settings on activation, without overwriting existing ones.
 *
 * @return void
 */
function activate() {
	add_option( 'psss_settings', Frontend::DEFAULTS );
}
register_activation_hook( __FILE__, __NAMESPACE__ . '\\activate' );

add_action(
	'plugins_loaded',
	function () {
		new Frontend();

		if ( is_admin() ) {
			new Admin();
		}
	}
);
