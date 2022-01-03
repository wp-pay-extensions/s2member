<?php

namespace Pronamic\WordPress\Pay\Extensions\S2Member;

/**
 * Title: s2Member
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   1.0.0
 */
class S2Member {
	/**
	 * Get periods
	 *
	 * @since 1.2.0
	 *
	 * @return array
	 */
	public static function get_periods() {
		// Periods
		$periods = array(
			'1 D'    => __( '1 day', 'pronamic_ideal' ),
			'2 D'    => __( '2 day', 'pronamic_ideal' ),
			'3 D'    => __( '3 day', 'pronamic_ideal' ),
			'4 D'    => __( '4 day', 'pronamic_ideal' ),
			'5 D'    => __( '5 day', 'pronamic_ideal' ),
			'6 D'    => __( '6 day', 'pronamic_ideal' ),
			'1 W'    => __( '1 week', 'pronamic_ideal' ),
			'2 W'    => __( '2 week', 'pronamic_ideal' ),
			'3 W'    => __( '3 week', 'pronamic_ideal' ),
			'1 M'    => __( '1 month', 'pronamic_ideal' ),
			'2 M'    => __( '2 month', 'pronamic_ideal' ),
			'3 M'    => __( '3 month', 'pronamic_ideal' ),
			'4 M'    => __( '4 month', 'pronamic_ideal' ),
			'5 M'    => __( '5 month', 'pronamic_ideal' ),
			'6 M'    => __( '6 month', 'pronamic_ideal' ),
			'1 Y'    => __( '1 year', 'pronamic_ideal' ),
			'2 Y'    => __( '2 years', 'pronamic_ideal' ),
			'3 Y'    => __( '3 years', 'pronamic_ideal' ),
			'4 Y'    => __( '4 years', 'pronamic_ideal' ),
			'5 Y'    => __( '5 years', 'pronamic_ideal' ),
			'1 L'    => __( 'lifetime', 'pronamic_ideal' ),
			'R1 D'   => __( '1 day', 'pronamic_ideal' ),
			'R2 D'   => __( '2 day', 'pronamic_ideal' ),
			'R3 D'   => __( '3 day', 'pronamic_ideal' ),
			'R4 D'   => __( '4 day', 'pronamic_ideal' ),
			'R5 D'   => __( '5 day', 'pronamic_ideal' ),
			'R6 D'   => __( '6 day', 'pronamic_ideal' ),
			'R1 W'   => __( '1 week', 'pronamic_ideal' ),
			'R2 W'   => __( '2 week', 'pronamic_ideal' ),
			'R3 W'   => __( '3 week', 'pronamic_ideal' ),
			'R1 M'   => __( '1 month', 'pronamic_ideal' ),
			'R2 M'   => __( '2 month', 'pronamic_ideal' ),
			'R3 M'   => __( '3 month', 'pronamic_ideal' ),
			'R4 M'   => __( '4 month', 'pronamic_ideal' ),
			'R5 M'   => __( '5 month', 'pronamic_ideal' ),
			'R6 M'   => __( '6 month', 'pronamic_ideal' ),
			'R365 D' => __( '1 year', 'pronamic_ideal' ),
		);

		$periods = apply_filters( 'pronamic_ideal_s2member_default_periods', $periods );

		return $periods;
	}
}
