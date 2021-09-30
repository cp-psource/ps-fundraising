<?php
if(!class_exists('WDF_Gateway_PayPal')) {
	class WDF_Gateway_PayPal extends WDF_Gateway {

		// Private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
		var $plugin_name = 'paypal';

		// Name of your gateway, for the admin side.
		var $admin_name = '';

		// Public name of your gateway, for lists and such.
		var $public_name = '';

		// Whether or not ssl is needed for checkout page
		var $force_ssl = false;

		// An array of allowed payment types (simple, advanced)
		var $payment_types = 'simple, advanced';

		// If you are redirecting to a 3rd party make sure this is set to true
		var $skip_form = true;

		// Allow recurring payments with your gateway
		var $allow_reccuring = true;

		function skip_form() {
			if(isset($_SESSION['funder_id'])) {
				$collect_address = get_post_meta($_SESSION['funder_id'],'wdf_collect_address', true);
				if($collect_address)
					return false;
			}

			return $this->skip_form;
		}

		function on_creation() {
			$this->public_name = $this->admin_name = __('PayPal','wdf');

			if(isset($_SESSION['funder_id'])) {
				$collect_address = get_post_meta($_SESSION['funder_id'],'wdf_collect_address', true);
				if($collect_address)
					$this->skip_form = false;
			}

			$settings = get_option('wdf_settings');

			$this->query = array();

			$this->API_Username = (isset($settings['paypal']['advanced']['api_user']) ? $settings['paypal']['advanced']['api_user'] : '');
			$this->API_Password = (isset($settings['paypal']['advanced']['api_pass']) ? $settings['paypal']['advanced']['api_pass'] : '');
			$this->API_Signature = (isset($settings['paypal']['advanced']['api_sig']) ? $settings['paypal']['advanced']['api_sig'] : '');
			if (isset($settings['paypal_sb']) && $settings['paypal_sb'] == 'yes')	{
				$this->Standard_Endpoint = "https://www.sandbox.paypal.com/webscr?";
				$this->Adaptive_Endpoint = "https://svcs.sandbox.paypal.com/AdaptivePayments/";
				$this->paypalURL = "https://www.sandbox.paypal.com/webscr?cmd=_ap-preapproval&preapprovalkey=";
				// Generic PayPal AppID for Sandbox Testing
				$this->appId = 'APP-80W284485P519543T';
			} else {
				$this->Standard_Endpoint = "https://www.paypal.com/cgi-bin/webscr?";
				$this->Adaptive_Endpoint = "https://svcs.paypal.com/AdaptivePayments/";
				$this->paypalURL = "https://www.paypal.com/webscr?cmd=_ap-preapproval&preapprovalkey=";
				$this->appId = (isset($settings['paypal']['advanced']['app_id']) ? $settings['paypal']['advanced']['app_id'] : '');
			}

		}

		function payment_form() {
			$funder_id = get_the_ID();

			$content = '<div class="wdf_paypal_payment_form wdf_payment_form">';

			$content .= '<p class="wdf_paypal_payment_form_basic_message wdf_payment_form_basic_message">'.__('Bitte fülle alle Details aus','wdf').'</p>';

			$collect_address_message = get_post_meta($funder_id,'wdf_collect_address_message', true);
			if($collect_address_message)
				$content .= '<p class="wdf_paypal_payment_form_address_message wdf_payment_form_address_message">'.$collect_address_message.'</p>';

			$content .= '<p class="wdf_paypal_payment_form_address_info wdf_payment_form_address_info">';
				$collect_address_country = get_post_meta($funder_id,'wdf_collect_address_country', true);
				if($collect_address_country) {
					$content .= '<label for="country" class="wdf_country">'.__('Land','wdf').':</label><br />';
					$content .= '<input type="text" class="wdf_country" name="country" value="'.( isset($_POST['country']) ? esc_attr($_POST['country']) : '') .'" /><br />';
				}
				$content .= '<label for="address1" class="wdf_address1">'.__('Addresse','wdf').' <small>'.__('(Anschrift, Postfach, Firmenname, c/o)','wdf').'</small>:</label><br />';
				$content .= '<input type="text" class="wdf_address1" name="address1" value="'.( isset($_POST['address1']) ? esc_attr($_POST['address1']) : '') .'" /><br />';
				$content .= '<label for="address2" class="wdf_address2">'.__('Addresse 2','wdf').' <small>'.__('(Wohnung, Suite, Einheit, Gebäude, Etage, etc.)','wdf').'</small>:</label><br />';
				$content .= '<input type="text" class="wdf_address2" name="address2" value="'.( isset($_POST['address2']) ? esc_attr($_POST['address2']) : '') .'" /><br />';
				$content .= '<label for="city" class="wdf_city">'.__('Stadt','wdf').':</label><br />';
				$content .= '<input type="text" class="wdf_city" name="city" value="'.( isset($_POST['city']) ? esc_attr($_POST['city']) : '') .'" /><br />';
				$content .= '<label for="state" class="wdf_state">'.__('Bundesland','wdf').':</label><br />';
				$content .= '<input type="text" class="wdf_state" name="state" value="'.( isset($_POST['state']) ? esc_attr($_POST['state']) : '') .'" /><br />';
				$content .= '<label for="zip" class="wdf_zip">'.__('Postleitzahl','wdf').':</label><br />';
				$content .= '<input type="text" class="wdf_zip" name="zip" value="'.( isset($_POST['zip']) ? esc_attr($_POST['zip']) : '') .'" />';
			$content .= '</p>';

			$content .= '</div>';
			return $content;
		}

		function process_simple() {
			if(
				( (isset($_POST['city']) && !empty($_POST['address1']) && !empty($_POST['city'])) || !isset($_POST['city']) ) &&
				( (isset($_POST['country']) && !empty($_POST['country'])) || !isset($_POST['country']) )
			) {
				$settings = get_option('wdf_settings');
				global $wdf;

				if($funder = get_post($_SESSION['funder_id']) ){
					$pledge_id = $wdf->generate_pledge_id();
					$_SESSION['wdf_pledge_id'] = $pledge_id;
					$this->return_url =  add_query_arg('pledge_id', $pledge_id, wdf_get_funder_page('confirmation',$funder->ID));

					$custom = array();
					$custom['funder_id'] = $funder->ID;
					$custom['pledge_id'] = $pledge_id;

					//handle custom
					$custom['reward'] = (isset($_SESSION['wdf_reward'])) ? $_SESSION['wdf_reward'] : '0';
					$custom['country'] = (isset($_POST['country']) ? $_POST['country'] : '0' );
					$custom['address1'] = (isset($_POST['address1']) ? $_POST['address1'] : '0' );
					$custom['address2'] = (isset($_POST['address2']) ? $_POST['address2'] : '0' );
					$custom['city'] = (isset($_POST['city']) ? $_POST['city'] : '0' );
					$custom['state'] = (isset($_POST['state']) ? $_POST['state'] : '0' );
					$custom['zip'] = (isset($_POST['zip']) ? $_POST['zip'] : '0' );

					$custom_ready = array();
					foreach ($custom as $key => $value)
						$custom_ready[$key] = str_replace('||', '', $value);
					$custom = implode('||', $custom_ready);

					if( isset($_SESSION['wdf_recurring']) && $_SESSION['wdf_recurring'] != false ) {
						//$this->add_query('cmd', '_xclick-auto-billing');
						//$this->add_query('&min_amount', 1.00);
						//$this->add_query('&max_amount', $wdf_send_obj->send_amount);
						$nvp = 'cmd=_xclick-subscriptions';
						$nvp .= '&a3='.$_SESSION['wdf_pledge'];
						$nvp .= '&p3=1';
						$nvp .= '&t3='.$_SESSION['wdf_recurring'];
						$nvp .= '&bn=WPMUDonations_Subscribe_WPS_'.$settings['currency'];
						$nvp .= '&src=1';
						$nvp .= '&sra=1';
						$nvp .= '&modify=1';

                        //Make sure that future single payments are not taken as recurring.
                        $_SESSION['wdf_recurring'] = false;
					} else {
						$nvp = 'cmd=_donations';
						$nvp .= '&amount='.urlencode($_SESSION['wdf_pledge']);
						$nvp .= '&cbt='.urlencode( isset($settings['paypal_return_text']) ? $settings['paypal_return_text'] : __('Klicke hier, um Deine Spende abzuschließen', 'wdf') );
						$nvp .= '&bn=WPMUDonations_Donate_WPS_'.$settings['currency'];
					}
					$nvp .= '&no_shipping=1';
					$nvp .= '&business='.urlencode($settings['paypal_email']);
					$nvp .= '&item_name='.urlencode($funder->post_title);
					$nvp .= '&item_number='.apply_filters('wdf_paypal_gateway_standard_item_number',$pledge_id);
					$nvp .= '&custom='.urlencode($custom);
					$nvp .= '&currency_code='.$settings['currency'];
					$nvp .= '&cpp_header_image='.urlencode($settings['paypal_image_url']);
					$nvp .= '&return='.urlencode($this->return_url);
					$nvp .= '&rm=2';
					$nvp .= '&notify_url='.urlencode($this->ipn_url);
					$nvp .= '&charset='.urlencode('utf-8');

					if(!headers_sent()) {
						wp_redirect($this->Standard_Endpoint .$nvp);
						exit;
					}

				} else {
					//No $_SESSION['funder_id'] was passed to this function.
					$this->create_gateway_error(__('Spendenaktion konnte nicht ermittelt werden','wdf'));
				}
			} else {
				$_POST['wdf_step'] = 'gateway';
				$this->create_gateway_error(__('Stelle sicher, dass alle Details korrekt ausgefüllt sind.','wdf'));
			}
		}
		function process_advanced() {
			if(
				( (isset($_POST['city']) && !empty($_POST['address1']) && !empty($_POST['city'])) || !isset($_POST['city']) ) &&
				( (isset($_POST['country']) && !empty($_POST['country'])) || !isset($_POST['country']) )
			) {
				$settings = get_option('wdf_settings');
				global $wdf;
				$funder_id = $_SESSION['funder_id'];
				$pledge_id = $wdf->generate_pledge_id();
				$start_stamp = time();
				$end_stamp =  strtotime(get_post_meta($funder_id, 'wdf_goal_end', true));
				$this->return_url =  add_query_arg('pledge_id', $pledge_id, wdf_get_funder_page('confirmation',$funder_id));

				$custom = array();
				$custom['reward'] = (isset($_SESSION['wdf_reward'])) ? $_SESSION['wdf_reward'] : '0';
				$custom['country'] = (isset($_POST['country']) ? $_POST['country'] : '0' );
				$custom['address1'] = (isset($_POST['address1']) ? $_POST['address1'] : '0' );
				$custom['address2'] = (isset($_POST['address2']) ? $_POST['address2'] : '0' );
				$custom['city'] = (isset($_POST['city']) ? $_POST['city'] : '0' );
				$custom['state'] = (isset($_POST['state']) ? $_POST['state'] : '0' );
				$custom['zip'] = (isset($_POST['zip']) ? $_POST['zip'] : '0' );

				$custom_ready = array();
				foreach ($custom as $key => $value)
					$custom_ready[$key] = str_replace('||', '', $value);
				$custom = implode('||', $custom_ready);

				$this->ipn_url = add_query_arg(array('fundraiser' => $funder_id, 'pledge_id' => $pledge_id, 'custom' => urlencode($custom)), $this->ipn_url);

				$nvpstr = "actionType=Preapproval";
				$nvpstr .= "&returnUrl=" . urlencode($this->return_url);
				$nvpstr .= "&cancelUrl=" . urlencode(get_post_permalink($funder_id));
				$nvpstr .= "&ipnNotificationUrl=" . urlencode($this->ipn_url);
				$nvpstr .= "&currencyCode=" . esc_attr($settings['paypal']['advanced']['currency']);
				$nvpstr .= "&feesPayer=SENDER";
				$nvpstr .= "&maxAmountPerPayment=" . $wdf->filter_price($_SESSION['wdf_pledge']);
				$nvpstr .= "&maxTotalAmountOfAllPayments=" . $wdf->filter_price($_SESSION['wdf_pledge']);
				$nvpstr .= "&displayMaxTotalAmount=true";
				$nvpstr .= "&memo=" . urlencode(__('Wenn das Ziel erreicht ist, wird Dein Konto sofort belastet', 'wdf'));
				$nvpstr .= "&startingDate=".gmdate('Y-m-d\Z',$start_stamp);
				$nvpstr .= "&endingDate=".gmdate('Y-m-d\Z',$end_stamp);
				$nvpstr .= '&charset='.urlencode('utf-8');

				// Make the API Call to receive a token
				$response = $this->adaptive_api_call('Preapproval',$nvpstr);

				if(is_array($response) && isset($response['responseEnvelope_ack'])) {
					switch($response['responseEnvelope_ack']) {
						case 'Success' ;
							$proceed = true;
							break;
						case 'Failure' ;
							$proceed = false;
							$status_code = ( isset($response['error(0)_errorId']) ? $response['error(0)_errorId'] : '' );
							$error_msg = ( isset($response['error(0)_message']) ? $response['error(0)_message'] : '' );
							break;
						case 'Warning' ;
							$proceed = true;
							break;
						case 'SuccessWithWarning' ;
							$proceed = true;
							break;
						case 'FailureWithWarning' ;
							$proceed = false;
							$status_code = ( isset($response['error(0)_errorId']) ? $response['error(0)_errorId'] : '' );
							$error_msg = ( isset($response['error(0)_message']) ? $response['error(0)_message'] : '' );
							break;
						default :
							$proceed = false;
							$status_code = __('Kein Statuscode angegeben','wdf');
							$error_msg = '';
					}
				} else {
					// We most likely return an WP_Error object instead of a valid paypal response.
					$proceed = false;
					$status_code = '';
					$error_msg = __('Beim Kontaktieren der PayPal-Server ist ein Fehler aufgetreten.' ,'wdf');
					if(is_wp_error($response))
						$error_msg .= ' '.$response->get_error_message();
				}

				if( $proceed === true && isset($response['preapprovalKey']) ) {
					$_SESSION['wdf_pledge_id'] = $pledge_id;

					//Set transient data for one day to handle ipn
					//set_transient( 'wdf_'.$this->plugin_name.'_'.$pledge_id.'_'.$_SESSION['wdf_type'], array('pledge_id' => $pledge_id), 60 * 60 * 24 );
					if(!headers_sent()) {
						wp_redirect( $this->paypalURL . $response['preapprovalKey'] );
						exit;
					} else {
						// TODO create error output for headers already sent
					}
				} else {
					$this->create_gateway_error(__('Es gab ein Problem mit der Verbindung zum Paypal-Gateway.  (CODE)'.$status_code.' ' . $error_msg,'wdf'));
				}
			} else {
				$_POST['wdf_step'] = 'gateway';
				$this->create_gateway_error(__('Stelle sicher, dass alle Details korrekt ausgefüllt sind.','wdf'));
			}
		}
		function confirm() {
			//$this->process_payment();
		}
		function payment_info( $content, $transaction ) {
			$content = '<div class="paypal_transaction_info">';

			$content .= '</div>';
			return $content;
		}
		function adaptive_api_call($methodName, $nvpStr) {
			global $wdf;

			//build args
			$args['headers'] = array(
				'X-PAYPAL-SECURITY-USERID' => $this->API_Username,
				'X-PAYPAL-SECURITY-PASSWORD' => $this->API_Password,
				'X-PAYPAL-SECURITY-SIGNATURE' => $this->API_Signature,
				'X-PAYPAL-DEVICE-IPADDRESS' => $_SERVER['REMOTE_ADDR'],
				'X-PAYPAL-REQUEST-DATA-FORMAT' => 'NV',
				'X-PAYPAL-REQUEST-RESPONSE-FORMAT' => 'NV',
				'X-PAYPAL-APPLICATION-ID' => $this->appId
			);
			$args['user-agent'] = "Fundraising/{$wdf->version}: https://n3rds.work/piestingtal_source/ps-fundraising/ | Plugin für adaptive Zahlungen von PayPal/{$wdf->version}";
			$args['body'] = $nvpStr . '&requestEnvelope.errorLanguage=en_US';
			$args['sslverify'] = false;
			$args['timeout'] = 60;
			$args['httpversion'] = '1.1';

			//use built in WP http class to work with most server setups
			$response = wp_remote_post($this->Adaptive_Endpoint . $methodName, $args);

			if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
				$this->create_gateway_error( __('Beim Herstellen einer Verbindung zu PayPal ist ein Problem aufgetreten. Bitte versuche es erneut.', 'wdf'));
				return $response;
			} else {
				//convert NVPResponse to an Associative Array
				$nvpResArray = $this->deformatNVP($response['body']);
				return $nvpResArray;
			}
		}

        function handle_ipn() {
            if( isset($_POST['transaction_type']) && $_POST['transaction_type'] == 'Adaptive Payment PREAPPROVAL' && isset($_REQUEST['pledge_id']) ) {
                //Handle IPN for advanced payments
                if($this->verify_paypal()) {
                    $nvp = 'preapprovalKey='.$_POST['preapproval_key'];
                    $nvp .= '&getBillingAddress=1';
                    $details = $this->adaptive_api_call( 'PreapprovalDetails', $nvp );

                    global $wdf;
                    $transaction = array();

                    $custom = explode('||',urldecode($_REQUEST['custom']));

                    //proccess additional custom fields
                    $possible_custom_fields = array('reward', 'country', 'address1', 'address2', 'city', 'state', 'zip');
                    foreach ($possible_custom_fields as $key => $possible_custom_field)
                        if(isset($custom[$key]) && $custom[$key])
                            $transaction[$possible_custom_field] = $custom[$key];

                    $post_title = $_REQUEST['pledge_id'];
                    $funder_id = $_REQUEST['fundraiser'];
                    $transaction['currency_code'] = ( isset($_POST['currency_code']) ? $_POST['currency_code'] : $settings['currency']);
                    $transaction['payer_email'] = $_POST['sender_email'];
                    $transaction['gateway_public'] = $this->public_name;
                    $transaction['gateway'] = $this->plugin_name;
                    $transaction['gross'] = ( isset($_POST['max_total_amount_of_all_payments']) ? $_POST['max_total_amount_of_all_payments'] : '' );
                    $transaction['ipn_id'] = ( isset($_POST['preapproval_key']) ? $_POST['preapproval_key'] : '' );
                    //Make sure you pass the correct type back into the transaction
                    $transaction['type'] = 'advanced';

                    $full_name = (isset($details['addressList_address(0)_addresseeName']) ? explode(' ',$details['addressList_address(0)_addresseeName'],2) : false);
                    if($full_name != false) {
                        $transaction['first_name'] = $full_name[0];
                        $transaction['last_name'] = $full_name[1];
                    }
                    switch($_POST['status']) {
                        case 'ACTIVE' :
                            $status = 'wdf_approved';
                            $transaction['status'] = __('Vorgeprüft','wdf');
                            $transaction['gateway_msg'] = __('Transaktion vorab genehmigt','wdf');
                            break;
                        case 'CANCELED' :
                            $status = 'wdf_canceled';
                            $transaction['status'] = __('Storniert','wdf');
                            $transaction['gateway_msg'] = __('Transaktion vorab genehmigt','wdf');
                            break;
                        default :
                            $status = 'wdf_canceled';
                            $transaction['status'] = __('Unbekannt','wdf');
                            $transaction['gateway_msg'] = __('Unbekannter PayPal-Status.','wdf');
                            break;
                    }

                    $wdf->update_pledge( $post_title, $funder_id, $status, $transaction);

                } else {
                    header("HTTP/1.1 503 Service Unavailable");
                    _e( 'Beim Überprüfen der IPN-Zeichenfolge mit PayPal ist ein Problem aufgetreten. Bitte versuche es erneut.','wdf' );
                    exit;
                }
            } elseif ( isset( $_POST['txn_type'] ) || $_POST['reason_code'] == 'refund' ) {

                $settings = get_option('wdf_settings');
                //Handle IPN for simple payments
                if($this->verify_paypal()) {
                    $transaction = array();

                    $custom = explode('||',$_POST['custom']);
                    $funder_id = $custom[0];
                    $post_title = $custom[1];
                    $is_recurring_payment = false;

                    //proccess additional custom fields
                    $possible_custom_fields = array('reward', 'country', 'address1', 'address2', 'city', 'state', 'zip');
                    foreach ($possible_custom_fields as $key => $possible_custom_field)
                        if(isset($custom[$key+2]) && $custom[$key+2])
                            $transaction[$possible_custom_field] = $custom[$key+2];

                	$type = $_POST['reason_code'] == 'refund' ? $_POST['reason_code'] : $_POST['txn_type'];

                    switch($type){
                        case 'subscr_signup':
                        case 'subscr_payment':
                        case 'subscr_eot':
                        case 'subscr_cancel':
                        case 'refund':
                            $transaction = $this->process_subscription_payment( $post_title, $transaction, $_POST );
                            break;
                        case 'web_accept':
                            $transaction['gross'] = (!empty($_POST['payment_gross']) ? $_POST['payment_gross'] : $_POST['mc_gross']);
                            break;
                        default:
                            //Not an accepted transaction type
                            die();
                    }

                    $transaction['txn_type'] = $_POST['txn_type'];
                    $transaction['type'] = 'simple';
                    $transaction['currency_code'] = ( isset($_POST['mc_currency']) ? $_POST['mc_currency'] : $settings['currency']);
                    if( isset( $_POST['txn_id'] ) ){
                        $transaction['ipn_id'] =  $_POST['txn_id'];
                    }
                    if( isset( $_POST['first_name'] ) ){
                        $transaction['first_name'] =  $_POST['first_name'];
                    }
                    if( isset( $_POST['last_name'] ) ){
                        $transaction['last_name'] =  $_POST['last_name'];
                    }
                    if( isset( $_POST['payment_fee'] ) ){
                        $transaction['payment_fee'] =  $_POST['payment_fee'];
                    }
                    $transaction['payer_email'] = (isset($_POST['payer_email']) ? $_POST['payer_email'] : 'johndoe@' . home_url() );
                    $transaction['gateway_public'] = $this->public_name;
                    $transaction['gateway'] = $this->plugin_name;

                    if( isset($_POST['payment_status']) ) {
                        switch($_POST['payment_status']) {
                            case 'Pending' :
                                $status = 'wdf_approved';
                                $transaction['status'] = __('Ausstehend/Genehmigt','wdf');
                                $transaction['gateway_msg'] = (isset($_POST['pending_reason']) ? $_POST['pending_reason'] : __('Fehlender Ausstehend Status.','wdf') );
                                break;
                            case 'Refunded' :
                                $status = 'wdf_refunded';
                                $transaction['status'] = __('Zurückerstattet','wdf');
                                $transaction['gateway_msg'] = __('Zahlung zurückerstattet','wdf');
                                break;
                            case 'Reversed' :
                                $status = 'wdf_canceled';
                                $transaction['status'] = __('Storniert','wdf');
                                $transaction['gateway_msg'] = __('Zahlung storniert','wdf');
                                break;
                            case 'Expired' :
                                $status = 'wdf_canceled';
                                $transaction['status'] = __('Abgelaufen','wdf');
                                $transaction['gateway_msg'] = __('Zahlung abgelaufen','wdf');
                                break;
                            case 'Processed' :
                                $status = 'wdf_complete';
                                $transaction['status'] = __('Verarbeitet','wdf');
                                $transaction['gateway_msg'] = __('Zahlung verarbeitet','wdf');
                                break;
                            case 'Completed' :
                                $status = 'wdf_complete';
                                if( 'subscr_payment' == $_POST['txn_type'] ){
                                    $transaction['status'] = __('Laufendes Abonnement','wdf');
                                } else {
                                    $transaction['status'] = __('Bezahlvorgang abgeschlossen','wdf');
                                }
                                break;
                            case 'Ended' :
                                $status = 'wdf_complete';
                                $transaction['status'] = __('Abonnement abgelaufen/storniert.','wdf');
                                break;
                            default:
                                $status = 'wdf_canceled';
                                $transaction['status'] = __('Unbekannter Zahlungsstatus','wdf');
                                break;
                        }
                    } else {
                        if( 'subscr_signup' == $_POST['txn_type'] ){
                            $status = 'wdf_approved';
                            $transaction['status'] = __('Abonnement bestätigt. Warten auf Zahlung...','wdf');
                        } else {
                            $status = 'wdf_canceled';
                            $transaction['status'] = __('Zahlungsstatus nicht angegeben','wdf');
                        }

                    }

                    global $wdf;
                    $wdf->update_pledge($post_title,$funder_id,$status,$transaction);
                }
            }

            die();
        }

        /**
         * Parses subscription request parameters into a transaction object.
         *
         * @since 2.6.1.3
         * @access public
         *
         * @param  string $post_title Post/Unterstützung title used as identifier.
         * @param  array $transaction Transaction object.
         * @param  array $request Subscription request parameters.
         * @return array
         */
        function process_subscription_payment( $post_title, $transaction, $request ){
            global $wpdb;

            $pledge_exists = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title = %s", $post_title) );

            $type = $request['reason_code'] == 'refund' ? $request['reason_code'] : $request['txn_type'];
            
            switch($type){
                case 'subscr_signup':
                    $transaction['gross'] = 0;//We should wait for the first payment to increase the value.
                    $transaction['cycle'] = $request['period3'];
                    $transaction['recurring'] = $request['recurring'];
                    $transaction['recurring_amount'] = $request['mc_amount3'];

                    //Sometimes subscr_payment arrives before subscr_signup, so we need to check if the pledge already exists.
                    if(!empty($pledge_exists) &&  $pledge_exists != false) {
                        $previous_transaction = get_post_meta($pledge_exists, 'wdf_transaction', true);

                        //Merge with the subscr_payment transaction data. Previous transaction data will take precedence.
                        $transaction = array_merge( $transaction, $previous_transaction);

                        //A payment was already processed, so we need to override the status.
                        $_POST['payment_status'] = 'Completed';
                    }

                    break;
                case 'subscr_payment':
                case 'refund':
                    $transaction['gross'] = (!empty($request['payment_gross']) ? $request['payment_gross'] : $request['mc_gross']);

                    //Sometimes subscr_payment arrives before subscr_signup, so we need to check if the pledge already exists.
                    if(!empty($pledge_exists) &&  $pledge_exists != false) {
                        $previous_transaction = get_post_meta($pledge_exists, 'wdf_transaction', true);
                        //Increase the amount paid on each recurring transaction.
                        $transaction['gross'] += $previous_transaction['gross'];
                        //Merge with the subscr_signup transaction data. New transaction data will take precedence.
                        $transaction = array_merge($previous_transaction, $transaction);
                    }
                    //Keep track of how many payments were processed.
                    $transaction['recurring_transactions'] = isset($previous_transaction['recurring_transactions']) ? $previous_transaction['recurring_transactions'] + 1 : 1;

                    break;
                case 'subscr_eot':
                case 'subscr_cancel':
                    $_POST['payment_status'] = 'Ended';
                    if(!empty($pledge_exists) &&  $pledge_exists != false) {
                        $previous_transaction = get_post_meta($pledge_exists, 'wdf_transaction', true);
                        //Merge with the subscr_signup transaction data. New transaction data will take precedence.
                        $transaction = array_merge($previous_transaction, $transaction);
                    }
                    break;
                default:
                    //Not an accepted transaction type
                    die();
            }

            return $transaction;
        }

		function execute_payment($type, $pledge, $transaction) {

			if($type == 'advanced') {
				$settings = get_option('wdf_settings');
				global $wdf;

				$nvp = 'actionType=PAY';
				$nvp .= '&preapprovalKey='.urlencode($transaction['ipn_id']);
				$nvp .= '&senderEmail='.urlencode($transaction['payer_email']);
				$nvp .= '&receiverList.receiver(0).email='.urlencode($settings['paypal']['advanced']['email']);
				$nvp .= '&receiverList.receiver(0).amount='.urlencode($transaction['gross']);
				$nvp .= '&feesPayer=SENDER';
				$nvp .= '&currencyCode='.urlencode($transaction['currency_code']);
				$nvp .= '&cancelUrl='.urlencode( wdf_get_funder_page('',$pledge->post_parent) );
				$nvp .= '&returnUrl='.urlencode( wdf_get_funder_page('',$pledge->post_parent) );
				$response = $this->adaptive_api_call( 'Pay', $nvp );
				if( isset($response['responseEnvelope_ack']) && $response['responseEnvelope_ack'] == 'Success' ) {
					switch($response['paymentExecStatus']) {
						case 'CREATED' :

							break;
						case 'COMPLETED' :
							$transaction['status'] = __('Abgeschlossen','wdf');
							$transaction['gateway_msg'] = __('Zahlung abgeschlossen.', 'wdf');
							$transaction['ipn_id'] = ( isset($response['payKey']) ? $response['payKey'] : '' );
							$status = 'wdf_complete';
							break;
						case 'INCOMPLETE' :
							$transaction['status'] = __('Unvollständig','wdf');
							$transaction['gateway_msg'] = __('Vorabgenehmigung unvollständig.', 'wdf');
							$status = $pledge->post_status;
							break;
						case 'ERROR' :
							$transaction['status'] = __('Fehler','wdf');
							$transaction['gateway_msg'] = (isset($reponse['payErrorList']) ? $response['payErrorList'] : __('Fehler beim Erfassen der Zahlung', 'wdf'));
							$status = 'wdf_canceled';
							break;
						case 'REVERSALERROR' :
							$transaction['status'] = __('Stornierungs Fehler','wdf');
							$transaction['gateway_msg'] = __('Fehler beim Stornieren der Zahlung.', 'wdf');
							$status = 'wdf_canceled';
							break;
						case 'PROCESSING' :
							$transaction['status'] = __('Verabeitung','wdf');
							$transaction['gateway_msg'] = __('Zahlung verarbeiten.', 'wdf');
							$status = $pledge->post_status;
							break;
						case 'PENDING' :
							$transaction['status'] = __('Ausstehend','wdf');
							$transaction['gateway_error'] = __('Fehler beim Reservieren der Zahlung.', 'wdf');
							$status = $pledge->post_status;
							break;
						default :
							$transaction['status'] = __('Unbekannt','wdf');
							$transaction['gateway_error'] = __('Unbekannter Zahlungsantwortstatus nach Ausführungsversuch.', 'wdf');
							$status = 'wdf_canceled';
							break;
					}
					$wdf->update_pledge($pledge->post_title, $pledge->post_parent, $status, $transaction);
				}

			} else if($type == 'simple') {
				// Nothing to do?
			}
		}
		function verify_paypal() {
			global $wdf;

			$settings = get_option('wdf_settings');
			if ($settings['paypal_sb'] == 'yes') {
				$domain = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
			} else {
				$domain = 'https://www.paypal.com/cgi-bin/webscr';
			}

            $req = 'cmd=_notify-validate&' . file_get_contents("php://input");

			$args = array();
			$args['user-agent'] = "Fundraising/{$wdf->version}: https://n3rds.work/piestingtal_source/ps-fundraising/";
			$args['body'] = $req;
			$args['sslverify'] = false;
			$args['timeout'] = 60;

			//use built in WP http class to work with most server setups
			$response = wp_remote_post($domain, $args);
			if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200 || $response['body'] != 'VERIFIED') {
				return false;
			} else {
				return true;
			}
		}

		//This function will take NVPString and convert it to an Associative Array and it will decode the response.
		function deformatNVP($nvpstr) {
			parse_str($nvpstr, $nvpArray);
			return $nvpArray;
		}

		function admin_settings() {
			if (!class_exists('Psource_HelpTooltips')) require_once WDF_PLUGIN_BASE_DIR . '/lib/external/class.wd_help_tooltips.php';
				$tips = new Psource_HelpTooltips();
				$tips->set_icon_url(WDF_PLUGIN_URL.'/img/information.png');
				$settings = get_option('wdf_settings'); ?>
			<table class="form-table">
				<tbody>
				<tr valign="top">
					<th scope="row"> <label for="wdf_settings[paypal_sb]"><?php echo __('PayPal Modus','wdf'); ?></label>
					</th>
					<td><select name="wdf_settings[paypal_sb]" id="wdf_settings_paypal_sb">
							<option value="no" <?php ( isset($settings['paypal_sb']) ? selected($settings['paypal_sb'],'no') : '' ); ?>><?php _e('Live','wdf'); ?></option>
							<option value="yes" <?php ( isset($settings['paypal_sb']) ?  selected($settings['paypal_sb'],'yes') : '' ); ?>><?php _e('Sandbox','wdf'); ?></option>
						</select></td>
				</tr>
			<?php if(in_array('simple', $settings['payment_types'])) : ?>
				<tr>
					<td colspan="2">
						<h4><?php _e('Einfache Zahlungsmöglichkeiten (einfache Spenden)','wdf'); ?></h4>
						<div class="message updated below-h2" style="width: auto;">
                            <p><?php _e('Damit Fundraising richtig funktioniert, musst Du bei PayPal eine IPN-Listening-URL einrichten. Andernfalls wird Deine Webseite nicht benachrichtigt, wenn eine wiederkehrende Zahlung storniert wird.','wdf'); ?>
                                <br /><?php echo __( 'Deine IPN-Listening-URL lautet:', 'wdf' ); ?>
                                <span class="description"><?php echo $this->ipn_url; ?></span> <br />
                                <a href="<?php echo __( 'https://developer.paypal.com/docs/classic/ipn/integration-guide/IPNSetup/', 'wdf' ); ?>"><?php echo __( 'Anweisungen &raquo;', 'wdf' ); ?></a>
                            </p>
                        </div>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"> <label for="wdf_settings[paypal_email]"><?php echo __('PayPal Email Addresse:','wdf'); ?></label>
					</th>
					<td><input class="regular-text" type="text" id="wdf_settings_paypal_email" name="wdf_settings[paypal_email]" value="<?php echo ( isset($settings['paypal_email']) ?  esc_attr($settings['paypal_email']) : '' ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"> <label for="wdf_settings[paypal_image_url]"><?php echo __('PayPal Checkout Header-Bild ','wdf'); ?></label></th>
					<td>
						<input class="regular-text" type="text" name="wdf_settings[paypal_image_url]" value="<?php echo (isset($settings['paypal_image_url']) ? $settings['paypal_image_url'] : ''); ?>" />
						<?php echo $tips->add_tip('PayPal ermöglicht es Dir, während des Kaufvorgangs benutzerdefinierte Header-Bilder zu verwenden. PayPal empfiehlt, ein Bild von einem sicheren https://-Link zu verwenden, dies ist jedoch nicht erforderlich.'); ?>
					</td>
				</tr>
			<?php endif; ?>
			<?php if(in_array('advanced', $settings['payment_types'])) : ?>
				<tr>
					<td colspan="2">
					<h4><?php _e('Erweiterte Zahlungsoptionen (erweitertes Crowdfunding)','wdf'); ?></h4>
					</td>
				</tr>
				<?php /*?><tr>
				<th scope="row"><?php _e('Fees To Collect', 'wdf'); ?></th>
				<td><span class="description">
					<?php _e('Enter a percentage of all store sales to collect as a fee. Decimals allowed.', 'wdf') ?>
					</span><br />
					<input value="<?php echo esc_attr( (isset($settings['paypal']['advanced']['percentage']) ? $settings['paypal']['advanced']['percentage'] : '') ); ?>" size="3" name="wdf_settings[paypal][advanced][percentage]" type="text" />%
				</td>
				</tr><?php */?>
				<tr>
				<th scope="row"><?php _e('PayPal Email Addresse', 'wdf') ?></th>
				<td><span class="description">
					<?php _e('Bitte gib Deine PayPal-E-Mail-Adresse oder Geschäftsnummer ein, an die Du Gebühren erhalten möchtest.', 'wdf') ?>
					</span><br />
					<input value="<?php echo esc_attr( (isset($settings['paypal']['advanced']['email']) ? $settings['paypal']['advanced']['email'] : '') ); ?>" size="40" name="wdf_settings[paypal][advanced][email]" type="text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Paypal-Währung','wdf') ?></th>
					<td>
						<select name="wdf_settings[paypal][advanced][currency]">
						<?php
						$sel_currency = isset($settings['paypal']['advanced']['currency']) ? $settings['paypal']['advanced']['currency'] : $settings['currency'];
						$currencies = array(
							'AUD' => 'AUD - Australian Dollar',
							'BRL' => 'BRL - Brazilian Real',
							'CAD' => 'CAD - Canadian Dollar',
							'CHF' => 'CHF - Swiss Franc',
							'CZK' => 'CZK - Czech Koruna',
							'DKK' => 'DKK - Danish Krone',
							'EUR' => 'EUR - Euro',
							'GBP' => 'GBP - Pound Sterling',
							'ILS' => 'ILS - Israeli Shekel',
							'HKD' => 'HKD - Hong Kong Dollar',
							'HUF' => 'HUF - Hungarian Forint',
							'JPY' => 'JPY - Japanese Yen',
							'MYR' => 'MYR - Malaysian Ringgits',
							'MXN' => 'MXN - Mexican Peso',
							'NOK' => 'NOK - Norwegian Krone',
							'NZD' => 'NZD - New Zealand Dollar',
							'PHP' => 'PHP - Philippine Pesos',
							'PLN' => 'PLN - Polish Zloty',
							'SEK' => 'SEK - Swedish Krona',
							'SGD' => 'SGD - Singapore Dollar',
							'TWD' => 'TWD - Taiwan New Dollars',
							'THB' => 'THB - Thai Baht',
							'TRY' => 'TRY - Turkish lira',
							'USD' => 'USD - U.S. Dollar'
						);

						foreach ($currencies as $k => $v) {
							echo '		<option value="' . $k . '"' . ($k == $sel_currency ? ' selected' : '') . '>' . esc_html($v, true) . '</option>' . "\n";
						}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('PayPal-API-Anmeldeinformationen', 'wdf') ?></th>
					<td><span class="description">
						<?php _e('Du musst Dich bei PayPal anmelden und eine App erstellen, um Deine Zugangsdaten zu erhalten.', 'wdf') ?>
						</span>
						<p>
							<label>
								<?php _e('API-Benutzername', 'wdf') ?>
								<br />
								<input value="<?php echo esc_attr( (isset($settings['paypal']['advanced']['api_user']) ? $settings['paypal']['advanced']['api_user'] : '') ); ?>" size="30" name="wdf_settings[paypal][advanced][api_user]" type="text" />
							</label>
						</p>
						<p>
							<label>
								<?php _e('API Passwort', 'wdf') ?>
								<br />
								<input value="<?php echo esc_attr( (isset($settings['paypal']['advanced']['api_pass']) ? $settings['paypal']['advanced']['api_pass'] : '') ); ?>" size="20" name="wdf_settings[paypal][advanced][api_pass]" type="text" />
							</label>
						</p>
						<p>
							<label>
								<?php _e('Signatur', 'wdf') ?>
								<br />
								<input value="<?php echo esc_attr( (isset($settings['paypal']['advanced']['api_sig']) ? $settings['paypal']['advanced']['api_sig'] : '') ); ?>" size="70" name="wdf_settings[paypal][advanced][api_sig]" type="text" />
							</label>
						</p>
						<p>
							<label>
								<?php _e('Anwendungs-ID', 'wdf') ?>
								<br />
								<input value="<?php echo esc_attr( (isset($settings['paypal']['advanced']['app_id']) ? $settings['paypal']['advanced']['app_id'] : '') ); ?>" size="50" name="wdf_settings[paypal][advanced][app_id]" type="text" />
							</label><?php echo $tips->add_tip(__('Bei Verwendung von PayPal im Sandbox-Modus ist keine Anwendungs-ID erforderlich.','wdf')); ?>
						</p>
						<span class="description">
						<?php _e('Du musst diese Anwendung mit Deinem Geschäftskonto-Login bei PayPal registrieren, um eine Anwendungs-ID zu erhalten, die mit Deinen API-Anmeldeinformationen funktioniert. Die App verwendet "Adaptive Payments -> Preapprovals", um den Benutzer nach Abschluss des Ziels zu belasten. <a target="_blank" href="https://www.paypal-apps.com/user/my-account/applications">Registriere Dich und reiche Deine Anwendung ein</a>, während Du beim Entwicklerportal angemeldet bist.</a> Beachte, dass Du zum Testen im Sandbox-Modus keine Anwendungs-ID benötigst. Bitte denke daran, nicht dasselbe Sandbox-Konto für die Finanzierung und den Erhalt von Spenden zu verwenden. <a target="_blank" href="https://developer.paypal.com/docs/classic/lifecycle/goingLive/">More Information &raquo;</a>', 'wdf') ?>
						</span>
					</td>
				</tr>
				<?php /*?><tr>
					<th scope="row"><?php _e('Gateway Settings Page Message', 'mp'); ?></th>
					<td><span class="description">
						<?php _e('This message is displayed at the top of the gateway settings page to store admins. It\'s a good place to inform them of your fees or put any sales messages. Optional, HTML allowed.', 'mp') ?>
						</span><br />
						<textarea class="mp_msgs_txt" name="mp[gateways][paypal-chained][msg]"><?php echo esc_html($settings['gateways']['paypal-chained']['msg']); ?></textarea></td>
				</tr><?php */?>

			<?php endif; ?>
				</tbody>
			</table>
			<?php
		}
		function save_gateway_settings() {

			if( isset($_POST['wdf_settings']['paypal']) ) {
				// Init array for new settings
				$new = array();

				// Advanced Settings
				if( isset($_POST['wdf_settings']['paypal']['advanced']) && is_array($_POST['wdf_settings']['paypal']['advanced'])) {
					$new['paypal']['advanced'] = $_POST['wdf_settings']['paypal']['advanced'];
					$new['paypal']['advanced'] = array_map('esc_attr',$new['paypal']['advanced']);

					$settings = get_option('wdf_settings');
					$settings = array_merge($settings,$new);
					update_option('wdf_settings',$settings);
				}

			}
		}

	}
wdf_register_gateway_plugin('WDF_Gateway_PayPal', 'paypal', __('PayPal','wdf'), array('simple','standard','advanced'));
}
?>