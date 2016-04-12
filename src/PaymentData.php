<?php

/**
 * Title: s2Member payment data
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.2.3
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_S2Member_PaymentData extends Pronamic_WP_Pay_PaymentData {
	public $data;

	//////////////////////////////////////////////////

	/**
	 * Constructs and intialize an s2Member payment data object
	 *
	 * @param array $data
	 */
	public function __construct( $data ) {
		parent::__construct();

		$this->data = $data;
	}

	public function get_payment_method() {
		$payment_method = null;

		if ( isset( $this->data['payment_method'] ) ) {
			$payment_method = $this->data['payment_method'];
		}

		return $payment_method;
	}

	//////////////////////////////////////////////////
	// s2Member specific data
	//////////////////////////////////////////////////

	public function get_period() {
		return $this->data['period'];
	}

	public function get_level() {
		return $this->data['level'];
	}

	public function get_ccaps() {
		return $this->data['ccaps'];
	}

	//////////////////////////////////////////////////

	public function get_order_id() {
		return $this->data['order_id'];
	}

	public function get_description() {
		$search = array(
			'{{order_id}}'
		);

		$replace = array(
			$this->get_order_id()
		);

		return str_replace( $search, $replace, $this->data['description'] );
	}

	public function get_items() {
		$items = new Pronamic_IDeal_Items();

		$item = new Pronamic_IDeal_Item();
		$item->setNumber( $this->get_order_id() );
		$item->setDescription( $this->get_description() );
		$item->setPrice( $this->data['cost'] );
		$item->setQuantity( 1 );

		$items->addItem( $item );

		return $items;
	}

	public function get_source() {
		return 's2member';
	}

	public function get_source_id() {
		return $this->data['order_id'];
	}

	//////////////////////////////////////////////////
	// Currency
	//////////////////////////////////////////////////

	/**
	 * Get currency
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_currency_alphabetic_code()
	 * @return string
	 */
	public function get_currency_alphabetic_code() {
		return 'EUR';
	}

	//////////////////////////////////////////////////
	// Customer
	//////////////////////////////////////////////////

	public function get_email() {
		$email = parent::get_email();

		if ( filter_has_var( INPUT_POST, 'pronamic_pay_s2member_email' ) ) {
			$email = filter_input( INPUT_POST, 'pronamic_pay_s2member_email', FILTER_VALIDATE_EMAIL );
		}

		return $email;
	}

	public function get_customer_name() {
		return parent::get_customer_name();
	}

	public function get_address() {
		return '';
	}

	public function get_city() {
		return '';
	}

	public function get_zip() {
		return '';
	}
}
