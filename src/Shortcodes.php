<?php

namespace Pronamic\WordPress\Pay\Extensions\S2Member;

use c_ws_plugin__s2member_list_servers;
use Pronamic\WordPress\Pay\Core\Util as Core_Util;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Util as Pay_Util;

/**
 * Title: s2Member shortcodes
 * Description:
 * Copyright: 2005-2020 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.5
 * @since   1.0.0
 */
class Shortcodes {
	/**
	 * Index to identify shortcodes
	 *
	 * @var int
	 */
	private $index = 0;

	/**
	 * Payment errors
	 *
	 * @var array
	 */
	private $error = array();

	/**
	 * Constructs and initializes s2Member pay shortcodes
	 */
	public function __construct() {
		add_shortcode( 'pronamic_ideal_s2member', array( $this, 'shortcode_pay' ) );
	}

	/**
	 * Create an hash
	 *
	 * @param array $data Data to hash.
	 *
	 * @return string
	 */
	public function create_hash( $data ) {
		ksort( $data );

		return sha1( implode( '', $data ) . AUTH_SALT );
	}

	/**
	 * Handles the generation of the form from shortcode arguments.
	 *
	 * Expected shortcode example (made by generator)
	 *
	 * [pronamic_ideal_s2member cost="10" period="1 Y" level="1" description="asdfasdfasdfas asdf asdf asdfa" ]
	 *
	 * period represents one of the predetermined durations they can
	 * selected from the dropdown.
	 *
	 * cost is set by the shortcode generator.  Must be ISO standard format ( . as decimal seperator )
	 *
	 * level is the level access upon payment will be granted.
	 *
	 * description is text shown at payment.
	 *
	 * @param array $atts All arguments inside the shortcode.
	 *
	 * @return string
	 */
	public function shortcode_pay( $atts ) {
		$this->handle_payment();

		$this->index ++;

		$defaults = array(
			'period'          => null,
			'cost'            => null,
			'level'           => null,
			'description'     => __( 'iDEAL s2Member Payment || {{order_id}}', 'pronamic_ideal' ),
			'button_text'     => __( 'Pay', 'pronamic_ideal' ),
			'ccaps'           => null,
			'payment_method'  => null,
			'recurring'       => null,
			'subscription_id' => null,
		);

		// Combine the passed options.
		$atts = shortcode_atts( $defaults, $atts );

		$atts['order_id'] = uniqid();

		// Output.
		$output = '';

		// Get the config ID.
		$config_id = get_option( 'pronamic_pay_s2member_config_id' );

		// Get the gateway from the configuration.
		$gateway = Plugin::get_gateway( $config_id );

		if ( ! $gateway ) {
			return $output;
		}

		if ( null !== $atts['payment_method'] ) {
			$supported_payment_methods = $gateway->get_supported_payment_methods();

			if ( in_array( $atts['payment_method'], $supported_payment_methods, true ) ) {
				$gateway->set_payment_method( $atts['payment_method'] );
			} else {
				$atts['payment_method'] = null;
			}
		}

		// Data.
		$data = new PaymentData( $atts );

		// Hash.
		$hash_data = array(
			'order_id'        => $atts['order_id'],
			'period'          => $atts['period'],
			'cost'            => $atts['cost'],
			'level'           => $atts['level'],
			'description'     => $atts['description'],
			'ccaps'           => $atts['ccaps'],
			'payment_method'  => $atts['payment_method'],
			'recurring'       => $atts['recurring'],
			'subscription_id' => $atts['subscription_id'],
		);

		// Output.
		$output .= $this->payment_error();

		$output .= '<form method="post" action="">';

		if ( ! is_user_logged_in() ) {
			$output .= sprintf(
				'<label for="%s">%s</label>',
				esc_attr( 'pronamic_pay_s2member_email' ),
				esc_html__( 'Email', 'pronamic_ideal' )
			);
			$output .= ' ';
			$output .= sprintf(
				'<input id="%s" name="%s" value="%s" type="text" />',
				esc_attr( 'pronamic_pay_s2member_email' ),
				esc_attr( 'pronamic_pay_s2member_email' ),
				$data->get_email()
			);
			$output .= ' ';
		}

		// List servers opt-in checkbox.
		if ( Core_Util::class_method_exists( 'c_ws_plugin__s2member_list_servers', 'list_servers_integrated' ) && ! empty( $GLOBALS['WS_PLUGIN__']['s2member']['o']['custom_reg_opt_in'] ) && c_ws_plugin__s2member_list_servers::list_servers_integrated() ) {
			$output .= sprintf(
				'<label for="pronamic_pay_s2member_opt_in">
					<input type="checkbox" name="pronamic_pay_s2member_opt_in" id="pronamic_pay_s2member_opt_in" value="1" %1$s /> %2$s
				</label><br />',
				checked( $GLOBALS['WS_PLUGIN__']['s2member']['o']['custom_reg_opt_in'], 1, false ),
				$GLOBALS['WS_PLUGIN__']['s2member']['o']['custom_reg_opt_in_label']
			);
		}

		$output .= $gateway->get_input_html();

		$output .= ' ';

		$output .= Pay_Util::html_hidden_fields(
			array(
				'pronamic_pay_s2member_index'             => $this->index,
				'pronamic_pay_s2member_hash'              => $this->create_hash( $hash_data ),
				'pronamic_pay_s2member_data[order_id]'    => $atts['order_id'],
				'pronamic_pay_s2member_data[period]'      => $atts['period'],
				'pronamic_pay_s2member_data[cost]'        => $atts['cost'],
				'pronamic_pay_s2member_data[level]'       => $atts['level'],
				'pronamic_pay_s2member_data[description]' => $atts['description'],
				'pronamic_pay_s2member_data[ccaps]'       => $atts['ccaps'],
				'pronamic_pay_s2member_data[payment_method]' => $atts['payment_method'],
				'pronamic_pay_s2member_data[recurring]'   => $atts['recurring'],
				'pronamic_pay_s2member_data[subscription_id]' => $atts['subscription_id'],
			)
		);

		$output .= sprintf(
			'<input name="%s" value="%s" type="submit" />',
			esc_attr( 'pronamic_pay_s2member' ),
			esc_attr( $atts['button_text'] )
		);

		$output .= '</form>';

		return $output;
	}

