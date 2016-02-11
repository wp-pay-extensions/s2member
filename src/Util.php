<?php

/**
 * Title: s2Member utility class
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
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
}
