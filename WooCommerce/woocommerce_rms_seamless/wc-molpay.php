<?php
/**
 * Razer Merchant Services WooCommerce Shopping Cart Plugin
 * 
 * @author Razer Merchant Services Technical Team <technical-sa@razer.com>
 * @version 3.1.0
 * @example For callback : http://shoppingcarturl/?wc-api=WC_Molpay_Gateway
 * @example For notification : http://shoppingcarturl/?wc-api=WC_Molpay_Gateway
 */

/**
 * Plugin Name: WooCommerce Razer Merchant Services Seamless
 * Plugin URI: https://github.com/RazerMS/WordPress_WooCommerce_WP-eCommerce_ClassiPress
 * Description: WooCommerce Razer Merchant Services | The leading payment gateway in South East Asia Grow your business with Razer Merchant Services payment solutions & free features: Physical Payment at 7-Eleven, Seamless Checkout, Tokenization, Loyalty Program and more for WooCommerce
 * Author: Razer Merchant Services Tech Team
 * Author URI: https://merchant.razer.com/
 * Version: 3.1.0
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
            $this->url = ($this->get_option('account_type')=='1') ? "https://www.onlinepayment.com.my/" : "https://sandbox.merchant.razer.com/" ;
            $this->inquiry_url = ($this->get_option('account_type')=='1') ? "https://api.merchant.razer.com/" : "https://sandbox.merchant.razer.com/" ;
            
            // Define channel setting variables
            $this->credit = ($this->get_option('credit')=='yes' ? true : false);
            $this->fpx_mb2u = ($this->get_option('fpx_mb2u')=='yes' ? true : false);
            $this->fpx_cimbclicks = ($this->get_option('fpx_cimbclicks')=='yes' ? true : false);
            $this->fpx_hlb = ($this->get_option('fpx_hlb')=='yes' ? true : false);
            $this->fpx_rhb = ($this->get_option('fpx_rhb')=='yes' ? true : false);
            $this->fpx_amb = ($this->get_option('fpx_amb')=='yes' ? true : false);
            $this->fpx_pbb = ($this->get_option('fpx_pbb')=='yes' ? true : false);
            $this->fpx_abb = ($this->get_option('fpx_abb')=='yes' ? true : false);
            $this->fpx_bimb = ($this->get_option('fpx_bimb')=='yes' ? true : false);
            $this->fpx_abmb = ($this->get_option('fpx_abmb')=='yes' ? true : false);
            $this->fpx_bkrm = ($this->get_option('fpx_bkrm')=='yes' ? true : false);
            $this->fpx_bmmb = ($this->get_option('fpx_bmmb')=='yes' ? true : false);
            $this->fpx_bsn = ($this->get_option('fpx_bsn')=='yes' ? true : false);
            $this->fpx_hsbc = ($this->get_option('fpx_hsbc')=='yes' ? true : false);
            $this->fpx_kfh = ($this->get_option('fpx_kfh')=='yes' ? true : false);
            $this->fpx_ocbc = ($this->get_option('fpx_ocbc')=='yes' ? true : false);
            $this->fpx_scb = ($this->get_option('fpx_scb')=='yes' ? true : false);
            $this->fpx_uob = ($this->get_option('fpx_uob')=='yes' ? true : false);
            $this->FPX_M2E = ($this->get_option('FPX_M2E')=='yes' ? true : false);
            $this->FPX_B2B_ABB = ($this->get_option('FPX_B2B_ABB')=='yes' ? true : false);
            $this->FPX_B2B_ABBM = ($this->get_option('FPX_B2B_ABBM')=='yes' ? true : false);
            $this->FPX_B2B_ABMB = ($this->get_option('FPX_B2B_ABMB')=='yes' ? true : false);
            $this->FPX_B2B_AMB = ($this->get_option('FPX_B2B_AMB')=='yes' ? true : false);
            $this->FPX_B2B_BIMB = ($this->get_option('FPX_B2B_BIMB')=='yes' ? true : false);
            $this->FPX_B2B_BKRM = ($this->get_option('FPX_B2B_BKRM')=='yes' ? true : false);
            $this->FPX_B2B_BMMB = ($this->get_option('FPX_B2B_BMMB')=='yes' ? true : false);
            $this->FPX_B2B_BNP = ($this->get_option('FPX_B2B_BNP')=='yes' ? true : false);
            $this->FPX_B2B_CIMB = ($this->get_option('FPX_B2B_CIMB')=='yes' ? true : false);
            $this->FPX_B2B_CITIBANK = ($this->get_option('FPX_B2B_CITIBANK')=='yes' ? true : false);
            $this->FPX_B2B_DEUTSCHE = ($this->get_option('FPX_B2B_DEUTSCHE')=='yes' ? true : false);
            $this->FPX_B2B_HLB = ($this->get_option('FPX_B2B_HLB')=='yes' ? true : false);
            $this->FPX_B2B_HSBC = ($this->get_option('FPX_B2B_HSBC')=='yes' ? true : false);
            $this->FPX_B2B_KFH = ($this->get_option('FPX_B2B_KFH')=='yes' ? true : false);
            $this->FPX_B2B_OCBC = ($this->get_option('FPX_B2B_OCBC')=='yes' ? true : false);
            $this->FPX_B2B_PBB = ($this->get_option('FPX_B2B_PBB')=='yes' ? true : false);
            $this->FPX_B2B_PBBE = ($this->get_option('FPX_B2B_PBBE')=='yes' ? true : false);
            $this->FPX_B2B_RHB = ($this->get_option('FPX_B2B_RHB')=='yes' ? true : false);
            $this->FPX_B2B_SCB = ($this->get_option('FPX_B2B_SCB')=='yes' ? true : false);
            $this->FPX_B2B_UOB = ($this->get_option('FPX_B2B_UOB')=='yes' ? true : false);
            $this->FPX_B2B_UOBR = ($this->get_option('FPX_B2B_UOBR')=='yes' ? true : false);
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
            $this->WeChatPayMY = ($this->get_option('WeChatPayMY')=='yes' ? true : false);
            $this->BOOST = ($this->get_option('BOOST')=='yes' ? true : false);
            $this->MB2U_QRPay_Push = ($this->get_option('MB2U_QRPay-Push')=='yes' ? true : false);
            $this->RazerPay = ($this->get_option('RazerPay')=='yes' ? true : false);
            $this->ShopeePay = ($this->get_option('ShopeePay')=='yes' ? true : false);
            $this->TNG_EWALLET = ($this->get_option('TNG-EWALLET')=='yes' ? true : false);
            $this->GrabPay = ($this->get_option('GrabPay')=='yes' ? true : false);
            $this->BAY_IB_U = ($this->get_option('BAY_IB_U')=='yes' ? true : false);
            $this->BBL_IB_U = ($this->get_option('BBL_IB_U')=='yes' ? true : false);
            $this->KBANK_PayPlus = ($this->get_option('KBANK_PayPlus')=='yes' ? true : false);
            $this->KTB_IB_U = ($this->get_option('KTB_IB_U')=='yes' ? true : false);
            $this->SCB_IB_U = ($this->get_option('SCB_IB_U')=='yes' ? true : false);
            $this->BigC = ($this->get_option('BigC')=='yes' ? true : false);
            $this->OMISE_TL = ($this->get_option('OMISE_TL')=='yes' ? true : false);

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
                        '3' => __( 'Advanced Order Numbers', 'wcmolpay' )
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
                'credit' => array(
                    'title' => __( 'Credit Card/ Debit Card', 'wcmolpay' ),
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
                'fpx_abmb' => array(
                    'title' => __( 'FPX Alliance Bank (Alliance Online)', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'fpx_bkrm' => array(
                    'title' => __( 'FPX Bank Kerjasama Rakyat Malaysia', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'fpx_bmmb' => array(
                    'title' => __( 'FPX Bank Muamalat', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'fpx_bsn' => array(
                    'title' => __( 'FPX Bank Simpanan Nasional (myBSN)', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'fpx_hsbc' => array(
                    'title' => __( 'FPX Hongkong and Shanghai Banking Corporation', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'fpx_kfh' => array(
                    'title' => __( 'FPX Kuwait Finance House', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'fpx_ocbc' => array(
                    'title' => __( 'FPX OCBC Bank', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'fpx_scb' => array(
                    'title' => __( 'FPX Standard Chartered Bank', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'fpx_uob' => array(
                    'title' => __( 'FPX United Overseas Bank (UOB)', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'FPX_M2E' => array(
                    'title' => __('FPX Maybank2e', 'wcmolpay'),
                    'type'  => 'checkbox',
                    'label' => __(' ', 'wcmolpay'),
                    'default' => 'no'
                ),
                'FPX_B2B_ABB' => array(
                    'title' => __('FPX B2B Affin Bank', 'wcmolpay'),
                    'type'  => 'checkbox',
                    'label' => __(' ', 'wcmolpay'),
                    'default' => 'no'
                ),
                'FPX_B2B_ABBM' => array(
                    'title' => __('FPX B2B AffinMax', 'wcmolpay'),
                    'type'  => 'checkbox',
                    'label' => __(' ', 'wcmolpay'),
                    'default' => 'no'
                ),
                'FPX_B2B_ABMB' => array(
                    'title' => __('FPX B2B Alliance Bank', 'wcmolpay'),
                    'type'  => 'checkbox',
                    'label' => __(' ', 'wcmolpay'),
                    'default' => 'no'
                ),
                'FPX_B2B_AMB' => array(
                    'title' => __('FPX B2B AmBank', 'wcmolpay'),
                    'type'  => 'checkbox',
                    'label' => __(' ', 'wcmolpay'),
                    'default' => 'no'
                ),
                'FPX_B2B_BIMB' => array(
                    'title' => __('FPX B2B Bank Islam Malaysia Berhad', 'wcmolpay'),
                    'type'  => 'checkbox',
                    'label' => __(' ', 'wcmolpay'),
                    'default' => 'no'
                ),
                'FPX_B2B_BKRM' => array(
                    'title' => __('FPX B2B i-bizRAKYAT', 'wcmolpay'),
                    'type'  => 'checkbox',
                    'label' => __(' ', 'wcmolpay'),
                    'default' => 'no'
                ),
                'FPX_B2B_BMMB' => array(
                    'title' => __('FPX B2B Bank Muamalat', 'wcmolpay'),
                    'type'  => 'checkbox',
                    'label' => __(' ', 'wcmolpay'),
                    'default' => 'no'
                ),
                'FPX_B2B_BNP' => array(
                    'title' => __('FPX B2B BNP Paribas', 'wcmolpay'),
                    'type'  => 'checkbox',
                    'label' => __(' ', 'wcmolpay'),
                    'default' => 'no'
                ),
                'FPX_B2B_CIMB' => array(
                    'title' => __('FPX B2B BizChannel@CIMB', 'wcmolpay'),
                    'type'  => 'checkbox',
                    'label' => __(' ', 'wcmolpay'),
                    'default' => 'no'
                ),
                'FPX_B2B_CITIBANK' => array(
                    'title' => __('FPX B2B CITIBANK', 'wcmolpay'),
                    'type'  => 'checkbox',
                    'label' => __(' ', 'wcmolpay'),
                    'default' => 'no'
                ),
                'FPX_B2B_DEUTSCHE' => array(
                    'title' => __('FPX B2B Deutsche Bank', 'wcmolpay'),
                    'type'  => 'checkbox',
                    'label' => __(' ', 'wcmolpay'),
                    'default' => 'no'
                ),
                'FPX_B2B_HLB' => array(
                    'title' => __('FPX B2B Hong Leong Connect', 'wcmolpay'),
                    'type'  => 'checkbox',
                    'label' => __(' ', 'wcmolpay'),
                    'default' => 'no'
                ),
                'FPX_B2B_HSBC' => array(
                    'title' => __('FPX B2B HSBC', 'wcmolpay'),
                    'type'  => 'checkbox',
                    'label' => __(' ', 'wcmolpay'),
                    'default' => 'no'
                ),
                'FPX_B2B_KFH' => array(
                    'title' => __('FPX B2B Kuwait Finance House Overseas Bank', 'wcmolpay'),
                    'type'  => 'checkbox',
                    'label' => __(' ', 'wcmolpay'),
                    'default' => 'no'
                ),
                'FPX_B2B_OCBC' => array(
                    'title' => __('FPX B2B OCBC Bank', 'wcmolpay'),
                    'type'  => 'checkbox',
                    'label' => __(' ', 'wcmolpay'),
                    'default' => 'no'
                ),
                'FPX_B2B_PBB' => array(
                    'title' => __('FPX B2B Public Bank', 'wcmolpay'),
                    'type'  => 'checkbox',
                    'label' => __(' ', 'wcmolpay'),
                    'default' => 'no'
                ),
                'FPX_B2B_PBBE' => array(
                    'title' => __('FPX B2B Public Bank Enterprise', 'wcmolpay'),
                    'type'  => 'checkbox',
                    'label' => __(' ', 'wcmolpay'),
                    'default' => 'no'
                ),
                'FPX_B2B_RHB' => array(
                    'title' => __('FPX B2B RHB Reflex', 'wcmolpay'),
                    'type'  => 'checkbox',
                    'label' => __(' ', 'wcmolpay'),
                    'default' => 'no'
                ),
                'FPX_B2B_SCB' => array(
                    'title' => __('FPX B2B Standard Chartered Bank', 'wcmolpay'),
                    'type'  => 'checkbox',
                    'label' => __(' ', 'wcmolpay'),
                    'default' => 'no'
                ),
                'FPX_B2B_UOB' => array(
                    'title' => __('FPX B2B United Overseas Bank', 'wcmolpay'),
                    'type'  => 'checkbox',
                    'label' => __(' ', 'wcmolpay'),
                    'default' => 'no'
                ),
                'FPX_B2B_UOBR' => array(
                    'title' => __('FPX B2B UOB Regional', 'wcmolpay'),
                    'type'  => 'checkbox',
                    'label' => __(' ', 'wcmolpay'),
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
                    'title' => __( '7-Eleven (Razer Cash)', 'wcmolpay' ),
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
                'WeChatPayMY' => array(
                    'title' => __( 'WeChatPayMY', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'BOOST' => array(
                    'title' => __( 'Boost', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'MB2U_QRPay-Push' => array(
                    'title' => __( 'Maybank QRPay', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'RazerPay' => array(
                    'title' => __( 'Razer Pay', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'ShopeePay' => array(
                    'title' => __( 'Shopee Pay', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'TNG-EWALLET' => array(
                    'title' => __( 'Touch `n Go eWallet', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'GrabPay' => array(
                    'title' => __( 'Grab Pay', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'BAY_IB_U' => array(
                    'title' => __( 'Bank of Ayudhya (Krungsri)', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'BBL_IB_U' => array(
                    'title' => __( 'Bangkok Bank (Fee on user)', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'KBANK_PayPlus' => array(
                    'title' => __( 'Kasikornbank K PLUS', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'KTB_IB_U' => array(
                    'title' => __( 'Krung Thai Bank (Fee on user)', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'SCB_IB_U' => array(
                    'title' => __( 'Siam Commercial Bank (Fee on user)', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'BigC' => array(
                    'title' => __( 'BigC', 'wcmolpay' ),
                    'type' => 'checkbox',
                    'label' => __( ' ', 'wcmolpay' ),
                    'default' => 'no'
                ),
                'OMISE_TL' => array(
                    'title' => __( 'Tesco Lotus via OMISE', 'wcmolpay' ),
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
            return "<form action='".$pay_url."/' method='post' id='molpay_payment_form' name='molpay_payment_form'>"
                    . implode('', $molpay_args_array)
                    ."<script src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js'></script>"
                    ."<script src='".$this->url."MOLPay/API/seamless/".$latest."/js/MOLPay_seamless.deco.js'></script>"
                    ."<h3><u>Pay via</u>:</h3><img src='".plugins_url( 'images/logo_RazerMerchantServices.png', __FILE__ )."' width='200px'>"
                    ."<br/>"
                    .($this->credit ? "<button type='button' style='background:none; padding:0px' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpstcctype='".$this->credit_tcctype."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='credit' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/credit.png', __FILE__ )."' width='100px' height='50px'/></button>" : '') 
                    .($this->fpx_mb2u ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_mb2u' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_mb2u.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->fpx_cimbclicks ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_cimbclicks' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_cimbclicks.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->fpx_hlb ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_hlb' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_hlb.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->fpx_rhb ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_rhb' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_rhb.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')                   
                    .($this->fpx_amb ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_amb' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_amb.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->fpx_pbb ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_pbb' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_pbb.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/>   </button>" : '')
                    .($this->fpx_abb ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_abb' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_abb.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/> </button>" : '')
                    .($this->fpx_bimb ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_bimb' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_bimb.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/> </button>" : '')
                    .($this->fpx_abmb ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_abmb' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_abmb.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->fpx_bkrm ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_bkrm' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_bkrm.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->fpx_bmmb ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_bmmb' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_bmmb.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->fpx_bsn ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_bsn' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_bsn.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->fpx_hsbc ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_hsbc' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_hsbc.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->fpx_kfh ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_kfh' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_kfh.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->fpx_ocbc ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_ocbc' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_ocbc.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->fpx_scb ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_scb' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_scb.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->fpx_uob ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='fpx_uob' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/fpx_uob.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->Point_BCard ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='Point-BCard' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/Point-BCard.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->dragonpay ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='dragonpay' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/dragonpay.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->NGANLUONG ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='NGANLUONG' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/NGANLUONG.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->paysbuy ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='paysbuy' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/paysbuy.png', __FILE__ )."' width='100px' height='50px'/>   </button>" : '')
                    .($this->cash_711 ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='cash-711' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/cash-711.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->ATMVA ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='ATMVA' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/ATMVA.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->enetsD ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='enetsD' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/enetsD.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->singpost ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='singpost' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/singpost.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->UPOP ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='UPOP' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/UPOP.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->alipay ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='alipay' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/alipay.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->WeChatPay ? "<button type='button' style='background:none; padding:0px' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='WeChatPay' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/WeChatPay.png', __FILE__ )."' width='100px' height='50px'/> </button>" : '')
                    .($this->WeChatPayMY ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='WeChatPayMY' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/wechatpay_my.png', __FILE__ )."' width='100px' height='50px'/></button>" : '')
                    .($this->BOOST ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='BOOST' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/boost.png', __FILE__ )."' width='100px' height='50px'/></button>" : '')
                    .($this->MB2U_QRPay_Push ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='MB2U_QRPay-Push' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/maybankQR.png', __FILE__ )."' width='100px' height='50px'/></button>" : '')
                    .($this->RazerPay ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='RazerPay' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/razerpay.png', __FILE__ )."' width='100px' height='50px'/></button>" : '')
                    .($this->ShopeePay ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='ShopeePay' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/shopeepay_2.png', __FILE__ )."' width='100px' height='50px'/></button>" : '')
                    .($this->TNG_EWALLET ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='TNG-EWALLET' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/touchngo_ewallet.png', __FILE__ )."' width='100px' height='50px'/></button>" : '')
                    .($this->GrabPay ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='GrabPay' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/grabpay.png', __FILE__ )."' width='100px' height='50px'/></button>" : '')
                    .($this->BAY_IB_U ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='BAY_IB_U' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/BAY_IB_U.png', __FILE__ )."' width='100px' height='50px'/></button>" : '')
                    .($this->BBL_IB_U ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='BBL_IB_U' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/BBL_IB_U.png', __FILE__ )."' width='100px' height='50px'/></button>" : '')
                    .($this->KBANK_PayPlus ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='KBANK_PayPlus' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/KBANK_PayPlus.png', __FILE__ )."' width='100px' height='50px'/></button>" : '')
                    .($this->KTB_IB_U ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='KTB_IB_U' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/KTB_IB_U.png', __FILE__ )."' width='100px' height='50px'/></button>" : '')
                    .($this->SCB_IB_U ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='SCB_IB_U' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/SCB_IB_U.png', __FILE__ )."' width='100px' height='50px'/></button>" : '')
                    .($this->BigC ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='BigC' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/BigC.png', __FILE__ )."' width='100px' height='50px'/></button>" : '')
                    .($this->OMISE_TL ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='OMISE_TL' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/OMISE_TL.png', __FILE__ )."' width='100px' height='50px'/></button>" : '')
                    .($this->FPX_M2E ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='FPX_M2E' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/FPX_M2E.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->FPX_B2B_ABB ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='FPX_B2B_ABB' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/FPX_B2B_ABB.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->FPX_B2B_ABBM ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='FPX_B2B_ABBM' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/FPX_B2B_ABBM.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->FPX_B2B_ABMB ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='FPX_B2B_ABMB' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/FPX_B2B_ABMB.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->FPX_B2B_AMB ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='FPX_B2B_AMB' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/FPX_B2B_AMB.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->FPX_B2B_BIMB ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='FPX_B2B_BIMB' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/FPX_B2B_BIMB.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->FPX_B2B_BKRM ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='FPX_B2B_BKRM' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/FPX_B2B_BKRM.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->FPX_B2B_BMMB ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='FPX_B2B_BMMB' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/FPX_B2B_BMMB.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->FPX_B2B_BNP ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='FPX_B2B_BNP' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/FPX_B2B_BNP.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->FPX_B2B_CIMB ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='FPX_B2B_CIMB' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/FPX_B2B_CIMB.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->FPX_B2B_CITIBANK ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='FPX_B2B_CITIBANK' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/FPX_B2B_CITIBANK.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->FPX_B2B_DEUTSCHE ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='FPX_B2B_DEUTSCHE' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/FPX_B2B_DEUTSCHE.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->FPX_B2B_HLB ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='FPX_B2B_HLB' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/FPX_B2B_HLB.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->FPX_B2B_HSBC ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='FPX_B2B_HSBC' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/FPX_B2B_HSBC.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->FPX_B2B_KFH ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='FPX_B2B_KFH' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/FPX_B2B_KFH.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->FPX_B2B_OCBC ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='FPX_B2B_OCBC' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/FPX_B2B_OCBC.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->FPX_B2B_PBB ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='FPX_B2B_PBB' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/FPX_B2B_PBB.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->FPX_B2B_PBBE ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='FPX_B2B_PBBE' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/FPX_B2B_PBBE.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->FPX_B2B_RHB ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='FPX_B2B_RHB' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/FPX_B2B_RHB.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->FPX_B2B_SCB ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='FPX_B2B_SCB' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/FPX_B2B_SCB.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->FPX_B2B_UOB ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='FPX_B2B_UOB' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/FPX_B2B_UOB.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
                    .($this->FPX_B2B_UOBR ? "<button type='button' style='background:none; padding:0px;' data-toggle='molpayseamless' data-mpsbill_mobile='".$order->get_billing_phone()."' data-mpsmerchantid='".$this->merchant_id."' data-mpsbill_desc='".$desc."' data-mpsbill_email='".$order->get_billing_email()."' data-mpscountry='".$order->get_billing_country()."' data-mpscurrency='".get_woocommerce_currency()."' data-mpschannel='FPX_B2B_UOBR' data-mpsamount='".$total."' data-mpsorderid='".$order_number."' data-mpsbill_name='".$order->get_billing_first_name()." ".$order->get_billing_last_name()."' data-mpsvcode='".$vcode."' data-mpsreturnurl='".$mpsreturn."'><img src='".plugins_url( 'images/FPX_B2B_UOBR.png', __FILE__ )."' width='100px' height='50px' style='border: 1px solid; border-radius: 5px; border-color: #DDD;'/></button>" : '')
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
                    $M_status = 'Invalid Transaction';
                    $W_status = 'on-hold';
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
                case '1' : 
                    $WCOrderId = wc_sequential_order_numbers()->find_order_by_order_number( $orderid );
                    break;
                case '2' : 
                    $WCOrderId = wc_seq_order_number_pro()->find_order_by_order_number( $orderid );
                    break;
                case '3' : 
                    $WCOrderId = substr($orderid,0,-6);
                    break;
                case '0' : 
                default :
                    $WCOrderId = $orderid;
                    break;
            }
            return $WCOrderId;
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