<?php

if(!function_exists('donate_button_shortcode')) {
	function donate_button_shortcode($atts) {
		if(isset($atts['title']))
			$content = sprintf( apply_filters( 'wdf_fundaiser_panel_shortcode_title', '<div class="wdf_shortcode_title"><h2>%s</h2></div>'), $atts['title'] );

		$content .= wdf_pledge_button(false,'widget_simple_donate',null,array('widget_args' => $atts));
		return $content;
	}
}

if(!function_exists('fundraiser_panel_shortcode')) {
	function fundraiser_panel_shortcode($atts) {
		$content = '';
		if(isset($atts['id']) && !empty($atts['id']) ) {
			global $wdf;
			$wdf->front_scripts($atts['id'],(isset($atts['style']) ? $atts['style'] : false));
			$atts['shortcode'] = true;
			$content .= wdf_fundraiser_panel(false, $atts['id'], 'shortcode', $atts);

		} else {
			$content .= __('Keine ID angegeben','wdf');
		}
		return $content;
	}
}

if(!function_exists('fundraiser_pledges_shortcode')) {
	function fundraiser_pledges_shortcode($atts) {
		$content = '';
		if(isset($atts['id']) && is_numeric($atts['id']) ) {
			$atts['shortcode'] = true;
			$content .= wdf_pledges_panel(false, $atts['id'], 'shortcode', $atts);

		} else {
			$content .= __('Keine ID angegeben','wdf');
		}
		return $content;
	}
}

if(!function_exists('wdf_fundraiser_page')) {
	function wdf_fundraiser_page($echo = true, $post_id = false, $atts = array()) {
		global $post; $content = '';
		$post_id = (empty($post_id) ? $post->ID : $post_id );
		if(!get_post($post_id))
			return false;
		$content = wdf_fundraiser_panel(false,$post_id,'','');

		if($echo) {echo $content;} else {return $content;}
	}
}

if(!function_exists('wdf_fundraiser_panel')) {
	function wdf_fundraiser_panel($echo = true, $post_id = '', $context = '', $args = array() ) {
		global $post, $wdf_checkout_from_panel;
		if(isset($args['shortcode']) && $args['shortcode'] == true && isset($args['id']) && $args['id'] == $post->ID)
			return false;

		$settings = get_option('wdf_settings');
		$content = '';
		if (empty($post_id)) {
			global $post;
			if (isset($post) && is_object($post) && isset($post->ID)) {
				$post_id = $post->ID;
			} else {
				return false; // Kein Post verfügbar, Funktion abbrechen
			}
		}
		$funder = get_post($post_id);
		if(!$funder)
			return false;

		$style = ( isset($args['style']) && !empty($args['style']) ? $args['style'] : wdf_get_style($post_id) );

		$content .= '<div class="wdf_fundraiser_panel '.$style.'">';

		if(isset($args['shortcode']) && $args['shortcode'] == true) {
			if( isset($args['show_title']) && strtolower($args['show_title']) == 'yes' )
				$content .= sprintf( apply_filters( 'wdf_fundaiser_panel_shortcode_title', '<div class="wdf_shortcode_title"><h2>%s</h2></div>'), get_the_title($post_id) );
			if( isset($args['show_content']) && strtolower($args['show_content']) == 'yes') {
				global $wdf;

				$funder_content = apply_filters('the_content', $funder->post_content);
				$content .= sprintf( apply_filters( 'wdf_fundaiser_panel_shortcode_content', '<div class="wdf_shortcode_content">%s</div>'), $funder_content );
			}
		}
		$backer_total = wdf_total_backers(false, $post_id);
		$content .= '<div class="wdf_total_backers"><div class="wdf_big_num">'.$backer_total.'</div><p>'.apply_filters('wdf_backer_label', ($backer_total > 1 ? esc_attr($settings['donation_labels']['backer_plural']) : esc_attr($settings['donation_labels']['backer_single'])) ).'</p></div>';
		if(wdf_has_goal($post_id)) {
			$content .= '<div class="wdf_amount_raised"><div class="wdf_big_num">'.wdf_amount_raised(false, $post_id).'</div><p>'.sprintf(__('insgesamt erhalten, von einem Ziel von %s','wdf'),wdf_goal(false, $post_id)).'</p></div>';
			$content .= '<div class="wdf_panel_progress_bar">'.wdf_progress_bar(false, $post_id).'</div>';
		} else {
			$content .= '<div class="wdf_amount_raised"><div class="wdf_big_num">'.wdf_amount_raised(false, $post_id).'</div><p>'.__('insgesamt erhalten','wdf').'</p></div>';
		}

		// Checking to see if this fundraiser can accept pledges.
		$fundraising_active = wdf_time_left(false, $post_id, true);

		if(wdf_panel_checkout($post_id)) {
			$wdf_checkout_from_panel = true;

			if( $fundraising_active !== false ) {
				$content .= wdf_checkout_page(false, $post_id);
			}
		} else {
			if( $fundraising_active !== false ) {
				$content .= '<div class="wdf_backer_button">'.wdf_backer_button(false, $post_id).'</div>';
			}
		}

		// Show the time left or time till start if a date range is available
		if(wdf_has_date_range($post_id))
			$content .= '<div class="wdf_time_left">'.wdf_time_left(false, $post_id).'</div>';

		$content .= '</div>';

		if($echo) {echo $content;} else {return $content;}

	}
}

