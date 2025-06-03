<?php
add_action('wp_ajax_wdf_stripe_create_session', 'wdf_stripe_create_session');
add_action('wp_ajax_nopriv_wdf_stripe_create_session', 'wdf_stripe_create_session');

if (!class_exists('WDF_Gateway_Stripe')) {
    class WDF_Gateway_Stripe extends WDF_Gateway {

        var $plugin_name = 'stripe';
        var $admin_name = '';
        var $public_name = '';
        var $force_ssl = true;
        var $payment_types = 'simple, advanced';
        var $skip_form = true;
        var $allow_reccuring = true;

        function skip_form() {
            return true;
        }

        function on_creation() {
            $this->public_name = $this->admin_name = 'Stripe';
            remove_filter('wdf_checkout_payment_form_stripe', array($this, '_payment_form_wrapper'), 20);
        }

        function admin_settings() {
            if (!class_exists('Psource_HelpTooltips')) require_once WDF_PLUGIN_BASE_DIR . '/lib/classes/class.wd_help_tooltips.php';
            $tips = new Psource_HelpTooltips();
            $tips->set_icon_url(WDF_PLUGIN_URL.'/img/information.png');
            $settings = get_option('wdf_settings'); ?>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row"><?php _e('Stripe Webhook-URL', 'wdf'); ?></th>
                    <td>
                        <code><?php echo esc_html( home_url( '/?wdf_stripe_webhook=1' ) ); ?></code>
                        <br>
                        <span class="description">
                            <?php _e('Diese URL muss im Stripe Dashboard als Webhook hinterlegt werden. Aktiviere mindestens das Event <code>checkout.session.completed</code>.', 'wdf'); ?>
                        </span>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Stripe Secret Key', 'wdf'); ?></th>
                    <td>
                        <input class="regular-text" type="text" name="wdf_settings[stripe][secret_key]" value="<?php echo esc_attr(isset($settings['stripe']['secret_key']) ? $settings['stripe']['secret_key'] : ''); ?>" />
                        <?php echo $tips->add_tip(__('Dein Stripe Secret Key aus dem Stripe Dashboard.', 'wdf')); ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Stripe Publishable Key', 'wdf'); ?></th>
                    <td>
                        <input class="regular-text" type="text" name="wdf_settings[stripe][publishable_key]" value="<?php echo esc_attr(isset($settings['stripe']['publishable_key']) ? $settings['stripe']['publishable_key'] : ''); ?>" />
                        <?php echo $tips->add_tip(__('Dein Stripe Publishable Key aus dem Stripe Dashboard.', 'wdf')); ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Webhook Secret', 'wdf'); ?></th>
                    <td>
                        <input class="regular-text" type="text" name="wdf_settings[stripe][webhook_secret]" value="<?php echo esc_attr(isset($settings['stripe']['webhook_secret']) ? $settings['stripe']['webhook_secret'] : ''); ?>" />
                        <?php echo $tips->add_tip(__('Das Webhook Secret aus deinem Stripe Dashboard für die Webhook-Überprüfung.', 'wdf')); ?>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php
        }

        function save_gateway_settings() {
            if (isset($_POST['wdf_settings']['stripe'])) {
                $new = array();
                $new['stripe'] = $_POST['wdf_settings']['stripe'];
                $new['stripe'] = array_map('sanitize_text_field', $new['stripe']);
                $settings = get_option('wdf_settings');
                $settings = array_merge($settings, $new);
                update_option('wdf_settings', $settings);
            }
        }

        function payment_form() {
            global $wdf;
            $settings = get_option('wdf_settings');
            $publishable_key = isset($settings['stripe']['publishable_key']) ? $settings['stripe']['publishable_key'] : '';
            $betrag = isset($_SESSION['wdf_pledge']) ? $wdf->format_currency('', $_SESSION['wdf_pledge']) : '';
            ob_start();
            ?>
            <div class="wdf_payment_summary">
                <h4><?php printf(__('Deine Unterstützung von %s ist fast vollständig.','wdf'), $betrag); ?></h4>
                <?php
                if (isset($wdf->wdf_error) && $wdf->wdf_error == true) {
                    echo apply_filters('wdf_error_gateway','');
                }
                ?>
            </div>
            <div class="wdf_stripe_payment_form wdf_payment_form">
                <p><?php _e('Du wirst zum Stripe Checkout weitergeleitet.', 'wdf'); ?></p>
                <button type="button" id="wdf_stripe_button" class="button button-primary"><?php _e('Jetzt mit Stripe zahlen', 'wdf'); ?></button>
            </div>
            <script src="https://js.stripe.com/v3/"></script>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var btn = document.getElementById('wdf_stripe_button');
                if(btn){
                    btn.addEventListener('click', async function(e) {
                        e.preventDefault();
                        const response = await fetch('<?php echo admin_url('admin-ajax.php?action=wdf_stripe_create_session'); ?>', { method: 'POST' });
                        const session = await response.json();
                        const stripe = Stripe('<?php echo esc_js($publishable_key); ?>');
                        stripe.redirectToCheckout({ sessionId: session.id });
                    });
                }
            });
            </script>
            <?php
            return ob_get_clean();
        }

        function process_simple() {}
        function process_advanced() {}

        function handle_webhook() {
            $settings = get_option('wdf_settings');
            $secret = isset($settings['stripe']['webhook_secret']) ? $settings['stripe']['webhook_secret'] : '';
            $payload = @file_get_contents('php://input');
            $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
            if (!$secret) {
                status_header(400);
                exit('Webhook secret not set.');
            }
            require_once(WDF_PLUGIN_BASE_DIR . '/lib/gateways/vendor/autoload.php');
            try {
                $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $secret);
            } catch(Exception $e) {
                status_header(400);
                exit('Webhook error: ' . $e->getMessage());
            }
            if ($event->type == 'checkout.session.completed') {
                $session = $event->data->object;
                // Hier kannst du die Spende als bezahlt markieren
            }
            status_header(200);
            exit('Webhook received.');
        }
    }

    wdf_register_gateway_plugin('WDF_Gateway_Stripe', 'stripe', 'Stripe', array('simple','advanced'));

    // Webhook-Handler
    add_action('init', function() {
        if (isset($_GET['wdf_stripe_webhook'])) {
            $gateway = new WDF_Gateway_Stripe();
            $gateway->handle_webhook();
            exit;
        }
    });
}

