<?php
/**
* RMS Wordpress e-Commerce Plugin
*
* @package Payment Method
* @author RMS Technical Team <technical@RMS.com>
* @version 2.1.1
*
*/

$nzshpcrt_gateways[$num] = array(
    'name'              => 'RMS Online Payment Gateway',
    'display_name'      => 'RMS Online Payment Gateway',
    'internalname'      => 'rms',
    'function'          => 'gateway_rms',
    'form'              => 'form_rms',
    'submit_function'   => 'submit_rms'
);

/**
 * Initialize the order if rms payment method was selected
 * 
 * @global object $wpdb
 * @global object $wp_object_cache
 * @param type $seperator
 * @param int $sessionid
 * @return void
 */
function gateway_rms($seperator, $sessionid) {
    global $wpdb, $wp_object_cache;

    $ob_cache = $wp_object_cache->cache;
    $cur = $ob_cache['options']['alloptions'];
    $cur_type = $cur['currency_type'];

    $cur_sql = "SELECT * FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`= ".$cur_type." LIMIT 1";
    $cur_res = $wpdb->get_results($cur_sql,ARRAY_A) ;
    $cur_code = $cur_res[0]['code'];	
    $cur_code = (strnatcasecmp($cur_code,"myr")=="0")? "rm" : strtolower($cur_code);

    $purchase_log_sql = "SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1";
    $purchase_log = $wpdb->get_results($purchase_log_sql,ARRAY_A);

    $cart_sql = "SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`='".$purchase_log[0]['id']."'"; 
    $cart = $wpdb->get_results($cart_sql,ARRAY_A) ;
        
    $rms_get_url = get_option('rms_url');

    $data['merchant_id'] = get_option('rms_merchant_id');
    $data['verify_key']  = get_option('rms_vkey');
    $data['returnurl']   = get_option('transact_url');
    $data['callbackurl'] = get_option('transact_url');
    $rms_url = "https://www.onlinepayment.com.my/rms/pay/" . $data['merchant_id'] . "/";  
   
    //User details
    if($_POST['collected_data'][get_option('rms_form_first_name')] != '') {   
        $data['f_name'] = $_POST['collected_data'][get_option('rms_form_first_name')];
    }
    if($_POST['collected_data'][get_option('rms_form_last_name')] != "") {   
        $data['s_name'] = $_POST['collected_data'][get_option('rms_form_last_name')];
    }
    if($_POST['collected_data'][get_option('rms_form_address')] != '') {   
        $data['street'] = str_replace("\n",', ', $_POST['collected_data'][get_option('rms_form_address')]); 
    }
    if($_POST['collected_data'][get_option('rms_form_city')] != '') {
        $data['city'] = $_POST['collected_data'][get_option('rms_form_city')]; 
    }
    if(preg_match("/^[a-zA-Z]{2}$/",$_SESSION['selected_country'])) {   
        $data['country'] = $_SESSION['selected_country'];
    }    
 
    //Get user email
    $email_data = $wpdb->get_results("SELECT `id`,`type` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type` IN ('email') AND `active` = '1'",ARRAY_A);
    foreach((array)$email_data as $email) {
        $data['email'] = $_POST['collected_data'][$email['id']];
    }
    if(($_POST['collected_data'][get_option('email_form_field')] != null) && ($data['email'] == null)) {
        $data['email'] = $_POST['collected_data'][get_option('email_form_field')];
    }
	
    //collect item(s) in cart information
    $prod_sql  = "SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`='".$cart[0]['purchaseid']."'";
    $prod_res  = $wpdb->get_results($prod_sql,ARRAY_A) ;
    $prod_size = sizeof($prod_res);

    for ($i=0; $i<$prod_size; $i++) {
        $p_name[] = $prod_res[$i]['name']." x ".$prod_res[$i]['quantity'];
    }
    
    if($p_name){
    	$p_desc = implode("\n",$p_name);
    }
							
    $ship_sql  = "SELECT form_id,value FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id`='".$cart[0]['purchaseid']."' ";
    $ship_res  = $wpdb->get_results($ship_sql,ARRAY_A);
        
    $size_ship = sizeof($ship_res);
    
    for($k = 0; $k < $size_ship; $k++) {
        $form_id = $ship_res[$k]['form_id'];

        switch($form_id) {
            // ------------------- billing information -------------------
            //Billing first name
            case "2" :
                $b_name = $ship_res[$k]['value'];
            break;
            //Billing last name
            case "3" :
                $b_name.= " ".$ship_res[$k]['value'];
            break;
            //Billing contact
            case "18" :
                $b_fon = $ship_res[$k]['value'];
            break;
            //Billing address
            case "4" :
                $b_address = $ship_res[$k]['value'];
            break;
            //Billing city
            case "5" :
                $b_city = $ship_res[$k]['value'];
            break;
            //Billing state
            case "6" :
                $b_state = $ship_res[$k]['value'];
            break;
            //Billing country
            case "7" :
                $b_county = $ship_res[$k]['value'];
            break;
            //Billing postcode
            case "8" :
                $b_postcode = $ship_res[$k]['value'];
            break;

            // -------------------  shipping information ------------------- 
            //
            case "11" :
                $s_name = (strlen(preg_replace('/\s+/', '', $ship_res[$k]['value'])) != 0)? $ship_res[$k]['value'] : $ship_res[0]['value'];
                $_SESSION['shippingSameBilling'] = 1;
            break;

            case "12" :
                $s_name2 = (strlen(preg_replace('/\s+/', '', $ship_res[$k]['value'])) != 0)? $ship_res[$k]['value'] : $ship_res[1]['value'];
            break;

            case "13" :
                $s_address = (strlen(preg_replace('/\s+/', '', $ship_res[$k]['value'])) != 0)? $ship_res[$k]['value'] : $ship_res[2]['value'];
            break;

            case "14" :
                $s_address2 = (strlen(preg_replace('/\s+/', '', $ship_res[$k]['value'])) != 0)? $ship_res[$k]['value'] : $ship_res[3]['value'];
            break;

            case "15" :
                $s_address3 = (strlen(preg_replace('/\s+/', '', $ship_res[$k]['value'])) != 0)? $ship_res[$k]['value'] : $ship_res[4]['value'];
            break;

            case "16" :
                $s_address4 = (strlen(preg_replace('/\s+/', '', $ship_res[$k]['value'])) != 0)? $ship_res[$k]['value'] : $ship_res[5]['value'];
            break;

            default:
                echo "";

        }	
    }
    
    //Construct information about buying    
    $desc .= "------------------------\nProduct(s) Information\n------------------------\n";
    $desc .= $p_desc . "\n";

    $desc .= "------------------------\nShipping Information\n------------------------\n";
    $desc .= $s_name . ' ' . $s_name2;
    $desc .= "\n" . $s_address . "\n" . $s_address2 . "\n" . $s_address3 . "\n" . $s_address4;

    $data['product_price'] = $total_price; //This data cannot be used in rms system
    $data['amount'] = $purchase_log[0]['totalprice'];
    $data['orderid'] = $purchase_log[0]['id'];	
    $data['bill_mobile'] = $b_fon;			
    $data['bill_name'] = $b_name;			
    $data['bill_email'] = $data['email'];		
    $data['bill_desc'] = $desc;
    $data['currency'] = $cur_code;			
    $data['country'] = "MY";				
    $data['returnurl'] = $data['returnurl'];		
    $data['vcode'] = md5($data['amount'] . $data['merchant_id'] . $data['orderid'] . $data['verify_key']); //Generate verfication code
	
    //Create Form to post to rms Online Payment Gateway
    $output= "<center><form id='rms_form' name='rms_form' method='post' action='$rms_url'>\n";
	
    foreach($data as $n => $v) {
        $output .= "<input type='hidden' name='$n' value='$v' />\n";
    }
	
    $plugins_url = plugins_url();    
    $output .= "<br><br>";
    $output .= "<input type='image' src='$plugins_url/wp-e-commerce/images/rms_logo.gif' name='submit'></form>";
    $output .= "<br><input type='image' src='$plugins_url/wp-e-commerce/images/connect_rms.gif' width='44' length='44'>";
    $output .= "<br><br><font face='arial' size='2'>Please wait for a while.. You'll redirect to rms Online Payment Gateway.</font></center>";
    
    //flush all the form to the browser view
    echo($output);

    if(get_option('rms_debug') == 0) {
        //Auto submit javascript
        echo "<script language='javascript'type='text/javascript'>setTimeout(\"document.getElementById('rms_form').submit()\",1500);</script>";
    }
    exit();
}