if(!function_exists('wdf_pledges_panel')) {
	function wdf_pledges_panel($echo = true, $post_id = '', $context = '', $args = array() ) {
		global $post, $wdf;

		$content = '';

		$post_id = (empty($post_id) && $post_id != '0') ? $post->ID : $post_id;

		$donations = $wdf->get_pledge_list($post_id);
		if($donations) {
			$settings = get_option('wdf_settings');

			$funder = get_post($post_id);
			if(!$funder)
				return false;

			$content .= '<div class="wdf_pledges_panel">';

			$content .= '<ul>';
			$count = 0;
			$donations_ready = array();
			foreach($donations as $key => $donation) {
				$trans = $wdf->get_transaction($donation->ID);

				$donations_ready[$key] = $donation;
				$donations_ready[$key]->trans = $trans;
			}

			if($args['sort_type'] == 'top')
				usort($donations_ready, "wdf_pladges_compare");

			foreach($donations_ready as $donation) {
				if($count == $args['number_pledges'])
					break;

				if($donation->post_status != 'wdf_complete')
					continue;

				$count ++;
				$content .= '<li>'.$donation->trans['first_name'].' '.substr($donation->trans['last_name'], 0, 1).' - '.$wdf->format_currency('',$donation->trans['gross']).'</li>';
			}
			$content .= '</ul>';

			$content .= '</div>';
		}

		if($echo) {echo $content;} else {return $content;}

	}
}
if(!function_exists('wdf_pladges_compare')) {
	function wdf_pladges_compare($a, $b) {
	    return $b->trans['gross'] - $a->trans['gross'];
	}
}


if(!function_exists('wdf_rewards')) {
	function wdf_rewards($echo = false, $post_id = '') {
		global $wdf, $post;
		$post_id = (empty($post_id) ? $post->ID : $post_id );
		if(!get_post($post_id))
			return;

		$meta = get_post_custom($post_id);
		if(wdf_has_rewards($post_id)) {
			if(isset($meta['wdf_levels'][0])) {
				$levels = '<div class="wdf_rewards">';
				foreach($meta['wdf_levels'] as $level) {
					$level = maybe_unserialize($level);
					foreach($level as $index => $data) {
						$levels .= '
							<div class="wdf_reward_item wdf_reward_'.$index.'">
								<div class="wdf_level_amount" rel="'.$data['amount'].'">'.$wdf->format_currency('',$data['amount']).'</div>
								<p>'.$data['description'].'</p>
							</div>';
					}
				}
				$levels .= '</div>';
			}
			if($echo) {echo $levels;} else {return $levels;}
		}
	}
}

if(!function_exists('wdf_has_rewards')) {
	function wdf_has_rewards($post_id = '') {
		global $post;
		$post_id = (empty($post_id) ? $post->ID : $post_id );
		if(!get_post($post_id))
			return false;

		$meta = get_post_meta($post_id,'wdf_has_reward',true);

		if( $meta === '1' )
			return true;
		else
			return false;

	}
}
if(!function_exists('wdf_panel_checkout')) {
	function wdf_panel_checkout($post_id = false) {
		$settings = get_option('wdf_settings');
		if($post_id == false) {
			global $post;
			$post_id = $post->ID;
		}
		$type = $settings['checkout_type'];
		if( isset($settings['single_checkout_type']) && $settings['single_checkout_type'] === '1' ) {
			$type = get_post_meta($post_id, 'wdf_checkout_type', true);
		}

		if( $type === '2' )
			return false;
		else
			return true;

	}
}
if(!function_exists('wdf_has_date_range')) {
	function wdf_has_date_range($post_id) {
		global $post;
		$post_id = (empty($post_id) ? $post->ID : $post_id );
		if(!get_post($post_id))
			return false;

		$start = get_post_meta($post_id,'wdf_goal_start',true);
		$end = get_post_meta($post_id,'wdf_goal_end',true);

		if($start != false && $end != false)
			return true;
		else
			return false;

	}
}
if(!function_exists('wdf_has_goal')) {
	function wdf_has_goal($post_id = '') {
		global $wdf, $post;
		$post_id = (empty($post_id) ? $post->ID : $post_id );
		if(!get_post($post_id))
			return false;

		return $wdf->has_goal($post_id);
	}
}

