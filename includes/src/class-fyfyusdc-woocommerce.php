<?php
if (!defined('ABSPATH')) exit;

class WC_fyfy_usdc_gateway_Gateway extends WC_Payment_Gateway {


    public $rendered;

    public function __construct()
    {
        $this->id = 'fyfy_usdc_gateway'; // payment gateway plugin ID
        $this->asset_url = FYFY_PLUGIN_URL;
        $this->icon = $this->asset_url . 'assets/images/coin_payment_logo.png'; // URL of the icon that will be displayed on checkout page near your gateway name
        $this->has_fields = true; // in case you need a custom credit card form
        $this->method_title = 'FyFy USDC Gateway';
        $this->method_description = 'Allow customers to pay using USDC'; // will be displayed on the options page

        // but in this tutorial we begin with simple payments
        $this->supports = array(
            'products'
        );

        // Method with all the options fields
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->store_address =  $this->get_option('store_address');
        $this->valid_store_address =  $this->get_option('valid_store_address');
        $this->customer_display_message = $this->get_option('customer_display_message');
        $this->usdc_logo_url = $this->asset_url . '/assets/images/usdc_logo.png';
        $this->usdc_contract_address = 'CpMah17kQEL2wqyMKt3mZBdTnZbkbfx4nqmQMFDP5vwp';
        $this->transaction_verification_api = 'http://localhost:3000/check_fyfy';
        $this->nonce = "GwM8awN0w3Hzcl6aqNLf978ST0QIPZDp1U/sJWOs06BonPgzQPYg5uacZ+zbM/J5+nMExLGBSWr5vSa/o/A/czG5oFGKMFtuPulZCph3GC8cvq0B2y/ZMBT/cA24ed0Dz5PbtdvvtfX8SIyrrrutCCiMUcv7DieKSbFxc8e8Q7CJN/iXMPC0VzDwtfMD9x46Ie0fLrDyS1iI1nZHQWcHmMAJdEmUI2z2a7XunkbwFJbvaS+guKhs2i2p2OQLgo7PjUlNrIlBbQl25zjlIhIBLNxH7g/0r7HvHMU4DtdK37rLiH9mtqkM4XWx7wtN04eeAw75ZrqQRjf0UZrVv7cW6/P+fG6SJY2iwcev3swGNcwdp+q8Df6CxEjaNop0B/B83mcSO3k9O25hBwsxnnCyaf9lStg/xVozqQ4XqwmUA0BqyCYQM5hHt4gV5GYsGotHJ11L99yRv66lsLhJJFdcZWpnGZEMXqnaQMkOO2JfLOIwy8MDIDQZhW7XFiv36pHLa51mOGA1X+/iS1aXgddSOSpMOwb7cIDkZJNxrsLdpNxTzAG/+pGFzjeO8VR2nhGXqQfM35RI1hNESoTHUZmqeZ77AwNvxYSFJrqVluccwt+IbLHg/7lxpW5efmB2Z7i9eC/YZBSoEHofRLOq92g87qIzCRlQaKX+pB9o6+Y3yxA=";
        $this->sol_logo = 'data:image/svg+xml;base64,PHN2ZyBmaWxsPSJub25lIiBoZWlnaHQ9IjUwIiB2aWV3Qm94PSIwIDAgNTAgNTAiIHdpZHRoPSI1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+PGxpbmVhckdyYWRpZW50IGlkPSJhIj48c3RvcCBvZmZzZXQ9IjAiIHN0b3AtY29sb3I9IiNmZmMxMGIiLz48c3RvcCBvZmZzZXQ9IjEiIHN0b3AtY29sb3I9IiNmYjNmMmUiLz48L2xpbmVhckdyYWRpZW50PjxsaW5lYXJHcmFkaWVudCBpZD0iYiIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSI2LjQ3ODM1IiB4Mj0iMzQuOTEwNyIgeGxpbms6aHJlZj0iI2EiIHkxPSI3LjkyIiB5Mj0iMzMuNjU5MyIvPjxyYWRpYWxHcmFkaWVudCBpZD0iYyIgY3g9IjAiIGN5PSIwIiBncmFkaWVudFRyYW5zZm9ybT0ibWF0cml4KDQuOTkyMTg4MzIgMTIuMDYzODc5NjMgLTEyLjE4MTEzNjU1IDUuMDQwNzEwNzQgMjIuNTIwMiAyMC42MTgzKSIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHI9IjEiIHhsaW5rOmhyZWY9IiNhIi8+PHBhdGggZD0ibTI1LjE3MDggNDcuOTEwNGMuNTI1IDAgLjk1MDcuNDIxLjk1MDcuOTQwM3MtLjQyNTcuOTQwMi0uOTUwNy45NDAyLS45NTA3LS40MjA5LS45NTA3LS45NDAyLjQyNTctLjk0MDMuOTUwNy0uOTQwM3ptLTEuMDMyOC00NC45MTU2NWMuNDY0Ni4wMzgzNi44Mzk4LjM5MDQuOTAyNy44NDY4MWwxLjEzMDcgOC4yMTU3NGMuMzc5OCAyLjcxNDMgMy42NTM1IDMuODkwNCA1LjY3NDMgMi4wNDU5bDExLjMyOTEtMTAuMzExNThjLjI3MzMtLjI0ODczLjY5ODktLjIzMTQ5Ljk1MDcuMDM4NTEuMjMwOS4yNDc3Mi4yMzc5LjYyNjk3LjAxNjEuODgyNzdsLTkuODc5MSAxMS4zOTU4Yy0xLjgxODcgMi4wOTQyLS40NzY4IDUuMzY0MyAyLjI5NTYgNS41OTc4bDguNzE2OC44NDAzYy40MzQxLjA0MTguNzUxNy40MjM0LjcwOTMuODUyNC0uMDM0OS4zNTM3LS4zMDc0LjYzOTUtLjY2MjguNjk0OWwtOS4xNTk0IDEuNDMwMmMtMi42NTkzLjM2MjUtMy44NjM2IDMuNTExNy0yLjEzMzkgNS41NTc2bDMuMjIgMy43OTYxYy4yNTk0LjMwNTguMjE4OC43NjE1LS4wOTA4IDEuMDE3OC0uMjYyMi4yMTcyLS42NDE5LjIyNTYtLjkxMzguMDIwM2wtMy45Njk0LTIuOTk3OGMtMi4xNDIxLTEuNjEwOS01LjIyOTctLjI0MTctNS40NTYxIDIuNDI0M2wtLjg3NDcgMTAuMzk3NmMtLjAzNjIuNDI5NS0uNDE3OC43NDg3LS44NTI1LjcxMy0uMzY5LS4wMzAzLS42NjcxLS4zMDk3LS43MTcxLS42NzIxbC0xLjM4NzEtMTAuMDQzN2MtLjM3MTctMi43MTQ0LTMuNjQ1NC0zLjg5MDQtNS42NzQzLTIuMDQ1OWwtMTIuMDUxOTUgMTAuOTc0Yy0uMjQ5NDcuMjI3MS0uNjM4MDkuMjExNC0uODY4LS4wMzUtLjIxMDk0LS4yMjYyLS4yMTczNS0uNTcyNC0uMDE0OTMtLjgwNmwxMC41MTgxOC0xMi4xMzg1YzEuODE4Ny0yLjA5NDIuNDg0OS01LjM2NDQtMi4yODc2LTUuNTk3OGwtOC43MTg3Mi0uODQwNWMtLjQzNDEzLS4wNDE4LS43NTE3Mi0uNDIzNS0uNzA5MzYtLjg1MjQuMDM0OTMtLjM1MzcuMzA3MzktLjYzOTQuNjYyNy0uNjk1bDkuMTUzMzgtMS40Mjk5YzIuNjU5NC0uMzYyNSAzLjg3MTgtMy41MTE3IDIuMTQyMS01LjU1NzZsLTIuMTkyLTIuNTg0MWMtLjMyMTctLjM3OTItLjI3MTMtLjk0NDMuMTEyNi0xLjI2MjEuMzI1My0uMjY5NC43OTYzLS4yNzk3IDEuMTMzNC0uMDI0OWwyLjY5MTggMi4wMzQ3YzIuMTQyMSAxLjYxMDkgNS4yMjk3LjI0MTcgNS40NTYxLTIuNDI0M2wuNzI0MS04LjU1OTk4Yy4wNDU3LS41NDA4LjUyNjUtLjk0MjU3IDEuMDczOS0uODk3Mzd6bS0yMy4xODczMyAyMC40Mzk2NWMuNTI1MDQgMCAuOTUwNjcuNDIxLjk1MDY3Ljk0MDNzLS40MjU2My45NDAzLS45NTA2Ny45NDAzYy0uNTI1MDQxIDAtLjk1MDY3LS40MjEtLjk1MDY3LS45NDAzcy40MjU2MjktLjk0MDMuOTUwNjctLjk0MDN6bTQ3LjY3OTczLS45NTQ3Yy41MjUgMCAuOTUwNy40MjEuOTUwNy45NDAzcy0uNDI1Ny45NDAyLS45NTA3Ljk0MDItLjk1MDctLjQyMDktLjk1MDctLjk0MDIuNDI1Ny0uOTQwMy45NTA3LS45NDAzem0tMjQuNjI5Ni0yMi40Nzk3Yy41MjUgMCAuOTUwNi40MjA5NzMuOTUwNi45NDAyNyAwIC41MTkzLS40MjU2Ljk0MDI3LS45NTA2Ljk0MDI3LS41MjUxIDAtLjk1MDctLjQyMDk3LS45NTA3LS45NDAyNyAwLS41MTkyOTcuNDI1Ni0uOTQwMjcuOTUwNy0uOTQwMjd6IiBmaWxsPSJ1cmwoI2IpIi8+PHBhdGggZD0ibTI0LjU3MSAzMi43NzkyYzQuOTU5NiAwIDguOTgwMi0zLjk3NjUgOC45ODAyLTguODgxOSAwLTQuOTA1My00LjAyMDYtOC44ODE5LTguOTgwMi04Ljg4MTlzLTguOTgwMiAzLjk3NjYtOC45ODAyIDguODgxOWMwIDQuOTA1NCA0LjAyMDYgOC44ODE5IDguOTgwMiA4Ljg4MTl6IiBmaWxsPSJ1cmwoI2MpIi8+PC9zdmc+';
        $this->phan_logo = 'data:image/svg+xml;base64,PHN2ZyBmaWxsPSJub25lIiBoZWlnaHQ9IjM0IiB3aWR0aD0iMzQiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGxpbmVhckdyYWRpZW50IGlkPSJhIiB4MT0iLjUiIHgyPSIuNSIgeTE9IjAiIHkyPSIxIj48c3RvcCBvZmZzZXQ9IjAiIHN0b3AtY29sb3I9IiM1MzRiYjEiLz48c3RvcCBvZmZzZXQ9IjEiIHN0b3AtY29sb3I9IiM1NTFiZjkiLz48L2xpbmVhckdyYWRpZW50PjxsaW5lYXJHcmFkaWVudCBpZD0iYiIgeDE9Ii41IiB4Mj0iLjUiIHkxPSIwIiB5Mj0iMSI+PHN0b3Agb2Zmc2V0PSIwIiBzdG9wLWNvbG9yPSIjZmZmIi8+PHN0b3Agb2Zmc2V0PSIxIiBzdG9wLWNvbG9yPSIjZmZmIiBzdG9wLW9wYWNpdHk9Ii44MiIvPjwvbGluZWFyR3JhZGllbnQ+PGNpcmNsZSBjeD0iMTciIGN5PSIxNyIgZmlsbD0idXJsKCNhKSIgcj0iMTciLz48cGF0aCBkPSJtMjkuMTcwMiAxNy4yMDcxaC0yLjk5NjljMC02LjEwNzQtNC45NjgzLTExLjA1ODE3LTExLjA5NzUtMTEuMDU4MTctNi4wNTMyNSAwLTEwLjk3NDYzIDQuODI5NTctMTEuMDk1MDggMTAuODMyMzctLjEyNDYxIDYuMjA1IDUuNzE3NTIgMTEuNTkzMiAxMS45NDUzOCAxMS41OTMyaC43ODM0YzUuNDkwNiAwIDEyLjg0OTctNC4yODI5IDEzLjk5OTUtOS41MDEzLjIxMjMtLjk2MTktLjU1MDItMS44NjYxLTEuNTM4OC0xLjg2NjF6bS0xOC41NDc5LjI3MjFjMCAuODE2Ny0uNjcwMzggMS40ODQ3LTEuNDkwMDEgMS40ODQ3LS44MTk2NCAwLTEuNDg5OTgtLjY2ODMtMS40ODk5OC0xLjQ4NDd2LTIuNDAxOWMwLS44MTY3LjY3MDM0LTEuNDg0NyAxLjQ4OTk4LTEuNDg0Ny44MTk2MyAwIDEuNDkwMDEuNjY4IDEuNDkwMDEgMS40ODQ3em01LjE3MzggMGMwIC44MTY3LS42NzAzIDEuNDg0Ny0xLjQ4OTkgMS40ODQ3LS44MTk3IDAtMS40OS0uNjY4My0xLjQ5LTEuNDg0N3YtMi40MDE5YzAtLjgxNjcuNjcwNi0xLjQ4NDcgMS40OS0xLjQ4NDcuODE5NiAwIDEuNDg5OS42NjggMS40ODk5IDEuNDg0N3oiIGZpbGw9InVybCgjYikiLz48L3N2Zz4K';


        add_filter( 'woocommerce_available_payment_gateways', array( $this, 'checkout_payment_gateway' ) );
        add_filter( 'woocommerce_currency', array($this, 'set_usdc_currency'));
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_filter( 'woocommerce_checkout_fields' , array( $this,'remove_checkout_fields'));

        // Rest API Actions
        add_action( 'rest_api_init', array( $this, 'fyfy_check_payment_endpoint' ) );

        if ( 'yes' === $this->enabled ) {
            add_filter( 'woocommerce_thankyou_order_received_text', array( $this, 'order_received_text' ), 10, 2 );
        }
    }

