<?php
/**
 *
 * Functionality for sending DMS via Twilio
 *
 * @package dm-twilio
 */

// Disallow direct access.
if ( ! defined( 'WPINC' ) ) {
	die;
}
use Twilio\Rest\Client;

/**
 * Admin notice to display if Twilio SDK is not available.
 *
 * @return void
 */
function dmwilio_admin_twilio_sdk_error() {
	?>
	<div class="notice notice-error">
		<p><?php _esc_html_e( 'DM Twilio requires the Twilio SDK, but it isn\'t loaded.', 'dmtwilio' ); ?></p>
	</div>
	<?php
}

if ( ! class_exists( 'Twilio\Rest\Client' ) ) {
	add_action( 'admin_notices', 'dmwilio_admin_twilio_sdk_error' );
}

/**
 * Get the callback URL, or or the local override.
 *
 * @return string
 */
function dmtwilio_get_callback_url() {
	if ( defined( 'DMTWILIO_OVERRIDE_CALLBACK_URL' ) ) {
		return DMTWILIO_OVERRIDE_CALLBACK_URL; }
	return rest_url( trailingslashit( DMTWILIO_CALLBACK_ENDPOINT ) );
}

/**
 * Get the account SID.
 *
 * @return string
 */
function dmtwilio_get_account_sid() {
	$dmtwilio_settings = get_option( 'dmtwilio_settings' );
	return sanitize_text_field( $dmtwilio_settings['dmtwilio_account_sid'] );
}

/**
 * Get the account token.
 *
 * @return string
 */
function dmtwilio_get_account_token() {
	$dmtwilio_settings = get_option( 'dmtwilio_settings' );
	return sanitize_text_field( $dmtwilio_settings['dmtwilio_account_token'] );
}

/**
 * Get the Twilio number to send from.
 *
 * @return string
 */
function dmtwilio_get_from_number() {
	$dmtwilio_settings = get_option( 'dmtwilio_settings' );
	return sanitize_text_field( $dmtwilio_settings['dmtwilio_from_number'] );
}

/**
 * Check if the Twilio account is marked as OK.
 *
 * @return bool
 */
function dmtwilio_is_account_ok() {
	return get_option( 'dmtwilio_account_verified', false );
}

/**
 * Fetch the numbers in our Twilio account which support sending SMS, caching them in transients.
 *
 * @param  boolean $force_refresh Set to true to force fetching from Twilio and ignore our cache.
 *
 * @return array
 */
function dmtwilio_get_available_numbers( $force_refresh = false ) {

	// Attempt to fetch a stored value from transients.
	$twilio_numbers = ( get_transient( 'dmtwilio_available_numbers' ) );

	// If there's nothing stored, or the force_refresh flag is set, fetch from the Twilio API.
	if ( ! isset( $twilio_numbers ) || ! is_array( $twilio_numbers ) || $force_refresh ) {

		$twilio_client = new Client( dmtwilio_get_account_sid(), dmtwilio_get_account_token() );
		$twilio_numbers = [];

		try {
			$numbers = $twilio_client->incomingPhoneNumbers->read();
		} catch ( Exception $e ) {
			update_option( 'dmtwilio_account_verified', false );
			return new WP_Error( 'DMTWILIO_ACCOUNT_FAIL', $e->getMessage() );
		}

		// Loop through the available numbers.
		foreach ( $numbers as $number ) {
			// Only include numbers which support SMS messages.
			if ( true === $number->capabilities['sms'] ) {
				$twilio_numbers[] = $number->phoneNumber;
			}
		}

		set_transient( 'dmtwilio_available_numbers', $twilio_numbers, 3 * DAY_IN_SECONDS );
	}

	if ( ! count( $twilio_numbers ) ) {
		return new WP_Error( 'DMTWILIO_NO_NUMBERS', 'Twilio did not return any numbers supporting SMS using the credentials you supplied.' );
	}

	update_option( 'dmtwilio_account_verified', true );
	return $twilio_numbers;

}

/**
 * Attempt to send an SMS via the Twilio API.
 *
 * @param  string $to_number Number to send the SMS to.
 * @param  string $message   Message content.
 *
 * @return string|WP_Error   Returns the message SID if successful, WP_Error object if not.
 */
function dmtwilio_send_message( $to_number, $message ) {
	$client = new Client( dmtwilio_get_account_sid(), dmtwilio_get_account_token() );
	try {
		$result = $client->messages->create(
			$to_number,
			[
				'from'           => dmtwilio_get_from_number(),
				'body'           => sanitize_text_field( $message ),
				'statusCallback' => dmtwilio_get_callback_url(),
			]
		);
	} catch ( Exception $e ) {
		return new WP_Error( 'DMTWILIO_SENDING_FAILED', $e->getMessage() );
	}

	dmtwilio_store_message_status( $result->sid, $result->status, $to_number, $message );

	return $result->sid;

}
/**
 * Register REST API endpoint for Twilio callbacks
 */