if(!function_exists('wdf_goal')) {
	function wdf_goal($echo = true, $post_id = '') {
		global $wdf, $post;
		$post_id = (empty($post_id) ? $post->ID : $post_id );
		if(!get_post($post_id))
			return;

		$goal = get_post_meta($post_id,'wdf_goal_amount',true);
		$goal = $wdf->format_currency('',$goal);
		if($echo) {echo $goal;} else {return $goal;}
	}
}

if(!function_exists('wdf_amount_raised')) {
	function wdf_amount_raised($echo = true, $post_id = '') {
		global $post, $wdf;
		$post_id = (empty($post_id) ? $post->ID : $post_id );
		if(!get_post($post_id))
			return false;
		$raised = $wdf->format_currency('',$wdf->get_amount_raised($post_id));

		$raised = apply_filters( 'wdf_amount_raised', $raised, $wdf, $post_id );

		if($echo) {echo $raised;} else {return $raised;}

	}
}

if(!function_exists('wdf_time_left')) {
	function wdf_time_left($echo = true, $post_id = '', $active_bool = false ) {
		global $post, $wdf;
		$post_id = (empty($post_id) ? $post->ID : $post_id );

		if(!get_post($post_id) || (wdf_has_goal($post_id) && !wdf_has_date_range($post_id)) )
			return false;

		$active_status = true;
		$future_start = false;
		$end_date = strtotime(get_post_meta($post_id, 'wdf_goal_end',true));
		$start_date = strtotime(get_post_meta($post_id, 'wdf_goal_start', true));
		$now = current_time('timestamp');

		$post_id_for_meta = (isset($post) && is_object($post) && isset($post->ID)) ? $post->ID : $post_id;
		$meta = get_post_custom($post_id_for_meta);

		if($now > $end_date && isset($meta['wdf_has_goal'][0]) && $meta['wdf_has_goal'][0] == 1 ) {
			if($active_bool === true)
				return false;

			$end_date = false;
			$content = __("Die Zeit ist um",'wdf');
			if($echo) {echo $content;} else {return $content;}
		}

		if($start_date < $now) {
			$start_date = $now;
		} else if($start_date > $now) {
			if($active_bool === true)
				return false;

			$future_start = true;
			$end_date = $start_date;
			$start_date = $now;
		}

		if($active_bool === true)
			return true;

		//something is not right with start or end date
		if( $start_date === false || $end_date === false )
			return false;

		$days = $wdf->datediff('d', $start_date, $end_date, true);
		$hours = $wdf->datediff('h', $start_date, $end_date, true);
		$min = $wdf->datediff('n', $start_date, $end_date, true);
		$weeks = $wdf->datediff('ww', $start_date, $end_date, true);
		$months = $wdf->datediff('m', $start_date, $end_date, true);

		if((int)$days >= 2) {
			$time = $days . ' ' . ((int)$days == 1 ? __('Tag übrig','wdf') : __('Tage übrig','wdf'));
		} elseif((int)$hours < 1) {
			$time = $min . ' ' . ((int)$min == 1 ? __('Minute übrig','wdf') : __('Minuten übrig','wdf'));
		} else {
			$time = $hours . ' ' . ((int)$hours == 1 ? __('Eine Stunde noch','wdf') : __('Stunden übrig','wdf'));
		}
		if($future_start === true) {
			$time = sprintf(__('Beginnt in %s','wdf'), ( (int)$days >= 2 ? (int)$days == 1 ? $days . ' ' . __('Tag','wdf') : $days . ' ' . __('Tage','wdf') : ((int)$hours == 1 ? $hours . ' ' . __('Stunde','wdf') : $hours . ' ' . __('Stunden','wdf')) ));
		}

		$content = apply_filters('wdf_time_left', $time, $hours, $days, $weeks, $months, $start_date, $end_date );

		if($echo) {echo $time;} else {return $time;}
	}
}

if(!function_exists('wdf_get_page_link')) {
	function wdf_get_page_link($post_id, $type) {
		$permlink = get_option('permalink_structure');
		if($permlink)
			$settings = get_option('wdf_settings');

		switch($type) {
			case'checkout' :
				if($permlink)
					$link = get_post_permalink($post_id) . $settings['checkout_slug'] .'/';
				else
					$link = add_query_arg( array('funder_checkout' => '1'), get_post_permalink($post_id) );
				break;
			case'confirmation' :
				if($permlink)
					$link = get_post_permalink($post_id) . $settings['confirm_slug'] .'/';
				else
					$link = add_query_arg( array('funder_confirm' => '1'), get_post_permalink($post_id) );
				break;
			default:
				$link = '';
		}
		return $link;
	}
}

