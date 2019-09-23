<?php

namespace Pronamic\WordPress\Pay\Extensions\S2Member;

use c_ws_plugin__s2member_list_servers;
use c_ws_plugin__s2member_utils_time;
use Pronamic\WordPress\Pay\Core\Server;
use Pronamic\WordPress\Pay\Core\Statuses;
use Pronamic\WordPress\Pay\Core\Util as Core_Util;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Subscriptions\Subscription;
use WP_User;

/**
 * Title: s2Member extension
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.0
 * @since   1.0.0
 */
class Extension {
	/**
	 * Slug
	 *
	 * @var string
	 */
	const SLUG = 's2member';

	/**
	 * Bootstrap
	 */
	public static function bootstrap() {
		add_action( 'plugins_loaded', array( __CLASS__, 'plugins_loaded' ), 100 );
	}

	/**
	 * Plugins loaded
	 */
	public static function plugins_loaded() {
		if ( ! S2Member::is_active() ) {
			return;
		}

		// Bridge classes.
		new Settings();
		new Shortcodes();

		$slug = 's2member';

		add_action( 'pronamic_payment_status_update_' . $slug, array( __CLASS__, 'update_status' ), 10, 1 );
		add_action( 'pronamic_subscription_renewal_notice_' . self::SLUG, array( __CLASS__, 'subscription_renewal_notice' ) );

		add_action( 'pronamic_payment_status_update_' . $slug, array( __CLASS__, 'status_update' ), 10, 2 );
		add_filter( 'pronamic_payment_source_text_' . $slug, array( __CLASS__, 'source_text' ), 10, 2 );
		add_filter( 'pronamic_payment_source_description_' . $slug, array( __CLASS__, 'source_description' ), 10, 2 );

		$option_name = 'pronamic_pay_s2member_signup_email_message';
		add_filter( 'default_option_' . $option_name, array( __CLASS__, 'default_option_s2member_signup_email_message' ) );

		$option_name = 'pronamic_pay_s2member_subscription_renewal_notice_email_subject';
		add_filter( 'default_option_' . $option_name, array( __CLASS__, 'default_option_s2member_subscription_renewal_notice_email_subject' ) );

		$option_name = 'pronamic_pay_s2member_subscription_renewal_notice_email_message';
		add_filter( 'default_option_' . $option_name, array( __CLASS__, 'default_option_s2member_subscription_renewal_notice_email_message' ) );
	}

	/**
	 * Default option s2Member signup email message
	 *
	 * @param string $default Default.
	 */
	public static function default_option_s2member_signup_email_message( $default ) {
		$default = sprintf(
			/* translators: 1: %%email%%, 2: %%password%%, 3: blog name */
			__(
				'Thanks %1$s! Your membership has been approved.

Your password is %2$s. Please change your password when you login.

If you have any trouble, please feel free to contact us.

Best Regards,
%3$s',
				'pronamic_ideal'
			),
			'%%email%%',
			'%%password%%',
			get_bloginfo( 'name' )
		);

		return $default;
	}

	/**
	 * Default option s2Member subscription renewal notice email subject.
	 *
	 * @param string $default Default.
	 */
	public static function default_option_s2member_subscription_renewal_notice_email_subject( $default ) {
		return __( 'Subscription Renewal Notice', 'pronamic_ideal' ) . ' | ' . get_bloginfo( 'name' );
	}

	/**
	 * Default option s2Member subscription renewal notice email message.
	 *
	 * @param string $default Default.
	 */
	public static function default_option_s2member_subscription_renewal_notice_email_message( $default ) {
		return sprintf(
			/* translators: 1: %%email%%, 2: %%subscription_renewal_date%%, 3: %%subscription_cancel_url%%, 4: blog name */
			__(
				'Dear %1$s,

Your membership is due for renewal on %2$s.

To cancel your subscription, visit %3$s

Best Regards,
%4$s',
				'pronamic_ideal'
			),
			'%%email%%',
			'%%subscription_renewal_date%%',
			'%%subscription_cancel_url%%',
			get_bloginfo( 'name' )
		);
	}