    public function init_form_fields(){

        $pages_options = array();

        $pages = get_pages();
        if ( $pages ) {
            foreach ( $pages as $page ) {
                $pages_options[ $page->ID ] = $page->post_title;
            }
        }

        $this->form_fields = array(
            'enabled' => array(
                'title'       => 'Enable/Disable',
                'label'       => 'Enable FyFy USDC Payment Gateway',
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no'
            ),
            'title' => array(
                'title'       => 'Title',
                'type'        => 'text',
                'description' => 'This controls the title which the user sees during checkout.',
                'default'     => 'FyFy USDC Payment Gateway',
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => 'Description',
                'type'        => 'textarea',
                'description' => 'This controls the description which the user sees during checkout.',
                'default'     => 'Pay with your Phantom and SolFare in USDC.',
            ),
            'store_address' => array(
                'title'       => 'Store Wallet Address',
                'type'        => 'text',
                'description' => 'public key where store owner receive payment from customers',
                'required'    => true
            ),
            'valid_store_address' => array(
                'type'        => 'hidden',
                'default'     => 'valid',
            ),
            'fyfy_payment_processing_page' => array(
                'type' => 'select',
                'title' => 'Payment processing page',
                'options' => $pages_options,
                'description' => 'This is the FyFy USDC payment processing page where buyers will pay their order. The [fyfy_payment_proceed_page] shortcode must be on this page.'
            )
        );
    }

