<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}?>
<?php

class WDF_Simple_Donation extends WP_Widget {
	
	/**
     * @var		string	$translation_domain	Translation domain
     */
	
	function __construct() {
		$settings = get_option('wdf_settings');
		$title = sprintf(__('%s Schaltfläche','wdf'),esc_attr($settings['donation_labels']['singular_name']));
		// Instantiate the parent object
		parent::__construct( false, $title, array(
			'description' =>  sprintf(__('Erstelle eine einfache Schaltfläche zum annehmen von %s','wdf'),esc_attr($settings['donation_labels']['singular_name']))
		) );
	}

	function widget( $args, $instance ) {
		$defaults = array(
			'title' => '',
			'description' => '',
			'thankyou_msg' => '',
			'style' => '',
			'button_type' => 'default',
			'button_text' => '',
			'allow_note' => '',
			'ref_label' => '',
			'donation_amount' => '',
			'paypal_email' => ''
		);
		$instance = wp_parse_args( (array)$instance, $defaults );
		
		$content = $args['before_widget'];
		if(isset($instance['title']) && !empty($instance['title']))
			$content .= $args['before_title'] . esc_attr(apply_filters('widget_title', $instance['title'])) . $args['after_title'];
		$content .= '<p class="wdf_widget_description">' . esc_attr($instance['description']) . '</p>';
		$content .= wdf_pledge_button(false,'widget_simple_donate',null,array('widget_args' => $instance));
		$content .= $args['after_widget'];
		echo $content;
	}

	function update( $new_instance, $old_instance ) {
		
		$instance = $old_instance;
		$instance['title'] = esc_attr($new_instance['title']);
		$instance['description'] = esc_attr($new_instance['description']);
		$instance['thankyou_msg'] = esc_textarea($new_instance['thankyou_msg']);
		$instance['style'] = esc_attr($new_instance['style']);
		$instance['button_type'] = esc_attr($new_instance['button_type']);
		$instance['button_text'] = esc_attr($new_instance['button_text']);
		$instance['allow_note'] = esc_attr($new_instance['allow_note']);
		$instance['ref_label'] = esc_attr($new_instance['ref_label']);
		
		if($new_instance['donation_amount'] == '')
			unset($instance['donation_amount']);
		else
			$instance['donation_amount'] = round(preg_replace("/[^0-9.]/", "", $new_instance['donation_amount']), 2);
			
		if($new_instance['paypal_email'] == '')
			unset($instance['paypal_email']);
		else
			$instance['paypal_email'] = is_email($new_instance['paypal_email']);
		
		return $instance;
	}