/**
 * Received status about the order
 * 
 * @global object $wpdb
 */
function nzshpcrt_rms_callback() {    
    global $wpdb;
    
    //Check skey
    $key0 = md5($_REQUEST['tranID'] . $_REQUEST['orderid'] . $_REQUEST['status'] . get_option('rms_merchant_id') . $_REQUEST['amount'] . $_REQUEST['currency']);
    $key1 = md5($_REQUEST['paydate'] . get_option('rms_merchant_id') . $key0 . $_REQUEST['appcode'] . get_option('rms_vkey'));
    
    if(isset($_REQUEST['skey']) && $_REQUEST['skey'] == $key1) {
        $data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id` = ".$_REQUEST['orderid']."");
        
        $ship_res = rms_inline_classes_object_function::query_data($wpdb, $_REQUEST['orderid']);
        
        $_POST['sessionid'] = $sessionid = $data->sessionid;
        $transid = $_REQUEST['tranID'];
        $retStatus = $_REQUEST['status'];
        $url = get_option('transact_url') . "?sessionid=" . $sessionid;
        
        if($retStatus == '00') {            
            $data = array(
                'processed'  => 3,
                'transactid' => $transid,
                'date'       => time()
            );
            $where = array( 'sessionid' => $sessionid );
            $format = array( '%d', '%s', '%s' );
            $wpdb->update( WPSC_TABLE_PURCHASE_LOGS, $data, $where, $format );
            transaction_results($sessionid, false, $transid);
            
            $bodyContent = "
                rms Plugin Auto-Sender\n\n
                Be inform that we have capture for payment : \n
                Order ID : " . $_REQUEST['orderid'] . "\n
                Approval code : " . $_REQUEST['appcode'] . "\n
                Amount : " . $_REQUEST['currency'] . $_REQUEST['amount'] . "\n\n
                --------------------------------------------------------------\n
                Buyer Name : " . $ship_res[0]['value'] . ' ' . $ship_res[1]['value'] . "\n
                Buyer Phone : " . $ship_res[15]['value'] . "\n
                Buyer Email : " . $ship_res[7]['value'] . "\n
                Buyer Address : " . $ship_res[2]['value'] . ', ' . $ship_res[6]['value'] . ', ' . $ship_res[3]['value'] . ', ' . $ship_res[4]['value'] . "\n
                Shipping Name : " . $ship_res[8]['value'] . ' ' . $ship_res[9]['value'] . "\n
                Shipping Address : " . $ship_res[10]['value'] . ', ' . $ship_res[14]['value'] . ', ' . $ship_res[11]['value'] . ', ' . $ship_res[12]['value'] . "\n                
            ";
            
            wp_mail( get_option('admin_email'), 'Accepted Payment Notification | rms', $bodyContent);
        }
        else if($retStatus == '11') {
            $data = array(
                'processed'  => 2,
                'transactid' => $transid,
                'date'       => time()
            );
            $where = array( 'sessionid' => $sessionid );
            $format = array( '%d', '%s', '%s' );
            $wpdb->update( WPSC_TABLE_PURCHASE_LOGS, $data, $where, $format );
            transaction_results($sessionid, false, $transid);
        }
        
        echo '<script>window.location.href = "'.$url.'"</script>';
    }
    //Callback
    else if (isset($_REQUEST['nbcb'])) {	
        $key0 = md5($_REQUEST['tranID'].$_REQUEST['orderid'].$_REQUEST['status'].get_option('rms_merchant_id').$_REQUEST['amount'].$_REQUEST['currency']);
        $key1 = md5($_REQUEST['paydate'].get_option('rms_merchant_id').$key0.$_REQUEST['appcode'].get_option('rms_vkey'));

        if( $skey != $key1 )
            $status= -1;

        switch($status) {  
            case '00':
                $data = array(
                    'processed'  => 3,
                    'transactid' => $tranID,
                    'date'       => time()
                );
                $where = array( 'sessionid' => $sessionid );
                $format = array( '%d', '%s', '%s' );
                $wpdb->update( WPSC_TABLE_PURCHASE_LOGS, $data, $where, $format );
            break;

            case '11': // if it fails, delete it -- changed status to job dispatched/closed order (2009 April 15)
                $data = array(
                    'processed'  => 2,
                    'transactid' => $tranID,
                    'date'       => time()
                );
                $where = array( 'sessionid' => $sessionid );
                $format = array( '%d', '%s', '%s' );
                $wpdb->update( WPSC_TABLE_PURCHASE_LOGS, $data, $where, $format );
            break;

            default: // do nothing, safest course of action here.
            break;
        }
    }
    //Either merchant missconfigure the merchantID or vcode
    else if(isset($_REQUEST['skey']) && $_REQUEST['skey'] != $key1) {
        echo '<h1>There was an error during processing the information</h1>';
        echo '<p>Incorrect merchantID or vcode was provided. Please recheck!';
    }
}

function nzshpcrt_rms_results() {
    if($_POST['orderid'] !='' && $_GET['sessionid'] == '') {
        $_GET['sessionid'] = $_POST['sessionid'];
    }
}


function submit_rms() {  
    if($_POST['rms_merchant_id'] != null) {
        update_option('rms_merchant_id', $_POST['rms_merchant_id']);
    }

    if($_POST['rms_vkey'] != null) {
        update_option('rms_vkey', $_POST['rms_vkey']);
    }

    if($_POST['rms_url'] != null) {
        update_option('rms_url', $_POST['rms_url']);
    }


    if($_POST['rms_debug'] != null) {
        update_option('rms_debug', $_POST['rms_debug']);
    }

    foreach((array)$_POST['rms_form'] as $form => $value) {
        update_option(('rms_form_'.$form), $value);
    }
    return true;
}

function form_rms() {	
    $select_currency[get_option('rms_curcode')] = "selected='true'";

    $rms_debug = get_option('rms_debug');
    $rms_debug1 = "";
    $rms_debug2 = "";

    $output = "
            <tr>
              <td>Merchant ID</td>
              <td><input type='text' size='40' value='".get_option('rms_merchant_id')."' name='rms_merchant_id' /></td>
            </tr>

            <tr>
              <td>Verify Key</td>
              <td><input type='text' size='40' value='".get_option('rms_vkey')."' name='rms_vkey' /></td>
            </tr>
            <tr>
              <td>Return URL</td>
              <td><input type='text' size='40' value='".get_option('transact_url')."' name='rms_return_url' readonly/></td>
            </tr>
            <tr>
              <td>Callback URL</td>
              <td><input type='text' size='40' value='".get_option('transact_url')."' name='rms_callback_url' readonly/></td>
            </tr>

    ";
    return $output;
}
add_action('init', 'nzshpcrt_rms_callback');
add_action('init', 'nzshpcrt_rms_results');

/**
 * Add rms class to prevent name conflict
 * 
 */
class rms_inline_classes_object_function {
    
    /**
     * Get the order cart details
     * 
     * @param object $wpdb
     */
    static function query_data( $wpdb, $orderId ) {
        $cart_sql = "SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`='".$orderId."'"; 
        $cart = $wpdb->get_results($cart_sql, ARRAY_A) ;
        
        $ship_sql  = "SELECT form_id,value FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id`='".$cart[0]['purchaseid']."' ";
        return $wpdb->get_results($ship_sql, ARRAY_A);
    }
    
}
?>
