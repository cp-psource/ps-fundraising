<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}?>
<?php

class WDF_Fundraiser_Panel extends WP_Widget {

	/**
     * @var		string	$translation_domain	Translation domain
     */

	function __construct() {
		// Instantiate the parent object
		$settings = get_option('wdf_settings');
		$title = sprintf(__('%s Panel','wdf'),esc_attr($settings['funder_labels']['singular_name']));
		parent::__construct( 'wdf_fundraiser_panel', $title, array(
			'description' =>  sprintf(__('Wenn es sich bei der aktuellen Seite um eine einzelne %1$s Seite handelt, werden in diesem Bereich Informationen angezeigt und Aktionen für %1$s aufgerufen. Du kannst es auch verwenden, um Informationen für bestimmte %1$s anzuzeigen','wdf'),esc_attr($settings['funder_labels']['singular_name']))
		) );

	}

	function widget( $args, $instance ) {
		// Widget output
		global $wp_query, $wdf;

		// Standardwert setzen, falls nicht vorhanden
		if (!isset($instance['single_fundraiser'])) {
			$instance['single_fundraiser'] = '0';
		}

		if($instance['single_fundraiser'] == '1') {
			// Specific Single Fundraiser
			$wdf->front_scripts($instance['funder']);
			if(isset($instance['style']) && !empty($instance['style']))
				$wdf->load_style($instance['style']);

			$content = $args['before_widget'];

			if(isset($instance['title']) && !empty($instance['title']))
				$content .= $args['before_title'] . apply_filters('fundraiser_panel_funder_widget_title', get_the_title($instance['funder']), $instance['funder']) . $args['after_title'];

			$content .= $this->show_thumb($instance);
			$content .= '<p class="wdf_widget_description">' . $instance['description'] . '</p>';

			$content .= wdf_fundraiser_panel( false, $instance['funder'], 'widget', $instance );
			$content .= $args['after_widget'];
			echo $content;
		} else {
			if(!isset($wp_query) || !isset($wp_query->query)) {
				echo 'Dieses Widget kann nur funktionieren, wenn die aktuelle Seite angezeigt wird';
				return;
			}
			if($wp_query->query_vars['post_type'] == 'funder' && $wp_query->is_single && (!isset($wp_query->query_vars['funder_checkout']) || $wp_query->query_vars['funder_checkout'] != '1') && (!isset($wp_query->query_vars['funder_confirm']) || $wp_query->query_vars['funder_confirm'] != '1') ) {
				// Single Fundraiser Page
				$wdf->front_scripts(get_the_ID());
				if(isset($instance['style']) && !empty($instance['style']))
					$wdf->load_style($instance['style']);

				$content = $args['before_widget'];

				if(isset($instance['title']) && !empty($instance['title']))
					$content .= $args['before_title'] . esc_attr(apply_filters('widget_title', $instance['title'])) . $args['after_title'];

				$content .= $this->show_thumb($instance);

				if(isset($instance['description']) && !empty($instance['description']))
					$content .= '<p class="wdf_widget_description">' . $instance['description'] . '</p>';

				$content .= wdf_fundraiser_panel( false, get_the_ID(), 'widget', $instance );
				$content .= $args['after_widget'];
				echo $content;
			} /*else if($wp_query->query_vars['post_type'] == 'funder' && $wp_query->query_vars['funder_checkout'] == '1'){
				// Fundraiser Checkout & Confirm Page
				$wdf->front_scripts($wp_query->posts[0]->ID);
				$content = $args['before_widget'];

				//if(isset($instance['title']) && !empty($instance['title']))
					$content .= $args['before_title'] . esc_attr(get_the_title($wp_query->posts[0]->ID)) . $args['after_title'];

				$content .= '<div><a class="button" href="'.wdf_get_funder_page('',$wp_query->posts[0]->ID).'">'.__('Go Back','wdf').'</a></div>';
				$content .= $args['after_widget'];
				echo $content;
			}*/
		}
	}
	function show_thumb($instance) {
		global $wp_query;
		if( function_exists('has_post_thumbnail') ) {
			if( isset($instance['show_thumb']) && (int)$instance['show_thumb'] == 1 ) {
				$post_id = ($instance['single_fundraiser'] == '1' ? $instance['funder'] : $wp_query->posts[0]->ID );
				if( has_post_thumbnail($post_id) ) {
					// Width and Height Default to the blog's thumbnail size if they are not set in the widget options.
					$width = (isset($instance['thumb_width']) && !empty($instance['thumb_width']) ? $instance['thumb_width'] : get_option('thumbnail_size_w'));
					$height = (isset($instance['thumb_height']) && !empty($instance['thumb_height']) ? $instance['thumb_height'] : get_option('thumbnail_size_h'));

					// Run the size and attributes through some filters incase you wanna do hoodrat stuff with your friends
					$size = apply_filters('wdf_panel_widget_thumb_size',array($width,$height));
					$attr = apply_filters('wdf_panel_widget_thumb_atts','');

					$thumb_id = apply_filters('wdf_panel_widget_thumb_id',get_post_thumbnail_id($post_id));
					return get_the_post_thumbnail( $post_id, $size, $attr );
				}
			}
		}
	}
	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;
		$instance['title'] = esc_attr($new_instance['title']);
		$instance['show_thumb'] = esc_attr($new_instance['show_thumb']);
		if(isset($new_instance['thumb_width']) && !empty($new_instance['thumb_width']))
			$instance['thumb_width'] = absint($new_instance['thumb_width']);
		else
			unset($instance['thumb_width']);

