<?php
/**
 *
 * Settings page.
 *
 * @package dm-twilio
 */

// Disallow direct access.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Add our options page.
 *
 * @return void.
 */
function dmtwilio_add_admin_menu() {
	add_options_page( 'DM Twilio', 'DM Twilio', 'manage_options', 'dm_twilio', 'dmtwilio_options_page' );
}
add_action( 'admin_menu', 'dmtwilio_add_admin_menu' );

/**
 * Initialize our settings.
 *
 * @return void
 */
function dmtwilio_settings_init() {
	register_setting( 'dmtwilio', 'dmtwilio_settings' );
	add_settings_section( 'dmtwilio_dmtwilio_section', __( 'Twilio Account Settings', 'dmtwilio' ), 'dmtwilio_settings_section_callback', 'dmtwilio' );
	add_settings_field( 'dmtwilio_account_sid', __( 'Twilio account SID', 'dmtwilio' ), 'dmtwilio_account_sid_render', 'dmtwilio', 'dmtwilio_dmtwilio_section' );
	add_settings_field( 'dmtwilio_account_token', __( 'Twilio account token', 'dmtwilio' ), 'dmtwilio_account_token_render', 'dmtwilio', 'dmtwilio_dmtwilio_section' );
	add_settings_field( 'dmtwilio_from_number', __( 'Twilio number to send as', 'dmtwilio' ), 'dmtwilio_from_number_render', 'dmtwilio', 'dmtwilio_dmtwilio_section' );
}
add_action( 'admin_init', 'dmtwilio_settings_init' );

/**
 * Render settings field.
 *
 * @return void
 */
function dmtwilio_account_sid_render() {

	$dmtwilio_settings = get_option( 'dmtwilio_settings' );
	?>
	<input type='text' name='dmtwilio_settings[dmtwilio_account_sid]' value='<?php echo esc_html( $dmtwilio_settings['dmtwilio_account_sid'] ); ?>' autocomplete="nope">
	<?php
}

/**
 * Render settings field.
 *
 * @return void
 */
function dmtwilio_account_token_render() {

	$dmtwilio_settings = get_option( 'dmtwilio_settings' );
	?>
	<input type='password' name='dmtwilio_settings[dmtwilio_account_token]' value='<?php echo esc_html( $dmtwilio_settings['dmtwilio_account_token'] ); ?>' autocomplete="nope">
	<?php
}

/**
 * Render settings field.
 *
 * @return void
 */
function dmtwilio_from_number_render() {

	if ( ! dmtwilio_is_account_ok() ) {
		echo __( '<em>You need to provide valid account details before you can continue setup.<em/>' ); // WPCS: XSS ok.
		return;
	}

	$dmtwilio_settings = get_option( 'dmtwilio_settings' );

	?>
<select name='dmtwilio_settings[dmtwilio_from_number]'>	
	<?php

	foreach ( dmtwilio_get_available_numbers() as $number ) {
		echo '<option value="' . esc_html( $number ) . '" ' . selected( $dmtwilio_settings['dmtwilio_from_number'], 1 ) . '>' . esc_html( $number ) . '</option>';
	}
	?>
	</select>
	<?php
}

/**
 * Render settings field.
 *
 * @return void
 */
function dmtwilio_enabled_render() {
	if ( ! dmtwilio_is_account_ok() ) {
		echo __( '<em>You need to provide valid account details before you can continue setup.<em/>' ); // WPCS: XSS ok.
		return;
	}
	$dmtwilio_settings = get_option( 'dmtwilio_settings' );
	?>
	<input type='checkbox' name='dmtwilio_settings[dmtwilio_enabled]' <?php checked( $dmtwilio_settings['dmtwilio_enabled'], 1 ); ?> value='1'>
	<?php
}

/**
 * Render settings section content.
 *
 * @return void
 */
function dmtwilio_settings_section_callback() {

	echo __( 'Setup your Twilio account details here. You can find your account SID and token in the <a href="https://www.twilio.com/console" target="_blank" rel=”noreferrer noopener”>Twilio Console</a>.', 'dmtwilio' ); // WPCS: XSS ok.
}

/**
 * Render options page.
 *
 * @return void
 */
function dmtwilio_options_page() {
	?>
	<form action="options.php" method="post" autocomplete="off">
	<h2>DM Twilio Notifications</h2>
	<?php
	settings_fields( 'dmtwilio' );
	do_settings_sections( 'dmtwilio' );
	submit_button();
	?>
	</form>
	<?php
}

/**
 * When saving settings, check if we have an account SID & token, if so try and get the numbers
 * which are available in the account.
 *
 * @return void
 */
function dmtwilio_settings_updated( $option_name, $old_value, $value ) {
	$dmtwilio_settings = get_option( 'dmtwilio_settings' );

	if ( 'dmtwilio_settings' != $option_name ) {
		return;
	}

	if ( ! isset( $dmtwilio_settings['dmtwilio_account_sid'] ) && ! isset( $dmtwilio_settings['dmtwilio_account_token'] ) ) {
		update_option( 'dmtwilio_account_verified', false );
		return;
	}

	if ( empty( $dmtwilio_settings['dmtwilio_account_sid'] ) || empty( $dmtwilio_settings['dmtwilio_account_token'] ) ) {
		update_option( 'dmtwilio_account_verified', false );
		return;
	}

	dmtwilio_get_available_numbers( true );

}
add_action( 'updated_option', 'dmtwilio_settings_updated', 10, 3 );

/**
 * Admin notice to display if settings are not marked as OK.
 *
 * @return void
 */
function dmwilio_admin_settings_warning() {
	if ( dmtwilio_is_account_ok() ) {
		return;
	}
	?>
	<div class="notice notice-warning is-dismissible">
		<p><?php printf( 'DM Twilio is not setup, please visit the <a href="%s">settings page</a> to complete setup.', admin_url( '/options-general.php?page=dm_twilio' ) ); ?></p>
	</div>
	<?php
}
add_action( 'admin_notices', 'dmwilio_admin_settings_warning' );

 /**
  * Add a link to the settings page to the plugin list.
  *
  * @param  array $links Existing links.
  * @return array
  */
function dmtwilio_plugin_settings_link( $links ) {
	$links = array_merge(
		[
			'<a href="' . esc_url( admin_url( '/options-general.php?page=dm_twilio' ) ) . '">' . __( 'Settings', 'dmtwilio' ) . '</a>',
		], $links
	);
	return $links;
}
add_action( 'plugin_action_links_' . DMTWILIO_PLUGIN_PATH, 'dmtwilio_plugin_settings_link' );
