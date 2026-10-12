<?php
/**
 * Uninstall: remove plugin settings.
 *
 * @package PavelSilinskii\SocialShare
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'psss_settings' );
