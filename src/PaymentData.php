<?php

/**
 * Title: s2Member payment data
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.2.7
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

		$this->data         = $data;
		$this->recurring    = false;
		$this->subscription = false;

		$user_subscription_id = get_user_option( 's2member_subscr_id', $this->get_user_id() );

		if ( '' !== $user_subscription_id ) {
			$this->subscription = new Pronamic_WP_Pay_Subscription( $user_subscription_id );
		}

		if ( ! empty( $data['subscription_id'] ) ) {
			$this->subscription = new Pronamic_WP_Pay_Subscription( $data['subscription_id'] );

			if ( $this->subscription ) {
				$this->recurring = true;
			}
		}
	}

	public function get_payment_method() {
		return $this->data['payment_method'];
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
			'{{order_id}}',
		);

		$replace = array(
			$this->get_order_id(),
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
		if ( $this->recurring && $this->subscription ) {
			$first = $this->subscription->get_first_payment();

			return $first->get_source_id();
		}

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
		$customer_name = parent::get_customer_name();

		if ( 'Y' === $this->data['recurring'] ) {
			$customer_name = $this->get_email();
		}

		return $customer_name;
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

	//////////////////////////////////////////////////
	// Subscription
	//////////////////////////////////////////////////

	/**
	 * Get subscription.
	 *
	 * @return string|bool
	 */
	public function get_subscription() {
		if ( 'Y' !== $this->data['recurring'] ) {
			return false;
		}

		// Interval
		$period = $this->get_period();

		list( $interval, $interval_period ) = explode( ' ', $period );

		if ( $this->subscription ) {
			$subscription = $this->subscription;
		} else {
			$subscription = new Pronamic_Pay_Subscription();
		}

		$subscription->interval        = $interval;
		$subscription->interval_period = $interval_period;
		$subscription->amount          = $this->get_amount();
		$subscription->currency        = $this->get_currency();
		$subscription->description     = $this->get_description();

		return $subscription;
	}

	public function get_subscription_id() {
		if ( $this->subscription ) {
			return $this->subscription->get_id();
		}

		return intval( $this->data['subscription_id'] );
	}
}
