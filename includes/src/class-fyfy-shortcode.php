<?php


defined('ABSPATH') || exit;

/**
 * FyFy Shortcodes class.
 */
class fyfy_Shortcodes extends fyfy_component
{

    /**
     * Init Shortcodes.
     */
    public static function init()
    {
        $shortcodes = array(
            'fyfy_payment_proceed_page' => __CLASS__ . '::processing_payment'
        );

        foreach ($shortcodes as $shortcode => $function) {
            add_shortcode(apply_filters("{$shortcode}_shortcode_tag", $shortcode), $function);
        }
    }


    /**
     * Render the Payment processing content
     */
    public static function processing_payment(){
        $fa_cssPath = FYFY_PLUGIN_URL.'/assets/css/all.min.css';
        $cssPath = FYFY_PLUGIN_URL.'/assets/css/payment_process.css';
        wp_enqueue_style('fyfy-fa-process', $fa_cssPath);
        wp_enqueue_style('fyfy-payment-process', $cssPath);

        $solana_web3 = "https://unpkg.com/@solana/web3.js@latest/lib/index.iife.min.js";
        $solana_spl = "https://unpkg.com/@solana/spl-token@latest/lib/index.iife.min.js";
        wp_enqueue_script( 'solana_web3', $solana_web3);
        wp_enqueue_script( 'solana_spl', $solana_spl);

        $fyfy_qrcode_lib = FYFY_PLUGIN_URL.'/assets/js/qrcode.min.js';
        wp_enqueue_script( 'fyfy_qrcode_lib', $fyfy_qrcode_lib);
        $fyfy_transfer = FYFY_PLUGIN_URL.'/assets/js/transfer.js';
        wp_enqueue_script( 'fyfy_transfer', $fyfy_transfer);

        wp_localize_script('fyfy_transfer', 'wpFyFYApi', array(
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest')
        ));


        $unauthorized = false;

        if(!wp_verify_nonce( $_GET['_fyfynonce'], 'fyfy_payment_nonce_key' )){
            $unauthorized = true;
        }

        if(isset($_GET['cmd']) && $_GET['cmd'] == 'fyfy_cart'){
            //
        }
        else{
            $unauthorized = true;
        }

        if(empty($_GET['return']) || empty($_GET['cancel_return'])){
            $unauthorized = true;
        }

        if(empty($_GET['custom'])){
            $unauthorized = true;
        }
        else{
            $data = stripslashes($_GET['custom']);
            $order_array = json_decode($data, true);

            if(!is_array($order_array) || !isset($order_array['order_key'])){

                $unauthorized = true;
            }
            else{

                $order_id = wc_get_order_id_by_order_key($order_array['order_key']);
                if(empty($order_id))
                {
                    $unauthorized = true;
                }
                else{
                    $order = new WC_Order($order_id);
                    $order_data = array(
                        'order_id' => $order_id,
                        'order_total_price' => (float)$order->get_total()
                    );
                }

            }
        }

        if($unauthorized){
            return self::view('unauthorized');
        }
        else{

            return self::view('payment_process',apply_filters('fyfy_payment_process_params', array_merge($_GET, $order_data)));
        }

    }

    /**
     * @param string $viewName Directory name within the folder
     * @return void
     */
    protected static function view($viewName, array $args = [])
    {
        ob_start();
        self::load_file('wc_fyfy_custom_'. $viewName .'.php', 'component', $args);
        echo ob_get_clean();
    }


}

add_action('init', array('fyfy_Shortcodes', 'init'));