add_action(
	'rest_api_init', function () {
		register_rest_route(
			'dmtwilio/v1', '/callback/', [
				'methods' => 'POST',
				'callback' => 'dmtwilio_process_callback',
			]
		);
	}
);

/**
 * Process Twilio callbacks.
 *
 * @param  array $data Contents of the POST request from Twilo.
 *
 * @return bool        Returns false if the request doesn't contain the required fields.
 */
function dmtwilio_process_callback( $data ) {

	// Twilio should send us our account SID with each request, reject calls are missing it.
	if ( dmtwilio_get_account_sid() != $data['AccountSid'] ) {
		return false;
	}

	// We need the message SID and status for this to work.
	if ( ! isset( $data['MessageSid'] ) || ! isset( $data['MessageStatus'] ) ) {
		return false;
	}

	// Store the status.
	dmtwilio_store_message_status( sanitize_text_field( $data['MessageSid'] ), sanitize_text_field( $data['MessageStatus'] ) );

	return true;

}

/**
 * Look for an SMS by SID and return the post ID if it exists.
 *
 * @param  string $sid Message identifier.
 *
 * @return bool|int
 */
function dmtwilio_get_message_id_from_sid( $sid ) {
	$post_object = get_page_by_path( $sid, OBJECT, 'dmtwilio_message' );
	if ( null === $post_object ) {
		return false;
	}
	return $post_object->ID;
}

/**
 * Store the message status against a post with the relevant SID as the slug, creating it if it doesn't exist.
 *
 * @param  string $sid       Message SID from Twilio.
 * @param  string $status    Status, must be a term in the dmtwilio_message_status taxonomy. If new ones are added,
 *                           be sure to update dmtwilio_populate_dmtwilio_message_status_tax().
 * @param  string $recipient The number the message was sent to, only required for new messages.
 * @param  string $message   Message contents, only required for new messages.
 *
 * @return int|WP_Error   Returns the Post ID which the message status has been stored.
 */
function dmtwilio_store_message_status( $sid, $status, $recipient = '', $message = '' ) {

	do_action( 'dmtwilio_before_store_message_status', $sid, $status );

	// Prepend our prefix to the status.
	$status = 'dmtwilio_' . $status;

	if ( ! term_exists( $status, 'dmtwilio_message_status' ) ) {
		return new WP_Error( 'DMTWILIO_INVALID_STATUS', 'Tried to set message ' . $sid . ' to status ' . $status . ' but that status does not exist.' );
	}

	// Find a post ID with the existing.
	$message_id = dmtwilio_get_message_id_from_sid( $sid );

	// If there isn't one, then create one.
	if ( ! $message_id ) {
		$message_post = [
			'post_title'    => $sid,
			'slug'          => $sid,
			'post_content'  => '',
			'post_status'   => 'publish',
			'post_author'   => 1,
			'post_type'     => 'dmtwilio_message',
		];

		// Insert the post into the database.
		$message_id = wp_insert_post( $message_post );

		// Store message data.
		update_post_meta( $message_id, 'dmtwilio_message_content', $message );
		update_post_meta( $message_id, 'dmtwilio_recipient', $recipient );

	}

	// Set the status.
	wp_set_object_terms( $message_id, $status, 'dmtwilio_message_status', false );

	do_action( 'dmtwilio_after_store_message_status', $sid, $status );

	return $message_id;

}

/**
 * Get the status of a message.
 *
 * @param  string $sid Message SID.
 *
 * @return string      Term from dmtwilio_message_status stripped of the dmtwilio_ prefix.
 */
function dmtwilio_get_message_status( $sid ) {

	$message_id = dmtwilio_get_message_id_from_sid( $sid );

	if ( ! $message_id ) {
		return new WP_Error( 'DMTWILIO_MESSAGE_SID', 'dmtwilio_get_message_status tried to get the status of message ' . $sid . ' but that message does not exist.' );
	}

	// Get the term in the dmtwilio_message_status taxonomy. There should only ever be one term assigned.
	$terms = wp_get_object_terms( $message_id, 'dmtwilio_message_status', 'fields=slugs&orderby=slug&order=desc' );

	// Nothing? return false.
	if ( ! isset( $terms ) || empty( $terms ) ) {
		return false;
	}

	// Just return a single flag.
	return str_replace( 'dmtwilio_', '', $terms[0] );

}

