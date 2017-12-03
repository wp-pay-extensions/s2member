<?php

/**
 * Title: s2Member utility class
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_S2Member_Util {
	/**
	 * Converts an custom capabilities string to an array
	 *
	 * @see https://github.com/websharks/s2member/blob/150311/s2member/includes/classes/paypal-notify-in-subscr-modify-w-level.inc.php#L103-L111
	 * @see https://github.com/websharks/s2member/blob/150311/s2member/includes/menu-pages/api-ops.inc.php#L192
	 */
	public static function ccap_string_to_array( $string ) {
		$array = explode( ',', $string );
		$array = array_map( 'trim', $array );

		return $array;
	}

	/**
	 * Update users custom capabilities
	 *
	 * @see https://github.com/websharks/s2member/blob/150311/s2member/includes/classes/paypal-notify-in-subscr-modify-w-level.inc.php#L103-L111
	 */
	public static function ccap_user_update( WP_User $user, array $custom_capabilities ) {
		// Remove all custom capabilities
		foreach ( $user->allcaps as $capability => $granted ) {
			if ( 'access_s2member_ccap_' === substr( $capability, 0, 21 ) ) {
				$user->remove_cap( $capability );
			}
		}

		// Add custom capabilities
		foreach ( $custom_capabilities as $custom_capability ) {
			$user->add_cap( 'access_s2member_ccap_' . $custom_capability );
		}
	}

	/**
	 * Update user auto EOT time to now + grace time setting. Membership will end on next s2Member auto EOT process.
	 *
	 * @param $user
	 */
	public static function auto_eot_now_user_update( $user ) {
		if ( ! ( $user instanceof WP_User ) ) {
			return;
		}

		$auto_eot_time  = strtotime( 'now' );

		if ( isset( $GLOBALS['WS_PLUGIN__']['s2member']['o']['eot_grace_time'] ) ) {
			$eot_grace_time = (integer) $GLOBALS['WS_PLUGIN__']['s2member']['o']['eot_grace_time'];
			$eot_grace_time = (integer) apply_filters( 'ws_plugin__s2member_eot_grace_time', $eot_grace_time );

			$auto_eot_time += $eot_grace_time;
		}

		update_user_option( $user->ID, 's2member_auto_eot_time', $auto_eot_time );
	}

	/**
	 * Update users custom capabilities
	 *
	 * @see https://github.com/websharks/s2member/blob/150311/s2member/includes/classes/paypal-notify-in-subscr-modify-w-level.inc.php#L103-L111
	 */
	public static function get_payment_data( Pronamic_WP_Pay_Payment $payment ) {
		if ( $payment->get_recurring() ) {
			return array(
				'level'           => get_post_meta( $payment->get_subscription_id(), '_pronamic_subscription_s2member_level', true ),
				'period'          => get_post_meta( $payment->get_subscription_id(), '_pronamic_subscription_s2member_period', true ),
				'ccaps'           => get_post_meta( $payment->get_subscription_id(), '_pronamic_subscription_s2member_ccaps', true ),
				'recurring'       => 'Y',
				'subscription_id' => $payment->get_subscription_id(),
			);
		}

		$recurring = null;

		if ( $payment->get_subscription_id() ) {
			$recurring = 'Y';
		}

		return array(
			'level'           => get_post_meta( $payment->get_id(), '_pronamic_payment_s2member_level', true ),
			'period'          => get_post_meta( $payment->get_id(), '_pronamic_payment_s2member_period', true ),
			'ccaps'           => get_post_meta( $payment->get_id(), '_pronamic_payment_s2member_ccaps', true ),
			'recurring'       => $recurring,
			'subscription_id' => $payment->get_subscription_id(),
		);
	}
}
