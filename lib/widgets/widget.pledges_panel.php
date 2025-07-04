<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}?>
<?php

class WDF_Pledges_Panel extends WP_Widget {
	
	/**
     * @var		string	$translation_domain	Translation domain
     */
	
	function __construct() {
		// Instantiate the parent object
		$settings = get_option('wdf_settings');
		parent::__construct( 'wdf_pledges_panel', sprintf(__('%s Panel','wdf'),esc_attr($settings['donation_labels']['plural_name'])), array(
			'description' =>  sprintf(__('Wenn es sich bei der aktuellen Seite um eine einzelne %1$s-Seite handelt, werden in diesem Bereich die letzten %2$s für die %1$s angezeigt. Du kannst es auch verwenden, um Informationen für bestimmte %1$s oder alle anzuzeigen','wdf'),esc_attr($settings['funder_labels']['singular_name']),esc_attr($settings['donation_labels']['plural_name']))
		) );
	}

	function widget( $args, $instance ) {
		// Widget output
		global $wp_query, $wdf;

	    // Standardwerte setzen
		$defaults = array(
			'title' => '',
			'number_pledges' => '',
			'style' => '',
			'single_fundraiser' => '0',
			'funder' => '',
			'sort_type' => ''
		);
		$instance = wp_parse_args( $instance, $defaults );

		if($instance['single_fundraiser'] == '1') {
			$donations = $wdf->get_pledge_list($instance['funder']);
			if($donations) {
				// Specific Single Fundraiser
				$content = $args['before_widget'];

				if(isset($instance['title']) && !empty($instance['title']))
					$content .= $args['before_title'] . esc_attr(apply_filters('widget_title', $instance['title'])) . $args['after_title'];

				$content .= wdf_pledges_panel( false, $instance['funder'], 'widget', $instance );
				$content .= $args['after_widget'];
				echo $content;
			}
		} else {
			if(
				isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] == 'funder'
				&& $wp_query->is_single
				&& (!isset($wp_query->query_vars['funder_checkout']) || $wp_query->query_vars['funder_checkout'] != '1')
				&& (!isset($wp_query->query_vars['funder_confirm']) || $wp_query->query_vars['funder_confirm'] != '1')
			) {
				$donations = $wdf->get_pledge_list($wp_query->posts[0]->ID);
				if($donations) {
					// Single Fundraiser Page
					$content = $args['before_widget'];

					if(isset($instance['title']) && !empty($instance['title']))
						$content .= $args['before_title'] . esc_attr(apply_filters('widget_title', $instance['title'])) . $args['after_title'];

					$content .= wdf_pledges_panel( false, $wp_query->posts[0]->ID, 'widget', $instance );
					$content .= $args['after_widget'];
					echo $content;
				}
			}
		}
	}

	function update( $new_instance, $old_instance ) {
		
		$instance = $old_instance;
		$instance['title'] = esc_attr($new_instance['title']);
		$instance['sort_type'] = esc_attr($new_instance['sort_type']);
		$instance['number_pledges'] = absint($new_instance['number_pledges']);
		$instance['single_fundraiser'] = esc_attr($new_instance['single_fundraiser']);
		$instance['funder'] = esc_attr( absint($new_instance['funder']) );
		
		return $instance;
	}

	function form( $instance ) {		
		global $wdf;
		$settings = get_option('wdf_settings');

		$instance_defaults = array( 'title', 'number_pledges', 'style', 'single_fundraiser', 'funder', 'sort_type' );
		foreach($instance_defaults as $instance_default)
			if(!isset($instance[$instance_default]))
				$instance[$instance_default] = '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Titel','wdf'); ?></label><br />
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" type="text" name="<?php echo $this->get_field_name('title'); ?>" class="widefat" value="<?php echo (isset($instance['title']) ? $instance['title'] : __('Recent Fundraisers','wdf')); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number_pledges' ); ?>"><?php printf(__('Anzahl der anzuzeigenden %s','wdf'),esc_attr($settings['donation_labels']['singular_name']) ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number_pledges' ); ?>" type="number" min="1" name="<?php echo $this->get_field_name('number_pledges'); ?>" value="<?php echo (isset($instance['number_pledges']) ? $instance['number_pledges'] : ''); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'sort_type' ); ?>"><?php _e('Sortiertyp','wdf'); ?></label>
			<select id="<?php echo $this->get_field_id( 'sort_type' ); ?>" name="<?php echo $this->get_field_name('sort_type'); ?>">
				<option value="last" <?php echo selected($instance['sort_type'],'last'); ?>><?php _e('Zuletzt zuerst','wdf'); ?></option>
				<option value="top" <?php echo selected($instance['sort_type'],'top'); ?>><?php _e('Erfolgreichste zuerst','wdf'); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'single_fundraiser' ); ?>"><?php printf(__('Anzeige für eine bestimmte %s oder alle','wdf'),esc_attr($settings['funder_labels']['singular_name'])); ?></label>
			<select id="<?php echo $this->get_field_id( 'single_fundraiser' ); ?>" class="wdf_toggle" rel="wdf_panel_single_pledges" name="<?php echo $this->get_field_name('single_fundraiser'); ?>">
				<option value="0" <?php echo selected($instance['single_fundraiser'],'0'); ?>><?php _e('Nein','wdf'); ?></option>
				<option value="1" <?php echo selected($instance['single_fundraiser'],'1'); ?>><?php _e('Ja','wdf'); ?></option>
			</select>
		</p>

		<div rel="wdf_panel_single_pledges" <?php echo ((int)$instance['single_fundraiser'] != 1 ? 'style="display: none;"' : ''); ?>>

			<?php $query = array( 'numberposts' => -1, 'post_type' => 'funder', 'post_status' => 'publish'); ?>

				<?php if($query = get_posts($query) ) : ?>
				<p>
					<label for="<?php echo $this->get_field_id( 'funder_a' ); ?>">
						<input id="<?php echo $this->get_field_id( 'funder_a' ); ?>" <?php echo checked($instance['funder'],'0'); ?> type="radio" name="<?php echo $this->get_field_name('funder'); ?>" value="0" />
						<?php printf(__('Alle %s','wdf'), $settings['funder_labels']['plural_name']); ?>
					</label><br />
					<?php foreach($query as $funder) : ?>
						<label for="<?php echo $this->get_field_id( 'funder_b' ); ?>">
							<input id="<?php echo $this->get_field_id( 'funder_b' ); ?>" <?php echo checked($instance['funder'],$funder->ID); ?> type="radio" name="<?php echo $this->get_field_name('funder'); ?>" value="<?php echo $funder->ID; ?>" />
							<?php echo $funder->post_title; ?>
						</label><br />
					<?php endforeach; ?>
				</p>
				<?php else : ?>
				<div class="error below-h2"><p><?php _e('Du hast noch keine Spendenaktionen erstellt','wdf'); ?></p></div>
				<?php endif; ?>
		</div>
		<?php
	}
}