	/**
	 * Update status.
	 *
	 * @param Payment $payment Payment.
	 */
	public static function update_status( Payment $payment ) {
		if ( Statuses::SUCCESS !== $payment->get_status() ) {
			return;
		}

		$payment_data = Util::get_payment_data( $payment );

		$data = new PaymentData( $payment_data );

		$email = $payment->get_email();

		// Get account from email address.
		$user = get_user_by( 'email', $email );

		if ( ! $user && $payment->get_recurring() ) {
			// Invalid user for recurring payment, abort to prevent account creation.
			return;
		}

		$random_string = '';

		// No valid user?
		if ( ! $user ) {
			// Make a random string for password.
			$random_string = wp_generate_password( 10 );

			// Make a user with the username as the email.
			$user_id = wp_create_user( $email, $random_string, $email );

			// Subject.
			$subject = __( 'Account Confirmation', 'pronamic_ideal' ) . ' | ' . get_bloginfo( 'name' );

			// Message.
			$message = get_option( 'pronamic_pay_s2member_signup_email_message' );

			$message = str_replace(
				array(
					'%%email%%',
					'%%password%%',
				),
				array(
					$email,
					$random_string,
				),
				$message
			);

			// Mail.
			wp_mail( $email, $subject, $message );

			$user = new WP_User( $user_id );

			// Update subscription post author.
			if ( $payment->get_subscription_id() ) {
				$arg = array(
					'ID'          => $payment->get_subscription_id(),
					'post_author' => $user->ID,
				);

				wp_update_post( $arg );
			}
		}

		// Set s2Member subscription ID.
		update_user_option( $user->ID, 's2member_subscr_gateway', $payment->get_method() );
		update_user_option( $user->ID, 's2member_subscr_id', $payment->get_subscription_id() );

		$level  = $data->get_level();
		$period = $data->get_period();
		$ccaps  = $data->get_ccaps();

		$capability = 'access_s2member_level' . $level;
		$role       = 's2member_level' . $level;

		// Update user role.
		$user->set_role( $role );

		$note = sprintf(
			/* translators: 1: email, 2: role, 3: capability */
			__( 'Update user "%1$s" to role "%2$s" and added custom capability "%3$s".', 'pronamic_ideal' ),
			$email,
			$role,
			$capability
		);

		$payment->add_note( $note );

		// Custom Capabilities.
		if ( ! empty( $ccaps ) ) {
			$ccaps = Util::ccap_string_to_array( $ccaps );

			Util::ccap_user_update( $user, $ccaps );
		}

		// Registration times.
		$registration_time = time();

		$registration_times = get_user_option( 's2member_paid_registration_times', $user->ID );
		if ( empty( $registration_times ) ) {
			$registration_times = array();
		}

		$registration_times[ 'level' . $level ] = $registration_time;

		update_user_option( $user->ID, 's2member_paid_registration_times', $registration_times );

		if ( in_array( $period, array( '1 L' ), true ) ) {
			// Lifetime, delete end of time option.
			delete_user_option( $user->ID, 's2member_auto_eot_time' );
		} else {
			/*
			 * Auto end of time.
			 * @link https://github.com/WebSharks/s2Member/blob/131126/s2member/includes/classes/utils-time.inc.php#L100
			 */
			$eot_time_current = get_user_option( 's2member_auto_eot_time', $user->ID );

			if ( ! is_numeric( $eot_time_current ) ) {
				$eot_time_current = time();
			}

			if ( $payment->get_recurring() ) {
				add_filter( 'ws_plugin__s2member_eot_grace_time', '__return_zero' );

				// Calculate EOT time for period from today.
				$eot_time_new = c_ws_plugin__s2member_utils_time::auto_eot_time( 0, '', '', $period, 0, $eot_time_current );

				remove_filter( 'ws_plugin__s2member_eot_grace_time', '__return_zero' );
			} else {
				$eot_time_new = c_ws_plugin__s2member_utils_time::auto_eot_time( $user->ID, '', $period, false, $eot_time_current );
			}

			update_user_option( $user->ID, 's2member_auto_eot_time', $eot_time_new );
		}

		// Subscribe with list servers.
		if ( Core_Util::class_method_exists( 'c_ws_plugin__s2member_list_servers', 'process_list_servers' ) ) {
			// IP address.
			$ip = Server::get( 'REMOTE_ADDR' );

			$customer = $payment->customer;

			if ( null !== $customer ) {
				$ip = $customer->get_ip_address();
			}

			// Name.
			$first_name = $user->first_name;
			$last_name  = $user->last_name;

			// Opt in?
			$opt_in = 1 === \intval( get_post_meta( $payment->get_id(), '_pronamic_payment_s2member_opt_in', true ) );

			c_ws_plugin__s2member_list_servers::process_list_servers( $role, $level, $email, $random_string, $email, $first_name, $last_name, $ip, $opt_in, true, $user->ID );
		}
	}

