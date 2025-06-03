<?php
if(!class_exists('WDF_Gateway_Manual')) {
	class WDF_Gateway_Manual extends WDF_Gateway {

		// Private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
		var $plugin_name = 'manual';

		// Name of your gateway, for the admin side.
		var $admin_name = '';

		// Public name of your gateway, for lists and such.
		var $public_name = '';

		// Whether or not ssl is needed for checkout page
		var $force_ssl = false;

		// An array of allowed payment types (simple, advanced)
		var $payment_types = 'simple';

		// If you are redirecting to a 3rd party make sure this is set to true
		var $skip_form = false;

		// Allow recurring payments with your gateway
		var $allow_reccuring = false;

		function on_creation() {
			$this->public_name = $this->admin_name = __('Überweisung','wdf');
		}

		function payment_form() {
			$content = '<div class="wdf_manual_payment_form wdf_payment_form">';

			$content .= '<p class="wdf_manual_payment_form_basic_message wdf_payment_form_basic_message">'.__('Bitte fülle alle Details aus um den Vorgang abzuschließen','wdf').'</p>';

			$content .= '<p class="wdf_manual_payment_form_basic_info wdf_payment_form_basic_info">';
				$content .= '<label for="first_name class="wdf_first_name">'.__('Dein Vorname','wdf').':</label><br />';
				$content .= '<input type="text" class="wdf_first_name" name="first_name" value="'.( isset($_POST['first_name']) ? esc_attr($_POST['first_name']) : '' ).'" /><br />';
				$content .= '<label for="last_name class="wdf_last_name">'.__('Dein Familienname','wdf').':</label><br />';
				$content .= '<input type="text" class="wdf_last_name" name="last_name" value="'.( isset($_POST['last_name']) ? esc_attr($_POST['last_name']) : '' ).'" /><br />';
				$content .= '<label for="e-mail" class="wdf_email">'.__('Deine E-mail Adresse','wdf').':</label><br />';
				$content .= '<input type="text" class="wdf_email" name="e-mail" value="'.( isset($_POST['e-mail']) ? esc_attr($_POST['e-mail']) : '') .'" />';
			$content .= '</p>';

			$funder_id = get_the_ID();
			if(get_post_meta($funder_id,'wdf_collect_address', true)) {
				$collect_address_message = get_post_meta($funder_id,'wdf_collect_address_message', true);
				if($collect_address_message)
					$content .= '<p class="wdf_manual_payment_form_address_message wdf_payment_form_address_message">'.$collect_address_message.'</p>';

				$content .= '<p class="wdf_manual_payment_form_address_info wdf_payment_form_address_info">';
					$collect_address_country = get_post_meta($funder_id,'wdf_collect_address_country', true);
					if($collect_address_country){
						$content .= '<label for="country" class="wdf_country">'.__('Land','wdf').':</label><br />';
						$content .= '<input type="text" class="wdf_country" name="country" value="'.( isset($_POST['country']) ? esc_attr($_POST['country']) : '') .'" /><br />';
					}
					$content .= '<label for="address1" class="wdf_address1">'.__('Addresse','wdf').' <small>'.__('(Straße, Postfach, Firmenname, c/o)','wdf').'</small>:</label><br />';
					$content .= '<input type="text" class="wdf_address1" name="address1" value="'.( isset($_POST['address1']) ? esc_attr($_POST['address1']) : '') .'" /><br />';
					$content .= '<label for="address2" class="wdf_address2">'.__('Addresse 2','wdf').' <small>'.__('(Wohnung, Suite, Einheit, Gebäude, Etage usw.)','wdf').'</small>:</label><br />';
					$content .= '<input type="text" class="wdf_address2" name="address2" value="'.( isset($_POST['address2']) ? esc_attr($_POST['address2']) : '') .'" /><br />';
					$content .= '<label for="city" class="wdf_city">'.__('Stadt','wdf').':</label><br />';
					$content .= '<input type="text" class="wdf_city" name="city" value="'.( isset($_POST['city']) ? esc_attr($_POST['city']) : '') .'" /><br />';
					$content .= '<label for="state" class="wdf_state">'.__('Bundesland','wdf').':</label><br />';
					$content .= '<input type="text" class="wdf_state" name="state" value="'.( isset($_POST['state']) ? esc_attr($_POST['state']) : '') .'" /><br />';
					$content .= '<label for="zip" class="wdf_zip">'.__('Postleitzahl','wdf').':</label><br />';
					$content .= '<input type="text" class="wdf_zip" name="zip" value="'.( isset($_POST['zip']) ? esc_attr($_POST['zip']) : '') .'" />';
				$content .= '</p>';
			}

			$content .= '</div>';
			return $content;
		}

		function process_simple() {
			if( !empty($_POST['first_name']) && !empty($_POST['last_name']) &&
				!empty($_POST['e-mail']) && preg_match("/^[-+\\.0-9=a-z_]+@([-0-9a-z]+\\.)+([0-9a-z]){2,4}$/i", $_POST['e-mail']) &&
				(isset($_POST['city']) && !empty($_POST['address1']) && !empty($_POST['city'])) || !isset($_POST['city']) &&
				(isset($_POST['country']) && !empty($_POST['country'])) || !isset($_POST['country'])
			) {
				global $wdf;
				$settings = get_option('wdf_settings');
				$funder_id = $_SESSION['funder_id'];

				if($funder = get_post($funder_id) ){
					$pledge_id = $wdf->generate_pledge_id();
					$this->return_url =  add_query_arg('pledge_id', $pledge_id, wdf_get_funder_page('confirmation',$funder->ID));

					$_SESSION['wdf_pledge_id'] = $pledge_id;

					$settings = get_option('wdf_settings');

					$transaction = array();

					$transaction['gross'] = $_SESSION['wdf_pledge'];
					$transaction['type'] = 'simple';
					$transaction['currency_code'] = ( isset($settings['currency']) ? $settings['currency'] : 'USD');
					$transaction['first_name'] = (isset($_POST['first_name']) ? $_POST['first_name'] : '' );
					$transaction['last_name'] = (isset($_POST['last_name']) ? $_POST['last_name'] : '' );
					$transaction['payer_email'] = (isset($_POST['e-mail']) ? $_POST['e-mail'] : '' );
					$transaction['gateway_public'] = $this->public_name;
					$transaction['gateway'] = $this->plugin_name;
					$status = (isset($settings['manual']['status']) ? $settings['manual']['status'] : 'wdf_complete' );
					$transaction['status'] = __('Ausstehend/Genehmigt','wdf');
					$transaction['gateway_msg'] = __('Manuelle Zahlung.','wdf');

					if(isset($_SESSION['wdf_reward']))
						$transaction['reward'] = $_SESSION['wdf_reward'];

					$collect_address = get_post_meta($funder_id,'wdf_collect_address', true);
					if($collect_address) {
						$transaction['country'] = (isset($_POST['country']) ? $_POST['country'] : '' );
						$transaction['address1'] = (isset($_POST['address1']) ? $_POST['address1'] : '' );
						$transaction['address2'] = (isset($_POST['address2']) ? $_POST['address2'] : '' );
						$transaction['city'] = (isset($_POST['city']) ? $_POST['city'] : '' );
						$transaction['state'] = (isset($_POST['state']) ? $_POST['state'] : '' );
						$transaction['zip'] = (isset($_POST['zip']) ? $_POST['zip'] : '' );
					}


					$wdf->update_pledge( $pledge_id, $funder_id, $status, $transaction);

					if(!headers_sent()) {
						wp_redirect($this->return_url);
						exit;
					}

				} else {
					$_POST['wdf_step'] = 'gateway';
					//No $_SESSION['funder_id'] was passed to this function.
					$this->create_gateway_error(__('Spendenaktion konnte nicht ermittelt werden','wdf'));
				}
			} else {
				$_POST['wdf_step'] = 'gateway';
				$this->create_gateway_error(__('Stelle sicher, dass alle Details korrekt ausgefüllt sind.','wdf'));
			}
		}
		function process_advanced() {
			$this->process_simple();
		}
		function confirm() {
		}
		function payment_info( $content, $transaction ) {
			$settings = get_option('wdf_settings');

			$content = '<div class="manual_transaction_info">';
			$content .= html_entity_decode(stripslashes($settings['manual']['after_info']));
			$content .= '</div>';
			return $content;
		}
		function handle_ipn() {
		}
		function execute_payment($type, $pledge, $transaction) {
		}

		function admin_settings() {
			$settings = get_option('wdf_settings');
		?>
			<table class="form-table">
				<tbody>
				<tr valign="top" >
					<th scope="row">
						<label for="wdf_settings[manual][status]"><?php echo __('Standardstatus für Zahlungen','wdf'); ?></label>
					</th>
					<td><select name="wdf_settings[manual][status]">
							<option value="wdf_complete" <?php ( isset($settings['manual']['status']) ? selected($settings['manual']['status'],'wdf_complete') : '' ); ?>><?php _e('Komplett','wdf'); ?></option>
							<option value="wdf_approved" <?php ( isset($settings['manual']['status']) ?  selected($settings['manual']['status'],'wdf_approved') : '' ); ?>><?php _e('Genehmigt','wdf'); ?></option>
							<option value="wdf_canceled" <?php ( isset($settings['manual']['status']) ?  selected($settings['manual']['status'],'wdf_canceled') : '' ); ?>><?php _e('Storniert','wdf'); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top" id="wdf_settings_manual_after_info">
					<th scope="row">
						<label for="wdf_settings[manual][after_info]"><?php echo __('Informationen, die nach der Unterstützung angezeigt werden (Du kannst HTML verwenden)','wdf'); ?></label>
					</th>
					<td>
						<textarea name="wdf_settings[manual][after_info]" class="widefat" rows="5"><?php echo ( isset($settings['manual']['after_info']) ?  $settings['manual']['after_info'] : '' ); ?></textarea>
					</td>
				</tr>
				</tbody>
			</table>
		<?php
		}
		function save_gateway_settings() {

			if( isset($_POST['wdf_settings']['manual']) ) {
				$new['manual']['after_info'] = htmlentities($_POST['wdf_settings']['manual']['after_info']);
				$statuses = array('wdf_complete', 'wdf_approved', 'wdf_canceled');
				if(in_array($_POST['wdf_settings']['manual']['status'], $statuses))
					$new['manual']['status'] = $_POST['wdf_settings']['manual']['status'];
				else
					$new['manual']['status'] = 'wdf_complete';

				$settings = get_option('wdf_settings');
				$settings = array_merge($settings,$new);
				update_option('wdf_settings',$settings);
			}
		}

	}
wdf_register_gateway_plugin('WDF_Gateway_Manual', 'manual', 'Überweisung', array('simple','standard','advanced'));
}
?>