    /**
     * process the saving of FyFy USDC Gateway plugin
     */
    public function process_admin_options() {

        $saved = parent::process_admin_options();

        return $saved;
    }



    public function set_usdc_currency($array){

        if($this->enabled && $this->valid_store_address == 'valid'){
            return 'FYFYUSDC';
        }
    }



    /**
     * @param int $order_id
     * @return array
     */


    public function process_payment($order_id)
    {


        if( $this->enabled == "yes" && $this->valid_store_address == 'valid'){

            include_once dirname( __FILE__ ) . '/wc-fyfy-gateway-payment-process.php';
            // we need it to get any order detailes
            $order          = wc_get_order( $order_id );
            $paypal_request = new WC_GATEWAY_fyfy_request( $this );

            // Redirect to the thank you page

            $url_redirect = $paypal_request->get_request_url($order);

            $nonce = wp_create_nonce( 'fyfy_payment_nonce_key' );

            $url_redirect = add_query_arg(['_fyfynonce'=>$nonce], $url_redirect);

            return array(
                'result' => 'success',
                'redirect' => $url_redirect
            );
        }
    }

    public function checkout_payment_gateway($available_gateways){

        if ( $this->enabled && isset( $available_gateways['fyfy_usdc_gateway'] ) && $this->valid_store_address == 'valid' ) {
           foreach ($available_gateways as $key => $gateway){
               if($key != 'fyfy_usdc_gateway'){
                   unset($available_gateways[$key]);
               }
           }
        }
        else if(isset( $available_gateways['fyfy_usdc_gateway'] )){
            unset($available_gateways['fyfy_usdc_gateway']);
        }
        return $available_gateways;
    }