if(!function_exists('wdf_backer_button')) {
	function wdf_backer_button($echo = false, $post_id = '') {
		global $post;
		$settings = get_option('wdf_settings');
		$post_id = (empty($post_id) ? $post->ID : $post_id );
		if(!get_post($post_id))
			return false;

		$link = wdf_get_page_link($post_id,'checkout');;
		$link = apply_filters('wdf_backer_button_link',trailingslashit($link) );

		$classes = apply_filters('wdf_backer_button_classes','wdf_button');
		$button = '<a class="'.$classes.'" href="'.$link.'">'.$settings['donation_labels']['action_name'].'</a>';
		return apply_filters('wdf_backer_button', $button);
	}
}

if(!function_exists('wdf_get_style')) {
	function wdf_get_style( $post_id = '' ) {
		global $post;
		$settings = get_option('wdf_settings');
		$post_id = (empty($post_id) ? $post->ID : $post_id );

		if( $settings['single_styles'] == 'no' ) {
			$style = $settings['default_style'];
		} else {
			$meta = get_post_meta($post_id,'wdf_style',true);
			$style = ($meta != false ? $meta : $settings['default_style'] );
		}

		return $style;
	}
}

if(!function_exists('wdf_total_backers')) {
	function wdf_total_backers($echo = false, $post_id = '') {
		global $post, $wdf;
		$post_id = (empty($post_id) ? $post->ID : $post_id );
		if(!get_post($post_id))
			return false;
		$backers = $wdf->get_pledge_list( $post_id );

		if( $backers ){
			$backers = count( $backers );
		}
		else{
			$backers = 0;
		}

		$backers = apply_filters( 'wdf_total_backers', $backers, $post_id );

		return $backers;
	}
}

if(!function_exists('wdf_confirmation_page')) {
    function wdf_confirmation_page( $echo = true, $post_id = '' ) {
        global $wdf; $content = '';
        $settings = get_option('wdf_settings');

        $pledge_id = (isset($_SESSION['wdf_pledge_id']) ? $_SESSION['wdf_pledge_id'] : (isset($_REQUEST['wdf_pledge_id']) ? $_REQUEST['wdf_pledge_id'] : ''));
        if(empty($post_id)) {
            global $post;
            $post_id = $post->ID;
        }
        $wdf->front_scripts($post_id);
        $content .= '<div class="wdf_confirmation_page">';
        if ( get_post($post_id) ) {
            $pledge_query = new WP_Query([
                'post_type'      => 'donation',
                'title'          => $pledge_id,
                'post_status'    => 'any',
                'posts_per_page' => 1,
            ]);
            $pledge = $pledge_query->have_posts() ? $pledge_query->posts[0] : false;

            if ( $pledge ) {
                $transaction = $wdf->get_transaction($pledge->ID);

                if(isset($_SESSION['wdf_bp_activity']) && $_SESSION['wdf_bp_activity'] == true) {
                    global $bp;
                    if( isset($bp->loggedin_user->id) && $bp->loggedin_user->id ) {
                        $activity_args = array(
                            'action' => sprintf( __('%s hat mit %s %s eine Unterstützung für %s geleistet','wdf'), '<a href="'.$bp->loggedin_user->domain.'">'.$bp->loggedin_user->fullname.'</a>', $wdf->format_currency('',$transaction['gross']), esc_attr($settings['donation_labels']['singular_name']), '<a href="'.wdf_get_funder_page('',$post_id).'">'.get_the_title($post_id).'</a>' ),
                            'primary_link' => wdf_get_funder_page('',$post_id),
                            'type' => 'pledge'
                        );
                        $activity_args = apply_filters('wdf_bp_activity_args',$activity_args);
                        bp_wdf_record_activity($activity_args);

                        unset($_SESSION['wdf_bp_activity']);
                    }
                }

                $content .= wdf_thanks_panel( false, $post_id, $transaction );

                if(isset($_SESSION['wdf_gateway'])) {
                    $content .= '<div class="wdf_gateway_payment_info">'.apply_filters('wdf_gateway_payment_info_'.$_SESSION['wdf_gateway'], '', $transaction).'</div>';
                }
            } else {
                $message_waiting = sprintf( __('Wir warten auf Deine Zahlung','wdf'), esc_attr($settings['donation_labels']['singular_name']), esc_attr($settings['donation_labels']['singular_name']) );

                $message = (isset($settings['message_pledge_not_found']) && $settings['message_pledge_not_found']) ? $settings['message_pledge_not_found'] : sprintf( __('Oh No, we can\'t find your %s.  Sometimes it take little bit longer for your %s to be logged.','wdf'), esc_attr($settings['donation_labels']['singular_name']), esc_attr($settings['donation_labels']['singular_name']) );
                if((!isset($settings['message_pledge_not_found']) || !$settings['message_pledge_not_found']) && get_post_meta($post_id,'wdf_send_email',true)) {
                    $message .= ' '.__('In diesem Fall senden wir Dir eine E-Mail').'.';
                }
                $content .= '<p class="wdf-pledge-waiting" data-wdf-pledge-id="'.$pledge_id.'">'.$message_waiting.'<span class="wdf-loading-dots"></span></p>';
                $content .= '<p class="error wdf-pledge-error" style="display: none;">'.$message.'</p>';
            }
        }
        $content .= '</div>';

        //Unset all the session information
        $wdf->clear_session();

        if($echo) {echo $content;} else {return $content;}
    }
}

