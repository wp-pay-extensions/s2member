<?php

namespace Pronamic\WordPress\Pay\Extensions\S2Member;

use Pronamic\WordPress\Pay\Payments\Payment;
use WP_User;

/**
 * Title: s2Member utility class
 * Description:
 * Copyright: 2005-2021 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 1.0.0
 * @since   1.0.0
 */
class Util {
	/**
	 * Converts an custom capabilities string to an array
	 *
	 * @link https://github.com/websharks/s2member/blob/150311/s2member/includes/classes/paypal-notify-in-subscr-modify-w-level.inc.php#L103-L111
	 * @link https://github.com/websharks/s2member/blob/150311/s2member/includes/menu-pages/api-ops.inc.php#L192
	 *
	 * @param string $string Custom capabilities string.
	 *
	 * @return array
	 */
	public static function ccap_string_to_array( $string ) {
		$array = explode( ',', $string );
		$array = array_map( 'trim', $array );

		return $array;
	}

	/**
	 * Update users custom capabilities
	 *
	 * @link https://github.com/websharks/s2member/blob/150311/s2member/includes/classes/paypal-notify-in-subscr-modify-w-level.inc.php#L103-L111
	 *
	 * @param WP_User $user                User.
	 * @param array   $custom_capabilities Custom capabilities.
	 */
	public static function ccap_user_update( WP_User $user, array $custom_capabilities ) {
		// Remove all custom capabilities.
		foreach ( $user->allcaps as $capability => $granted ) {
			if ( 'access_s2member_ccap_' === substr( $capability, 0, 21 ) ) {
				$user->remove_cap( $capability );
			}
		}

		// Add custom capabilities.
		foreach ( $custom_capabilities as $custom_capability ) {
			$user->add_cap( 'access_s2member_ccap_' . $custom_capability );
		}
	}

	/**
	 * Update user auto EOT time to now + grace time setting. Membership will end on next s2Member auto EOT process.
	 *
	 * @param WP_User $user User.
	 */
	public static function auto_eot_now_user_update( $user ) {
		if ( ! ( $user instanceof WP_User ) ) {
			return;
		}

		$auto_eot_time = strtotime( 'now' );

		if ( isset( $GLOBALS['WS_PLUGIN__']['s2member']['o']['eot_grace_time'] ) ) {
			$eot_grace_time = (int) $GLOBALS['WS_PLUGIN__']['s2member']['o']['eot_grace_time'];
			$eot_grace_time = (int) apply_filters( 'ws_plugin__s2member_eot_grace_time', $eot_grace_time );

			$auto_eot_time += $eot_grace_time;
		}

		update_user_option( $user->ID, 's2member_auto_eot_time', $auto_eot_time );
	}

	/**
	 * Update users custom capabilities
	 *
	 * @link https://github.com/websharks/s2member/blob/150311/s2member/includes/classes/paypal-notify-in-subscr-modify-w-level.inc.php#L103-L111
	 *
	 * @param Payment $payment Payment.
	 *
	 * @return array
	 */
	public static function get_payment_data( Payment $payment ) {
		// Subscriptions.
		$subscriptions = $payment->get_subscriptions();

		// Get subscription ID from payment periods.
		$subscription_id = null;

		$periods = $payment->get_periods();

		if ( null !== $periods ) {
			foreach ( $periods as $period ) {
				$subscription_id = $period->get_phase()->get_subscription()->get_id();
			}
		}

		// Determine post ID and recurring.
		$post_id = $payment->get_id();

		$meta_prefix = '_pronamic_payment_';

		$recurring = null;

		if ( null !== $subscription_id ) {
			if ( \count( $subscriptions ) > 0 ) {
				$post_id = $subscription_id;

				$meta_prefix = '_pronamic_subscription_';
			}

			$recurring = 'Y';
		}

		// Return payment data.
		return array(
			'level'           => get_post_meta( $post_id, $meta_prefix . 's2member_level', true ),
			'period'          => get_post_meta( $post_id, $meta_prefix . 's2member_period', true ),
			'ccaps'           => get_post_meta( $post_id, $meta_prefix . 's2member_ccaps', true ),
			'recurring'       => $recurring,
			'subscription_id' => $subscription_id,
		);
	}

	/**
	 * Get email address from logged in user or form input.
	 *
	 * @return string|null
	 */
	public static function get_user_input_email() {
		$email = null;

		// Get email from logged in user.
		if ( \is_user_logged_in() ) {
			$user = \wp_get_current_user();

			$email = $user->user_email;
		}

		// Get email from form input.
		if ( \filter_has_var( \INPUT_POST, 'pronamic_pay_s2member_email' ) ) {
			$email = (string) \filter_input( \INPUT_POST, 'pronamic_pay_s2member_email', \FILTER_VALIDATE_EMAIL );
		}

		return $email;
	}
}
