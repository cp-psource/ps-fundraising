<?php

global $wdf, $pagenow;

if($pagenow == 'nav-menus.php') { ?>

    <p><a href="#" id="wdf_add_nav_archive" class="button secondary-button"><?php _e('Add Archive Page To Menu','wdf'); ?></a></p>
    <?php
    $funder_obj['args']->name = 'funder';
    wp_nav_menu_item_post_type_meta_box('', $funder_obj);
} else {

    //Setup tooltips for all metaboxes
    if (!class_exists('Psource_HelpTooltips')) require_once WDF_PLUGIN_BASE_DIR . '/lib/external/class.wd_help_tooltips.php';
    $tips = new Psource_HelpTooltips();
    $tips->set_icon_url(WDF_PLUGIN_URL.'/img/information.png');

    // Setup $meta for all metaboxes
    $meta = get_post_custom($post->ID);
    $settings = get_option('wdf_settings');

    $current_type = (isset($meta['wdf_type'][0]) && $meta['wdf_type'][0]) ? $meta['wdf_type'][0] : 'simple';

    //pull out the meta_box id and pass it through a switch instead of using individual functions
    switch($data['id']) {

        ///////////////////////////
        // PLEDGE STATUS METABOX //
        ///////////////////////////
        case 'wdf_pledge_status' : ?>

            <?php $trans = $this->get_transaction($post->ID); ?>
            <label><?php _e('Gateway Status','wdf'); ?>: <?php echo isset($trans['status']) ? $trans['status'] : ''; ?></label>
            <p>
                <label><?php _e('Unterstützung Status','wdf'); ?></label><br />
                <select class="widefat" name="post_status">
                    <option value="wdf_complete" <?php selected($post->post_status,'wdf_complete'); ?>><?php _e('Komplett','wdf'); ?></option>
                    <option value="wdf_approved" <?php selected($post->post_status,'wdf_approved'); ?>><?php _e('Genehmigt','wdf'); ?></option>
                    <option value="wdf_refunded" <?php selected($post->post_status,'wdf_refunded'); ?>><?php _e('Rückerstattet','wdf'); ?></option>
                    <option value="wdf_canceled" <?php selected($post->post_status,'wdf_canceled'); ?>><?php _e('Storniert','wdf'); ?></option>
                </select>
            </p>
            <p><input type="submit" class="button-primary" value="Unterstützung speichern" /></p>
            <?php break;
        ///////////////////////////
        // PLEDGE INFO METABOX //
        ///////////////////////////
        case 'wdf_pledge_info' :

            $trans = $this->get_transaction($post->ID);


            if(!isset($meta['wdf_native'][0]) || $meta['wdf_native'][0] !== '1') : ?>
                <?php $funders = get_posts(array('post_type' => 'funder', 'numberposts' => -1, 'post_status' => 'publish')); ?>
                <?php if(!$funders) : ?>
                    <div class="error below-h2"><p style="width: 100%;"><?php echo __('Du hast noch keine Spendenaktionen durchgeführt. Du musst eine Spendenaktion erstellen, um eine Zusage zu machen.','wdf') ?></p></div>
                <?php else : ?>
                    <input type="hidden" name="post_title" value="Manual Payment" />
                    <input type="hidden" name="wdf[transaction][status]" value="Manual Payment" />
                    <table class="form-table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php echo __('Wähle die Spendenaktion','wdf') ?></label>
                            </th>
                            <td>
                                <p>
                                    <select name="post_parent">
                                        <?php foreach($funders as $funder) : ?>
                                            <option <?php selected($post->post_parent,$funder->ID); ?> value="<?php echo $funder->ID ?>"><?php echo $funder->post_title; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php _e('Vor & Nachname','wdf'); ?></label>
                            </th>
                            <td>
                                <p><input type="text" name="wdf[transaction][name]" value="<?php echo isset($trans['first_name']) ? $trans['first_name'] : ''; ?><?php echo isset($trans['last_name']) ? ' ' . $trans['last_name'] : ''; ?>" /></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php _e('Email Addresse','wdf'); ?></label>
                            </th>
                            <td>
                                <p><input type="text" name="wdf[transaction][payer_email]" value="<?php echo isset($trans['payer_email']) ? $trans['payer_email'] : ''; ?>" /></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php _e('Spendenbetrag','wdf'); ?></label>
                            </th>
                            <td>
                                <p><input type="text" name="wdf[transaction][gross]" value="<?php echo isset($trans['gross']) ? $trans['gross'] : ''; ?>" /></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php _e('Zahlungsquelle','wdf'); ?>:</label>
                            </th>
                            <td>
                                <select name="wdf[transaction][gateway]">
                                    <?php global $wdf_gateway_plugins; ?>
                                    <?php foreach($wdf_gateway_plugins as $name => $plugin) : ?>
                                        <option value="<?php echo $name; ?>"><?php echo $plugin[1]; ?></option>
                                    <?php endforeach; ?>
                                    <option value="manual"><?php _e('Überweisung/Bargeld','wdf'); ?></option>
                                </select>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                <?php endif; ?>
            <?php else : ?>
                <?php $parent = get_post($post->post_parent); ?>
                <?php if($parent) : ?>
                    <h4><?php _e('Spendenaktion','wdf'); ?>:</h4><p><a href="<?php echo get_edit_post_link($parent->ID); ?>"><?php echo $parent->post_title; ?></a></p>
                <?php else : ?>
                    <?php $donations = get_posts(array('post_type' => 'funder', 'numberposts' => -1, 'post_status' => 'publish')); ?>
                    <p>
                        <?php if(!$donations) : ?>
                            <label><?php echo sprintf( __('Du hast noch keine %s gemacht.','wdf'), esc_attr($settings['funder_labels']['plural_name']) ); ?></label>
                        <?php else : ?>
                            <label><?php echo sprintf( __('Nicht an %s angehängt, bitte wähle eine aus','wdf'), esc_attr($settings['funder_labels']['singular_name']) ); ?></label>
                            <select name="post_parent">
                                <?php foreach($donations as $donation) : ?>
                                    <option value="<?php echo $donation->ID ?>"><?php echo $donation->post_title; ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
                <?php $trans = $this->get_transaction(); ?>
                <h4><?php _e('Von','wdf'); ?>:</h4>
                <p><?php echo $trans['first_name'] . ' ' . $trans['last_name']; ?> | <a href="mailto:<?php echo $trans['payer_email']; ?>"><?php echo $trans['payer_email']; ?></a></p>

                <?php if(isset($trans['address1']) && !empty($trans['address1'])) :?>
                    <h4><?php _e('Addresse','wdf'); ?>:</h4>
                    <p>
                        <?php if(isset($trans['country']) && !empty($trans['country'])) : echo $trans['country'].' ('.__('Land','wdf').')</br>'; endif; ?>
                        <?php if(isset($trans['address1']) && !empty($trans['address1'])) : echo $trans['address1'].' ('.__('Addresse','wdf').')</br>'; endif; ?>
                        <?php if(isset($trans['address2']) && !empty($trans['address2'])) : echo $trans['address2'].' ('.__('Addresse 2','wdf').')</br>'; endif; ?>
                        <?php if(isset($trans['city']) && !empty($trans['city'])) : echo $trans['city'].' ('.__('Stadt','wdf').')</br>'; endif; ?>
                        <?php if(isset($trans['state']) && !empty($trans['state'])) : echo $trans['state'].' ('.__('Bundesland','wdf').')</br>'; endif; ?>
                        <?php if(isset($trans['zip']) && !empty($trans['zip'])) : echo $trans['zip'].' ('.__('Postleitzahl/Postfach','wdf').')</br>'; endif; ?>
                    </p>
                <?php endif; ?>

                <h4><?php _e('Gespendeter Betrag','wdf'); ?>:</h4>
                <?php if(isset($trans['recurring']) && $trans['recurring'] == 1) :?>
                    <p>
                        <?php echo $this->format_currency($trans['currency_code'],$trans['gross']); ?>
                    </p>
                    <h4><?php _e( 'Wiederkehrende Abonnementdetails', 'wdf'); ?>:</h4>
                    <p>
                        <?php
                        $period = $wdf->format_cycle($trans['cycle']);
                        $currency = isset( $trans['currency_code'] ) ? $trans['currency_code'] : '';
                        $recurring_amount = isset( $trans['recurring_amount'] ) ? $trans['recurring_amount'] : '';
                        $detail_text = $this->format_currency( $currency, $recurring_amount ) . ' ' . $period;

                        if( isset( $trans['recurring_transactions'] ) ){
                            $recurring_transactions = $trans['recurring_transactions'] . ' ' . _n( 'Bezahlvorgang abgeschlossen.', 'Zahlungen abgeschlossen.', $trans['recurring_transactions'], 'wdf' );
                            $detail_text .= '<br />' . $recurring_transactions;
                        }

                        echo $detail_text;
                        ?>
                    </p>
                <?php else: ?>
                    <p><?php echo $this->format_currency($trans['currency_code'],$trans['gross']); ?></p>
                <?php endif; ?>

                <?php if(isset($trans['reward'])) : ?>
                    <?php
                    $rewards = get_post_meta($parent->ID,'wdf_levels', true);
                    $reward_description = isset($rewards[$trans['reward']-1]['description']) ? $rewards[$trans['reward']-1]['description'] : '';
                    $reward_limit = isset($rewards[$trans['reward']-1]['limit']) ? $rewards[$trans['reward']-1]['limit'] : 0;
                    $reward_used = isset($rewards[$trans['reward']-1]['used']) ? $rewards[$trans['reward']-1]['used'] : 0;
                    $reward_left = $reward_limit - $reward_used;
                    ?>
                    <h4><?php echo $settings['funder_labels']['singular_level'] ?>:</h4>
                    <p>
                        <?php
                        echo $trans['reward'];
                        echo !empty($reward_description) ? ' - '.$reward_description : '';
                        echo !empty($reward_limit) ? ' ('.$reward_left.'/'.$reward_limit.' '.__('links','wdf').')' : '';
                        ?>
                    </p>
                <?php endif; ?>

                <?php if( isset($trans['gateway_public']) ) : ?><h4><?php _e('Zahlungsquelle','wdf'); ?>:</h4><p><?php echo esc_attr($trans['gateway_public']); ?></p><?php endif; ?>
                <?php if( isset($trans['gateway_msg']) ) : ?><h4><?php _e('Letzte Gateway-Aktivität','wdf'); ?>:</h4><p><?php echo esc_attr($trans['gateway_msg']); ?></p><?php endif; ?>
                <?php if( isset($trans['ipn_id']) ) : ?><h4><?php _e('Transaktions-ID','wdf'); ?>:</h4><p><?php echo esc_attr($trans['ipn_id']); ?></p><?php endif; ?>
            <?php endif; ?>
            <?php break;

        /////////////////////
        // FUNDER PROGRESS //
        /////////////////////
        case 'wdf_progress' : ?>

            <?php if($this->has_goal($post->ID)) : ?>
                <?php if(isset($meta['wdf_goal_start'][0]) && strtotime($meta['wdf_goal_start'][0]) > time()) : ?>
                    <div class="below-h2 updated"><p><?php echo sprintf(__('Deine %s %s','wdf'),esc_attr($settings['funder_labels']['singular_name']), wdf_time_left(false,$post->ID)); ?></p></div>
                <?php endif; ?>
                <?php echo $this->prepare_progress_bar($post->ID,null,null,'admin_metabox',true); ?>
            <?php else : ?>
                <label><?php _e('Bisher erhaltener Betrag','wdf'); ?></label><br /><span class="wdf_bignum"><?php echo $this->format_currency('',$this->get_amount_raised($post->ID)); ?></span>
            <?php endif; ?>

            <?php break;

        /////////////////////////
        // FUNDER TYPE METABOX //
        /////////////////////////
        case 'wdf_type' :
            $settings = get_option('wdf_settings');	?>
            <div id="wdf_type">
                <p style="display:none"><?php _e('Bitte gib den Titel ein und wähle den Fundraising-Typ, den Du verwenden möchtest (diese Option kann nicht geändert werden, nachdem sich jemand verpflichtet hat).','wdf'); ?></p>
                <?php if( isset($settings['payment_types']) && is_array($settings['payment_types']) && count($settings['payment_types']) >= 1 ) : ?>
                    <?php  ?>
                    <?php foreach($settings['payment_types'] as $name) : ?>
                        <?php
                        if($name == 'simple') {
                            $label = __('Einfache Spenden','wdf');
                            $description = __('Ermöglicht eine einfache kontinuierliche Spende ohne Ziele oder Belohnungen','wdf');
                            $description = __('Ermöglicht eine einfache kontinuierliche Spende','wdf');
                        } elseif($name == 'advanced') {
                            $label = __('Erweitertes Crowdfunding','wdf');
                            $description = __('Setze Dir Spendenziele und Belohnungen. Bestätigungen sind nur autorisiert und Zahlungen werden erst bearbeitet, wenn Dein Ziel erreicht ist.','wdf');
                            $description = __('Spenden sind nur autorisiert und Zahlungen werden erst bearbeitet, wenn Dein Ziel erreicht ist.','wdf');
                        } else {
                            $label = '';
                            $description = '';
                        }
                        // Some filters incase your trying to make new available types
                        $label = apply_filters('wdf_funder_type_label', $label, $name);
                        $description = apply_filters('wdf_funder_type_description', $description, $name);
                        ?>
                        <?php // if(!isset($meta['wdf_type'][0]) || empty($meta['wdf_type'][0])) : ?>

                        <?php //if(isset($settings['payment_types']) && count($settings['payment_types']) >= 1 ) : ?>
                        <?php //if(count($settings['payment_types']) > 1) : ?>
                        <p>
                            <label>
                                <input name="wdf[type]" type="radio" value="<?php echo $name; ?>" <?php checked($current_type,$name); ?>/>
                                <?php echo $label; ?>

                            </label>
                            <?php echo $tips->add_tip($description); ?>
                        </p>
                        <?php /*?><?php else : ?>
									<h3>
										<label><span class="description"><?php echo $label; ?></span></label>
										<div style="float:right;"><input name="wdf[type]" type="hidden" value="<?php echo $name; ?>" /><?php echo $tips->add_tip($description); ?></div>
									</h3>
								<?php endif; ?>	<?php */?>
                        <?php //endif; ?>

                        <?php /*?><?php else : // Type Has Been Set ?>
							<?php if($meta['wdf_type'][0] == $name) : //Current Funder Type Matches The Foreach ?>
								<h3>
									<label><span class="description"><?php echo $label; ?></span></label>
									<div style="float:right;"><input name="wdf[type]" type="hidden" value="<?php echo $meta['wdf_type'][0]; ?>" /><?php echo $tips->add_tip($description); ?></div>
								</h3>
							<?php endif; ?><?php */?>

                        <?php //endif; ?>
                    <?php endforeach; ?>
                    <?php if(count($settings['payment_types']) > 1) { ?>
                        <small><?php _e('Diese Option kann nicht geändert werden, nachdem sich jemand verpflichtet hat.','wdf'); ?></small>
                    <?php } else { ?>
                        <small><?php printf(__('<a href="%s">Hier</a> kannst Du "Advanced Crowdfunding" aktivieren.','wdf'), admin_url('edit.php?post_type=funder&page=wdf_settings')); ?></small>
                    <?php } ?>
                    <?php
                    /*
                    <div id="submitdiv">
                        <div class="submitbox" id="submitpost">
                            <div id="minor-publishing">
                                <div id="minor-publishing-actions" style="padding:0px;">
                                    <div id="save-action">
                                        <input type="submit" name="save" style="float:left;" id="save-post" value="<?php _e('Continue','wdf'); ?>" class="button button-primary fundraising-continue-edit" />
                                        <span class="spinner"></span>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    */ ?>

                <?php else : // No Valid Payment Types Available?>
                    <div class="message updated below-h2"><p><?php _e('Es wurden noch keine Zahlungsarten aktiviert.','wdf'); ?></p></div>
                <?php endif; ?>
            </div><!-- #wdf_type -->
            <?php break;

        ////////////////////////////
        // FUNDER OPTIONS METABOX //
        ////////////////////////////
        case 'wdf_options' :
            global $pagenow;
            $settings = get_option('wdf_settings'); ?>
            <?php if($this->get_pledge_list($post->ID)) : ?>
            <h4><?php _e('Typ : ','wdf'); ?><?php echo ($current_type == 'advanced' ? __('Erweitertes Crowdfunding','wdf') : __('Einfache Spenden','wdf') ); ?></h4>
            <?php endif; ?>
            <?php if($settings['single_styles'] == 'yes') : ?>
            <div id="wdf_style">
                <p>
                    <label><?php echo __('Wähle einen Anzeigestil','wdf'); ?>
                        <select name="wdf[style]">
                            <?php if(is_array($this->styles) && !empty($this->styles)) : ?>
                                <?php foreach($this->styles as $key => $label) : ?>
                                    <option <?php (isset($meta['wdf_style'][0]) ? selected($meta['wdf_style'][0],$key) : ''); ?> value="<?php echo $key ?>"><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select></label>
                </p>
            </div>
            <?php endif; ?>
            <?php if (isset($settings['active_gateways']['paypal']) && $settings['active_gateways']['paypal']) : ?>
                <p id="wdf_recurring"><label><?php _e('Wiederkehrende Spenden zulassen?', 'wdf') ?>
                    <select name="wdf[recurring]" rel="wdf_recurring" class="wdf_toggle">
                        <option value="yes" <?php (isset($meta['wdf_recurring'][0]) ? selected($meta['wdf_recurring'][0], 'yes') : ''); ?>><?php _e('Ja', 'wdf'); ?></option>
                        <option value="no" <?php (isset($meta['wdf_recurring'][0]) ? selected($meta['wdf_recurring'][0], 'no') : ''); ?>><?php _e('Nein', 'wdf'); ?></option>
                    </select>
                </label>
                </p>
            <?php endif; ?>
            <p>
                <label><?php _e('Panel Position','wdf') ?>
                    <select name="wdf[panel_pos]">
                        <option value="top" <?php (isset($meta['wdf_panel_pos'][0]) ? selected($meta['wdf_panel_pos'][0],'top') : ''); ?>><?php _e('Über dem Inhalt','wdf'); ?></option>
                        <option value="bottom" <?php (isset($meta['wdf_panel_pos'][0]) ? selected($meta['wdf_panel_pos'][0],'bottom') : ''); ?>><?php _e('Unter dem Inhalt','wdf'); ?></option>
                        <option value="hide" <?php (isset($meta['wdf_panel_pos'][0]) ? selected($meta['wdf_panel_pos'][0],'hide') : ''); ?>><?php _e('Ausblenden','wdf'); ?></option>
                    </select>
                </label><?php echo $tips->add_tip(__('Wenn Du das Seitenleisten-Widget "Fundraiser" nicht verwendest, wähle die Position Deines Infofensters.','wdf')); ?>
            </p>
            <?php if($settings['single_checkout_type'] == '1') : ?>
            <p>
                <label><span class="description"><?php _e('Checkout Typ','wdf') ?></span>
                    <select name="wdf[checkout_type]">
                        <option value="1" <?php (isset($meta['wdf_checkout_type'][0]) ? selected($meta['wdf_checkout_type'][0],'1') : ''); ?>><?php _e('Checkout direkt vom Panel','wdf'); ?></option>
                        <option value="2" <?php (isset($meta['wdf_checkout_type'][0]) ? selected($meta['wdf_checkout_type'][0],'2') : ''); ?>><?php _e('Verwende eigene Checkout-Seite','wdf'); ?></option>
                    </select>
                </label>
            </p>
            <?php endif; ?>

        <?php break;

        //////////////////////////
        // FUNDER GOALS METABOX //
        //////////////////////////
        case 'wdf_goals' :
            $settings = get_option('wdf_settings');

            if($current_type == 'advanced' && $post->post_status == 'publish' && $this->get_pledge_list($post->ID) != false)
                $disabled = 'disabled="disabled"';
            else
                $disabled = '';
        ?>

        <script type="text/javascript">
            jQuery(document).ready( function($) {
                $('input#publish').on( 'click', function(e) {
                    var has_goal = $('select#wdf_has_goal option:selected').val();
                    var start_date = $('input#wdf_goal_start_date').val();
                    var end_date = $('input#wdf_goal_end_date').val();
                    var goal_amount = $('input#wdf_goal_amount').val();
                    var type = $('input[name="wdf[type]"]:checked', '#wdf_type').val();

                    if(has_goal == '1') {
                        if(start_date == '' || typeof start_date == 'undefined') {
                            alert("<?php _e('Du musst ein Startdatum festlegen','wdf'); ?>");
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            return false;
                        } else if(end_date == '' || typeof start_date == 'undefined') {
                            alert("<?php _e('Du musst ein Enddatum festlegen, das nach dem aktuellen Datum liegt','wdf'); ?>");
                            e.preventDefault();
                            e.stopImmediatePropagation()
                            return false;
                        }  else if( goal_amount == '' || typeof goal_amount == 'undefined' || parseInt(goal_amount) < 1  ) {
                            alert("<?php _e('Du musst einen Zielbetrag festlegen, der größer als mindestens 1 ist','wdf'); ?>");
                            e.preventDefault();
                            e.stopImmediatePropagation()
                            return false;
                        }
                    }
                });
            });
        </script>
        
        <?php if($disabled != '') : ?>
            <div class="below-h2 updated"><p><?php _e('Deine Spendentermine, Ziele und Belohnungen sind festgelegt.','wdf'); ?></p></div>
        <?php endif; ?>
            <div id="wdf_funder_goals">
                <?php //if( in_array('advanced', $settings['payment_types']) || in_array('standard', $settings['payment_types']) ) : ?>
                <h4><?php echo __('Ein Crowdfunding-Ziel erstellen?','wdf'); ?>
                    <input type="hidden" name="wdf[has_goal]" value="1" />
                    <select class="wdf_toggle" id="wdf_has_goal" rel="wdf_has_goal" name="wdf[has_goal]" <?php echo $current_type == 'advanced' ? ' disabled' : ''; ?>>
                        <option <?php (isset($meta['wdf_has_goal'][0]) ? selected($meta['wdf_has_goal'][0],'0') : ''); ?> value="0"><?php _e('Nein','wdf'); ?></option>
                        <option <?php (isset($meta['wdf_has_goal'][0]) ? selected($meta['wdf_has_goal'][0],'1') : '');  ?> value="1"><?php _e('Ja','wdf'); ?></option>
                    </select>
                    <?php echo $tips->add_tip(__('Das Ziel ist optional für "Einfache Spenden" und für "Erweitertes Crowdfunding" erforderlich.','wdf')); ?>
                </h4>
            </div>
            <div rel="wdf_has_goal" <?php echo (isset($meta['wdf_has_goal'][0]) && $meta['wdf_has_goal'][0] == '1' ? '' : 'style="display:none"') ?>>
                <?php /*?><input type="hidden" name="wdf[show_progress]" value="0" />
						<p><label><input type="checkbox" name="wdf[show_progress]" value="1" <?php checked($meta['wdf_show_progress'][0],'1'); ?> /> <?php echo __('Show Progress Bar','wdf') ?></label></p><?php */?>

                <table class="widefat">
                    <thead>
                    <tr>
                        <th class="wdf_goal_start_date"><?php _e('Anfangsdatum','wdf') ?></th>
                        <th class="wdf_goal_end_date"><?php _e('Enddatum','wdf') ?></th>
                        <th class="wdf_goal_amount" align="right"><?php _e('Zielbetrag','wdf') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>

                        <td class="wdf_goal_start_date">
                            <input <?php echo $disabled; ?> id="wdf_goal_start_date" style="background-image: url(<?php echo admin_url('images/date-button.gif'); ?>);" type="text" name="wdf[goal_start]" class="wdf_biginput" value="<?php echo (isset($meta['wdf_goal_start'][0]) ? $meta['wdf_goal_start'][0] : ''); ?>" />
                        </td>
                        <td class="wdf_goal_end_date">
                            <input <?php echo $disabled; ?> id="wdf_goal_end_date" style="background-image: url(<?php echo admin_url('images/date-button.gif'); ?>);" type="text" name="wdf[goal_end]" class="wdf_biginput" value="<?php echo (isset($meta['wdf_goal_end'][0]) ? $meta['wdf_goal_end'][0] : ''); ?>" />
                        </td>
                        <td class="wdf_goal_amount">
                            <?php echo ( (isset($settings['curr_symbol_position'])) && $settings['curr_symbol_position'] == 1 || $settings['curr_symbol_position'] == 2 ? '<span class="wdf_bignum wdf_disabled">'.$this->format_currency().'</span>' : ''); ?>
                            <input <?php echo $disabled; ?> id="wdf_goal_amount" type="text" name="wdf[goal_amount]" class="wdf_input_switch active wdf_biginput wdf_bignum" value="<?php echo (isset($meta['wdf_goal_amount'][0]) ? $this->filter_price($meta['wdf_goal_amount'][0]) : '') ?>" />
                            <?php echo ($settings['curr_symbol_position'] == 3 || $settings['curr_symbol_position'] == 4 ? '<span class="wdf_bignum wdf_disabled">'.$this->format_currency().'</span>' : ''); ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <h4>
                <?php echo sprintf(__('Erstelle %s','wdf'), esc_attr($settings['funder_labels']['plural_level'])); ?>?
                <select <?php echo $disabled; ?> class="wdf_toggle" rel="wdf_has_reward" name="wdf[has_reward]">
                    <option <?php (isset($meta['wdf_has_reward'][0]) ? selected($meta['wdf_has_reward'][0],'0') : ''); ?> value="0"><?php _e('Nein','wdf'); ?></option>
                    <option <?php (isset($meta['wdf_has_reward'][0]) ? selected($meta['wdf_has_reward'][0],'1') : ''); ?> value="1"><?php _e('Ja','wdf'); ?></option>
                </select>
                <?php echo $tips->add_tip(sprintf(__('Dadurch wird %s aktiviert. Du kannst den erforderlichen Mindestbetrag auswählen, den verfügbaren Betrag begrenzen und eine Beschreibung für jeden %s hinzufügen.','wdf'), esc_attr($settings['funder_labels']['plural_level']), esc_attr($settings['funder_labels']['singular_level']))); ?>
            </h4>
            <div id="wdf_has_reward" rel="wdf_has_reward" <?php echo (isset($meta['wdf_has_reward'][0]) && $meta['wdf_has_reward'][0] == '1' ? '' : 'style="display:none"') ?>>
                <table id="wdf_levels_table" class="widefat">
                    <thead>
                    <tr>
                        <th class="wdf_level_amount"><?php echo __('Wähle Betrag','wdf'); ?></th>
                        <th class="wdf_level_limit"><?php echo __('Wähle Limit','wdf'); ?></th>
                        <th class="wdf_level_description"><?php echo sprintf(__('%s Beschreibung','wdf'), esc_attr($settings['funder_labels']['singular_level'])); ?></th>
                        <th class="delete" align="right"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if(isset($meta['wdf_levels']) && is_array($meta['wdf_levels'])) :
                        $level_count = count($meta['wdf_levels']);
                        $i = 1;

                        foreach($meta['wdf_levels'] as $level) :
                            $level = maybe_unserialize($level);
                            foreach($level as $index => $data) : ?>
                                <tr class="wdf_level <?php echo ($level_count == $i ? 'last' : ''); ?>">
                                    <td class="wdf_level_amount">
                                        <?php echo ($settings['curr_symbol_position'] == 1 || $settings['curr_symbol_position'] == 2 ? '<span class="wdf_bignum wdf_disabled">'.$this->format_currency().'</span>' : ''); ?>
                                        <input <?php echo $disabled; ?> class="wdf_input_switch active wdf_biginput wdf_bignum" type="text" name="wdf[levels][<?php echo $index ?>][amount]" value="<?php echo (isset($data['amount']) ? $this->filter_price($data['amount']) : '' ); ?>" />
                                        <?php echo ($settings['curr_symbol_position'] == 3 || $settings['curr_symbol_position'] == 4 ? '<span class="wdf_bignum wdf_disabled">'.$this->format_currency().'</span>' : ''); ?>
                                    </td>
                                    <td class="wdf_level_limit">
                                        <input <?php echo $disabled; ?> class="wdf_input_switch active wdf_biginput wdf_bignum" type="text" name="wdf[levels][<?php echo $index ?>][limit]" value="<?php echo (isset($data['limit']) ? $data['limit'] : '' ); ?>" /></br>
                                        <input <?php echo $disabled; ?> class="wdf_input_switch active wdf_biginput wdf_bignum" type="hidden" name="wdf[levels][<?php echo $index ?>][used]" value="<?php echo (isset($data['used']) ? $data['used'] : 0 ); ?>" />
                                        <?php
                                        if(isset($data['limit']) && is_numeric($data['limit'])) :
                                            $reward_left = $data['limit'] - (isset($data['used']) ? $data['used'] : 0);
                                            ?>
                                            <p><?php echo $reward_left.'/'.$data['limit'].' '.__('links','wdf'); ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="wdf_level_description"><textarea <?php echo $disabled; ?> class="wdf_input_switch active " name="wdf[levels][<?php echo $index ?>][description]"><?php echo (isset($data['description']) ? $data['description'] : '') ?></textarea></td>
                                    <td class="delete">
                                        <?php if($disabled == false) : ?>
                                            <a href="#"><span style="background-image: url(<?php echo admin_url('images/xit.gif'); ?>);" class="wdf_ico_del"></span><?php _e('Löschen','wdf'); ?></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                                $i++;
                            endforeach;
                        endforeach;
                    else : ?>
                        <tr class="wdf_level last">
                            <td class="wdf_level_amount">
                                <?php echo ($settings['curr_symbol_position'] == 1 || $settings['curr_symbol_position'] == 2 ? '<span class="wdf_bignum wdf_disabled">'.$this->format_currency().'</span>' : ''); ?>
                                <input class="wdf_input_switch wdf_biginput wdf_bignum" type="text" name="wdf[levels][0][amount]" value="" />
                                <?php echo ($settings['curr_symbol_position'] == 3 || $settings['curr_symbol_position'] == 4 ? '<span class="wdf_bignum wdf_disabled">'.$this->format_currency().'</span>' : ''); ?>
                            </td>
                            <?php /*?><td class="wdf_level_title"><input class="wdf_input_switch wdf_biginput wdf_bignum" type="text" name="wdf[levels][0][title]" value="" /></td><?php */?>
                            <td class="wdf_level_limit">
                                <input <?php echo $disabled; ?> class="wdf_input_switch active wdf_biginput wdf_bignum" type="text" name="wdf[levels][0][limit]" value="" />
                            </td>
                            <td class="wdf_level_description"><textarea class="wdf_input_switch" name="wdf[levels][0][description]"><?php //echo __('Add a description for this level','wdf'); ?></textarea></td>
                            <?php /*?><td class="wdf_level_reward"><input class="wdf_check_switch" type="checkbox" name="wdf[levels][0][reward]" value="1" /></td><?php */?>
                            <td class="delete">
                                <?php if($disabled == false) : ?>
                                    <a href="#"><span style="background-image: url(<?php echo admin_url('images/xit.gif'); ?>);" class="wdf_ico_del"></span><?php _e('Löschen','wdf'); ?></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php
                    endif;
                    ?>
                    <tr rel="wdf_level_template" style="display:none">
                        <td class="wdf_level_amount">
                            <?php echo ($settings['curr_symbol_position'] == 1 || $settings['curr_symbol_position'] == 2 ? '<span class="wdf_bignum wdf_disabled">'.$this->format_currency().'</span>' : ''); ?>
                            <input class="wdf_input_switch active wdf_biginput wdf_bignum" type="text" rel="wdf[levels][][amount]" value="" />
                            <?php echo ($settings['curr_symbol_position'] == 3 || $settings['curr_symbol_position'] == 4 ? '<span class="wdf_bignum wdf_disabled">'.$this->format_currency().'</span>' : ''); ?>
                        </td>
                        <td class="wdf_level_limit">
                            <input <?php echo $disabled; ?> class="wdf_input_switch active wdf_biginput wdf_bignum" type="text"  rel="wdf[levels][][limit]" value="" />
                        </td>
                        <?php /*?><td class="wdf_level_title"><input class="wdf_input_switch active wdf_biginput wdf_bignum" type="text" rel="wdf[levels][][title]" value="" /></td><?php */?>
                        <td class="wdf_level_description"><textarea class="wdf_input_switch active" rel="wdf[levels][][description]"></textarea></td>
                        <?php /*?><td class="wdf_level_reward"><input class="wdf_check_switch" type="checkbox" rel="wdf[levels][][reward]" value="1" /></td><?php */?>
                        <td class="delete"><a href="#"><span style="background-image: url(<?php echo admin_url('images/xit.gif'); ?>);" class="wdf_ico_del"></span><?php _e('Löschen','wdf'); ?></a></td>
                    </tr>
                    <?php if($disabled == false) : ?>
                        <tr><td colspan="3" align="right"><a href="#" id="wdf_add_level"><?php echo sprintf(__('Füge %s hinzu','wdf'), esc_attr($settings['funder_labels']['singular_level'])); ?></a></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div><!-- #wdf_has_reward -->
            <h4><?php echo sprintf(__('Adresse sammeln','wdf'), esc_attr($settings['funder_labels']['plural_level'])); ?>?
                <select <?php echo $disabled; ?> class="wdf_toggle" rel="wdf_collect_address" name="wdf[collect_address]">
                    <option <?php (isset($meta['wdf_collect_address'][0]) ? selected($meta['wdf_collect_address'][0],'0') : ''); ?> value="0"><?php _e('Nein','wdf'); ?></option>
                    <option <?php (isset($meta['wdf_collect_address'][0]) ? selected($meta['wdf_collect_address'][0],'1') : ''); ?> value="1"><?php _e('Ja','wdf'); ?></option>
                </select>
                <?php echo $tips->add_tip(__('Auf diese Weise kannst Du zusätzliche Informationen wie Adresse, Stadt, Bundesland und Postleitzahl sammeln. Dadurch wird eine ausgearbeitete Checkout-Seite erzwungen.','wdf')); ?>
            </h4>
            <div id="wdf_collect_address_message_holder" rel="wdf_collect_address" <?php echo (isset($meta['wdf_collect_address'][0]) && $meta['wdf_collect_address'][0] == '1' ? '' : 'style="display:none"') ?>>
                <label><?php echo __('Füge während der Spende eine Nachricht hinzu, die den Zweck des Sammelns der Adresse beschreibt. Zum Deaktivieren leer lassen.','wdf'); ?></label><br />
                <textarea id="wdf_collect_address_message" name="wdf[collect_address_message]"><?php echo (isset($meta['wdf_collect_address_message'][0]) ? urldecode(wp_kses_post($meta['wdf_collect_address_message'][0])) : ''); ?></textarea>
                <label for=""><?php _e('Lasse das Feld "Land" zu, während Du die Adresse erfasst','wdf'); ?>?
                    <select <?php echo $disabled; ?> class="wdf_toggle" rel="wdf_collect_address_country" name="wdf[collect_address_country]">
                        <option <?php (isset($meta['wdf_collect_address_country'][0]) ? selected($meta['wdf_collect_address_country'][0],'0') : ''); ?> value="0"><?php _e('Nein','wdf'); ?></option>
                        <option <?php (isset($meta['wdf_collect_address_country'][0]) ? selected($meta['wdf_collect_address_country'][0],'1') : ''); ?> value="1"><?php _e('Ja','wdf'); ?></option>
                    </select>
                </label>
            </div>

            <?php break;

        ////////////////////
        // LEVELS METABOX //
        ////////////////////
        case 'wdf_levels' : ?>
            <?php $settings = get_option('wdf_settings'); ?>

            <?php break;

        //////////////////////
        // ACTIVITY METABOX //
        //////////////////////
        case 'wdf_activity' : ?>
            <?php $donations = $this->get_pledge_list($post->ID); ?>
            <table class="widefat">
                <thead>
                <tr>
                    <th><?php _e('Betrag','wdf'); ?>:</th>
                    <th><?php _e('Status','wdf'); ?>:</th>
                    <th><?php echo esc_attr($settings['donation_labels']['backer_single']) ?>:</th>
                    <th><?php _e('Methode','wdf'); ?>:</th>
                    <th><?php _e('Datum','wdf'); ?>:</th>
                    <th class="wdf_actvity_edit"><br /></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($donations as $donation) : ?>
                    <?php $trans = $this->get_transaction($donation->ID); ?>
                    <tr class="wdf_actvity_level">
                        <td><?php echo $this->format_currency('',$trans['gross']); ?></td>
                        <td><?php echo $trans['status']; ?></td>
                        <td><label><?php echo $trans['first_name'].' '.$trans['last_name']; ?></label><br /><a href="mailto:<?php echo $trans['payer_email']; ?>"><?php echo $trans['payer_email']; ?></a></</td>
                        <td><?php echo $trans['gateway']; ?></td>
                        <td><?php echo get_post_modified_time('F d Y', null, $donation->ID) ?></td>
                        <td><a class="hidden" href="<?php echo get_edit_post_link($donation->ID); ?>">Details anzeigen</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php break;

        //////////////////////
        // MESSAGES METABOX //
        //////////////////////
        case 'wdf_messages' :
            $settings = get_option('wdf_settings');
            ?>
            <?php /*?><label id="wdf_thanks_type"><?php echo __('Thank You Message','wdf'); ?>
			<select class="wdf_toggle" rel="wdf_thanks_type" name="wdf[thanks_type]">
				<option <?php selected($meta['wdf_thanks_type'][0],'custom'); ?> value="custom"><?php echo __('Custom Thank You Message','wdf'); ?></option>
				<option <?php selected($meta['wdf_thanks_type'][0],'post'); ?> value="post"><?php echo __('Use A Post or Page ID','wdf'); ?></option>
				<option <?php selected($meta['wdf_thanks_type'][0],'url'); ?> value="url"><?php echo __('Use A Custom URL','wdf'); ?></option>
			</select></label><?php */?>
            <p<?php //echo ($meta['wdf_thanks_type'][0] == 'custom' || $pagenow == 'post-new.php' ? 'style="display: block;"' : ''); ?> rel="wdf_thanks_type" class="wdf_thanks_custom">
                <label><?php echo __('Text oder HTML erlaubt','wdf'); ?><?php echo $tips->add_tip(__('Stelle eine benutzerdefinierte Dankesnachricht für Benutzer bereit. Du kannst die folgenden Codes verwenden, um bestimmte Informationen aus der Zahlung anzuzeigen: %DONATIONTOTAL% %FIRSTNAME% %LASTNAME%', 'wdf')); ?></label><br />
                <textarea id="wdf_thanks_custom" name="wdf[thanks_custom]"><?php echo (isset($meta['wdf_thanks_custom'][0]) ? urldecode(wp_kses_post($meta['wdf_thanks_custom'][0])) : ''); ?></textarea>
            </p>
            <?php /*?><p <?php echo ($meta['wdf_thanks_type'][0] == 'post' ? 'style="display: block;"' : 'style="display: none;"'); ?> rel="wdf_thanks_type" class="wdf_thanks_post">
				<?php do_action('wdf_error_thanks_post');?>
				<label><?php echo __('Insert A Post or Page ID','wdf'); ?><input type="text" name="wdf[thanks_post]" value="<?php echo $meta['wdf_thanks_post'][0]; ?>" /></label>
			</p>
			<p <?php echo ($meta['wdf_thanks_type'][0] == 'url' ? 'style="display: block;"' : 'style="display: none;"'); ?> rel="wdf_thanks_type" class="wdf_thanks_url">
				<label><?php echo __('Insert A Custom URL','wdf'); ?><input type="text" name="wdf[thanks_url]" value="<?php echo $meta['wdf_thanks_url'][0]; ?>" /></label>
			</p><?php */?>

            <h4>
                <?php echo __('Nach Zahlungseingang eine Bestätigungs-E-Mail senden?','wdf'); ?>
                <select class="wdf_toggle" rel="wdf_send_email" name="wdf[send_email]" id="wdf_send_email">
                    <option value="0" <?php (isset($meta['wdf_send_email'][0]) ? selected($meta['wdf_send_email'][0],'0') : ''); ?>><?php _e('Nein','wdf'); ?></option>
                    <option value="1" <?php (isset($meta['wdf_send_email'][0]) ? selected($meta['wdf_send_email'][0],'1') : ''); ?>><?php _e('Ja','wdf'); ?></option>
                </select>
            </h4>

            <div <?php echo (isset($meta['wdf_send_email'][0]) && $meta['wdf_send_email'][0] == '1' ? '' : 'style="display: none;"');?> rel="wdf_send_email">
                <label><?php echo __('Erstelle eine benutzerdefinierte E-Mail-Nachricht oder verwende die Standardnachricht.','wdf'); ?></label><?php $tips->add_tip(__('Die E-Mail kommt von Deiner Administrator-E-Mail','wdf').' <strong>'.get_bloginfo('admin_email').'</strong>')?><br />
                <p><label><?php echo __('E-Mail Betreff','wdf'); ?></label><br />
                    <input class="regular-text" type="text" name="wdf[email_subject]" value="<?php echo (isset($meta['wdf_email_subject'][0]) ? $meta['wdf_email_subject'][0] : __('Vielen Dank für Deine Unterstützung', 'wdf')); ?>" /></p>
                <p><textarea id="wdf_email_msg" name="wdf[email_msg]"><?php echo (isset($meta['wdf_email_msg'][0]) ? $meta['wdf_email_msg'][0] : esc_textarea($settings['default_email'])); ?></textarea></p>
            </div>
            <?php break;
    }
}