if(!function_exists('wdf_thanks_panel')) {
	function wdf_thanks_panel( $echo = true, $post_id = '', $trans = '' ) {
		global $wdf; $content = '';
		$settings = get_option('wdf_settings');
		$meta = get_post_custom($post_id);
		if($funder = get_post($post_id) && !empty($trans)) {
            $amount = isset($trans['recurring']) ? $trans['recurring_amount'] : $trans['gross'];
            $content .= '<div class="wdf_thanks_panel">';
            $content .= '<h3 class="wdf_confirm_pledge_amount">' . sprintf(__('Deine %s von %s war erfolgreich','wdf'), esc_attr($settings['donation_labels']['singular_name']), $wdf->format_currency($trans['currency_code'],$amount) ) . '</h3>';
			$content .= '<h3 class="wdf_left_to_go">';
			if(!wdf_has_goal($post_id))
				$content .= sprintf(__('%s bisher erhalten','wdf'), wdf_amount_raised(false, $post_id));
			$content .= '</h3>';

			if(wdf_has_goal($post_id)) {
				$content .= '<div class="wdf_amount_raised"><div class="wdf_big_num">'.wdf_amount_raised(false, $post_id).'</div><p>'.sprintf(__('erhalten vom Ziel %s','wdf'),wdf_goal(false, $post_id)).'</p></div>';
				$content .= wdf_progress_bar(false, $post_id);
			}

			if(isset($meta['wdf_thanks_custom'][0]) && $meta['wdf_thanks_custom'][0]) {
				$thanksmsg = $meta['wdf_thanks_custom'][0];
				$thanksmsg = $wdf->filter_thank_you($thanksmsg, $trans);
				$content .= '<div class="wdf_custom_thanks">' . $thanksmsg . '</div>';
			}

			$content .= '</div>';
		}
		$content = apply_filters('wdf_thanks_panel',$content);
		if($echo) {echo $content;} else {return $content;}
	}
}

if(!function_exists('wdf_progress_bar')) {
	function wdf_progress_bar( $echo = true, $post_id = '', $total = NULL, $goal = NULL, $context = 'general' ) {
		global $wdf;
		$content = '';
		if(wdf_has_goal($post_id) != false)
			$content .= $wdf->prepare_progress_bar($post_id, $total, $goal, $context, false);
		//else if(!empty($total) && !empty($goal))
			//$content .= $wdf->prepare_progress_bar($post_id, $total, $goal,'general',false);

		if($echo) {echo $content;} else {return $content;}
	}
}
if(!function_exists('wdf_progress_bar_shortcode')) {
	function wdf_progress_bar_shortcode($atts) {
		global $post;
		$defaults = array(
			'id' => ($post->post_type == 'funder' ? $post->ID : ''),
			'total' => NULL,
			'goal' => NULL,
			'show_totals' => 'no',
			'show_title' => 'no'
		);
		$atts = array_merge($defaults, $atts);

		if(isset($atts['id']) && !empty($atts['id']) ) {
			global $wdf;
			$wdf->front_scripts($atts['id'],$atts['style']);
		}

		if( $atts['show_totals'] == 'yes' || $atts['show_title'] == 'yes') {
			$context = 'shortcode';
			if($atts['show_title'] == 'yes')
				$context .= '_title';
			if($atts['show_totals'] == 'yes')
				$context .= '_totals';
		} else {
			$context = 'general';
		}

		return wdf_progress_bar(false, $atts['id'], (int)$atts['total'], (int)$atts['goal'], $context);
	}
}
// Coming Soon
/*if(!function_exists('wdf_activity_page')) {
	function wdf_activity_page($echo = false, $post_id = '') {
		global $post; $content = '';
		$post_id = (empty($post_id) ? $post->ID : $post_id );
		if(!get_post($post_id))
			return false;

		$content .= '<h1>Activity Page</h1>';
		if($echo) {echo $content;} else {return $content;}
	}
}*/

