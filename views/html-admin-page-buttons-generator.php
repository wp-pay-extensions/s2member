<?php

use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Extensions\S2Member\S2Member;

?>
<div class="wrap">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<div class="pronamic_ideal_shortcode_generator">
		<script type="text/javascript">
			jQuery( function() {
				var cost = jQuery( '.jPronamicIdealCost' ),
					period = jQuery( '.jPronamicIdealPeriodShortcode' ),
					level = jQuery( '.jPronamicIdealLevelShortcode' ),
					description = jQuery( '.jPronamicIdealDescriptionShortcode' ),
					button_text = jQuery( '.jPronamicIdealButtonTextShortcode' ),
					payment_method = jQuery( '.jPronamicIdealPaymentMethodShortcode' ),
					generate_button = jQuery( '.jPronamicIdealGenerateShortcode' ),
					output = jQuery( '.jPronamicIdealButtonShortcodeOutput' );

				jQuery( '.pronamic_ideal_shortcode_generator' ).on( 'keyup change', 'input, select', function() {
					var shortcode = '';

					shortcode += '[pronamic_ideal_s2member';

					if ( cost.val().length > 0 )
						shortcode += ' cost="' + cost.val() + '"';

					if ( period.val().length > 0 ) {
						if ( 'R' === period.val().substr( 0, 1 ) ) {
							shortcode += ' period="' + period.val().substr( 1 ) + '" recurring="Y"';
						} else {
							shortcode += ' period="' + period.val() + '"';
						}
					}

					if ( level.val().length > 0 )
						shortcode += ' level="' + level.val() + '"';

					if ( description.val().length > 0 )
						shortcode += ' description="' + description.val() + ' {{order_id}}"';

					if ( button_text.val().length > 0 )
						shortcode += ' button_text="' + button_text.val() + '"';

					if ( payment_method.val().length > 0 )
						shortcode += ' payment_method="' + payment_method.val() + '"';

					shortcode += ']';

					output.val( shortcode );
				});
			});
		</script>

		<table class="form-table">
			<tbody>
				<tr>
					<th><?php esc_html_e( 'Generator', 'pronamic_ideal' ); ?></th>
					<td>
						<p>
							<?php

							$input = '<input type="text" autocomplete="off" size="6" class="jPronamicIdealCost" />';

							$select  = '';
							$select .= '<select class="jPronamicIdealPeriodShortcode">';

							$prev_recurring = null;

							foreach ( S2Member::get_periods() as $key => $period ) {
								$is_recurring = ( 'R' === substr( $key, 0, 1 ) );

								if ( $is_recurring !== $prev_recurring ) {
									if ( null !== $prev_recurring ) {
										$select .= '</optgroup>';
									}

									$label = __( 'Single payment', 'pronamic_ideal' );

									if ( $is_recurring ) {
										$label = __( 'Recurring payment', 'pronamic_ideal' );
									}

									$select .= sprintf( '<optgroup label="%s">', $label );
								}

								$select .= sprintf( '<option value="%s">%s</option>', $key, $period );

								$prev_recurring = $is_recurring;
							}

							$select .= '</optgroup>';

							$select .= '</select>';

							/* translators: 1: amount input, 2: period select */
							printf( __( 'I want to charge %1$s for %2$s', 'pronamic_ideal' ), $input, $select ); // WPCS: xss OK

							?>
							<?php

							$select  = '';
							$select .= '<select class="jPronamicIdealLevelShortcode">';
							for ( $level = 1; $level <= 4; $level++ ) {
								$select .= sprintf( '<option value="%s">%s</option>', esc_attr( $level ), esc_html( $level ) );
							}
							$select .= '</select>';

							/* translators: %s: level select */
							printf( __( 'access to level %s content.', 'pronamic_ideal' ), $select ); // WPCS: xss OK

							?>
						</p>
						<p>
							<?php esc_html_e( 'Description:', 'pronamic_ideal' ); ?>
							<input type="text" size="70" class="jPronamicIdealDescriptionShortcode" />
						</p>
						<p>
							<?php esc_html_e( 'Button text:', 'pronamic_ideal' ); ?>
							<input type="text" size="50" class="jPronamicIdealButtonTextShortcode" />
							<?php

							/* translators: %s: Pay */
							printf( __( 'Default: <code>%s</code>.', 'pronamic_ideal' ), __( 'Pay', 'pronamic_ideal' ) ); // WPCS: xss OK

							?>
						</p>
						<p>
							<?php esc_html_e( 'Payment Method', 'pronamic_ideal' ); ?>:
							<select class="jPronamicIdealPaymentMethodShortcode">
								<option value=""><?php echo esc_html_x( 'All available methods', 'Payment method field', 'pronamic_ideal' ); ?></option>
								<?php

								$methods = PaymentMethods::get_payment_methods();

								foreach ( $methods as $method => $name ) {
									printf(
										'<option value="%s">%s</option>',
										esc_attr( $method ),
										esc_html( $name )
									);
								}

								?>
							</select>

						</p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Shortcode', 'pronamic_ideal' ); ?></th>
					<td>
						<textarea class="jPronamicIdealButtonShortcodeOutput" style="width: 100%; min-height: 30px;"></textarea>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
