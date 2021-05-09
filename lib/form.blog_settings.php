<?php
$settings = get_option('wdf_settings');

if (!class_exists('Psource_HelpTooltips')) require_once WDF_PLUGIN_BASE_DIR . '/lib/external/class.wd_help_tooltips.php';
	$tips = new Psource_HelpTooltips();
	$tips->set_icon_url(WDF_PLUGIN_URL.'/img/information.png');

	$tabs = array(
		'payments' => __('Zahlungen','wdf'),
		'presentation' => __('Präsentation','wdf'),
		'permissions' => __('Berechtigungen','wdf'),
		'other' => __('Sonstiges','wdf'),
	);
	if( defined('WDF_ALLOW_RESET') && WDF_ALLOW_RESET == true )
		$tabs['reset'] = __('Zurücksetzen','wdf');

	if(!isset($_GET['tab']))
		$active_tab = 'payments';
	else
		$active_tab = $_GET['tab'];

	$tabs = apply_filters('wdf_settings_tabs',$tabs);
	$active_tab = apply_filters('wdf_settings_active_tab',$active_tab);

?>
<div class="wrap">
	<div id="icon-wdf-admin" class="icon32"><br></div>
		<h2><?php echo sprintf(__('%s Einstellungen','wdf'),esc_attr($settings['funder_labels']['menu_name'])); ?></h2>
		<?php do_action('wdf_msg_general');?>
		<form action="" method="post" id="wdf_settings_<?php echo $active_tab ?>" class="nav-tabs">
			<input type="hidden" name="wdf_nonce" value="<?php echo wp_create_nonce('_wdf_settings_nonce');?>" />
			<h3 class="nav-tab-wrapper">
				<?php foreach($tabs as $k => $v) : ?>
					<a class="nav-tab <?php echo ($active_tab == $k ? 'nav-tab-active' : '') ?>" href="<?php echo admin_url('edit.php?post_type=funder&page=wdf_settings&tab='.$k); ?>" rel="#tab_<?php echo $k ?>"><?php echo $v ?></a>
				<?php endforeach; ?>
			</h3>
			<?php echo apply_filters('wdf_error_wdf_nonce',''); ?>

				<div>
					<?php switch($active_tab) {

						case 'presentation' : ?>
							<table class="form-table" id="wdf_label_settings">
								<tbody>
									<tr valign="top">
										<th scope="row">
											<label><?php _e('Name des Plugin-Menüs (Branding)','wdf'); ?></label>
										</th>
										<td>
											<input type="text" name="wdf_settings[funder_labels][menu_name]" value="<?php echo esc_attr($settings['funder_labels']['menu_name']); ?>" />
										</td>
									</tr>
								</tbody>
							</table>
							<h3><?php _e('Unterstützeretiketten','wdf'); ?></h3>
							<span class="description"><?php _e('Wird verwendet, um jemanden zu beschreiben, der eine Zahlung leistet','wdf'); ?></span>
							<table class="form-table" id="wdf_label_settings">
								<tbody>
									<tr valign="top">
										<th scope="row">
											<label><?php _e('Einzelner Unterstützer','wdf'); ?></label>
										</th>
										<td>
											<input type="text" name="wdf_settings[donation_labels][backer_single]" value="<?php echo esc_attr($settings['donation_labels']['backer_single']); ?>" />
										</td>
									</tr>
									<tr valign="top">
										<th scope="row">
											<label><?php _e('Mehrere Unterstützer','wdf'); ?></label>
										</th>
										<td>
											<input type="text" name="wdf_settings[donation_labels][backer_plural]" value="<?php echo esc_attr($settings['donation_labels']['backer_plural']); ?>" />
										</td>
									</tr>
								</tbody>
							</table>

							<h3><?php _e('Level-/Belohnungsetiketten','wdf'); ?></h3>
							<span class="description"><?php _e('Dies wird verwendet, um vorgeschlagene Zahlungsniveaus zu beschreiben','wdf'); ?></span>
							<table class="form-table" id="wdf_label_settings">
								<tbody>
									<tr valign="top">
										<th scope="row">
											<label><?php _e('Singlular Level','wdf'); ?></label>
										</th>
										<td>
											<input type="text" name="wdf_settings[funder_labels][singular_level]" value="<?php echo esc_attr($settings['funder_labels']['singular_level']); ?>" />
										</td>
									</tr>
									<tr valign="top">
										<th scope="row">
											<label><?php _e('Plural Level','wdf'); ?></label>
										</th>
										<td>
											<input type="text" name="wdf_settings[funder_labels][plural_level]" value="<?php echo esc_attr($settings['funder_labels']['plural_level']); ?>" />
										</td>
									</tr>
								</tbody>
							</table>
							<h3><?php _e('Spendenaktion-Labels','wdf'); ?></h3>
							<span class="description"><?php _e('Jedes Spendenprojekt wird mit diesen gekennzeichnet','wdf'); ?></span>
							<table class="form-table" id="wdf_label_settings">
								<tbody>

									<tr valign="top">
										<th scope="row">
											<label><?php _e('Singular Name','wdf'); ?></label>
										</th>
										<td>
											<input type="text" name="wdf_settings[funder_labels][singular_name]" value="<?php echo esc_attr($settings['funder_labels']['singular_name']); ?>" />
										</td>
									</tr>

									<tr valign="top">
										<th scope="row">
											<label><?php _e('Plural Name','wdf'); ?></label>
										</th>
										<td>
											<input type="text" name="wdf_settings[funder_labels][plural_name]" value="<?php echo esc_attr($settings['funder_labels']['plural_name']); ?>" />
										</td>
									</tr>

								</tbody>
							</table>

							<h3><?php _e('Unterstützung Labels','wdf'); ?></h3>
							<span class="description"><?php _e('Für jede Zahlung an eine Spendenaktion wird dieses Etikett verwendet','wdf'); ?></span>
							<table class="form-table" id="wdf_label_settings">
								<tbody>
									<?php /*?><tr valign="top">
										<th scope="row">
											<label><?php _e('Menu Name','wdf'); ?></label>
										</th>
										<td>
											<input type="text" name="wdf_settings[donation_labels][menu_name]" value="<?php echo esc_attr($settings['donation_labels']['menu_name']); ?>" />
										</td>
									</tr><?php */?>
									<tr valign="top">
										<th scope="row">
											<label><?php _e('Singular Name','wdf'); ?></label>
										</th>
										<td>
											<input type="text" name="wdf_settings[donation_labels][singular_name]" value="<?php echo esc_attr($settings['donation_labels']['singular_name']); ?>" />
										</td>
									</tr>
									<tr valign="top">
										<th scope="row">
											<label><?php _e('Plural Name','wdf'); ?></label>
										</th>
										<td>
											<input type="text" name="wdf_settings[donation_labels][plural_name]" value="<?php echo esc_attr($settings['donation_labels']['plural_name']); ?>" />
										</td>
									</tr>
									<tr valign="top">
										<th scope="row">
											<label><?php _e('Aktionsname','wdf'); ?><?php echo $tips->add_tip(__('Dies beschreibt die Aktion einer Zahlung. Es wird als Handlungsaufforderung für eine Zahlung verwendet.','wdf')); ?></label>
										</th>
										<td>
											<input type="text" name="wdf_settings[donation_labels][action_name]" value="<?php echo esc_attr($settings['donation_labels']['action_name']); ?>" />
										</td>
									</tr>
								</tbody>
							</table>
							<h3><?php _e('Präsentation an der Kasse','wdf'); ?></h3>
							<span class="description"><?php _e('Auf einer ausgearbeiteten Checkout-Seite kannst Du Unterstützern zusätzliche Informationen zu Deiner Spendenaktion anzeigen. Wenn sie direkt über das Fundraising-Panel auschecken, beginnt der Zahlungsvorgang sofort','wdf')?></span>
							<table class="form-table">
								<tbody>
									<tr valign="top">
										<th scope="row">
											<label><?php _e('Wähle einen Standard-Checkout-Typ','wdf'); ?></label>
										</th>
										<td>
											<select name="wdf_settings[checkout_type]">
												<option value="1" <?php selected($settings['checkout_type'],'1') ?>><?php _e('Checkout direkt vom Panel','wdf'); ?></option>
												<option value="2" <?php selected($settings['checkout_type'],'2') ?>><?php _e('Verwende eigene Checkout-Seite','wdf'); ?></option>
											</select>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row">
											<label><?php _e('Pro Spendenaktion Checkoutarten zulassen?','wdf'); ?></label>
										</th>
										<td>
											<select name="wdf_settings[single_checkout_type]">
												<option value="0" <?php selected($settings['single_checkout_type'],'0') ?>><?php _e('Nein','wdf'); ?></option>
												<option value="1" <?php selected($settings['single_checkout_type'],'1') ?>><?php _e('Ja','wdf'); ?></option>
											</select>
										</td>
									</tr>
								</tbody>
							</table>
							<h3><?php _e('Stileinstellungen','wdf'); ?></h3>
							<table class="form-table">
								<tbody>
									<tr valign="top">
										<th scope="row">
											<label><?php _e('Wähle einen Standardanzeigestil','wdf'); ?></label>
										</th>
										<td>
											<select name="wdf_settings[default_style]" id="wdf_default_style">
												<?php if(is_array($this->styles) && !empty($this->styles)) : ?>
													<?php foreach($this->styles as $key => $label) : ?>
														<option <?php selected($settings['default_style'],$key); ?> value="<?php echo $key ?>"><?php echo $label; ?></option>
													<?php endforeach; ?>
												<?php endif; ?>
											</select>
										</td>
									</tr>

									<tr valign="top">
										<th scope="row">
											<label><?php _e('Pro Spendenaktion Stile zulassen','wdf'); ?></label>
										</th>
										<td>
											<select name="wdf_settings[single_styles]">
												<option value="no" <?php selected($settings['single_styles'],'no') ?>><?php _e('Nein','wdf'); ?></option>
												<option value="yes" <?php selected($settings['single_styles'],'yes') ?>><?php _e('Ja','wdf'); ?></option>
											</select><?php echo $tips->add_tip(__('Wenn Du diese Option zulässt, kann jede Spendenaktion die Standardstile Deiner Webseite überschreiben','wdf')); ?>
										</td>
									</tr>



									<tr valign="top">
										<th scope="row">
											<label><?php _e('Benutzerdefinierte CSS','wdf'); ?></label><?php echo $tips->add_tip(__('In diesem Feld gespeicherte CSS-Stile werden auf jede Seite geladen, die Fundraising-Inhalte enthält. Verwende in '.esc_js('<style>').' diesem Feld keine Tags.','wdf')); ?>
										</th>
										<td>
											<textarea rows="15" class="widefat" id="wdf_custom_css" name="wdf_settings[custom_css]"><?php echo esc_attr($settings['custom_css']); ?></textarea>
										</td>
									</tr>


								</tbody>
							</table>
							<h3><?php _e('Sonstiges','wdf'); ?></h3>
							<table class="form-table">
								<tbody>
									<tr valign="top">
										<th scope="row">
											<label><?php _e('Nachricht, die den Benutzern angezeigt wird, wenn die Unterstützung nicht gefunden wurde','wdf'); ?></label>
										</th>
										<td>
											<textarea name="wdf_settings[message_pledge_not_found]" class="widefat" rows="2"><?php echo esc_attr($settings['message_pledge_not_found']); ?></textarea>
											<span class="description"><?php _e('Manchmal dauert es etwas länger, bis das Zahlungsgateway Deine Website über den Zahlungsstatus benachrichtigt. Hier kannst Du Deine eigene Nachricht einstellen.','wdf'); ?></span>
										</td>
									</tr>
								</tbody>
							</table>

						<?php break;
						case 'other' : ?>

							<h3><?php _e('Permalink Einstellungen','wdf'); ?></h3>
							<table class="form-table" id="wdf_permalink_settings">
								<tbody>

									<?php if(!get_option('permalink_structure')) : ?>

									<tr valign="top">
										<th scope="row">
											<label><?php _e('Fundraising-Permalink-Struktur','wdf'); ?></label>
										</th>
										<td>
											<div class="error below-h2"><p><?php _e('Du musst Deine Permalink-Struktur einrichten, bevor Du Deine Spenden-Slugs festlegst','wdf'); ?></p></div>
										</td>
									</tr>

									<?php else : ?>

									<?php
									$front_permlink = $this->get_mu_front_permlink('/', '');
									if(is_main_site() && is_multisite() && function_exists('is_subdomain_install') && !is_subdomain_install()) {
									?>
									<tr valign="top">
										<th scope="row">
											<label><?php _e('Aktiviere "Front" in Permlinks','wdf'); echo ' ("'.$this->get_mu_front_permlink('/', '/', 1).'")'; ?></label>
										</th>
										<td>
											<label><input class="wdf_auto_submit" value="1" name="wdf_settings[permlinks_front]" type="radio" <?php checked( $settings['permlinks_front'], 1 ); ?>> <?php _e('Ja','wdf'); ?></label>
											<label><input class="wdf_auto_submit" value="0" name="wdf_settings[permlinks_front]" type="radio" <?php checked( $settings['permlinks_front'], 0 ); ?>> <?php _e('Nein','wdf'); ?></label>
												
										</td>
									</tr>
									<?php
									}
									?>

									<tr valign="top">
										<th scope="row">
											<label><?php _e('Fundraising-Verzeichnis Ort','wdf'); ?></label>
										</th>
										<td>
											<span class="code"><?php echo home_url().$front_permlink; ?>/</span><input id="wdf_dir_slug" type="text" name="wdf_settings[dir_slug]" value="<?php echo esc_attr($settings['dir_slug']); ?>" />
										</td>
									</tr>

									<tr valign="top">
										<th scope="row">
											<label><?php _e('Checkout Seite','wdf'); ?></label>
										</th>
										<td>
											<span class="code"><?php echo home_url().$front_permlink.'/'.$settings['dir_slug'].'/{'.__('Der Name der Spendenaktion','wdf').'}/'; ?></span><input id="wdf_checkout_slug" type="text" name="wdf_settings[checkout_slug]" value="<?php echo esc_attr($settings['checkout_slug']); ?>" />
										</td>
									</tr>

									<tr valign="top">
										<th scope="row">
											<label><?php _e('Danke Seite','wdf'); ?></label>
										</th>
										<td>
											<span class="code"><?php echo home_url().$front_permlink.'/'.$settings['dir_slug'].'/{'.__('Der Name der Spendenaktion','wdf').'}/'; ?></span><input id="wdf_confirm_slug" type="text" name="wdf_settings[confirm_slug]" value="<?php echo esc_attr($settings['confirm_slug']); ?>" />
										</td>
									</tr>
									<?php endif; ?>
								</tbody>
							</table>
							<?php /*?><h3><?php _e('Other Settings','wdf'); ?></h3>
							<table class="form-table">
								<tbody>

									<tr valign="top">
										<th scope="row">
											<label><?php _e('Add fundraising directory to menu?','wdf'); ?></label>
										</th>
										<td>
											<select name="wdf_settings[inject_menu]">
												<option value="no" <?php selected($settings['inject_menu'],'no') ?>>No</option>
												<option value="yes" <?php selected($settings['inject_menu'],'yes') ?>>Yes</option>
											</select><?php echo $tips->add_tip(__('This option will only work for page menus not custom theme menus','wdf')); ?>
										</td>
									</tr>

								</tbody>
							</table><?php */?>

						<?php break;
						case 'permissions' : ?>

							<h3><?php _e('Berechtigungseinstellungen','wdf'); ?></h3>
							<p><?php _e('Steuere den Zugriff auf Spendenaktion-Funktionen für jede in Deiner WP-Installation verfügbare Benutzerrolle. Der Administrator erhält standardmäßig Zugriff auf alle.','wdf'); ?></p>
						<table id="wdf_permissions" class="widefat">
							<thead>
								<tr>
									<th><strong><?php _e('Benutzer-Rolle','wdf'); ?></strong></th>
									<?php foreach($this->capabilities as $key => $label) : ?>
										<th class="num">
											<?php echo $label; ?>
										</th>
									<?php endforeach; ?>
								</tr>
							</thead>

							<tbody>
								<?php foreach($wp_roles->get_names() as $name => $label) : ?>
									<?php if($name == 'administrator') continue; ?>
									<tr>
										<?php $role_obj = get_role($name); ?>
										<td><strong><?php echo $label; ?></strong></td>
										<?php foreach($this->capabilities as $key => $label) : ?>
												<td class="num"><input id="<?php echo $name.'_'.$key; ?>" type="checkbox" value="1" name="wdf_settings[user_caps][<?php echo $key; ?>][<?php echo $name; ?>]" <?php checked(isset($wp_roles->roles[$name]['capabilities'][$key]) ? $wp_roles->roles[$name]['capabilities'][$key] : '',true); ?> /></td>
										<?php endforeach; ?>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>

						<input type="hidden" value="1" name="wdf_settings[user_caps][viewed]" />

						<?php break;
						case 'payments' : ?>

							<h3><?php _e('Währungseinstellungen','wdf'); ?></h3>
							<table class="form-table">
								<tbody>
									<tr valign="top">
										<th scope="row">
											<label><?php _e('Stelle Deine Währung ein','wdf'); ?></label>
										</th>
										<td>
											<select id="wdf_settings_currency" name="wdf_settings[currency]">

												<?php foreach ($this->currencies as $key => $value) { ?>
													<option value="<?php echo $key; ?>"<?php selected($settings['currency'], $key); ?>><?php echo esc_attr($value[0]) . ' - ' . $this->format_currency($key); ?></option>
												<?php } ?>
											</select>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row">
											<label><?php _e('Position des Währungssymbols', 'wdf') ?></label>
										</th>
										<td>
										<label><input value="1" name="wdf_settings[curr_symbol_position]" type="radio"<?php checked($settings['curr_symbol_position'], 1); ?>>
											<?php echo $this->format_currency($settings['currency']); ?>100</label><br />
											<label><input value="2" name="wdf_settings[curr_symbol_position]" type="radio"<?php checked($settings['curr_symbol_position'], 2); ?>>
											<?php echo $this->format_currency($settings['currency']); ?> 100</label><br />
											<label><input value="3" name="wdf_settings[curr_symbol_position]" type="radio"<?php checked($settings['curr_symbol_position'], 3); ?>>
											100<?php echo $this->format_currency($settings['currency']); ?></label><br />
											<label><input value="4" name="wdf_settings[curr_symbol_position]" type="radio"<?php checked($settings['curr_symbol_position'], 4); ?>>
											100 <?php echo $this->format_currency($settings['currency']); ?></label>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row">
											<label><?php _e('Dezimalzahl in Preisen anzeigen', 'wdf') ?></label>
										</th>
										<td>
										<label><input value="1" name="wdf_settings[curr_decimal]" type="radio"<?php checked( ( ($settings['curr_decimal'] !== 0) ? 1 : 0 ), 1); ?>>
												<?php _e('Ja', 'wdf') ?></label>
												<label><input value="0" name="wdf_settings[curr_decimal]" type="radio"<?php checked($settings['curr_decimal'], 0); ?>>
												<?php _e('Nein', 'wdf') ?></label>
										</td>
									</tr>
								</tbody>
							</table>
							<h3><?php _e('Zulässige Spendenaktion-Typen','wdf'); ?></h3>

							<table class="form-table">
								<tbody>
									<tr valign="top">
										<th scope="row">
											<label><?php _e('Einfache Spenden','wdf'); ?></label>
											<input type="hidden" name="wdf_settings[payment_types]" value="" />
										</th>
										<td>
											<input class="wdf_auto_submit" type="checkbox" name="wdf_settings[payment_types][]" value="simple" <?php checked( !empty($settings['payment_types']) && in_array( 'simple', $settings['payment_types'] ), true ); ?> />
											<?php echo $tips->add_tip(__('Ermöglicht eine einfache kontinuierliche Spende','wdf')); ?>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row">
											<label><?php _e('Erweitertes Crowdfunding','wdf'); ?></label>
										</th>
										<td>
											<input class="wdf_auto_submit" type="checkbox" name="wdf_settings[payment_types][]" value="advanced" <?php checked( !empty($settings['payment_types']) && in_array( 'advanced', $settings['payment_types'] ), true); ?> id="wdf_allowed_fundraier_types" />
											<?php echo $tips->add_tip(__('Unterstützungen sind nur autorisiert und Zahlungen werden erst bearbeitet, wenn Dein Ziel erreicht ist.','wdf').' '.__('Dies erfordert eine erweiterte PayPal-Konfiguration.','wdf')); ?>
										</td>
									</tr>
								</tbody>
							</table>

							<?php if(isset($settings['payment_types']) && !empty($settings['payment_types']) ) : ?>
								<?php global $wdf_gateway_plugins, $wdf_gateway_active_plugins; ?>
								<?php if( is_array($wdf_gateway_plugins) ): ?>
									<h3><?php _e('Verfügbare Zahlungsgateways','wdf'); ?></h3>
									<table class="form-table">
										<tbody>
											<?php $getaways_select = array(); ?>
											<?php foreach( $wdf_gateway_plugins as $gateway => $data) : ?>
												<?php $flag = false; ?>
												<?php foreach($data[2] as $type) {
													if( in_array($type, $settings['payment_types']) )
														$flag = true;
												} ?>
												<?php if($flag != false) : ?>
												<?php

												$checked = isset($settings['active_gateways'][$gateway]) ? checked($settings['active_gateways'][$gateway],'1' , false) : '';

												if(!empty($checked))
													$getaways_select[$gateway] = $data[1];

												?>
												<tr valign="top">
													<th scope="row">
														<label for="wdf_settings_gateway_<?php echo $gateway; ?>"><span class="title"><?php echo $data[1] ?></span></label>
													</th>
													<td>
														<input type="hidden" name="wdf_settings[active_gateways][<?php echo $gateway ?>]" value="0" />
														<input class="gateway_switch wdf_auto_submit" type="checkbox" id="wdf_active_gateway_<?php echo $gateway; ?>" name="wdf_settings[active_gateways][<?php echo $gateway ?>]" value="1" <?php echo $checked; ?> />
													</td>
												</tr>
												<?php endif; ?>
											<?php endforeach; ?>
											<?php if(count($getaways_select) > 1) {?>
												<tr valign="top">
													<th scope="row">
														<label for="wdf_settings[default_gateway]"><span class="title" style="font-weight:bold;"><?php _e('Standard-Gateway','wdf'); ?></span></label>
													</th>
													<td>
														<select name="wdf_settings[default_gateway]">
															<?php
															$settings['default_gateway'] = isset($settings['default_gateway']) ? $settings['default_gateway'] : 'paypal';
															$this->the_select_options( $getaways_select, $settings['default_gateway']);
															?>
														</select>
													</td>
												</tr>
											<?php } ?>
										</tbody>
									</table>
								<?php endif; ?>

								<?php if( is_array($wdf_gateway_active_plugins) ) : ?>
									<?php foreach( $wdf_gateway_active_plugins as $gateway => $data) : ?>
										<?php if( isset( $settings['active_gateways'][$gateway]) ) : ?>
											<h3><?php echo $data->admin_name ?> <?php _e('Einstellungen','wdf'); ?></h3>
											<?php do_action('wdf_gateway_settings_form_'.$gateway); ?>
										<?php endif; ?>
									<?php endforeach; ?>
								<?php endif; ?>

							<?php endif; ?>
						<?php break;

						case 'reset' : ?>

							<table class="form-table">
									<tbody>
										<tr valign="top">
											<th scope="row">
												<label><span class="title"><?php _e('Setze Deine Installation auf die Standardeinstellungen zurück?','wdf'); ?></span></label>
											</th>
											<td>
												<input type="submit" class="button" name="wdf_reset" id="wdf_reset" value="Reset Data" />
											</td>
										</tr>
									</tbody>
							</table>


						<?php break;
						case 'default' :

							do_action('wdf_settings_custom_tab_'.$k,$settings);

							break;

					} ?>

				</div>
				<p class="submit"><input type="submit" value="Änderungen speichern" class="button-primary" name="save_settings" /></p>
			</form>
	<script type="text/javascript">
		jQuery(document).ready( function($) {
			$('#wdf_reset').on("click", function(e) {
				var check = confirm("Bist du sicher, dass du das machen willst? Du verlierst alle Daten, die mit Deinen Spenden und Einstellungen für Spendenaktionen verbunden sind!");
				if (check == true)  {
					return true;
				} else {
					return false;
				}
			});
			$('input.wdf_auto_submit').change(function(e) {
				$(this).parents('form').trigger('submit');
				return false;
			});
		});
	</script>
</div>