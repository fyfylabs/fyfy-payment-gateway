<?php
if (!defined('ABSPATH')) exit;

/**
 *
 * This file includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function that starts the plugin.
 *
 * @link              https://fyfy.io
 * @since             1.0.0
 * @package           fyfy_usdc
 *
 * @wordpress-plugin
 * Plugin Name:       FyFy USDC Gateway
 * Plugin URI:        https://fyfy.io
 * Description:       Add the USDC token in Woocommerce, making use of Phantom and SolFare for decentralized commerce.
 * Version:           1.0.0
 * Author:            FyFy
 * Author URI:        https://fyfy.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       fyfy_usdc
 */


if ( ! defined( 'FYFY_PLUGIN_FILE' ) ) {
    define( 'FYFY_PLUGIN_FILE', __FILE__ );
}

// Include the main UsdcPayment class.
if ( ! class_exists( 'UsdcPayment', false ) ) {


    include_once dirname( __FILE__ ) . '/includes/UsdcPayment.php';
}

define('fyfy_ABS_PATH', dirname(FYFY_PLUGIN_FILE));
define('fyfy_Payment_PROCESS', 'Finalise Order');

/**
 * Returns the main instance of UsdcPayment.
 *
 * @return UsdcPayment
 */
function FYFYUSDC() {
    return UsdcPayment::instance();
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/fyfy_usdc-activator.php
 */
function activate_fyfy_usdc() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/fyfy_usdc-activator.php';
    fyfycoin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/fyfy_usdc-deactivator.php
 */
function deactivate_fyfy_usdc() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/fyfy_usdc-deactivator.php';
}


register_activation_hook( __FILE__, 'activate_fyfy_usdc' );
register_deactivation_hook( __FILE__, 'deactivate_fyfy_usdc' );

//Get UsdcPayment Running.
FYFYUSDC();