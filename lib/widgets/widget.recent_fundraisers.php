<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}?>
<?php

class WDF_Recent_Fundraisers extends WP_Widget {
	
	/**
     * @var		string	$translation_domain	Translation domain
     */
	
	function __construct() {
		// Instantiate the parent object
		$settings = get_option('wdf_settings');
		parent::__construct( false, sprintf(__('Neueste %s','wdf'),esc_attr($settings['funder_labels']['plural_name'])), array(
			'description' =>  sprintf(__('Die neuesten %s auf Deiner Webseite','wdf'),esc_attr($settings['funder_labels']['plural_name']))
		) );
	}

	function widget( $args, $instance ) {
		// Standardwerte setzen
		$defaults = array(
			'title' => __('Aktuelle Spendenaktionen','wdf'),
			'numberposts' => 5
		);
		$instance = wp_parse_args( $instance, $defaults );

		$content = $args['before_widget'];
		$content .= $args['before_title'] . esc_attr(apply_filters('widget_title', $instance['title'])) . $args['after_title'];
		$query = array(
			'numberposts' => $instance['numberposts'],
			'post_type' => 'funder',
			'post_status' => 'publish'
		);
		$query = get_posts($query);
		$content .= '<ul class="wdf_recent_fundraisers">';
		foreach($query as $funder) {
			$content .= '<li><a href="'.get_post_permalink($funder->ID).'">'.$funder->post_title.'</a></li>';
		}
		$content .= '</ul>';
		$content .= $args['after_widget'];
		echo $content;
	}

	function update( $new_instance, $old_instance ) {
		
		$instance = $old_instance;
		$instance['title'] = esc_attr($new_instance['title']);
		$instance['numberposts'] = absint($new_instance['numberposts']);
		
		return $instance;
	}

	function form( $instance ) {		
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Titel</label>
			<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name('title'); ?>" class="widefat" value="<?php echo (isset($instance['title']) ? $instance['title'] : __('Aktuelle Spendenaktionen','wdf')); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'numberposts' ); ?>">Anzahl der anzuzeigenden Spendenaktionen:</label>
			<input id="<?php echo $this->get_field_id( 'numberposts' ); ?>" type="text" size="3" name="<?php echo $this->get_field_name('numberposts'); ?>" value="<?php echo (isset($instance['numberposts']) ? $instance['numberposts'] : ''); ?>" />
		</p>
		<?php
	}
}
?>