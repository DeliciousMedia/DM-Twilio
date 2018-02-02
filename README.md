# DM Twilio

WordPress plugin to send SMS via Twilio and track message delivery within WordPress.

This plugin is designed for developers and doesn't provide any "out of the box" functionality for end-users.

## Installation

The best way to install this plugin is via Composer `composer require deliciousmedia/dm-twilio`. You could also download it directly, but you'll need to manually include the [Twilio PHP SDK](https://www.twilio.com/docs/libraries/php).

## Usage

Message data is stored in the `dmtwilio_message` custom post type, and status tracked via the `dmtwilio_message_status` taxonomy allowing you to access the information via existing WordPress functionality if desired.

Send a message with `dmtwilio_send_message($to_number, $message);`.

Check the status of a message with `dmtwilio_get_message_status($sid);`.

## Development

You can set the `DMTWILIO_CALLBACK_ENDPOINT` constant to override the Twilio callback URL for local development via a service such as ngrok.

You can also expose the `dmtwilio_message` custom post type is hidden by default, but you can expose it be either setting `DM_ENVIRONMENT` to "DEV" or by overwriting the pluggable `dm_is_dev` function to return true in your development environment.

## Todo

- Housekeeping functions to remove old messages.
- Better handling of missing Twilio SDK.
- Option to remove all data on uninstall.
- Validate number before sending.

---
Built by the team at [Delicious Media](https://www.deliciousmedia.co.uk/), a specialist WordPress development agency based in Sheffield, UK.