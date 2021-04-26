<?php
/**
 * S2Member Dependency
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\S2Member
 */

namespace Pronamic\WordPress\Pay\Extensions\S2Member;

use Pronamic\WordPress\Pay\Dependencies\Dependency;

/**
 * S2Member Dependency
 *
 * @author  Reüel van der Steege
 * @version 2.1.0
 * @since   2.1.0
 */
class S2MemberDependency extends Dependency {
	/**
	 * Is met.
	 *
	 * @link https://github.com/WebSharks/s2Member/blob/130816/s2member/s2member.php#L69
	 * @return bool True if dependency is met, false otherwise.
	 */
	public function is_met() {
		return \defined( '\WS_PLUGIN__S2MEMBER_VERSION' );
	}
}