		if(isset($new_instance['thumb_height']) && !empty($new_instance['thumb_height']))
			$instance['thumb_height'] = absint($new_instance['thumb_height']);
		else
			unset($instance['thumb_height']);

		$instance['style'] = esc_attr($new_instance['style']);
		$instance['single_fundraiser'] = esc_attr($new_instance['single_fundraiser']);
		$instance['description'] = esc_textarea($new_instance['description']);
		$instance['funder'] = esc_attr( absint($new_instance['funder']) );
		return $instance;
	}

	function form( $instance ) {
		global $wdf;
		$settings = get_option('wdf_settings');

		$instance_defaults = array(
			'description' => '',
			'show_thumb' => '0',
			'thumb_width' => '',
			'thumb_height' => '',
			'style' => '',
			'single_fundraiser' => '0',
			'funder' => ''
		);
		foreach($instance_defaults as $key => $default)
			if(!isset($instance[$key]))
			$instance[$key] = $default;
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Titel','wdf') ?></label><br />
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" type="text" name="<?php echo $this->get_field_name('title'); ?>" class="widefat" value="<?php echo (isset($instance['title']) ? $instance['title'] : __('Empfohlene Spendenaktionen','wdf')); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'description' ); ?>"><?php _e('Zusätzliche Beschreibung','wdf') ?></label><br />
			<textarea id="<?php echo $this->get_field_id( 'description' ); ?>" class="widefat" name="<?php echo $this->get_field_name('description'); ?>"><?php echo esc_textarea($instance['description']) ?></textarea>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'show_thumb' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_thumb' ); ?>" type="checkbox" value="1" name="<?php echo $this->get_field_name('show_thumb'); ?>" <?php checked((int)$instance['show_thumb'],1); ?> />
				<?php _e('Empfohlenes Bild einschließen'); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'thumb_width' ); ?>">
				<?php _e('Maximale Bildbreite','wdf'); ?> : 
				<input id="<?php echo $this->get_field_id( 'thumb_width' ); ?>" type="text" class="small-text" value="<?php echo $instance['thumb_width']; ?>" name="<?php echo $this->get_field_name('thumb_width'); ?>"/>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'thumb_height' ); ?>">
				<?php _e('Maximale Bildhöhe','wdf'); ?> : 
				<input id="<?php echo $this->get_field_id( 'thumb_height' ); ?>" type="text" class="small-text" value="<?php echo $instance['thumb_height']; ?>" name="<?php echo $this->get_field_name('thumb_height'); ?>"/>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'style' ); ?>"><?php _e('Wähle einen Anzeigestil','wdf'); ?></label>
			<select id="<?php echo $this->get_field_id( 'style' ); ?>" name="<?php echo $this->get_field_name('style'); ?>">
				<?php
				$styles = method_exists($wdf, 'get_styles') ? $wdf->get_styles() : [];
				if(is_array($styles) && !empty($styles)) : ?>
					<option <?php selected($instance['style'],''); ?> value=""><?php _e('Standard','wdf'); ?></option>
					<?php foreach($styles as $key => $label) : ?>
						<option <?php selected($instance['style'],$key); ?> value="<?php echo $key ?>"><?php echo $label; ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'single_fundraiser' ); ?>"><?php printf(__('Zeige bestimmte %s an','wdf'),esc_attr($settings['funder_labels']['singular_name'])); ?></label>
			<select id="<?php echo $this->get_field_id( 'single_fundraiser' ); ?>" class="wdf_toggle" rel="wdf_panel_single" name="<?php echo $this->get_field_name('single_fundraiser'); ?>">
				<option value="0" <?php echo selected($instance['single_fundraiser'],'0'); ?>><?php _e('Nein','wdf'); ?></option>
				<option value="1" <?php echo selected($instance['single_fundraiser'],'1'); ?>><?php _e('Ja','wdf'); ?></option>
			</select>
		</p>
		<div rel="wdf_panel_single" <?php echo ((int)$instance['single_fundraiser'] != 1 ? 'style="display: none;"' : ''); ?>>

			<?php
				$query = array( 'numberposts' => -1, 'post_type' => 'funder', 'post_status' => 'publish');
				if($query = get_posts($query) ) : ?>
					<p>
					<?php foreach($query as $funder_key => $funder) : ?>
						<label for="<?php echo $this->get_field_id( 'funder_'.$funder_key ); ?>">
							<input id="<?php echo $this->get_field_id( 'funder_'.$funder_key ); ?>" <?php echo checked($instance['funder'],$funder->ID); ?> type="radio" name="<?php echo $this->get_field_name('funder'); ?>" value="<?php echo $funder->ID; ?>" />
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
?>