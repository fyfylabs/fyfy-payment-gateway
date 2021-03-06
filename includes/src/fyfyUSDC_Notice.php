<?php
/**
 * Admin Notices Class
 *
 * @package     UsdcPayment
 * @subpackage  Admin/Notices
 * @copyright
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * fyfy_Notices Class
 *
 * @since 1.0.0
 */


class fyfyUSDC_Notice extends fyfy_component {

    private $settings;

    public function __construct() {
        $this->settings = self::load_class('fyfyUSDC_Setting', 'fyfyUSDC_Setting.php', 'class');
        $this->show_notices();
    }

    /**
     * Show relevant notices
     *
     * @since 1.0.0
     */

    public function show_notices() {

        if (function_exists('WC')) {
            if ($this->get_setting('enabled') && $this->get_setting('store_address') == '') {
                add_action('admin_notices', function(){
                    $plugin_link = admin_url( 'admin.php?page=wc-settings&tab=checkout&section='. FYFY_DOMAIN );
                    $this->notice('error', sprintf( __('If you did not specify a Store address in the <a href="%1$s">FyFy USDC Gateway settings</a>, the plugin will not work. Please specify a store address first.', 'fyfy_usdc'), $plugin_link));
                });
            }

            $curr = get_woocommerce_currency();

            if($this->get_setting('enabled')  && $curr != 'FYFYUSDC'){
                add_action('admin_notices', function(){
                    $plugin_link = admin_url( 'admin.php?page=wc-settings&tab=general' );
                    $this->notice('error', sprintf( __('If you did not specify Store Currency as USDT in the <a href="%1$s">General settings</a>, the plugin will not work. Please specify a store currency first.', 'fyfy_usdc'), $plugin_link));
                });
            }

        } else {
            add_action('admin_notices', function(){
                $plugin_link = admin_url('plugins.php');
                $this->notice('error', sprintf( __( 'The FyFy USDC Gateway For WooCommerce plugin cannot run without <a href="%1$s">WooCommerce</a> activation. Please install and activate WooCommerce plugin first.', 'fyfy_usdc' ),  $plugin_link  ));
            });
        }
    }

    /**
     * @param string $Setting_option
     * @return FYFY payment gateway option information
     */

    protected function get_setting($option){
        return $this->settings->get_setting($option);
    }

    /**
     * @param string $type error, success more
     * @param string $notice notice to be given
     * @param bool $dismissible in-dismissible button show and hide
     * @return void
     */
    protected function notice( $type,  $notice,  $dismissible = false)
    {
        $this->view('notice', array(
            'type' => $type,
            'notice' => $notice,
            'dismissible' => $dismissible
        ));
    }

    /**
     * @param string $viewName Directory name within the folder
     * @return void
     */
    protected function view($viewName, array $args = [])
    {
        ob_start();
        self::load_file('admin_'. $viewName .'.php', 'component', $args);
        echo ob_get_clean();
    }
}



