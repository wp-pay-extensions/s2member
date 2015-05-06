<?php

/**
 * Title: s2Member order
 * Description:
 * Copyright: Copyright (c) 2005 - 2015
 * Company: Pronamic
 * @author Leon Rowland
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_S2Member_Order {
	public static $periods = array();

	public function __construct() {
		$label = __( 'One Time ( for %s access, non-recurring )', 'pronamic_ideal' );

		// Periods
		$periods = array(
			'1 D' => sprintf( $label, __( '1 day', 'pronamic_ideal' ) ),
			'2 D' => sprintf( $label, __( '2 day', 'pronamic_ideal' ) ),
			'3 D' => sprintf( $label, __( '3 day', 'pronamic_ideal' ) ),
			'4 D' => sprintf( $label, __( '4 day', 'pronamic_ideal' ) ),
			'5 D' => sprintf( $label, __( '5 day', 'pronamic_ideal' ) ),
			'6 D' => sprintf( $label, __( '6 day', 'pronamic_ideal' ) ),
			'1 W' => sprintf( $label, __( '1 week', 'pronamic_ideal' ) ),
			'2 W' => sprintf( $label, __( '2 week', 'pronamic_ideal' ) ),
			'3 W' => sprintf( $label, __( '3 week', 'pronamic_ideal' ) ),
			'1 M' => sprintf( $label, __( '1 month', 'pronamic_ideal' ) ),
			'2 M' => sprintf( $label, __( '2 month', 'pronamic_ideal' ) ),
			'3 M' => sprintf( $label, __( '3 month', 'pronamic_ideal' ) ),
			'4 M' => sprintf( $label, __( '4 month', 'pronamic_ideal' ) ),
			'5 M' => sprintf( $label, __( '5 month', 'pronamic_ideal' ) ),
			'6 M' => sprintf( $label, __( '6 month', 'pronamic_ideal' ) ),
			'1 Y' => sprintf( $label, __( '1 year', 'pronamic_ideal' ) ),
			'2 Y' => sprintf( $label, __( '1 year', 'pronamic_ideal' ) ),
			'3 Y' => sprintf( $label, __( '1 year', 'pronamic_ideal' ) ),
			'4 Y' => sprintf( $label, __( '1 year', 'pronamic_ideal' ) ),
			'5 Y' => sprintf( $label, __( '1 year', 'pronamic_ideal' ) ),
			'1 L' => sprintf( $label, __( 'lifetime', 'pronamic_ideal' ) ),
		);

		self::$periods = apply_filters( 'pronamic_ideal_s2member_default_periods', $periods );
	}
}
