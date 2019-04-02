<?php
/**
 * MOLPay WooCommerce Shopping Cart Plugin
 * 
 * @author MOLPay Technical Team <technical@molpay.com>
 * @version 2.6.0
 * @example For callback : http://shoppingcarturl/?wc-api=WC_Molpay_Gateway
 * @example For notification : http://shoppingcarturl/?wc-api=WC_Molpay_Gateway
 */

/**
 * Plugin Name: WooCommerce MOLPay Seamless for Sequential Order Numbers 
 * Plugin URI: http://www.molpay.com/
 * Description: WooCommerce MOLPay | The leading payment gateway in South East Asia Grow your business with MOLPay payment solutions & free features: Physical Payment at 7-Eleven, Seamless Checkout, Tokenization, Loyalty Program and more for WooCommerce
 * Author: MOLPay Tech Team
 * Author URI: http:/www.molpay.com/
 * Version: 2.6.0
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
    $message .= '<p>' . __( 'WooCommerce MOLPay Gateway depends on the last version of <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> to work!' , 'wcmolpay' ) . '</p>';
    $message .= '</div>';
    echo $message;
}

//Load the function
add_action( 'plugins_loaded', 'wcmolpay_gateway_load', 0 );

/**
 * Load MOLPay gateway plugin function
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
     * Add MOLPay gateway to ensure WooCommerce can load it
     * 
     * @param array $methods
     * @return array
     */
    function wcmolpay_add_gateway( $methods ) {
        $methods[] = 'WC_Molpay_Gateway';
        return $methods;
    }

    /**
     * Define the MOLPay gateway
     * 
     */
    class WC_Molpay_Gateway extends WC_Payment_Gateway {

        /**
         * Construct the MOLPay gateway class
         * 
         * @global mixed $woocommerce
         */
        public function __construct() {
            global $woocommerce;

            $this->id = 'molpay';
            $this->icon = plugins_url( 'images/molpay.gif', __FILE__ );
            $this->has_fields = false;
            $this->method_title = __( 'MOLPay', 'wcmolpay' );
            $this->method_description = __( 'Proceed payment via MOLPay Seamless Integration Plugin<br><font color="red"><b>This is for Sequential Order Numbers</b></font>', 'woocommerce' );

            // Load the form fields.
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();

            // Define user setting variables.
            $this->title = $this->settings['title'];
            $this->description = $this->settings['description'];
            $this->merchant_id = $this->settings['merchant_id'];
            $this->verify_key = $this->settings['verify_key'];
            $this->secret_key = $this->settings['secret_key'];
            $this->account_type = $this->settings['account_type'];
            
            // Define hostname based on account_type
            $this->url = ($this->get_option('account_type')=='1') ? "https://www.onlinepayment.com.my/" : "https://sandbox.molpay.com/" ;
            $this->inquiry_url = ($this->get_option('account_type')=='1') ? "https://api.molpay.com/" : "https://sandbox.molpay.com/" ;
            
            // Define channel setting variables
            $this->credit = ($this->get_option('credit')=='yes' ? true : false);
            $this->fpx = ($this->get_option('fpx')=='yes' ? true : false);
            $this->fpx_mb2u = ($this->get_option('fpx_mb2u')=='yes' ? true : false);
            $this->fpx_cimbclicks = ($this->get_option('fpx_cimbclicks')=='yes' ? true : false);
            $this->fpx_hlb = ($this->get_option('fpx_hlb')=='yes' ? true : false);
            $this->fpx_rhb = ($this->get_option('fpx_rhb')=='yes' ? true : false);
            $this->fpx_amb = ($this->get_option('fpx_amb')=='yes' ? true : false);
            $this->fpx_pbb = ($this->get_option('fpx_pbb')=='yes' ? true : false);
            $this->fpx_abb = ($this->get_option('fpx_abb')=='yes' ? true : false);
            $this->fpx_bimb = ($this->get_option('fpx_bimb')=='yes' ? true : false);
            $this->molwallet = ($this->get_option('molwallet')=='yes' ? true : false);
            $this->Point_BCard = ($this->get_option('Point-BCard')=='yes' ? true : false);
            $this->dragonpay = ($this->get_option('dragonpay')=='yes' ? true : false);
            $this->NGANLUONG = ($this->get_option('NGANLUONG')=='yes' ? true : false);
            $this->paysbuy = ($this->get_option('paysbuy')=='yes' ? true : false);
            $this->cash_711 = ($this->get_option('cash-711')=='yes' ? true : false);
            $this->ATMVA = ($this->get_option('ATMVA')=='yes' ? true : false);
            $this->enetsD = ($this->get_option('enetsD')=='yes' ? true : false);
            $this->singpost = ($this->get_option('singpost')=='yes' ? true : false);
            $this->UPOP = ($this->get_option('UPOP')=='yes' ? true : false);
            $this->alipay = ($this->get_option('alipay')=='yes' ? true : false);
            $this->WeChatPay = ($this->get_option('WeChatPay')=='yes' ? true : false);
            
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
            if ( !in_array( get_woocommerce_currency() , array( 'MYR' ) ) ) {
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
            <h3><?php _e( 'MOLPay Online Payment', 'wcmolpay' ); ?></h3>
            <p><?php _e( 'MOLPay Online Payment works by sending the user to MOLPay to enter their payment information.', 'wcmolpay' ); ?></p>
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
                    'label' => __( 'Enable MOLPay', 'wcmolpay' ),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __( 'Title', 'wcmolpay' ),
                    'type' => 'text',
                    'description' => __( 'This controls the title which the user sees during checkout.', 'wcmolpay' ),
                    'default' => __( 'MOLPay Malaysia Online Payment', 'wcmolpay' )
                ),
                'description' => array(
                    'title' => __( 'Description', 'wcmolpay' ),
                    'type' => 'textarea',
                    'description' => __( 'This controls the description which the user sees during checkout.', 'wcmolpay' ),
                    'default' => __( 'Pay with MOLPay Malaysia Online Payment', 'wcmolpay' )
                ),
                'merchant_id' => array(
                    'title' => __( 'Merchant ID', 'wcmolpay' ),
                    'type' => 'text',
                    'description' => __( 'Please enter your MOLPay Merchant ID.', 'wcmolpay' ) . ' ' . sprintf( __( 'You can to get this information in: %sMOLPay Account%s.', 'wcmolpay' ), '<a href="https://portal.molpay.com/" target="_blank">', '</a>' ),
                    'default' => ''
                ),
                'verify_key' => array(
                    'title' => __( 'Verify Key', 'wcmolpay' ),
                    'type' => 'text',
                    'description' => __( 'Please enter your MOLPay Verify Key.', 'wcmolpay' ) . ' ' . sprintf( __( 'You can to get this information in: %sMOLPay Account%s.', 'wcmolpay' ), '<a href="https://portal.molpay.com/" target="_blank">', '</a>' ),
                    'default' => ''
                ),
                'secret_key' => array(
                    'title' => __( 'Secret Key', 'wcmolpay' ),
                    'type' => 'text',
                    'description' => __( 'Please enter your MOLPay Secret Key.', 'wcmolpay' ) . ' ' . sprintf( __( 'You can to get this information in: %sMOLPay Account%s.', 'wcmolpay' ), '<a href="https://portal.molpay.com/" target="_blank">', '</a>' ),
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
                'credit' => array(
                    'title' => __( 'Credit Card/ Debit Card', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'fpx' => array(
                    'title' => __( 'MyClear FPX B2C', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'fpx_mb2u' => array(
                    'title' => __( 'FPX Maybank (Maybank2u)', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'fpx_cimbclicks' => array(
                    'title' => __( 'FPX CIMB Bank (CIMB Clicks)', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'fpx_hlb' => array(
                    'title' => __( 'FPX Hong Leong Bank (HLB Connect)', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'fpx_rhb' => array(
                    'title' => __( 'FPX RHB Bank (RHB Now)', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'fpx_amb' => array(
                    'title' => __( 'FPX Am Bank (Am Online)', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'fpx_pbb' => array(
                    'title' => __( 'FPX PublicBank (PBB Online)', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'fpx_abb' => array(
                    'title' => __( 'FPX Affin Bank (Affin Online)', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'fpx_bimb' => array(
                    'title' => __( 'FPX Bank Islam', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'molwallet' => array(
                    'title' => __( 'MOLWallet', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'Point-BCard' => array(
                    'title' => __( 'Point-BCard', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'dragonpay' => array(
                    'title' => __( 'Dragonpay', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'NGANLUONG' => array(
                    'title' => __( 'NGANLUONG', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'paysbuy' => array(
                    'title' => __( 'PaysBuy', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'cash-711' => array(
                    'title' => __( '7-Eleven (MOLPay Cash)', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'ATMVA' => array(
                    'title' => __( 'ATM Transfer via Permata Bank', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'enetsD' => array(
                    'title' => __( 'eNETS', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'singpost' => array(
                    'title' => __( 'Cash-SAM', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'UPOP' => array(
                    'title' => __( 'China Union Pay', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'alipay' => array(
                    'title' => __( 'Alipay', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'WeChatPay' => array(
                    'title' => __( 'WeChatPay Cross Border', 'wcmolpay' ),
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
            $total = $order->order_total;
            $order = wc_get_order( $order_id );
            $order_number = $order->get_order_number();
            $vcode = md5($order->order_total.$this->merchant_id.$order_number.$this->verify_key);
            
            if ( sizeof( $order->get_items() ) > 0 ) 
                foreach ( $order->get_items() as $item )
                    if ( $item['qty'] )
                        $item_names[] = $item['name'] . ' x ' . $item['qty'];

            $desc = sprintf( __( 'Order %s' , 'woocommerce'), $order_number ) . " - " . implode( ', ', $item_names );

            $molpay_args = array(
                'vcode' => $vcode,
                'orderid' => $order_number,
                'amount' => $total,
                'bill_name' => $order->billing_first_name." ".$order->billing_last_name,
                'bill_mobile' => $order->billing_phone,
                'bill_email' => $order->billing_email,
                'bill_desc' => $desc,
                'country' => $order->billing_country,
                'cur' => get_woocommerce_currency(),
                'returnurl' => add_query_arg( 'wc-api', 'WC_Molpay_Gateway', home_url( '/' ) )
            );

            $molpay_args_array = array();

            foreach ($molpay_args as $key => $value) {
                $molpay_args_array[] = "<input type='hidden' name='".$key."' value='". $value ."' />";
            }
            
             $mpsreturn = add_query_arg( 'wc-api', 'WC_Molpay_Gateway', home_url( '/' ));
            
            return "<form action='".$pay_url."/' method='post' id='molpay_payment_form' name='molpay_payment_form'>"
                    . implode('', $molpay_args_array)
                    // . "<input type='submit' class='button-alt' id='submit_molpay_payment_form' value='" . __('Pay via MOLPay', 'woothemes') . "' /> "
                    // . "<a class='buttoncancel' href='" . $order->get_cancel_order_url() . "'>".__('Cancel order &amp; restore cart', 'woothemes')."</a>"
                    ."<script src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js'></script>"
                    ."<script src='".$this->url."MOLPay/API/seamless/latest/js/MOLPay_seamless.deco.js'></script>"
                    ."<h3><u>Pay via</u>:</h3><img src='".plugins_url( 'images/molpay-logo.png', __FILE__ )."'>"
                    ."<br/>"
                    .($this->credit ? "<button type='button' style='background:none; padding:0px' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->billing_phone."' data-mpstcctype='".$this->credit_tcctype."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='credit' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/credit.png', __FILE__ )."' width='100px' height='50px'/></button>" : '') 
                    .($this->fpx ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->billing_phone."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx.png', __FILE__ )."' width='100px' height='50px'/>   </button>" : '')
                    .($this->fpx_mb2u ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->billing_phone."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_mb2u' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_mb2u.png', __FILE__ )."' width='100px' height='50px'/></button>" : '')
                    .($this->fpx_cimbclicks ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->billing_phone."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_cimbclicks' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_cimbclicks.png', __FILE__ )."' width='100px' height='50px'/></button>" : '')
                    .($this->fpx_hlb ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->billing_phone."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_hlb' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_hlb.png', __FILE__ )."' width='100px' height='50px'/></button>" : '')
                    .($this->fpx_rhb ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->billing_phone."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_rhb' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_rhb.png', __FILE__ )."' width='100px' height='50px'/></button>" : '')                   
                    .($this->fpx_amb ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->billing_phone."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_amb' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_amb.png', __FILE__ )."' width='100px' height='50px'/></button>" : '')
                    .($this->fpx_pbb ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->billing_phone."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_pbb' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_pbb.png', __FILE__ )."' width='100px' height='50px'/>   </button>" : '')
                    .($this->fpx_abb ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->billing_phone."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_abb' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_abb.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->fpx_bimb ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->billing_phone."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_bimb' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_bimb.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->molwallet ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->billing_phone."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='molwallet' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/molwallet.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->Point_BCard ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->billing_phone."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='Point-BCard' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/Point-BCard.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->dragonpay ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->billing_phone."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='dragonpay' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/dragonpay.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->NGANLUONG ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->billing_phone."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='NGANLUONG' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/NGANLUONG.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->paysbuy ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->billing_phone."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='paysbuy' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/paysbuy.png', __FILE__ )."' width='100px' height='50px'/>   </button>" : '')
                    .($this->cash_711 ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->billing_phone."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='cash-711' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/cash-711.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->ATMVA ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->billing_phone."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='ATMVA' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/ATMVA.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->enetsD ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->billing_phone."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='enetsD' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/enetsD.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->singpost ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->billing_phone."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='singpost' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/singpost.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->UPOP ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->billing_phone."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='UPOP' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/UPOP.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->alipay ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->billing_phone."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='alipay' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/alipay.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->WeChatPay ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->billing_phone."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='WeChatPay' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/WeChatPay.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
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
         * Check for MOLPay Response
         *
         * @access public
         * @return void
         */
        function check_ipn_response() {
            @ob_clean();

            if ( !( isset($_POST['nbcb']) ) ) {
                do_action( "valid_molpay_request_returnurl", $_POST );
            } else if ( $_POST['nbcb']=='1' ) {
                do_action ( "valid_molpay_request_callback", $_POST );
            } else if ( $_POST['nbcb']=='2' ) {
                do_action ( "valid_molpay_request_notification", $_POST );
            } else {
                wp_die( "MOLPay Request Failure" );
            }
        }
        
        /**
         * This part is returnurl function for MOLPay
         * 
         * @global mixed $woocommerce
         */
        function check_molpay_response_returnurl() {
            global $woocommerce;
            
            $_POST['treq']= '1'; // Additional parameter for IPN

            $amount = $_POST['amount'];
            $orderid = $_POST['orderid'];
            $appcode = $_POST['appcode'];
            $tranID = $_POST['tranID'];
            $domain = $_POST['domain'];
            $status = $_POST['status'];
            $currency = $_POST['currency'];
            $paydate = $_POST['paydate'];
            $channel = $_POST['channel'];
            $skey = $_POST['skey'];
            $vkey = $this->secret_key;

            foreach($_POST as $k => $v) {
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

            $key0 = md5($tranID.$orderid.$status.$domain.$amount.$currency);
            $key1 = md5($paydate.$domain.$key0.$appcode.$vkey);

            // invalid transaction
            if( $skey != $key1 )
                $status = -1;

            $post_id = wc_sequential_order_numbers()->find_order_by_order_number( $orderid );
            $order = new WC_Order( $post_id );
            $referer = "<br>Referer: ReturnURL";
            $getStatus =  $order->get_status();
        
            if($getStatus != 'success') {
                if ($status == "11") {
                    $referer .= " (Inquiry)";
                    $status = $this->inquiry_status( $tranID, $amount, $domain);
                }
                $this->update_Cart_by_Status($post_id, $status, $tranID, $referer);
                if (in_array($status, array("00","22"))) {
                    wp_redirect($order->get_checkout_order_received_url());
                } else {
                    wp_redirect($order->get_cancel_order_url());
                }
            } else {
                wp_redirect($order->get_checkout_order_received_url());
            }
            exit;
        }
        
        /**
         * This part is notification function for MOLPay
         * 
         * @global mixed $woocommerce
         */
        function check_molpay_response_notification() {
            global $woocommerce;
            
            $_POST['treq']= '1'; // Additional parameter for IPN
                        
            $nbcb = $_POST['nbcb'];
            $amount = $_POST['amount'];
            $orderid = $_POST['orderid'];
            $tranID = $_POST['tranID'];
            $status = $_POST['status'];
            $domain = $_POST['domain']; 
            $currency = $_POST['currency'];
            $appcode = $_POST['appcode'];
            $paydate = $_POST['paydate'];
            $skey = $_POST['skey'];
            $vkey = $this->secret_key;

            foreach($_POST as $k => $v) {
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
            
            $key0 = md5($tranID.$orderid.$status.$domain.$amount.$currency);
            $key1 = md5($paydate.$domain.$key0.$appcode.$vkey);

            if ($skey != $key1)
                $status = "-1";
            
            $post_id = wc_sequential_order_numbers()->find_order_by_order_number( $orderid );
            $referer = "<br>Referer: NotificationURL";
            $this->update_Cart_by_Status($post_id, $status, $tranID, $referer);
        }

        /**
         * This part is callback function for MOLPay
         * 
         * @global mixed $woocommerce
         */
        function check_molpay_response_callback() {
            global $woocommerce;
                        
            $nbcb = $_POST['nbcb'];
            $amount = $_POST['amount'];
            $orderid = $_POST['orderid'];
            $tranID = $_POST['tranID'];
            $status = $_POST['status'];
            $domain = $_POST['domain']; 
            $currency = $_POST['currency'];
            $appcode = $_POST['appcode'];
            $paydate = $_POST['paydate'];
            $skey = $_POST['skey'];
            $vkey = $this->secret_key;

            $key0 = md5($tranID.$orderid.$status.$domain.$amount.$currency);
            $key1 = md5($paydate.$domain.$key0.$appcode.$vkey);

            if ($skey != $key1)
                $status = "-1";
            
            $post_id = wc_sequential_order_numbers()->find_order_by_order_number( $orderid );
            $referer = "<br>Referer: CallbackURL";
            $this->update_Cart_by_Status($post_id, $status, $tranID, $referer);
            
            if ( $nbcb=='1' ) {
                //callback IPN feedback to notified MOLPay
                echo "CBTOKEN:MPSTATOK"; exit;
            }
        }

        /**
         * Adds error message when not configured the merchant_id.
         * 
         */
        public function merchant_id_missing_message() {
            $message = '<div class="error">';
            $message .= '<p>' . sprintf( __( '<strong>Gateway Disabled</strong> You should inform your Merchant ID in MOLPay. %sClick here to configure!%s' , 'wcmolpay' ), '<a href="' . get_admin_url() . 'admin.php?page=wc-settings&tab=checkout&section=wc_molpay_gateway">', '</a>' ) . '</p>';
            $message .= '</div>';
            echo $message;
        }

        /**
         * Adds error message when not configured the verify_key.
         * 
         */
        public function verify_key_missing_message() {
            $message = '<div class="error">';
            $message .= '<p>' . sprintf( __( '<strong>Gateway Disabled</strong> You should inform your Verify Key in MOLPay. %sClick here to configure!%s' , 'wcmolpay' ), '<a href="' . get_admin_url() . 'admin.php?page=wc-settings&tab=checkout&section=wc_molpay_gateway">', '</a>' ) . '</p>';
            $message .= '</div>';
            echo $message;
        }

        /**
         * Adds error message when not configured the secret_key.
         * 
         */
        public function secret_key_missing_message() {
            $message = '<div class="error">';
            $message .= '<p>' . sprintf( __( '<strong>Gateway Disabled</strong> You should fill in your Secret Key in MOLPay. %sClick here to configure!%s' , 'wcmolpay' ), '<a href="' . get_admin_url() . 'admin.php?page=wc-settings&tab=checkout&section=wc_molpay_gateway">', '</a>' ) . '</p>';
            $message .= '</div>';
            echo $message;
        }

        /**
         * Adds error message when not configured the account_type.
         * 
         */
        public function account_type_missing_message() {
            $message = '<div class="error">';
            $message .= '<p>' . sprintf( __( '<strong>Gateway Disabled</strong> Select account type in MOLPay. %sClick here to configure!%s' , 'wcmolpay' ), '<a href="' . get_admin_url() . 'admin.php?page=wc-settings&tab=checkout&section=wc_molpay_gateway">', '</a>' ) . '</p>';
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
         * Update Cart based on MOLPay status
         * 
         * @global mixed $woocommerce
         * @param int $order_id
         * @param int $MOLPay_status
         * @param int $tranID
         * @param string $referer
         */
        public function update_Cart_by_Status($orderid, $MOLPay_status, $tranID, $referer) {
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
                    $M_status = 'Invalid Transaction';
                    $W_status = 'on-hold';
                    break;
            }

            $order->add_order_note('MOLPay Payment Status: '.$M_status.'<br>Transaction ID: ' . $tranID . $referer);
            if ($MOLPay_status == "00") {
                $order->payment_complete();
            } else {
                $order->update_status($W_status, sprintf(__('Payment %s via MOLPay.', 'woocommerce'), $tranID ) );
            }
        }

    }
}