// AJAX-Handler – Stripe-Aktiv-Prüfung jetzt HIER!
function wdf_stripe_create_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $settings = get_option('wdf_settings');
    if (!isset($settings['active_gateways']['stripe']) || $settings['active_gateways']['stripe'] != '1') {
        wp_send_json_error(['error' => 'Stripe nicht aktiv!'], 400);
    }

    require_once(WDF_PLUGIN_BASE_DIR . '/lib/gateways/vendor/autoload.php');
    \Stripe\Stripe::setApiKey($settings['stripe']['secret_key']);
    $amount = isset($_SESSION['wdf_pledge']) ? $_SESSION['wdf_pledge'] : 0;
    $currency = isset($settings['currency']) ? strtolower($settings['currency']) : 'eur';

    if (!$amount || !$settings['stripe']['secret_key']) {
        wp_send_json_error([
            'error' => 'Amount or Stripe key missing',
            'amount' => $amount,
            'session' => $_SESSION
        ], 400);
    }

    try {
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => __('Spende', 'wdf'),
                    ],
                    'unit_amount' => $amount * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => add_query_arg('wdf_stripe_success', 1, wdf_get_funder_page('confirmation')),
            'cancel_url' => add_query_arg('wdf_stripe_cancel', 1, wdf_get_funder_page('checkout')),
            'client_reference_id' => isset($_SESSION['wdf_pledge_id']) ? $_SESSION['wdf_pledge_id'] : '',
        ]);
        wp_send_json(['id' => $session->id]);
    } catch (Exception $e) {
        wp_send_json_error(['error' => $e->getMessage()], 400);
    }
}
?>