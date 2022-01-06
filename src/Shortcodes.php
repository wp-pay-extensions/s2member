<?php

namespace Pronamic\WordPress\Pay\Extensions\S2Member;

use c_ws_plugin__s2member_list_servers;
use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\ContactName;
use Pronamic\WordPress\Pay\Core\Util as Core_Util;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Payments\PaymentLines;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Subscriptions\Subscription;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionHelper;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionInterval;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionPhase;
use Pronamic\WordPress\Pay\Util as Pay_Util;

/**
 * Title: s2Member shortcodes
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.1.2
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
		add_action( 'init', array( $this, 'handle_payment' ) );

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
	 * cost is set by the shortcode generator.  Must be ISO standard format ( . as decimal separator )
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

		// Period.
		$period = \str_replace( ' ', '', $atts['period'] );

		if ( ! empty( $period ) ) {
			$interval_value = (int) $period;

			$interval = (object) array(
				'value' => $interval_value,
				'unit'  => \str_replace( $interval_value, '', $period ),
			);

			$atts['period'] = sprintf( '%d %s', $interval->value, $interval->unit );
		}

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
				(string) Util::get_user_input_email()
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
		$email = Util::get_user_input_email();

		if ( empty( $email ) ) {
			return;
		}

		// Gateway.
		$config_id = (int) \get_option( 'pronamic_pay_s2member_config_id' );

		$gateway = Plugin::get_gateway( $config_id );

		if ( null === $gateway ) {
			// Set error message.
			$this->error[ $index ] = array(
				Plugin::get_default_error_message(),
				__( 'The payment gateway could not be found.', 'pronamic_ideal' ),
			);

			return;
		}

		/*
		 * Payment.
		 */
		$payment = new Payment();

		$order_id = $data['order_id'];

		$replacements = array(
			'{{order_id}}' => $order_id,
		);

		$description = strtr( $data['description'], $replacements );

		$payment->set_config_id( $config_id );
		$payment->set_description( $description );
		$payment->set_payment_method( $data['payment_method'] );

		$payment->order_id = $order_id;

		// Source.
		$payment->set_source( 's2member' );
		$payment->set_source_id( $order_id );

		// Data.
		$user = \wp_get_current_user();

		$first_name = $user->first_name;
		$last_name  = $user->last_name;
		$user_id    = \get_current_user_id();

		// Name.
		$name = null;

		$name_data = array(
			$first_name,
			$last_name,
		);

		$name_data = array_filter( $name_data );

		if ( ! empty( $name_data ) ) {
			$name = new ContactName();

			if ( ! empty( $first_name ) ) {
				$name->set_first_name( $first_name );
			}

			if ( ! empty( $last_name ) ) {
				$name->set_last_name( $last_name );
			}
		}

		// Customer.
		$customer_data = array(
			$name,
			$email,
			$user_id,
		);

		$customer_data = array_filter( $customer_data );

		if ( ! empty( $customer_data ) ) {
			$customer = new Customer();

			$customer->set_name( $name );
			$customer->set_email( $email );

			if ( ! empty( $user_id ) ) {
				$customer->set_user_id( (int) $user_id );
			}

			$payment->set_customer( $customer );
		}

		// Billing address.
		$address_data = array(
			$name,
			$email,
		);

		$address_data = array_filter( $address_data );

		if ( ! empty( $address_data ) ) {
			$address = new Address();

			if ( ! empty( $name ) ) {
				$address->set_name( $name );
			}

			$address->set_email( $email );

			$payment->set_billing_address( $address );
		}

		// Lines.
		$payment->lines = new PaymentLines();

		$line = $payment->lines->new_line();

		$price = new Money( $data['cost'], 'EUR' );

		$line->set_name( $description );
		$line->set_quantity( 1 );
		$line->set_unit_price( $price );
		$line->set_total_amount( $price );

		$payment->set_total_amount( $payment->lines->get_amount() );

		// Subscription.
		if ( 'Y' === $data['recurring'] && isset( $data['period'] ) && ! empty( $data['period'] ) ) {
			// Find existing subscription.
			$subscription = null;

			$start_date = new \DateTimeImmutable();

			$user_subscription_id = \get_user_option( 's2member_subscr_id', $user_id );

			if ( null === $subscription && ! empty( $user_subscription_id ) ) {
				$subscription = \get_pronamic_subscription( (int) $user_subscription_id );
			}

			// Cancel active phases.
			if ( null !== $subscription ) {
				foreach ( $subscription->get_phases() as $phase ) {
					// Check if phase has already been completed.
					if ( $phase->all_periods_created() ) {
						continue;
					}

					// Check if phase is already canceled.
					$canceled_at = $phase->get_canceled_at();

					if ( ! empty( $canceled_at ) ) {
						continue;
					}

					// Set start date for new phases (before setting canceled date).
					$next_date = $phase->get_next_date();

					if ( null !== $next_date ) {
						$start_date = $next_date;
					}

					// Set canceled date.
					$phase->set_canceled_at( new \DateTimeImmutable() );
				}
			}

			// New subscription.
			if ( null === $subscription ) {
				$subscription = new Subscription();

				$subscription->set_source( 's2member' );
				$subscription->set_source_id( $order_id );
			}

			// Data.
			$subscription->set_description( $description );
			$subscription->set_lines( $payment->get_lines() );

			// Phase.
			$period = \str_replace( ' ', '', $data['period'] );

			$interval_value = (int) $period;

			$interval = (object) array(
				'value' => $interval_value,
				'unit'  => \str_replace( $interval_value, '', $period ),
			);

			$phase = new SubscriptionPhase(
				$subscription,
				$start_date,
				new SubscriptionInterval( 'P' . $interval->value . Core_Util::to_period( $interval->unit ) ),
				$price
			);

			$subscription->add_phase( $phase );

			$period = $subscription->new_period();

			if ( null !== $period ) {
				$payment->add_period( $period );
			}

			// Update existing subscription dates.
			if ( null !== $payment->subscription ) {
				$next_payment_date = $phase->get_next_date();

				/**
				 * Next payment date?
				 *
				 * @todo See older implementation.
				 */
			}
		}

		// Start.
		try {
			$payment = Plugin::start_payment( $payment );
		} catch ( \Exception $e ) {
			// Set error message.
			$this->error[ $index ] = array(
				Plugin::get_default_error_message(),
				$e->getMessage(),
			);
		}

		update_post_meta( $payment->get_id(), '_pronamic_payment_s2member_period', $data['period'] );
		update_post_meta( $payment->get_id(), '_pronamic_payment_s2member_level', $data['level'] );
		update_post_meta( $payment->get_id(), '_pronamic_payment_s2member_ccaps', $data['ccaps'] );

		// List server opt-in.
		if ( ! empty( $opt_in ) ) {
			update_post_meta( $payment->get_id(), '_pronamic_payment_s2member_opt_in', $opt_in );
		}

		// Add s2Member meta to subscription.
		$periods = $payment->get_periods();

		if ( null !== $periods ) {
			foreach ( $periods as $period ) {
				$subscription_id = $period->get_phase()->get_subscription()->get_id();

				if ( null === $subscription_id ) {
					continue;
				}

				update_post_meta( $subscription_id, '_pronamic_subscription_s2member_period', $data['period'] );
				update_post_meta( $subscription_id, '_pronamic_subscription_s2member_level', $data['level'] );
				update_post_meta( $subscription_id, '_pronamic_subscription_s2member_ccaps', $data['ccaps'] );
			}
		}

		if ( ! \array_key_exists( $index, $this->error ) ) {
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
