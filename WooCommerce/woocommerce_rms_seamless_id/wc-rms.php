<?php
/**
 * Razer Merchant Services WooCommerce Shopping Cart Plugin
 * 
 * @author Razer Merchant Services Technical Team <technical-sa@razer.com>
 * @version 6.1.4
 * @example For callback : http://shoppingcarturl/?wc-api=WC_Molpay_Gateway
 * @example For notification : http://shoppingcarturl/?wc-api=WC_Molpay_Gateway
 */

/**
 * Plugin Name: WooCommerce Razer Merchant Services Seamless
 * Plugin URI: https://github.com/RazerMS/WordPress_WooCommerce_WP-eCommerce_ClassiPress
 * Description: WooCommerce Razer Merchant Services | The leading payment gateway in South East Asia Grow your business with Razer Merchant Services payment solutions & free features: Physical Payment at 7-Eleven, Seamless Checkout, Tokenization, Loyalty Program and more for WooCommerce
 * Author: Razer Merchant Services Tech Team
 * Author URI: https://merchant.razer.com/
 * Version: 6.1.4
 * License: MIT
 * Text Domain: wcmolpay
 * Domain Path: /languages/
 * For callback : http://shoppingcarturl/?wc-api=WC_Molpay_Gateway
 * For notification : http://shoppingcarturl/?wc-api=WC_Molpay_Gateway
 * Invalid Transaction maybe is because vkey not found / skey wrong generated
 */

/**
 * If WooCommerce plugin is not available
 * 
 */
function wcmolpay_woocommerce_fallback_notice() {
    $message = '<div class="error">';
    $message .= '<p>' . __( 'WooCommerce Razer Merchant Services Gateway depends on the last version of <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> to work!' , 'wcmolpay' ) . '</p>';
    $message .= '</div>';
    echo $message;
}

//Load the function
add_action( 'plugins_loaded', 'wcmolpay_gateway_load', 0 );

/**
 * Load Razer Merchant Services gateway plugin function
 * 
 * @return mixed
 */