if(!function_exists('wdf_gateway_choices')) {
	function wdf_gateway_choices( $echo = true, $default = '' ) {
		global $wdf_gateway_active_plugins; $content = '';

		if(count($wdf_gateway_active_plugins) == 1 ) {
			$gateway = array_keys($wdf_gateway_active_plugins);
			$gateway = $gateway[0];
			$content .= '<input type="hidden" name="wdf_step" value="gateway" />';
			$content .= '<input type="hidden" name="wdf_gateway" value="'.$gateway.'" />';
		} elseif(count($wdf_gateway_active_plugins) > 1) {
			$getaway_names = array_keys($wdf_gateway_active_plugins);
			if(!in_array($default, $getaway_names))
				$default = 'paypal';

			$content .= '<input type="hidden" name="wdf_step" value="gateway" />';
			$content .= '<div class="wdf_payment_options_title">'.__('Zahlungsmöglichkeiten','wdf').'</div>';
			$content .= '<div class="wdf_payment_options">';
			foreach($wdf_gateway_active_plugins as $gateway => $data) {
				$content .= '<label> <input type="radio" name="wdf_gateway" value="'.$gateway.'" '.checked( $gateway, $default, false ).'/> '.$data->public_name.'</label> ';
			}
			$content .= '</div>';
		} else {
			$content .= __('Es wurden keine Zahlungsgateways aktiviert', 'wdf');
		}

		if($echo) {echo $content;} else {return $content;}
	}
}

if(!function_exists('wdf_checkout_page')) {
	function wdf_checkout_page( $echo = true, $post_id = '' ) {
		global $wdf_checkout_from_panel, $wdf, $post; $content = '';
		$post_id = (empty($post_id) ? $post->ID : $post_id );
		if(!get_post($post_id))
			return false;

		$meta = get_post_custom($post_id);
		$settings = get_option('wdf_settings');

		$wdf->front_scripts($post_id);

		$style = ($wdf_checkout_from_panel == true ? '' : wdf_get_style($post_id) );
		$style = wdf_get_style($post_id);

		$content .= '<form style="display: inline-block" class="wdf_checkout_form '.$style.'" action="'.wdf_get_funder_page('checkout',$post_id).'" method="post">';
			global $wp_filter;
			$raised = $wdf->get_amount_raised($post_id);
			//$goal = $meta['wdf_goal_amount'][0];

			$content .= '<div class="wdf_rewards">';
			$content .= apply_filters('wdf_error_payment_submit','');

			$content .= '
			<div class="wdf_payment_options">
				<div class="wdf_donate_button">'.wdf_pledge_button(false, 'single', $post_id).'</div>
				<div class="wdf_gateway_choices">'.wdf_gateway_choices(false, (isset($settings['default_gateway']) ? $settings['default_gateway'] : '')).'</div>
			</div>';

			if(wdf_has_rewards($post_id) && isset($meta['wdf_levels'][0])) {
				$content .= apply_filters('wdf_before_rewards_title','');
					$level = maybe_unserialize($meta['wdf_levels'][0]);
					foreach($level as $index => $data) {
						$disabled_class = $disabled_input = '';
						if(isset($data['limit']) && is_numeric($data['limit'])) {
							$reward_left = $data['limit'] - (isset($data['used']) ? $data['used'] : 0);
							if($reward_left)
								$limit_text = ' <span class="wdf_reward_limit">'.__('Begrenzt','wdf').' ('.$reward_left.__(' übrig von ','wdf').$data['limit'].')'.'</span>';

							else {
								$limit_text = ' <span class="wdf_reward_limit wdf_reward_limit_gone">'.__('Alle weg.','wdf').'</span>';
								$disabled_class = ' wdf_reward_item_disabled';
								$disabled_input = ' disabled';
							}
						}
						else
							$limit_text = '';

						$content .= '
						<div class="wdf_reward_item'.$disabled_class.'">
							<div class="wdf_reward_choice"><input type="radio" name="wdf_reward" value="'.$index.'" '.$disabled_input.'/><span class="wdf_level_amount" rel="'.$data['amount'].'"> '.$wdf->format_currency('',$data['amount']).$limit_text.'</span></div>
							<div class="wdf_reward_description">'.html_entity_decode($data['description']).'</div>
						</div>';
					}
					$content .= '
					<div class="wdf_reward_item wdf_reward_item_none">
						<div class="wdf_reward_choice"><input type="radio" name="wdf_reward" value="none" /><span class="wdf_level_amount"> '.apply_filters('wdf_no_reward_description',__('Keine Belohnung','wdf')).'</span></div>
					</div>';
			}
			$content .= '</div>';
		$content .= '</form>';

		if($echo) {
			echo $content;
			return false;
		} else {
			return $content;
		}
	}
}