	function form( $instance ) {
		if ( !is_array($instance) ) {
			$instance = array();
		}
		$defaults = array(
			'title' => '',
			'description' => '',
			'thankyou_msg' => '',
			'style' => '',
			'button_type' => 'default',
			'button_text' => '',
			'allow_note' => '',
			'ref_label' => '',
			'donation_amount' => '',
			'paypal_email' => ''
		);
		$instance = wp_parse_args( (array)$instance, $defaults );
		$settings = get_option('wdf_settings');
		global $wdf;
		?>
		<p>
			<span for="<?php echo $this->get_field_id( 'button_type' ); ?>"><?php _e('Schaltflächentyp','wdf'); ?></span><br/>
			<label for="<?php echo $this->get_field_id( 'button_type_a' ); ?>">
			<input id="<?php echo $this->get_field_id( 'button_type_a' ); ?>" class="autosave_widget" type="radio" name="<?php echo $this->get_field_name('button_type'); ?>" value="default" <?php if(isset($instance['button_type'])) checked($instance['button_type'],'default'); ?> /><?php _e('Standard PayPal Button','wdf'); ?></label> <br />
			<label for="<?php echo $this->get_field_id( 'button_type_b' ); ?>">
			<input id="<?php echo $this->get_field_id( 'button_type_b' ); ?>" class="autosave_widget" type="radio" name="<?php echo $this->get_field_name('button_type'); ?>" value="custom" <?php if(isset($instance['button_type'])) checked($instance['button_type'],'custom'); ?> /><?php _e('Benutzerdefinierte Schaltfläche','wdf'); ?></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Titel','wdf') ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" type="text" class="widefat" name="<?php echo $this->get_field_name('title'); ?>" value="<?php if(isset($instance['title'])) echo esc_attr($instance['title']); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'description' ); ?>"><?php _e('Beschreibung','wdf') ?></label>
			<textarea id="<?php echo $this->get_field_id( 'description' ); ?>" class="widefat" name="<?php echo $this->get_field_name('description'); ?>"><?php if(isset($instance['description']))  echo esc_attr($instance['description']); ?></textarea>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'thankyou_msg' ); ?>"><?php _e('Danke-Nachricht (nach erfolgreicher Spende)','wdf'); ?></label>
			<textarea id="<?php echo $this->get_field_id( 'thankyou_msg' ); ?>" class="widefat" name="<?php echo $this->get_field_name('thankyou_msg'); ?>"><?php if(isset($instance['thankyou_msg'])) echo esc_textarea($instance['thankyou_msg']); ?></textarea>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'donation_amount' ); ?>"><?php _e('Spendenbetrag (leer = Auswahl)','wdf') ?></label><br/>
			<input id="<?php echo $this->get_field_id( 'donation_amount' ); ?>" type="text" name="<?php echo $this->get_field_name('donation_amount'); ?>" value="<?php echo ($instance['donation_amount'] == '' ? '' : $wdf->filter_price($instance['donation_amount'])); ?>" />
		</p>
		
			<p>
				<label for="<?php echo $this->get_field_id( 'style' ); ?>"><?php echo __('Wähle einen Anzeigestil','wdf'); ?></label>
				<select id="<?php echo $this->get_field_id( 'style' ); ?>" name="<?php echo $this->get_field_name('style'); ?>">
					<option <?php if(isset($instance['style'])) selected($instance['style'],'wdf_default'); ?> value="wdf_default"><?php _e('Basis','wdf'); ?></option>
					<option <?php if(isset($instance['style'])) selected($instance['style'],'wdf_dark'); ?> value="wdf_dark"><?php _e('Dunkel','wdf'); ?></option>
					<option <?php if(isset($instance['style'])) selected($instance['style'],'wdf_fresh'); ?> value="wdf_fresh"><?php _e('Frisch','wdf'); ?></option>
					<option <?php if(isset($instance['style'])) selected($instance['style'],'wdf_note'); ?> value="wdf_note"><?php _e('Notiz','wdf'); ?></option>
					<option <?php if(isset($instance['style'])) selected($instance['style'],'wdf_custom'); ?> value="custom"><?php _e('Keiner (benutzerdefiniertes CSS)','wdf'); ?></option>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'button_text' ); ?>"><?php _e('Spende Button Text','wdf'); ?></label>
				<input id="<?php echo $this->get_field_id( 'button_text' ); ?>" type="text" class="widefat" name="<?php echo $this->get_field_name('button_text'); ?>" value="<?php if(isset($instance['button_text'])) echo esc_attr($instance['button_text']); ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'allow_note' ); ?>"><?php _e('Zusätzliches Notizfeld zulassen','wdf'); ?></label>
				<input id="<?php echo $this->get_field_id( 'allow_note' ); ?>" type="checkbox" name="<?php echo $this->get_field_name('allow_note'); ?>" value="yes" <?php checked($instance['allow_note'],'yes'); ?> /><br/>
			</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'ref_label' ); ?>"><?php _e('Referenzetikett (optionale Beschreibung)','wdf') ?></label><br />
			<input id="<?php echo $this->get_field_id( 'ref_label' ); ?>" class="widefat" type="text" name="<?php echo $this->get_field_name( 'ref_label' ); ?>" value="<?php if(isset($instance['ref_label'])) echo esc_attr($instance['ref_label']); ?>" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'paypal_email' ); ?>"><?php _e('Überschreibe die PayPal-E-Mail-Adresse','wdf') ?></label><br />
			<span class="code"><?php echo $settings['paypal_email']; ?></span><br />
			<input id="<?php echo $this->get_field_id( 'paypal_email' ); ?>" class="widefat" type="text" name="<?php echo $this->get_field_name( 'paypal_email' ); ?>" value="<?php if(isset($instance['paypal_email'])) echo is_email($instance['paypal_email']); ?>" />
		</p>
		<?php
	}
}
?>