function wcmolpay_gateway_load() {
    if ( !class_exists( 'WC_Payment_Gateway' ) ) {
        add_action( 'admin_notices', 'wcmolpay_woocommerce_fallback_notice' );
        return;
    }

    //Load language
    load_plugin_textdomain( 'wcmolpay', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

    add_filter( 'woocommerce_payment_gateways', 'wcmolpay_add_gateway' );

    /**
     * Add Razer Merchant Services gateway to ensure WooCommerce can load it
     * 
     * @param array $methods
     * @return array
     */
    function wcmolpay_add_gateway( $methods ) {
        $methods[] = 'WC_Molpay_Gateway';
        return $methods;
    }

    /**
     * Define the Razer Merchant Services gateway
     * 
     */
    class WC_Molpay_Gateway extends WC_Payment_Gateway {

        /**
         * Construct the Razer Merchant Services gateway class
         * 
         * @global mixed $woocommerce
         */
        public function __construct() {
            global $woocommerce;

            $this->id = 'molpay';
            $this->icon = plugins_url( 'images/logo_RazerMerchantServices.png', __FILE__ );
            $this->has_fields = false;
            $this->method_title = __( 'Razer Merchant Services', 'wcmolpay' );
            $this->method_description = __( 'Proceed payment via Razer Merchant Services Seamless Integration Plugin', 'woocommerce' );

            // Load the form fields.
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();

            // Define user setting variables.
            $this->title = $this->settings['title'];
            $this->ordering_plugin = $this->get_option('ordering_plugin');
            $this->payment_title = $this->settings['payment_title'];
            $this->description = $this->settings['description'];
            $this->merchant_id = $this->settings['merchant_id'];
            $this->verify_key = $this->settings['verify_key'];
            $this->secret_key = $this->settings['secret_key'];
            $this->account_type = $this->settings['account_type'];
            
            // Define hostname based on account_type
            $this->url = ($this->get_option('account_type')=='1') ? "https://pg.e2pay.co.id/" : "https://pg-uat.e2pay.co.id/" ;
            $this->inquiry_url = ($this->get_option('account_type')=='1') ? "https://api.e2pay.co.id/" : "https://api-uat.e2pay.co.id/" ;
            
            // Define channel setting variables            
            $this->e2Pay_DANA = ($this->get_option('e2Pay_DANA')=='yes' ? true : false);
            $this->e2Pay_LINKAJA_APPLINK = ($this->get_option('e2Pay_LINKAJA_APPLINK')=='yes' ? true : false);
            $this->e2Pay_CIMB_OCTO_MOBILE = ($this->get_option('e2Pay_CIMB_OCTO_MOBILE')=='yes' ? true : false);
            $this->e2Pay_SHOPEEPAY_JUMPAPP = ($this->get_option('e2Pay_SHOPEEPAY_JUMPAPP')=='yes' ? true : false);
            $this->e2Pay_OVO = ($this->get_option('e2Pay_OVO')=='yes' ? true : false);
            $this->e2Pay_NUCash = ($this->get_option('e2Pay_NUCash')=='yes' ? true : false);
            $this->e2Pay_CIMBOctoClicks_IB = ($this->get_option('e2Pay_CIMBOctoClicks_IB')=='yes' ? true : false);
            $this->e2Pay_Kredivo_FN = ($this->get_option('e2Pay_Kredivo_FN')=='yes' ? true : false);
            $this->CIMB_NIAGA = ($this->get_option('CIMB_NIAGA')=='yes' ? true : false);
            $this->e2Pay_CIMB_Rekening_Ponsel = ($this->get_option('e2Pay_CIMB_Rekening_Ponsel')=='yes' ? true : false);
            $this->e2Pay_PERMATA_VA = ($this->get_option('e2Pay_PERMATA_VA')=='yes' ? true : false);
            $this->e2Pay_BNI_VA = ($this->get_option('e2Pay_BNI_VA')=='yes' ? true : false);
            $this->e2Pay_CIMB_VA = ($this->get_option('e2Pay_CIMB_VA')=='yes' ? true : false);
            $this->e2Pay_BCA_VA = ($this->get_option('e2Pay_BCA_VA')=='yes' ? true : false);
            $this->e2Pay_BRI_VA = ($this->get_option('e2Pay_BRI_VA')=='yes' ? true : false);
            $this->e2Pay_MANDIRI_VA = ($this->get_option('e2Pay_MANDIRI_VA')=='yes' ? true : false);

            // Transaction Type for Credit Channel
            $this->credit_tcctype = ($this->get_option('credit_tcctype')=='SALS' ? 'SALS' : 'AUTH');

            // Actions.
            add_action( 'valid_molpay_request_returnurl', array( &$this, 'check_molpay_response_returnurl' ) );
            add_action( 'valid_molpay_request_callback', array( &$this, 'check_molpay_response_callback' ) );
            add_action( 'valid_molpay_request_notification', array( &$this, 'check_molpay_response_notification' ) );
            add_action( 'woocommerce_receipt_molpay', array( &$this, 'receipt_page' ) );
            
            //save setting configuration
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
                        
            // Payment listener/API hook
            add_action( 'woocommerce_api_wc_molpay_gateway', array( $this, 'check_ipn_response' ) );
            
            // Checking if merchant_id is not empty.
            $this->merchant_id == '' ? add_action( 'admin_notices', array( &$this, 'merchant_id_missing_message' ) ) : '';

            // Checking if verify_key is not empty.
            $this->verify_key == '' ? add_action( 'admin_notices', array( &$this, 'verify_key_missing_message' ) ) : '';
            
            // Checking if secret_key is not empty.
            $this->secret_key == '' ? add_action( 'admin_notices', array( &$this, 'secret_key_missing_message' ) ) : '';
            
            // Checking if account_type is not empty.
            $this->account_type == '' ? add_action( 'admin_notices', array( &$this, 'account_type_missing_message' ) ) : '';
        }

        /**
         * Checking if this gateway is enabled and available in the user's country.
         *
         * @return bool
         */
        public function is_valid_for_use() {
            if ( !in_array( get_woocommerce_currency() , array( 'MYR', 'IDR' ) ) ) {
                return false;
            }
            return true;
        }

        /**
         * Admin Panel Options
         * - Options for bits like 'title' and availability on a country-by-country basis.
         *
         */
        public function admin_options() {
            ?>
            <h3><?php _e( 'Razer Merchant Services', 'wcmolpay' ); ?></h3>
            <p><?php _e( 'Razer Merchant Services works by sending the user to Razer Merchant Services to enter their payment information.', 'wcmolpay' ); ?></p>
            <table class="form-table">
                <?php $this->generate_settings_html(); ?>
            </table><!--/.form-table-->
            <?php
        }

        /**
         * Gateway Settings Form Fields.
         * 
         */
        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __( 'Enable/Disable', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( 'Enable Razer Merchant Services', 'wcmolpay' ),
                    'default' => 'yes'
                ),
                'ordering_plugin' => array(
                    'title' => __( '<p style="color:red;">Installed Ordering Plugins</p>', 'wcmolpay' ),
                    'type' => 'select',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'Sequential Order Numbers',
                    'options' => array(
                        '0' => __( 'Not install any ordering plugin', 'wcmolpay'),
                        '1' => __( 'Sequential Order Numbers', 'wcmolpay' ),
                        '2' => __( 'Sequential Order Numbers Pro', 'wcmolpay' ),
                        '3' => __( 'Advanced Order Numbers', 'wcmolpay' ),
                        '4' => __( 'Custom Order Numbers', 'wcmolpay' )
                    ),
                    'description' => __( 'Please select correct ordering plugin as it will affect your order result!!', 'wcmolpay' ),
                    'desc_tip' => true,
                ),
                'title' => array(
                    'title' => __( 'Title', 'wcmolpay' ),
                    'type' => 'text',
                    'description' => __( 'This controls the title which the user sees during checkout.', 'wcmolpay' ),
                    'default' => __( 'Razer Merchant Services', 'wcmolpay' ),
                    'desc_tip' => true,
                ),
                'payment_title' => array(
                    'title' => __( 'Payment Title', 'wcmolpay'),
                    'type' => 'checkbox',
                    'label' => __( 'Showing channel instead of gateway title after payment.'),
                    'description' => __( 'This controls the payment method which the user sees after payment.', 'wcmolpay' ),
                    'default' => 'no',
                    'desc_tip' => true
                ),
                'description' => array(
                    'title' => __( 'Description', 'wcmolpay' ),
                    'type' => 'textarea',
                    'description' => __( 'This controls the description which the user sees during checkout.', 'wcmolpay' ),
                    'default' => __( 'Razer Merchant Services', 'wcmolpay' ),
                    'desc_tip' => true,
                ),
                'merchant_id' => array(
                    'title' => __( 'Merchant ID', 'wcmolpay' ),
                    'type' => 'text',
                    'description' => __( 'Please enter your Razer Merchant Services Merchant ID.', 'wcmolpay' ) . ' ' . sprintf( __( 'You can to get this information in: %sRazer Merchant Services Account%s.', 'wcmolpay' ), '<a href="https://portal.merchant.razer.com/" target="_blank">', '</a>' ),
                    'default' => ''
                ),
                'verify_key' => array(
                    'title' => __( 'Verify Key', 'wcmolpay' ),
                    'type' => 'text',
                    'description' => __( 'Please enter your Razer Merchant Services Verify Key.', 'wcmolpay' ) . ' ' . sprintf( __( 'You can to get this information in: %sRazer Merchant Services Account%s.', 'wcmolpay' ), '<a href="https://portal.merchant.razer.com/" target="_blank">', '</a>' ),
                    'default' => ''
                ),
                'secret_key' => array(
                    'title' => __( 'Secret Key', 'wcmolpay' ),
                    'type' => 'text',
                    'description' => __( 'Please enter your Razer Merchant Services Secret Key.', 'wcmolpay' ) . ' ' . sprintf( __( 'You can to get this information in: %sRazer Merchant Services Account%s.', 'wcmolpay' ), '<a href="https://portal.merchant.razer.com/" target="_blank">', '</a>' ),
                    'default' => ''
                ),
                'account_type' => array(
                    'title' => __( 'Account Type', 'wcmolpay' ),
                    'type' => 'select',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'PRODUCTION',
                    'options' => array(
                        '1'  => __('PRODUCTION', 'wcmolpay' ),
                        '2' => __( 'SANDBOX', 'wcmolpay' )
                        )
                ),
                'channel' => array(
                    'title'         => 'Channel to be Enabled',
                    'type'          => 'title',
                    'description'   => '',
                ),
                'e2Pay_DANA' => array(
                    'title' => __( 'DANA', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'                
                ),
                'e2Pay_LINKAJA_APPLINK' => array(
                    'title' => __( 'LINKAJA', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'                
                ),
                'e2Pay_CIMB_OCTO_MOBILE' => array(
                    'title' => __( 'CIMB OCTO MOBILE', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'                
                ),
                'e2Pay_SHOPEEPAY_JUMPAPP' => array(
                    'title' => __( 'SHOPEEPAY', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'                
                ),
                'e2Pay_OVO' => array(
                    'title' => __( 'OVO', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'                
                ),
                'e2Pay_NUCash' => array(
                    'title' => __( 'NUCash', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'                
                ),
                'e2Pay_CIMBOctoClicks_IB' => array(
                    'title' => __( 'CIMB Octo Clicks IB', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'                
                ),
                'e2Pay_Kredivo_FN' => array(
                    'title' => __( 'Kredivo', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'                
                ),
                'CIMB_NIAGA' => array(
                    'title' => __( 'CIMB NIAGA', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'                
                ),
                'e2Pay_CIMB_Rekening_Ponsel' => array(
                    'title' => __( 'CIMB Rekening Ponsel', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'                
                ),
                'e2Pay_PERMATA_VA' => array(
                    'title' => __( 'PERMATA VA', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'                
                ),
                'e2Pay_BNI_VA' => array(
                    'title' => __( 'BNI VA', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'                
                ),
                'e2Pay_CIMB_VA' => array(
                    'title' => __( 'CIMB VA', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'                
                ),
                'e2Pay_BCA_VA' => array(
                    'title' => __( 'BCA VA', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'                
                ),
                'e2Pay_BRI_VA' => array(
                    'title' => __( 'BRI VA', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'                
                ),
                'e2Pay_MANDIRI_VA' => array(
                    'title' => __( 'MANDIRI VA', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'                
                ),
                'tcctype' => array(
                    'title'         => 'Transaction Type for Credit Card / Debit Card Channel',
                    'type'          => 'title',
                    'description'   => '',
                ),
                'credit_tcctype' => array(
                    'title' => __( 'Credit Card/ Debit Card', 'wcmolpay' ),
                    'type' => 'select',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'SALS',
                    'options' => array(
                        'SALS'  => __('SALS', 'wcmolpay' ),
                        'AUTH' => __( 'AUTH', 'wcmolpay' )
                    ),
                's' => array(
                    'title' => __( 'Credit Card/ Debit Card', 'wcmolpay' ),
                    'type' => 'select',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'SALS',
                    'options' => array(
                        'SALS'  => __('SALS', 'wcmolpay' ),
                        'AUTH' => __( 'AUTH', 'wcmolpay' )
                        )
                )
                )
            );
        }

        /**
         * Generate the form.
         *
         * @param mixed $order_id
         * @return string
         */
        public function generate_form( $order_id ) {
            $order = new WC_Order( $order_id );
            $pay_url = $this->url.'MOLPay/pay/'.$this->merchant_id;
            $total = $order->get_total();
            $order_number = $order->get_order_number();
            $vcode = md5($order->get_total().$this->merchant_id.$order_number.$this->verify_key);
            
            if ( sizeof( $order->get_items() ) > 0 ) 
                foreach ( $order->get_items() as $item )
                    if ( $item['qty'] )
                        $item_names[] = $item['name'] . ' x ' . $item['qty'];

            $desc = sprintf( __( 'Order %s' , 'woocommerce'), $order_number ) . " - " . implode( ', ', $item_names );
                        
            $molpay_args = array(
                'vcode' => $vcode,
                'orderid' => $order_number,
                'amount' => $total,
                'bill_name' => $order->get_billing_first_name()." ".$order->get_billing_last_name(),
                'bill_mobile' => $order->get_billing_phone(),
                'bill_email' => $order->get_billing_email(),
                'bill_desc' => $desc,
                'country' => $order->get_billing_country(),
                'cur' => get_woocommerce_currency(),
                'returnurl' => add_query_arg( 'wc-api', 'WC_Molpay_Gateway', home_url( '/' ) )
            );

            $molpay_args_array = array();

            foreach ($molpay_args as $key => $value) {
                $molpay_args_array[] = "<input type='hidden' name='".$key."' value='". $value ."' />";
            }
            
            $mpsreturn = add_query_arg( 'wc-api', 'WC_Molpay_Gateway', home_url( '/' ));
            $latest = ($this->get_option('account_type')=='1') ? "3.28" : "latest" ;
            return "<form action='".$pay_url."/' method='post' id='molpay_payment_form' name='molpay_payment_form'  
            onsubmit='if(document.getElementById(\"agree\").checked) { return true; } else { alert(\"Please indicate that you have read and agree to the Terms and Conditions and Privacy Policy\"); return false; }'>"
                    . implode('', $molpay_args_array)
                    ."<script src='".$this->url."MOLPay/API/seamless/".$latest."/js/MOLPay_seamless.deco.js'></script>"
                    ."<h3><u>Pay via</u>:</h3><img src='".plugins_url( 'images/logo_RazerMerchantServices.png', __FILE__ )."' width='200px'>"
                    ."<br/>"
                    ."<br/>"
                    ." <input type='checkbox' name='checkbox' value='check' id='agree' /> I have read and agree to the <b> Terms & Conditions, Refund Policy</b> and <b>Privacy Policy</b>."
                    ."<br/>"
                    ."<br/>"                    
                    .($this->e2Pay_DANA ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='e2Pay_DANA' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/DANA.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->e2Pay_LINKAJA_APPLINK ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='e2Pay_LINKAJA_APPLINK' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/LINKAJA.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->e2Pay_CIMB_OCTO_MOBILE ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='e2Pay_CIMB_OCTO_MOBILE' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/CIMBOctoClicks.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->e2Pay_SHOPEEPAY_JUMPAPP ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='e2Pay_SHOPEEPAY_JUMPAPP' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/SHOPEEPAY.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->e2Pay_OVO ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='e2Pay_OVO' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/OVO.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->e2Pay_NUCash ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='e2Pay_NUCash' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/NUCash.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->e2Pay_CIMBOctoClicks_IB ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='e2Pay_CIMBOctoClicks_IB' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/CIMBOctoClicks.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->e2Pay_Kredivo_FN ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='e2Pay_Kredivo_FN' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/kredivo.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->CIMB_NIAGA ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='credit21' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/CIMBNiaga.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->e2Pay_CIMB_Rekening_Ponsel ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='e2Pay_CIMB_Rekening_Ponsel' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/CIMB_Rekening_Ponsel.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->e2Pay_PERMATA_VA ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='e2Pay_PERMATA_VA' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/Permata.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->e2Pay_BNI_VA ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='e2Pay_BNI_VA' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/BNI.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->e2Pay_CIMB_VA ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='e2Pay_CIMB_VA' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/CIMB_VA.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->e2Pay_BCA_VA ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='e2Pay_BCA_VA' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/BCA.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->e2Pay_BRI_VA ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='e2Pay_BRI_VA' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/BRI.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    . "</form>";
        }
        

        /**
         * Order error button.
         *
         * @param  object $order Order data.
         * @return string Error message and cancel button.
         */
        protected function molpay_order_error( $order ) {
            $html = '<p>' . __( 'An error has occurred while processing your payment, please try again. Or contact us for assistance.', 'wcmolpay' ) . '</p>';
            $html .='<a class="buttoncancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . __( 'Click to try again', 'wcmolpay' ) . '</a>';
            return $html;
        }

        /**
         * Process the payment and return the result.
         *
         * @param int $order_id
         * @return array
         */
        public function process_payment( $order_id ) {
            $order = new WC_Order( $order_id );
            return array(
                'result' => 'success',
                'redirect' => $order->get_checkout_payment_url( true )
            );
        }

        /**
         * Output for the order received page.
         * 
         * @param  object $order Order data.
         */
        public function receipt_page( $order ) {
            echo $this->generate_form( $order );
        }

        /**
         * Check for Razer Merchant Services Response
         *
         * @access public
         * @return void
         */
        function check_ipn_response() {
            @ob_clean();

            if ( !( isset($_POST['nbcb']) )) {
                do_action( "valid_molpay_request_returnurl", $_POST );
            } else if ( $_POST['nbcb']=='1' ) {
                do_action ( "valid_molpay_request_callback", $_POST );
            } else if ( $_POST['nbcb']=='2' ) {
                do_action ( "valid_molpay_request_notification", $_POST );
            } else {
                wp_die( "Razer Merchant Services Request Failure" );
            }
        }
        
        /**
         * This part is handle return response
         * 
         * @global mixed $woocommerce
         */
        function check_molpay_response_returnurl() {
            global $woocommerce;
            
            $verifyresult = $this->verifySkey($_POST);
            $status = $_POST['status'];
            if( !$verifyresult )
                $status = "-1";

            $WCOrderId = $this->get_WCOrderIdByOrderId($_POST['orderid']);
            $order = new WC_Order( $WCOrderId );

            $referer = "<br>Referer: ReturnURL";
            $getStatus =  $order->get_status();
            if(!in_array($getStatus,array('processing','completed'))) {
                if ($status == "11") {
                    $referer .= " (Inquiry)";
                    $status = $this->inquiry_status( $_POST['tranID'], $_POST['amount'], $_POST['domain']);
                }
                $this->update_Cart_by_Status($WCOrderId, $status, $_POST['tranID'], $referer, $_POST['channel']);
                if (in_array($status, array("00","22"))) {
                    wp_redirect($order->get_checkout_order_received_url());
                } else {
                    wp_redirect($order->get_cancel_order_url());
                }
            } else {
                wp_redirect($order->get_checkout_order_received_url());
            }
            $this->acknowledgeResponse($_POST);
            exit;
        }
        
        /**
         * This part is handle notification response
         * 
         * @global mixed $woocommerce
         */
        function check_molpay_response_notification() {
            global $woocommerce;
            $verifyresult = $this->verifySkey($_POST);
            $status = $_POST['status'];
            if ( !$verifyresult )
                $status = "-1";

            $WCOrderId = $this->get_WCOrderIdByOrderId($_POST['orderid']);
            $referer = "<br>Referer: NotificationURL";
            $this->update_Cart_by_Status($WCOrderId, $status, $_POST['tranID'], $referer, $_POST['channel']);
            $this->acknowledgeResponse($_POST);
        }

        /**
         * This part is handle callback response
         * 
         * @global mixed $woocommerce
         */
        function check_molpay_response_callback() {
            global $woocommerce;
            $verifyresult = $this->verifySkey($_POST);
            $status = $_POST['status'];
            if ( !$verifyresult )
                $status = "-1";
            
            $WCOrderId = $this->get_WCOrderIdByOrderId($_POST['orderid']);
            $referer = "<br>Referer: CallbackURL";
            $this->update_Cart_by_Status($WCOrderId, $status, $_POST['tranID'], $referer, $_POST['channel']);
            $this->acknowledgeResponse($_POST);
        }

        /**
         * Adds error message when not configured the merchant_id.
         * 
         */
        public function merchant_id_missing_message() {
            $message = '<div class="error">';
            $message .= '<p>' . sprintf( __( '<strong>Gateway Disabled</strong> You should fill in your Merchant ID in Razer Merchant Services. %sClick here to configure!%s' , 'wcmolpay' ), '<a href="' . get_admin_url() . 'admin.php?page=wc-settings&tab=checkout&section=wc_molpay_gateway">', '</a>' ) . '</p>';
            $message .= '</div>';
            echo $message;
        }

        /**
         * Adds error message when not configured the verify_key.
         * 
         */
        public function verify_key_missing_message() {
            $message = '<div class="error">';
            $message .= '<p>' . sprintf( __( '<strong>Gateway Disabled</strong> You should fill in your Verify Key in Razer Merchant Services. %sClick here to configure!%s' , 'wcmolpay' ), '<a href="' . get_admin_url() . 'admin.php?page=wc-settings&tab=checkout&section=wc_molpay_gateway">', '</a>' ) . '</p>';
            $message .= '</div>';
            echo $message;
        }

        /**
         * Adds error message when not configured the secret_key.
         * 
         */
        public function secret_key_missing_message() {
            $message = '<div class="error">';
            $message .= '<p>' . sprintf( __( '<strong>Gateway Disabled</strong> You should fill in your Secret Key in Razer Merchant Services. %sClick here to configure!%s' , 'wcmolpay' ), '<a href="' . get_admin_url() . 'admin.php?page=wc-settings&tab=checkout&section=wc_molpay_gateway">', '</a>' ) . '</p>';
            $message .= '</div>';
            echo $message;
        }

        /**
         * Adds error message when not configured the account_type.
         * 
         */
        public function account_type_missing_message() {
            $message = '<div class="error">';
            $message .= '<p>' . sprintf( __( '<strong>Gateway Disabled</strong> Select account type in Razer Merchant Services. %sClick here to configure!%s' , 'wcmolpay' ), '<a href="' . get_admin_url() . 'admin.php?page=wc-settings&tab=checkout&section=wc_molpay_gateway">', '</a>' ) . '</p>';
            $message .= '</div>';
            echo $message;
        }

        /**
         * Inquiry transaction status
         *
         * @param int $tranID
         * @param double $amount
         * @param string $domain
         * @return status
         */
        public function inquiry_status($tranID, $amount, $domain) {
            $verify_key = $this->verify_key;
            $requestUrl = $this->inquiry_url."MOLPay/q_by_tid.php";
            $request_param = array(
                "amount"    => number_format($amount,2),
                "txID"      => intval($tranID),
                "domain"    => urlencode($domain),
                "skey"      => urlencode(md5(intval($tranID).$domain.$verify_key.number_format($amount,2))) );
            $post_data = http_build_query($request_param);
            $header[] = "Content-Type: application/x-www-form-urlencoded";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch,CURLOPT_URL, $requestUrl);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $response = trim($response);
            $temp = explode("\n", $response);
            foreach ( $temp as $value ) {
                $array = explode(':', $value);
                $key = trim($array[0], "[]");
                $result[$key] = trim($array[1]);
            }
            $verify = md5($result['Amount'].$this->secret_key.$result['Domain'].$result['TranID'].$result['StatCode']);
            if ($verify != $result['VrfKey']) {
                $result['StatCode'] = "99";
            }
            return $result['StatCode'];
        }

        /**
         * Update Cart based on Razer Merchant Services status
         * 
         * @global mixed $woocommerce
         * @param int $order_id
         * @param int $MOLPay_status
         * @param int $tranID
         * @param string $referer
         */
        public function update_Cart_by_Status($orderid, $MOLPay_status, $tranID, $referer, $channel) {
            global $woocommerce;

            $order = new WC_Order( $orderid );

            switch ($MOLPay_status) {
                case '00':
                    $M_status = 'SUCCESSFUL';
                    break;
                case '22':
                    $M_status = 'PENDING';
                    $W_status = 'pending';
                    break;
                case '11':
                    $M_status = 'FAILED';
                    $W_status = 'failed';
                    break;
                default:
                    $M_status = 'PENDING';
                    $W_status = 'pending';
                    break;
            }

            $getStatus = $order->get_status();
            if(!in_array($getStatus,array('processing','completed'))) {
                $order->add_order_note('Razer Merchant Services Payment Status: '.$M_status.'<br>Transaction ID: ' . $tranID . $referer);
                if ($MOLPay_status == "00") {
                    $order->payment_complete();
                } else {
                    $order->update_status($W_status, sprintf(__('Payment %s via Razer Merchant Services.', 'woocommerce'), $tranID ) );
                }
                if ($this->payment_title == 'yes') {
                    $paytitle = $this->form_fields[strtolower($channel)]['title'];
                    $order->set_payment_method_title($paytitle);
                    $order->save();
                }
            }
        }


        /**
         * Obtain the original order id based using the returned transaction order id
         * 
         * @global mixed $woocommerce
         * @param int $orderid
         * @return int $real_order_id
         */
        public function get_WCOrderIdByOrderId($orderid) {
            switch($this->ordering_plugin) {
                case '1' : // sequential order number
                    $WCOrderId = wc_sequential_order_numbers()->find_order_by_order_number( $orderid );
                    break;
                case '2' : // sequential order number pro
                    $WCOrderId = wc_seq_order_number_pro()->find_order_by_order_number( $orderid );
                    break;
                case '3' : // advanced order number
                    $WCOrderId = $this->find_order_by_advanced_order_number( $orderid, '_oton_number_ordernumber' );
                    break;
                case '4' : // custom order number
                    $WCOrderId = $this->find_order_by_custom_order_number($orderid, '_alg_wc_full_custom_order_number');
                    break;
                case '0' : 
                default :
                    $WCOrderId = $orderid;
                    break;
            }
            return $WCOrderId;
        }

        /**
         * Get order id from ordering plugin's order id.
         *
         * @global mixed  $woocommerce
         * @param  int    $orderid
         * @param  string $metaKey
         *
         * @return int
         */
        private function find_order_by_custom_order_number($orderid, $metaKey)
        {
            $query_args = array(
                'numberposts' => 1,
                'meta_key'    => $metaKey,
                'meta_value'  => $orderid,
                'post_type'   => 'shop_order',
                'post_status' => 'any',
                'fields'      => 'ids',
            );
            $post = get_posts( $query_args );
            list( $WCOrderId ) = $post;

            return $WCOrderId;
        }

        public function find_order_by_advanced_order_number( $order_number, $metaKey ) {

            $query_args = array(
                'numberposts' => 1,
                'meta_key'    => $metaKey,
                'meta_value'  => $order_number,
                'post_type'   => 'shop_order',
                'post_status' => 'any',
                'fields'      => 'ids',
            );
            $post = get_posts( $query_args );
            list( $order_number ) = ! empty( $post ) ? $post : null;

            return $order_number;

        }


        /**
         * Acknowledge transaction result
         * 
         * @global mixed $woocommerce
         * @param array $response
         */
        public function acknowledgeResponse($response) {
            if ($response['nbcb'] == '1') {
                echo "CBTOKEN:MPSTATOK"; exit;
            } else {
                $response['treq']= '1'; // Additional parameter for IPN
                foreach($response as $k => $v) {
                    $postData[]= $k."=".$v;
                }
                $postdata = implode("&",$postData);
                $url = $this->url."MOLPay/API/chkstat/returnipn.php";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_POST , 1 );
                curl_setopt($ch, CURLOPT_POSTFIELDS , $postdata );
                curl_setopt($ch, CURLOPT_URL , $url );
                curl_setopt($ch, CURLOPT_HEADER , 1 );
                curl_setopt($ch, CURLINFO_HEADER_OUT , TRUE );
                curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1 );
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , FALSE);
                curl_setopt($ch, CURLOPT_SSLVERSION , CURL_SSLVERSION_TLSv1 );
                $result = curl_exec( $ch );
                curl_close( $ch );
            }
        }

        /**
         * To verify transaction result using merchant secret key setting.
         * 
         * @global mixed $woocommerce
         * @param  array $response
         * @return boolean verifyresult
         */
        public function verifySkey($response) {

            $amount = $response['amount'];
            $orderid = $response['orderid'];
            $tranID = $response['tranID'];
            $status = $response['status'];
            $domain = $response['domain']; 
            $currency = $response['currency'];
            $appcode = $response['appcode'];
            $paydate = $response['paydate'];
            $skey = $response['skey'];
            $vkey = $this->secret_key;
            
            $key0 = md5($tranID.$orderid.$status.$domain.$amount.$currency);
            $key1 = md5($paydate.$domain.$key0.$appcode.$vkey);
            if ($skey != $key1)
                return false;
            else
                return true;
        }

    }
}