if(!function_exists('wdf_show_checkout')) {
	function wdf_show_checkout( $echo = true, $post_id = '', $checkout_step = '' ) {
		if( ((isset($_SESSION['wdf_pledge']) && (int)$_SESSION['wdf_pledge'] < 1) || !isset($_SESSION['wdf_pledge'])) && isset($_POST['wdf_pledge']) ) {
			$checkout_step = '';
			global $wdf;
			$wdf->create_error(sprintf(__('Du musst mindestens %s spenden','wdf'),$wdf->format_currency('',1)),'checkout_top');
		}

		switch($checkout_step) {
			case 'gateway' :
				$content = apply_filters('wdf_checkout_payment_form_'.$_SESSION['wdf_gateway'],'');
				break;
			default :
				$content = apply_filters('wdf_error_checkout_top', '');
				$content .= wdf_checkout_page( false, $post_id );
				break;
		}

		if($echo) {echo $content;} else {return $content;}

	}
}

if(!function_exists('wdf_get_funder_page')) {
	function wdf_get_funder_page($context = '', $post_id = '') {
		if(empty($post_id)) {
			global $post;
			if (isset($post) && is_object($post) && isset($post->ID)) {
				$post_id = $post->ID;
			} else {
				return false; // Kein Post verfügbar, Funktion abbrechen
			}
		}
		if($funder = get_post($post_id)) {
			$settings = get_option('wdf_settings');

			$context_types = array( 'checkout', 'confirmation' );
			if(in_array($context, $context_types))
				return wdf_get_page_link($post_id,$context);
			else
				return get_post_permalink($post_id);
		} else {
			return false;
		}
	}
}