    /**
     * Custom order received text.
     *
     * @since 3.9.0
     * @param string   $text Default text.
     * @param WC_Order $order Order data.
     * @return string
     */
    public function order_received_text( $text, $order ) {

        if ( $order && $this->id === $order->get_payment_method() ) {

            return __( 'Thank you for your payment. Your transaction has been completed, and a receipt for your purchase has been emailed to you. Visit <a href="https://explorer.solana.com/" target="_blank">explorer.solana.com</a> to view transaction details.', 'woocommerce' );
        }

        return $text;
    }

    /**
     * @param $request
     */

    public function fyfy_payment_validate($request){

        try{


            $signature = $request->get_param('signature');
            $order_id = $request->get_param('order_id');

            if(empty($signature) || empty($order_id)) {
                throw new Exception('Missing Parameters!');
            }

            $paymentRepo = new fyfy_Payment_Repo();
            $order = new WC_Order($order_id);


            if ( $order->has_status('completed') ) {
                throw new Exception('Order Payment was already done!');
            }

            $check_duplication = $paymentRepo->check_duplication($signature);

            if(!$check_duplication){
                throw new Exception('Duplicated Signature!');
            }

            $body = array(
                'fyfy_nonce'     => $this->nonce,
                'signature' => $signature,
            );

            $response = wp_remote_post( $this->transaction_verification_api, array(
                'method' => 'POST',
                'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
                'body' => json_encode($body)
            ));

            if ( is_wp_error( $response )) {
                $error_message = $response->get_error_message();
                throw new Exception ($error_message);
            } else {

                $formattedCryptoTotal = $order->get_total();

                $db_result = $paymentRepo->insert($this->store_address, $order_id, $formattedCryptoTotal, 'paid', $signature);

                if($db_result){
                    $orderNote = sprintf(
                        'Order payment of %s %s verified at %s. Transaction Hash: %s',
                        'USDC',
                        $formattedCryptoTotal,
                        date('Y-m-d H:i:s', time()),
                        apply_filters('fyfy_order_txhash', $signature));

                    $order->payment_complete();
                    $order->add_order_note($orderNote);
                    $order->update_status('completed');

                    return new WP_REST_Response( $response, 200 );
                }
                else{
                    throw new Exception ('DB error:');
                }

            }

        }
        catch (Exception $exception){
            return new WP_Error( 'transaction_Check_Error', $exception->getMessage(), array( 'status' => 500 ) );
        }

    }