	/**
	 * Status update.
	 *
	 * @param Payment $payment      Payment.
	 * @param bool    $can_redirect Can redirect.
	 */
	public static function status_update( Payment $payment, $can_redirect = false ) {
		$payment_data = Util::get_payment_data( $payment );

		$data = new PaymentData( $payment_data );

		$url = $data->get_normal_return_url();

		// Get account by email.
		$user = get_user_by( 'email', $payment->get_email() );

		switch ( $payment->status ) {
			case Statuses::CANCELLED:
				$url = $data->get_cancel_url();

				if ( $payment->get_recurring() ) {
					Util::auto_eot_now_user_update( $user );
				}

				break;
			case Statuses::EXPIRED:
				$url = $data->get_error_url();

				if ( $payment->get_recurring() ) {
					Util::auto_eot_now_user_update( $user );
				}

				break;
			case Statuses::FAILURE:
				$url = $data->get_error_url();

				if ( $payment->get_recurring() ) {
					Util::auto_eot_now_user_update( $user );
				}

				break;
			case Statuses::SUCCESS:
				$url = $data->get_success_url();

				break;
			case Statuses::OPEN:
				$url = $data->get_normal_return_url();

				break;
		}

		if ( $url && $can_redirect ) {
			wp_redirect( $url );

			exit;
		}
	}

	/**
	 * Send subscription renewal notice
	 *
	 * @param Subscription $subscription Subscription.
	 */
	public static function subscription_renewal_notice( Subscription $subscription ) {
		// Email address.
		$email = $subscription->get_meta( 'email' );

		// Subject.
		$subject = get_option( 'pronamic_pay_s2member_subscription_renewal_notice_email_subject' );

		// Message.
		$message = get_option( 'pronamic_pay_s2member_subscription_renewal_notice_email_message' );

		if ( '' === trim( $message ) ) {
			return;
		}

		// Get renewal date.
		$next_payment_date = $subscription->get_next_payment_date();

		if ( ! $next_payment_date ) {
			return;
		}

		$subscription_renewal_date = date_i18n( get_option( 'date_format' ), $next_payment_date->getTimestamp() );

		$replacements = array(
			'%%email%%'                     => $email,
			'%%amount%%'                    => $subscription->get_total_amount()->format_i18n(),
			'%%subscription_cancel_url%%'   => $subscription->get_cancel_url(),
			'%%subscription_renewal_url%%'  => $subscription->get_renewal_url(),
			'%%subscription_renewal_date%%' => $subscription_renewal_date,
		);

		$subject = strtr( $subject, $replacements );
		$message = strtr( $message, $replacements );

		// Mail.
		wp_mail( $email, $subject, $message );
	}

	/**
	 * Source text.
	 *
	 * @param string  $text    Source text.
	 * @param Payment $payment Payment.
	 *
	 * @return string
	 */
	public static function source_text( $text, Payment $payment ) {
		return __( 's2Member', 'pronamic_ideal' );
	}

	/**
	 * Source description.
	 *
	 * @param string  $description Source description.
	 * @param Payment $payment     Payment.
	 *
	 * @return string
	 */
	public static function source_description( $description, Payment $payment ) {
		return __( 's2Member', 'pronamic_ideal' );
	}
}