if(!function_exists('wdf_pledge_button')) {
	function wdf_pledge_button($echo = true, $context = '', $post_id = '', $args = array()) {
		global $wdf; $content = '';
		if(empty($post_id)) {
			global $post;
			if (isset($post) && is_object($post) && isset($post->ID)) {
				$post_id = $post->ID;
			} else {
				return false; // Kein Post verfügbar, Funktion abbrechen
			}
		}
		$settings = get_option('wdf_settings');
		$meta = get_post_custom($post_id);

		//Default $atts
		$default_args = array(
			//'widget_args' => '',
			//'recurring' => false,
			//'style'    => wdf_get_style($post_id)
		);

		$args = array_merge($default_args,$args);

		if($context == 'widget_simple_donate') {
			if( isset($args['widget_args']['paypal_email']) ) {
				$paypal_email = is_email($args['widget_args']['paypal_email']);
			} else if(isset($settings['paypal_email'])) {
				$paypal_email = is_email($settings['paypal_email']);
			} else {
				$paypal_email = '';
			}

			if ($settings['paypal_sb'] == 'yes') {
				$pp_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
			} else {
				$pp_url = 'https://www.paypal.com/cgi-bin/webscr';
			}

			$style = (isset($args['widget_args']['style']) ? $args['widget_args']['style'] : $meta['wdf_style'][0] );
			$content .= '
				<form action="'.$pp_url.'" method="post" target="_blank" class="'.$style.'">
				<input type="hidden" name="cmd" value="_donations" />
				<input type="hidden" name="business" value="'.is_email($paypal_email).'" />
				<input type="hidden" name="lc" value="'.esc_attr($settings['currency']).'" />
				<input type="hidden" name="item_name" value="'.esc_attr($args['widget_args']['title']).'" />
				<input type="hidden" name="currency_code" value="'.esc_attr($settings['currency']).'" />
			';

			if(!empty($args['widget_args']['donation_amount']) && isset($args['widget_args']['donation_amount'])) {
				$content .= '<input type="hidden" name="amount" value="'.$wdf->filter_price($args['widget_args']['donation_amount']).'" />';
				$content .= '<label>Donate ';
				$content .= ($settings['curr_symbol_position'] == 1 || $settings['curr_symbol_position'] == 2 ? '<span class="currency">'.$wdf->format_currency().' </span>' : '');
				$content .= $wdf->filter_price($args['widget_args']['donation_amount']);
				$content .= ($settings['curr_symbol_position'] == 3 || $settings['curr_symbol_position'] == 4 ? '<span class="currency">'.$wdf->format_currency().' </span>' : '');
				$content .= '</label><br />';
			}

			if(isset($args['widget_args']['ref_label'])) {
				$content .= '<input type="hidden" name="item_number" value="'.esc_attr($args['widget_args']['ref_label']).'" />';
			}

			if(isset($args['widget_args']['button_type']) && $args['widget_args']['button_type'] == 'default') {
				//Use default PayPal Button

				if(isset($args['widget_args']['small_button']) && $args['widget_args']['small_button'] == 'yes') {
					$content .= '<input type="hidden" name="bn" value="PP-DonationsBF:btn_donate_SM.gif:NonHostedGuest">';
					$content .= '<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">';
				} else {
					if(isset($args['widget_args']['show_cc']) && $args['widget_args']['show_cc'] == 'yes') {
						$content .= '<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest">';
						$content .= '<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">';
					} else {
						$content .= '<input type="hidden" name="bn" value="PP-DonationsBF:btn_donate_LG.gif:NonHostedGuest">';
						$content .= '<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">';
					}
				}
				$content .= '<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">';
			} else if (isset($args['widget_args']['button_type']) && $args['widget_args']['button_type'] == 'custom') {
				//Use Custom Submit Button
				wp_enqueue_style('wdf-style-'.$args['widget_args']['style']);
				$button_text = (!empty($args['widget_args']['button_text']) ? esc_attr($args['widget_args']['button_text']) : __('Donate Now','wdf'));
				$content .= '<input class="wdf_send_pledge" type="submit" name="submit" value="'.$button_text.'" /> ';
			}

			$content .= '</form>';
		} else {
			$settings = get_option('wdf_settings');
			//Default Button Display
			$content .= '<input type="hidden" name="funder_id" value="'.$post_id.'" />';
			$content .= '<input type="hidden" name="send_nonce" value="'.wp_create_nonce('send_nonce_'.$post_id).'" />';
			$content .= '
<div class="wdf_pledge_label_input">
    <label class="wdf_custom_donation_label" for="wdf_pledge_'.$post_id.'">'
        .apply_filters('wdf_choose_amount_label',__('Wähle einen Betrag','wdf')).
    '</label>
    <div class="wdf_pledge_amount_wrapper">
        '.(($settings['curr_symbol_position'] == 1 || $settings['curr_symbol_position'] == 2) ? '<span class="wdf_currency">'.$wdf->format_currency().'</span>' : '').'
        <input type="text" id="wdf_pledge_'.$post_id.'" name="wdf_pledge" class="wdf_pledge_amount" value="" />
        '.(($settings['curr_symbol_position'] == 3 || $settings['curr_symbol_position'] == 4) ? '<span class="wdf_currency">'.$wdf->format_currency().'</span>' : '').'
    </div>
</div>
';

			if(isset($meta['wdf_recurring'][0]) && $meta['wdf_recurring'][0] == 'yes' && isset($meta['wdf_type'][0]) && $meta['wdf_type'][0] == 'simple' && $settings['active_gateways']['paypal']) {
				$content .= '
				<span class="wdf_recurring_holder">
				<label class="wdf_recurring_label">'.__('Mache diese Spende','wdf').' </label>
				<select class="wdf_recurring_select" name="wdf_recurring">
					<option value="0">'.__('Einmalig','wdf').'</option>
					<option value="D">'.__('Täglich','wdf').'</option>
					<option value="W">'.__('Wöchentlich','wdf').'</option>
					<option value="M">'.__('Monatlich','wdf').'</option>
					<option value="Y">'.__('Jährlich','wdf').'</option>
				</select>
				</span>
				';
			}

			$content .= '<input type="hidden" name="funder_id" value="'.$post_id.'" />';
			$content .= '<input id="wdf_step" type="hidden" name="wdf_step" value="" />';
			$pledge_label = apply_filters( 'wdf_donate_button_text', esc_attr($settings['donation_labels']['action_name']) );
			if(defined('WDF_BP_INSTALLED') && WDF_BP_INSTALLED == true && is_user_logged_in())
					$content .= '<label class="wdf_bp_show_on_activity">'.__('Veröffentliche in deinem Profil','wdf').'<input type="checkbox" name="wdf_bp_activity" value="1" checked="checked" /></label>';
			$content .= '<input class="wdf_send_donation" type="submit" name="wdf_send_donation" value="'.$pledge_label.'" />';

			// Stripe: Recurring-Option ausblenden und auf "Einmal" setzen
			$content .= '
			<script>
			document.addEventListener("DOMContentLoaded", function() {
				function checkGateway() {
					var gateway = document.querySelector("input[name=wdf_gateway]:checked");
					var recurringHolder = document.querySelector(".wdf_recurring_holder");
					var recurringSelect = document.querySelector("select[name=wdf_recurring]");
					if(gateway && gateway.value === "stripe") {
						if(recurringHolder) recurringHolder.style.display = "none";
						if(recurringSelect) recurringSelect.value = "0";
					} else {
						if(recurringHolder) recurringHolder.style.display = "";
					}
				}
				// Bei Gateway-Wechsel prüfen
				document.querySelectorAll("input[name=wdf_gateway]").forEach(function(el){
					el.addEventListener("change", checkGateway);
				});
				// Beim Laden prüfen
				checkGateway();
			});
			</script>
			';
		}

		$content = apply_filters('wdf_pledge_button', $content, $post_id);

		if($echo) {echo $content;} else {return $content;}
	}
}
// Add our shortcodes
add_shortcode('fundraiser_panel', 'fundraiser_panel_shortcode');
add_shortcode('pledges_panel', 'fundraiser_pledges_shortcode');
add_shortcode('donate_button', 'donate_button_shortcode');
add_shortcode('progress_bar', 'wdf_progress_bar_shortcode');
?>