    /**
     *  Remove checkout fields in Woocommerce
     */
    public function remove_checkout_fields($fields){
        unset($fields['billing']['billing_first_name']);
        unset($fields['billing']['billing_last_name']);
        unset($fields['billing']['billing_company']);
        unset($fields['billing']['billing_address_1']);
        unset($fields['billing']['billing_address_2']);
        unset($fields['billing']['billing_city']);
        unset($fields['billing']['billing_postcode']);
        unset($fields['billing']['billing_country']);
        unset($fields['billing']['billing_state']);
        unset($fields['billing']['billing_phone']);
        unset($fields['order']['order_comments']);
        unset($fields['billing']['billing_email']);
        unset($fields['account']['account_username']);
        unset($fields['account']['account_password']);
        unset($fields['account']['account_password-2']);
        return $fields;
    }

    public function fyfy_check_payment_endpoint(){

        register_rest_route( 'fyfy-api/v1', 'check_payment',	array(
            'methods'  => 'POST',
            'callback' => array( $this, 'fyfy_payment_validate' ),
            'permission_callback' => function(){
                return true;
            }
        ));

        register_rest_route( 'fyfy-m-api/v1/', 'check_payment',	array(
            'methods'  => 'POST',
            'callback' => array( $this, 'fyfy_payment_validate' ),
            'permission_callback' => function(){
                return true;
            }
        ));
    }
}