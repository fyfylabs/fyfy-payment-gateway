<?php

/**
 * Generates requests to fyfy payment processing page.
 */

class WC_GATEWAY_fyfy_request {

    /**
     * Pointer to gateway making the request.
     *
     * @var WC_GATEWAY_FYFY
     */
    protected $gateway;


    /**
     * Endpoint for requests to processing page.
     *
     * @var string
     */
    protected $endpoint;


    /**
     * Constructor
     */

    public function __construct($gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Payment processing page URL
     * @param  WC_Order $order Order object.
     * @return string
     */
    public function get_request_url( $order ) {

        $processing_page_id = $this->gateway->get_option('fyfy_payment_processing_page');
        $this->endpoint = get_permalink($processing_page_id);

        $fyfy_args = $this->get_fyfy_args($order);

        return $this->endpoint . '?'. http_build_query( $fyfy_args, '', '&' );
    }

    /**
     * Get PayPal Args for passing to PP.
     *
     * @param  WC_Order $order Order object.
     * @return array
     */
    protected function get_fyfy_args( $order ) {

        $fyfy_args = apply_filters(
            'woocommerce_fyfy_args',
            array(
                'store_address' => $this->gateway->store_address,
                'customer_display_message' => $this->gateway->customer_display_message,
                'usdc_logo_url' => $this->gateway->usdc_logo_url,
                'usdc_contract_address' => $this->gateway->usdc_contract_address,
                'cmd'           => 'fyfy_cart',
                'currency_code' => get_woocommerce_currency(),
                'charset'       => 'utf-8',
                'return'        => esc_url_raw( $this->gateway->get_return_url( $order )),
                'cancel_return' => esc_url_raw( $order->get_cancel_order_url_raw() ),
                'image_url'     => esc_url_raw( $this->gateway->icon ),
                'custom'        => wp_json_encode(
                    array(
                        'order_key' => $order->get_order_key(),
                    )
                )
            ),
            $order
        );

        return $fyfy_args;
    }
}