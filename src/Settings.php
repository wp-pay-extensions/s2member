<?php

namespace Pronamic\WordPress\Pay\Extensions\S2Member;

/**
 * Title: s2Member settings
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.1
 * @since   1.0.0
 */
class Settings {
	/**
	 * Constructs and initializes an s2Member settings object
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Admin intialize
	 */
	public function admin_init() {
		// Settings - General
		add_settings_section(
			'pronamic_pay_s2member_general', // id
			__( 'General', 'pronamic_ideal' ), // title
			'__return_false', // callback
			'pronamic_pay_s2member' // page
		);

		// Setting - Config ID
		register_setting( 'pronamic_pay_s2member', 'pronamic_pay_s2member_config_id' );

		add_settings_field(
			'pronamic_pay_s2member_config_id', // id
			__( 'Configuration', 'pronamic_ideal' ), // title
			array( 'Pronamic\WordPress\Pay\Admin\AdminModule', 'dropdown_configs' ), // callback
			'pronamic_pay_s2member', // page
			'pronamic_pay_s2member_general', // section
			array( // args
				'name'      => 'pronamic_pay_s2member_config_id',
				'label_for' => 'pronamic_pay_s2member_config_id',
			)
		);

		// Setting - Signup e-mail message
		register_setting( 'pronamic_pay_s2member', 'pronamic_pay_s2member_signup_email_message' );

		add_settings_field(
			'pronamic_pay_s2member_signup_email_message', // id
			__( 'Signup Confirmation Email Message', 'pronamic_ideal' ), // title
			array( $this, 'wp_editor' ), // callback
			'pronamic_pay_s2member', // page
			'pronamic_pay_s2member_general', // section
			array( // args
				'name'      => 'pronamic_pay_s2member_signup_email_message',
				'label_for' => 'pronamic_pay_s2member_signup_email_message',
			)
		);

		// Setting - Subscription renewal notice email subject
		register_setting( 'pronamic_pay_s2member', 'pronamic_pay_s2member_subscription_renewal_notice_email_subject' );

		add_settings_field(
			'pronamic_pay_s2member_subscription_renewal_notice_email_subject', // id
			__( 'Subscription Renewal Notice Email Subject', 'pronamic_ideal' ), // title
			array( $this, 'text_field' ), // callback
			'pronamic_pay_s2member', // page
			'pronamic_pay_s2member_general', // section
			array( // args
				'name'      => 'pronamic_pay_s2member_subscription_renewal_notice_email_subject',
				'label_for' => 'pronamic_pay_s2member_subscription_renewal_notice_email_subject',
			)
		);

		// Setting - Subscription renewal notice email message
		register_setting( 'pronamic_pay_s2member', 'pronamic_pay_s2member_subscription_renewal_notice_email_message' );

		add_settings_field(
			'pronamic_pay_s2member_subscription_renewal_notice_email_message', // id
			__( 'Subscription Renewal Notice Email Message', 'pronamic_ideal' ), // title
			array( $this, 'wp_editor' ), // callback
			'pronamic_pay_s2member', // page
			'pronamic_pay_s2member_general', // section
			array( // args
				'name'      => 'pronamic_pay_s2member_subscription_renewal_notice_email_message',
				'label_for' => 'pronamic_pay_s2member_subscription_renewal_notice_email_message',
			)
		);
	}

	/**
	 * WordPress editor
	 *
	 * @param $args
	 */
	public function wp_editor( $args ) {
		$content = get_option( $args['name'] );

		wp_editor( $content, $args['name'] );
	}

	/**
	 * Text field.
	 *
	 * @param $args
	 */
	public function text_field( $args ) {
		$value = get_option( $args['name'] );

		printf(
			'<input type="text" name="%s" value="%s" class="regular-text" />',
			esc_attr( $args['name'] ),
			esc_attr( $value )
		);
	}

	/**
	 * Admin menu
	 */
	public function admin_menu() {
		$parent_slug = apply_filters( 'ws_plugin__s2member_during_add_admin_options_menu_slug', 'ws-plugin--s2member-start' );

		if ( apply_filters( 'ws_plugin__s2member_during_add_admin_options_add_divider_6', true, get_defined_vars() ) ) { /* Divider. */
			add_submenu_page( $parent_slug, '', '<span style="display:block; margin:1px 0 1px -5px; padding:0; height:1px; line-height:1px; background:#CCCCCC;"></span>', 'create_users', '#' );
		}

		add_submenu_page(
			$parent_slug,
			__( 'Pronamic Pay Options', 'pronamic_ideal' ),
			__( 'Payment Options', 'pronamic_ideal' ),
			'create_users',
			'pronamic_pay_s2member_settings',
			array( $this, 'page_options' )
		);

		add_submenu_page(
			$parent_slug,
			__( 'Pronamic Pay Buttons Generator', 'pronamic_ideal' ),
			__( 'Payment Buttons', 'pronamic_ideal' ),
			'create_users',
			'pronamic_pay_s2member_buttons',
			array( $this, 'page_buttons_generator' )
		);
	}

	/**
	 * Page view options
	 */
	public function page_options() {
		include dirname( __FILE__ ) . '/../views/html-admin-page-settings.php';
	}

	/**
	 * Page button generator
	 */
	public function page_buttons_generator() {
		include dirname( __FILE__ ) . '/../views/html-admin-page-buttons-generator.php';
	}
}
