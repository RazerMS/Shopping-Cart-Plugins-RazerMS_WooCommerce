<?php
/**
 * MOLPay WooCommerce Shopping Cart Plugin
 * 
 * @author MOLPay Technical Team <technical@molpay.com>
 * @version 2.3.0
 * @example For callback : http://shoppingcarturl/?wc-api=WC_Molpay_Gateway
 * @example For notification : http://shoppingcarturl/?wc-api=WC_Molpay_Gateway
 */

/**
 * Plugin Name: WooCommerce Seamless MOLPay
 * Plugin URI: http://www.molpay.com/
 * Description: WooCommerce MOLPay | The leading payment gateway in South East Asia Grow your business with MOLPay payment solutions & free features: Physical Payment at 7-Eleven, Seamless Checkout, Tokenization, Loyalty Program and more for WooCommerce v2.3
 * Author: MOLPay Tech Team
 * Author URI: http:/www.molpay.com/
 * Version: 2.3.0
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
            //$this->pay_url = $this->type.'MOLPay/pay/';
            $this->method_title = __( 'MOLPay', 'wcmolpay' );

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
            $this->type = $this->settings['type'];
            
            // Define channel setting variables
            $this->type = ($this->get_option('type')=='1') ? "https://www.onlinepayment.com.my/" : "https://sandbox.molpay.com/" ;
            $this->credit = ($this->get_option('credit')=='yes' ? true : false);
            $this->credit3 = ($this->get_option('credit3')=='yes' ? true : false);
            $this->fpx = ($this->get_option('fpx')=='yes' ? true : false);
            $this->maybank2u = ($this->get_option('maybank2u')=='yes' ? true : false);
            $this->cimbclicks = ($this->get_option('cimbclicks')=='yes' ? true : false);
            $this->hlb = ($this->get_option('hlb')=='yes' ? true : false);
            $this->rhb = ($this->get_option('rhb')=='yes' ? true : false);
            $this->amb = ($this->get_option('amb')=='yes' ? true : false);
            $this->pbb = ($this->get_option('pbb')=='yes' ? true : false);
            $this->affinonline = ($this->get_option('affinonline')=='yes' ? true : false);
            $this->bankislam = ($this->get_option('bankislam')=='yes' ? true : false);
            $this->molwallet = ($this->get_option('molwallet')=='yes' ? true : false);
            $this->Point_BCard = ($this->get_option('Point-BCard')=='yes' ? true : false);
            $this->dragonpay = ($this->get_option('dragonpay')=='yes' ? true : false);
            $this->NGANLUONG = ($this->get_option('NGANLUONG')=='yes' ? true : false);
            $this->paysbuy = ($this->get_option('paysbuy')=='yes' ? true : false);
            $this->cash_711 = ($this->get_option('cash-711')=='yes' ? true : false);
            $this->ATMVA = ($this->get_option('ATMVA')=='yes' ? true : false);
            
            // Transaction Type for Credit Channel
            $this->credit_tcctype = ($this->get_option('credit_tcctype')=='SALS' ? 'SALS' : 'AUTH');
            $this->credit3_tcctype = ($this->get_option('credit3_tcctype')=='SALS' ? 'SALS' : 'AUTH');

            $this->pay_url = $this->type.'MOLPay/pay/';
            
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
        
            $this->secret_key == '' ? add_action( 'admin_notices', array( &$this, 'secret_key_missing_message' ) ) : '';
            
            $this->type == '' ? add_action( 'admin_notices', array( &$this, 'type_missing_message' ) ) : '';
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
                'type' => array(
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
                    'title' => __( 'Credit Card/ Debit Card 1', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'credit3' => array(
                    'title' => __( 'Credit Card/ Debit Card 2', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'fpx' => array(
                    'title' => __( 'MyClear FPX', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'fpx_mb2u' => array(
                    'title' => __( 'Maybank(Maybank2u)', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'cimbclicks' => array(
                    'title' => __( 'CIMB Bank(CIMB Clicks)', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'hlb' => array(
                    'title' => __( 'Hong Leong Bank(HLB Connect)', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'rhb' => array(
                    'title' => __( 'RHB Bank(RHB Now)', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'amb' => array(
                    'title' => __( 'Am Bank (Am Online)', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'pbb' => array(
                    'title' => __( 'PublicBank (PBB Online)', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'affinonline' => array(
                    'title' => __( 'Affin Bank(Affin Online)', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'bankislam' => array(
                    'title' => __( 'Bank Islam', 'wcmolpay' ),
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
                    'title' => __( '7-Eleven(MOLPay Cash)', 'wcmolpay' ),
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
                'tcctype' => array(
                    'title'         => 'Transaction Type for Credit Card / Debit Card Channel',
                    'type'          => 'title',
                    'description'   => '',
                ),
                'credit_tcctype' => array(
                    'title' => __( 'Credit Card/ Debit Card 1', 'wcmolpay' ),
                    'type' => 'select',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'SALS',
                    'options' => array(
                        'SALS'  => __('SALS', 'wcmolpay' ),
                        'AUTH' => __( 'AUTH', 'wcmolpay' )
                        )
                ),
                'credit3_tcctype' => array(
                    'title' => __( 'Credit Card/ Debit Card 2', 'wcmolpay' ),
                    'type' => 'select',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'SALS',
                    'options' => array(
                        'SALS'  => __('SALS', 'wcmolpay' ),
                        'AUTH' => __( 'AUTH', 'wcmolpay' )
                        )
                ),
                
                
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
            $pay_url = $this->pay_url.$this->merchant_id;
            $total = $order->order_total;
            $vcode = md5($order->order_total.$this->merchant_id.$order->id.$this->verify_key);
            
            if ( sizeof( $order->get_items() ) > 0 ) 
                foreach ( $order->get_items() as $item )
                    if ( $item['qty'] )
                        $item_names[] = $item['name'] . ' x ' . $item['qty'];

            $desc = sprintf( __( 'Order %s' , 'woocommerce'), $order->get_order_number() ) . " - " . implode( ', ', $item_names );
                        
            $molpay_args = array(
                'vcode' => $vcode,
                'orderid' => $order->id,
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
            //str_replace( 'https:', 'http:', add_query_arg( 'wc-api', 'WC_Molpay_Gateway', home_url( '/' ) ));
            
            return "<form action='".$pay_url."/' method='post' id='molpay_payment_form' name='molpay_payment_form'>"
                    . implode('', $molpay_args_array)
                    // . "<input type='submit' class='button-alt' id='submit_molpay_payment_form' value='" . __('Pay via MOLPay', 'woothemes') . "' /> "
                    // . "<a class='buttoncancel' href='" . $order->get_cancel_order_url() . "'>".__('Cancel order &amp; restore cart', 'woothemes')."</a>"
                    ."<script src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js'></script>"
                    ."<script src='".$this->type."MOLPay/API/seamless/latest/js/MOLPay_seamless.deco.js'></script>"
                    ."<h3><u>Pay via</u>:</h3>"
                    ."<br/>"
                    .($this->credit ? "<button type='button' style='background:none; padding:0px' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpstcctype='".$this->credit_tcctype."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='credit' data-mpsamount='".$total."' data-mpsorderid='".$order->id."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/payment-credit.jpg', __FILE__ )."' width='100px' height='50px'/></button>" : '') 
                    .($this->credit3 ? "<button type='button' style='background:none; padding:0px' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpstcctype='".$this->credit3_tcctype."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='credit3' data-mpsamount='".$total."' data-mpsorderid='".$order->id."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/payment-credit.jpg', __FILE__ )."' width='100px' height='50px'/></button>" : '') 
                    .($this->fpx ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx' data-mpsamount='".$total."' data-mpsorderid='".$order->id."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/payment-fpx.jpg', __FILE__ )."' width='100px' height='50px'/>   </button>" : '')
                    .($this->maybank2u ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_mb2u' data-mpsamount='".$total."' data-mpsorderid='".$order->id."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/m2u.jpg', __FILE__ )."' width='100px' height='50px'/></button>" : '')
                    .($this->cimbclicks ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_cimbclicks' data-mpsamount='".$total."' data-mpsorderid='".$order->id."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/cimb.jpg', __FILE__ )."' width='100px' height='50px'/></button>" : '')
                    .($this->hlb ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_hlb' data-mpsamount='".$total."' data-mpsorderid='".$order->id."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/hlb.jpg', __FILE__ )."' width='100px' height='50px'/></button>" : '')
                    .($this->rhb ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_rhb' data-mpsamount='".$total."' data-mpsorderid='".$order->id."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/rhb.jpg', __FILE__ )."' width='100px' height='50px'/></button>" : '')                   
                    .($this->amb ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_amb' data-mpsamount='".$total."' data-mpsorderid='".$order->id."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/ambank.jpg', __FILE__ )."' width='100px' height='50px'/></button>" : '')
                    .($this->pbb ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_pbb' data-mpsamount='".$total."' data-mpsorderid='".$order->id."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/pbb.jpg', __FILE__ )."' width='100px' height='50px'/>   </button>" : '')
                    .($this->affinonline ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_abb' data-mpsamount='".$total."' data-mpsorderid='".$order->id."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/affin.jpg', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->bankislam ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_bimb' data-mpsamount='".$total."' data-mpsorderid='".$order->id."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/bankislam.jpg', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->molwallet ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='molwallet' data-mpsamount='".$total."' data-mpsorderid='".$order->id."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/payment-molwallet.jpg', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->Point_BCard ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='Point-BCard' data-mpsamount='".$total."' data-mpsorderid='".$order->id."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/payment-bcard.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->dragonpay ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='dragonpay' data-mpsamount='".$total."' data-mpsorderid='".$order->id."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/payment-dragonpay.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->NGANLUONG ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='NGANLUONG' data-mpsamount='".$total."' data-mpsorderid='".$order->id."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/payment-nganluong.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->paysbuy ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='paysbuy' data-mpsamount='".$total."' data-mpsorderid='".$order->id."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/payment-paysbuy.png', __FILE__ )."' width='100px' height='50px'/>   </button>" : '')
                    .($this->cash_711 ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='cash-711' data-mpsamount='".$total."' data-mpsorderid='".$order->id."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/payment-7e.jpg', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->ATMVA ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->billing_email."' data-mpscountry='".$order->billing_country."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='ATMVA' data-mpsamount='".$total."' data-mpsorderid='".$order->id."' data-mpsbill_name='".$order->billing_first_name." ".$order->billing_last_name."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/payment-ATMVA.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    //. "<script>document.molpay_payment_form.submit();</script>"
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
                'redirect' => add_query_arg( 'order', $order->id, add_query_arg( 'key', $order->order_key, get_permalink( woocommerce_get_page_id( 'pay' ) ) ) )
            );
        }

        /**
         * Output for the order received page.
         * 
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

            if ( !( $_POST['nbcb'] )) {
                do_action( "valid_molpay_request_returnurl", $_POST );
            } 
            else if ( $_POST['nbcb'] ) {
                do_action ( "valid_molpay_request_callback", $_POST );
            }
            else if ( $_POST['nbcb']=='2' ) {
                do_action ( "valid_molpay_request_notification", $_POST );
            }
            else {
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
            
            $_POST[treq]= '1'; // Additional parameter for IPN

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

            while ( list($k,$v) = each($_POST) ) {
            $postData[]= $k."=".$v;
            }
            $postdata = implode("&",$postData);
            $url = $this->type."MOLPay/API/chkstat/returnipn.php";
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
            
            $vkey = $this->secret_key;
            $order = new WC_Order( $orderid );

            $key0 = md5($tranID.$orderid.$status.$domain.$amount.$currency);
            $key1 = md5($paydate.$domain.$key0.$appcode.$vkey);

            // invalid transaction
            if( $skey != $key1 )
                $status = -1;

            $referer = "<br>Referer: ReturnURL";

            if ($status == '00') {
                $order->add_order_note('MOLPay Payment Status: SUCCESSFUL'.'<br>Transaction ID: ' . $tranID . $referer);        
                $order->payment_complete();
                wp_redirect($order->get_checkout_order_received_url());
                exit;
            }
            else if ($status == "22") { 
                $order->add_order_note('MOLPay Payment Status: PENDING'.'<br>Transaction ID: ' . $tranID . $referer);
                $order->update_status('pending', sprintf(__('Payment %s via MOLPay.', 'woocommerce'), $tranID ) );
                wp_redirect($order->get_checkout_order_received_url());
                exit;
            }
            else if ($status == "11") { 
                $order->add_order_note('MOLPay Payment Status: FAILED'.'<br>Transaction ID: ' . $tranID . $referer);
                $order->update_status('failed', sprintf(__('Payment %s via MOLPay.', 'woocommerce'), $tranID ) );
                wp_redirect($order->get_cancel_order_url());
                exit;
            } 
            else  {
                $order->add_order_note('MOLPay Payment Status: Invalid Transaction'.'<br>Transaction ID: ' . $tranID . $referer);
                $order->update_status('on-hold', sprintf(__('Payment %s via MOLPay.', 'woocommerce'), $tranID ) );
                wp_redirect($order->get_cancel_order_url());
                exit;
            }   
        }
        
        /**
         * This part is callback function for MOLPay
         * 
         * @global mixed $woocommerce
         */
        function check_molpay_response_notification() {
            global $woocommerce;
            
            $_POST[treq]= '1'; // Additional parameter for IPN
                        
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
            $vkey = $this->secret_key;;
            
            while ( list($k,$v) = each($_POST) ) {
            $postData[]= $k."=".$v;
            }
            $postdata = implode("&",$postData);
            $url = $this->type."MOLPay/API/chkstat/returnipn.php";
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
            
            $order = new WC_Order( $orderid );
            $referer = "<br>Referer: NotificationURL";  
            
            if ($status == "00") {              
                $order->add_order_note('MOLPay Payment Status: SUCCESSFUL'.'<br>Transaction ID: ' . $tranID . $referer);                                
                $order->payment_complete();
            }           
            else if ($status == "22") { 
                $order->add_order_note('MOLPay Payment Status: PENDING'.'<br>Transaction ID: ' . $tranID . $referer);
                $order->update_status('pending', sprintf(__('Payment %s via MOLPay.', 'woocommerce'), $tranID ) );
            }
            else if ($status == "11") { 
                $order->add_order_note('MOLPay Payment Status: FAILED'.'<br>Transaction ID: ' . $tranID . $referer);
                $order->update_status('failed', sprintf(__('Payment %s via MOLPay.', 'woocommerce'), $tranID ) );
            } 
            else {
                $order->add_order_note('MOLPay Payment Status: Invalid Transaction'.'<br>Transaction ID: ' . $tranID . $referer);
                $order->update_status('on-hold', sprintf(__('Payment %s via MOLPay.', 'woocommerce'), $tranID ) );
            }
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
            
            $order = new WC_Order( $orderid );
            $referer = "<br>Referer: CallbackURL";  
            
            if ($status == "00") {              
                $order->add_order_note('MOLPay Payment Status: SUCCESSFUL'.'<br>Transaction ID: ' . $tranID . $referer);                                
                $order->payment_complete();
            }           
            else if ($status == "22") { 
                $order->add_order_note('MOLPay Payment Status: PENDING'.'<br>Transaction ID: ' . $tranID . $referer);
                $order->update_status('pending', sprintf(__('Payment %s via MOLPay.', 'woocommerce'), $tranID ) );
            }
            else if ($status == "11") { 
                $order->add_order_note('MOLPay Payment Status: FAILED'.'<br>Transaction ID: ' . $tranID . $referer);
                $order->update_status('failed', sprintf(__('Payment %s via MOLPay.', 'woocommerce'), $tranID ) );
            } 
            else {
                $order->add_order_note('MOLPay Payment Status: Invalid Transaction'.'<br>Transaction ID: ' . $tranID . $referer);
                $order->update_status('on-hold', sprintf(__('Payment %s via MOLPay.', 'woocommerce'), $tranID ) );
            }
            
            if ( $nbcb=='1' ) {
                //callback IPN feedback to notified MOLPay
                echo "CBTOKEN:MPSTATOK"; exit;
            }
        }

        /**
         * Adds error message when not configured the app_key.
         * 
         */
        public function merchant_id_missing_message() {
            $message = '<div class="error">';
            $message .= '<p>' . sprintf( __( '<strong>Gateway Disabled</strong> You should inform your Merchant ID in MOLPay. %sClick here to configure!%s' , 'wcmolpay' ), '<a href="' . get_admin_url() . 'admin.php?page=wc-settings&tab=checkout&section=wc_molpay_gateway">', '</a>' ) . '</p>';
            $message .= '</div>';
            echo $message;
        }

        /**
         * Adds error message when not configured the app_secret.
         * 
         */
        public function verify_key_missing_message() {
            $message = '<div class="error">';
            $message .= '<p>' . sprintf( __( '<strong>Gateway Disabled</strong> You should inform your Verify Key in MOLPay. %sClick here to configure!%s' , 'wcmolpay' ), '<a href="' . get_admin_url() . 'admin.php?page=wc-settings&tab=checkout&section=wc_molpay_gateway">', '</a>' ) . '</p>';
            $message .= '</div>';
            echo $message;
        }

	public function secret_key_missing_message() {
            $message = '<div class="error">';
            $message .= '<p>' . sprintf( __( '<strong>Gateway Disabled</strong> You should fill in your Secret Key in MOLPay. %sClick here to configure!%s' , 'wcmolpay' ), '<a href="' . get_admin_url() . 'admin.php?page=wc-settings&tab=checkout&section=wc_molpay_gateway">', '</a>' ) . '</p>';
            $message .= '</div>';
            echo $message;
        }

	 public function type_missing_message() {
            $message = '<div class="error">';
            $message .= '<p>' . sprintf( __( '<strong>Gateway Disabled</strong> Select account type in MOLPay. %sClick here to configure!%s' , 'wcmolpay' ), '<a href="' . get_admin_url() . 'admin.php?page=wc-settings&tab=checkout&section=wc_molpay_gateway">', '</a>' ) . '</p>';
            $message .= '</div>';
            echo $message;
        }


    }
}