	/**
	 * Handle payment
	 */
	public function handle_payment() {
		if ( ! filter_has_var( INPUT_POST, 'pronamic_pay_s2member' ) ) {
			return;
		}

		$index  = filter_input( INPUT_POST, 'pronamic_pay_s2member_index', FILTER_SANITIZE_STRING );
		$hash   = filter_input( INPUT_POST, 'pronamic_pay_s2member_hash', FILTER_SANITIZE_STRING );
		$data   = filter_input( INPUT_POST, 'pronamic_pay_s2member_data', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY );
		$opt_in = filter_input( INPUT_POST, 'pronamic_pay_s2member_opt_in', FILTER_SANITIZE_NUMBER_INT );

		if ( $hash !== $this->create_hash( $data ) ) {
			return;
		}

		// Data.
		$data = new PaymentData( $data );

		$email = $data->get_email();

		if ( empty( $email ) ) {
			return;
		}

		// Gateway.
		$config_id = (int) \get_option( 'pronamic_pay_s2member_config_id' );

		$gateway = Plugin::get_gateway( $config_id );

		// Start.
		$payment = Plugin::start( $config_id, $gateway, $data, $data->get_payment_method() );

		update_post_meta( $payment->get_id(), '_pronamic_payment_s2member_period', $data->get_period() );
		update_post_meta( $payment->get_id(), '_pronamic_payment_s2member_level', $data->get_level() );
		update_post_meta( $payment->get_id(), '_pronamic_payment_s2member_ccaps', $data->get_ccaps() );

		// List server opt-in.
		if ( ! empty( $opt_in ) ) {
			update_post_meta( $payment->get_id(), '_pronamic_payment_s2member_opt_in', $opt_in );
		}

		if ( $payment->get_subscription_id() ) {
			update_post_meta( $payment->get_subscription_id(), '_pronamic_subscription_s2member_period', $data->get_period() );
			update_post_meta( $payment->get_subscription_id(), '_pronamic_subscription_s2member_level', $data->get_level() );
			update_post_meta( $payment->get_subscription_id(), '_pronamic_subscription_s2member_ccaps', $data->get_ccaps() );
		}

		$error = $gateway->get_error();

		if ( is_wp_error( $error ) ) {
			// Set error message.
			$this->error[ $index ] = array( Plugin::get_default_error_message() );

			foreach ( $error->get_error_messages() as $message ) {
				$this->error[ $index ][] = $message;
			}
		} else {
			// Redirect.
			$gateway->redirect( $payment );
		}
	}

	/**
	 * Payment error for shortcode
	 *
	 * @param int $index Shortcode index.
	 *
	 * @return bool/string Default: false. Error string in case of payment error
	 *
	 * @since 1.1.0
	 */
	public function payment_error( $index = null ) {
		if ( ! is_int( $index ) ) {
			$index = $this->index;
		}

		if ( isset( $this->error[ $index ] ) ) {
			return sprintf(
				'<p><strong>%s</strong><br><em>%s: %s</em></p>',
				$this->error[ $index ][0],
				__( 'Error', 'pronamic_ideal' ),
				$this->error[ $index ][1]
			);
		}

		return false;
	}
}
