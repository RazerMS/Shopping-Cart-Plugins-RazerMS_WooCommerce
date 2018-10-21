<?php
/*
	Plugin Name: Classipress MOLPay
	Plugin URI: http://www.github.com/MOLPay
	Description: MOLPay | The leading payment gateway in South East Asia Grow your business with MOLPay payment solutions & free features: Physical Payment at 7-Eleven, Seamless Checkout, Tokenization, Loyalty Program and more.
 	Author: MOLPay Tech Team
	Author URI: http://www.molpay.com/
	Version: 1.0
*/

/**
 * Payment gateways admin values plugin
 * This is pulled into the WordPress backend admin 
 * 
 *
 * Array param definitions are as follows:
 * name    = field name
 * desc    = field description
 * tip     = question mark tooltip text
 * id      = database column name or the WP meta field name
 * css     = any on-the-fly styles you want to add to that field
 * type    = type of html field or tab start/end
 * req     = if the field is required or not (1=required)
 * min     = minimum number of characters allowed before saving data
 * std     = default value. not being used
 * js      = allows you to pass in javascript for onchange type events
 * vis     = if field should be visible or not. used for dropdown values field
 * visid   = this is the row css id that must correspond with the dropdown value that controls this field
 * options = array of drop-down option value/name combo
 *
 *
 */

/***** MOLPAY ADMIN SETTING *********/
function molpay_add_gateway_values(){
	global $app_abbr, $action_gateway_values;

	$mol_gateway_values = array(
		array('type' => 'tab', 'tabname' => __('MOLPay', MOL_TD),
			'id' => ''),

			array('name' => __('<b>MOLPay Online Payment</b>', MOL_TD),
				'type' => 'title',
				'id' => ''),

			array('name' => __('Enable MOLPay', MOL_TD),
				'desc' => sprintf(__("<i>You must have a <a target='_new' href='%s'>MOLPay</a> account setup before using this feature.</i>", MOL_TD), 'http://www.molpay.com/v2/contact/merchant-enquiry'),
				'tip' => __('Set this to yes if you want to enable MOLPay as a payment option on your site.'),
				'id' => $app_abbr.'_enable_molpay',
				'css' => 'width:100px;',
				'std' => '',
				'js' => '',
				'type' => 'select',
				'options' => array('yes' => __('Yes', MOL_TD),
								'no' => __('No', MOL_TD))),

			array('name' => __('Merchant ID', MOL_TD),
				'desc' => sprintf(__("<i>Please enter your MOLPay Merchant ID. You can to get this information in: <a target='_new' href='%s'>MOLPay Account</i>", MOL_TD), 'https://www.onlinepayment.com.my/MOLPay/'),
				'tip'  => '',
				'id' => $app_abbr.'_molpay_merchant_id',
				'css' => 'min-width:250px;',
				'type' => 'text',
				'req' => '',
				'min' => '',
				'std' => '',
				'vis' => ''),

			array('name' => __('Verify Key', MOL_TD),
				'desc' => sprintf(__("<i>Please enter your MOLPay Verify Key. You can to get this information in: <a target='_new' href='%s'>MOLPay Account</i>", MOL_TD), 'https://www.onlinepayment.com.my/MOLPay/'),
				'tip' => '',
				'id' => $app_abbr.'_molpay_verify_key',
				'css' => 'min-width:250px;',
				'type' => 'text',
				'req' => '',
				'min' => '',
				'std' => '',
				'vis' => ''),

		array('type' => 'tabend', 'id' => ''),
	);

	// merge the above options with any passed into via the hook
	$action_gateway_values = array_merge((array)$action_gateway_values,(array)$mol_gateway_values);
}

add_action( 'cp_action_gateway_values', 'molpay_add_gateway_values' );


/**
 * add the option to the payment drop-down list on checkout
 *
 * @param array $order_vals contains all the order values
 *
 */

function molpay_add_gateway_option(){
	global $app_abbr, $gateway_name;

	if(get_option($app_abbr.'_enable_molpay') == 'yes')
		echo '<option value="molpay">'.__('MOLPay (Visa/MasterCard,M2U,FPX,etc.)', MOL_TD).'</option>';
}

add_action('cp_action_payment_method','molpay_add_gateway_option');


/**
* do all the payment processing work here
* @param array $order_vals contains all the order values
**/

function gateway_molpay($order_vals){
	global $wpdb, $gateway_name, $app_abbr, $post_url, $userdata;

	//if gateway wasn't selected then exit
	if($order_vals['cp_payment_method'] != 'molpay')
		return;

	$query = "SELECT * FROM $wpdb->users WHERE ID = ".$order_vals['user_id'];
	$stmt = $wpdb->get_results($query,ARRAY_A);
	$username = urlencode($stmt[0]['user_nicename']);
	$usermail = $stmt[0]['user_email'];

	//pack id
	$pack = $_POST['pack'];

	//get the merchant id
	$merchant_id = get_option($app_abbr.'_molpay_merchant_id');

	//get the verify key
	$verify_key = get_option($app_abbr.'_molpay_verify_key');
	
	$amount = $order_vals['item_amount'];
	$orderid = urlencode($order_vals['oid'].'U'.$order_vals['user_id'].'P'.$pack);

	$vcode = md5($amount.$merchant_id.$orderid.$verify_key);

	$molpay_url = "https://www.onlinepayment.com.my/MOLPay/pay/".$merchant_id.'/';

	$return_url = add_query_arg(array('oid' => $order_vals['oid'], 'molpay' => $order_vals['oid'].'_'.$userdata->ID), CP_DASHBOARD_URL);
	$return_url = wp_nonce_url($return_url,$order_vals['oid']);

	?>
	<form name='paymentform' method='post' action='<?php echo $molpay_url; ?>' accept-charset="utf-8">
		<input type="hidden" name="bill_name" value="<?php echo $username; ?>" />
		<input type="hidden" name="bill_email" value="<?php echo $usermail; ?>" />
		<input type="hidden" name="bill_desc" value="Membership Purchase" />
		<input type="hidden" name="merchant_id" value="<?php echo esc_attr( $merchant_id ); ?>"/>
		<input type="hidden" name="orderid" value="<?php echo $orderid; ?>"/>
		<input type="hidden" name="amount" value="<?php echo esc_attr( urlencode($order_vals['item_amount']) ); ?>" />
		<input type="hidden" name="currency" value="<?php echo esc_attr( $curr_code ); ?>" />
		<input type="hidden" name="vcode" value="<?php echo $vcode; ?>" />
		<input type="hidden" name="_charset_" value="utf-8" />
		<input type="hidden" name="returnurl" value="<?php echo esc_attr( $return_url ); ?>"/>

		<center><input type="submit" class="btn_orange" value="<?php _e('Continue &rsaquo;&rsaquo;', MOL_TD); ?>" /></center>
		<script type="text/javascript"> setTimeout("document.paymentform.submit();", 500); </script>
	</form>
<?php
}

add_action( 'cp_action_gateway', 'gateway_molpay', 10, 1 );


/**
 * Payment processing for ad dashboard so ad owners can pay for unpaid ads
**/
function dashboard_button_molpay($the_id,$type=''){
	global $wpdb, $app_abbr, $userdata;

	if(get_option($app_abbr.'_enable_molpay') != "yes")
		return;
	get_currentuserinfo();

	$pack = get_pack($the_id);

	//figure out the numbers of days this ad was listed for
	if(get_post_meta($the_id,'cp_sys_ad_duration',true))
		$prun_period = get_post_meta($the_id,'cp_sys_ad_duration',true);
	else
		$prun_period = get_option('cp_prun_period');

	//setup the variables based on purchase type
	if(isset($pack->pack_name) && stristr($pack->pack_status, 'membership')){
		//membership buttons not supported
		return;
	}else{
		$item_name = sprintf( __('Classified ad listing on %s for %s days', MOL_TD), get_bloginfo('name'), $prun_period);
		$item_number = get_post_meta($the_id, 'cp_sys_ad_conf_id', true); 
		$amount = get_post_meta($the_id, 'cp_sys_total_ad_cost', true);
		$orderid = get_post_meta( $the_id, 'cp_sys_ad_conf_id', true );
		$return_url = add_query_arg( array( 'oid' => $orderid, 'molpay' => $orderid .'_'. $userdata->ID ), CP_DASHBOARD_URL );
		$return_url = wp_nonce_url( $return_url, $orderid );
	}

	$username = urlencode($userdata->user_nicename);
	$usermail = $userdata->user_email;

	//get the merchant id
	$merchant_id = get_option($app_abbr.'_molpay_merchant_id');

	//get the verify key
	$verify_key = get_option($app_abbr.'_molpay_verify_key');
	$vcode = md5($amount.$merchant_id.$orderid.$verify_key);

	$molpay_url = "https://www.onlinepayment.com.my/MOLPay/pay/".$merchant_id.'/';

	$return_url = add_query_arg(array('oid' => $orderid, 'molpay' => $orderid.'_'.$userdata->ID), CP_DASHBOARD_URL);
	$return_url = wp_nonce_url($return_url,$orderid);
	
	?>
	<form name='paymentform' method='post' action='<?php echo $molpay_url; ?>' accept-charset="utf-8">
		<input type="hidden" name="bill_name" value="<?php echo $username; ?>" />
		<input type="hidden" name="bill_email" value="<?php echo $usermail; ?>" />
		<input type="hidden" name="bill_desc" value="Purchase Ads" />
		<input type="hidden" name="merchant_id" value="<?php echo esc_attr( $merchant_id ); ?>"/>
		<input type="hidden" name="orderid" value="<?php echo $orderid; ?>"/>
		<input type="hidden" name="amount" value="<?php echo esc_attr( urlencode($amount) ); ?>" />
		<input type="hidden" name="currency" value="<?php echo esc_attr( $curr_code ); ?>" />
		<input type="hidden" name="vcode" value="<?php echo $vcode; ?>" />
		<input type="hidden" name="_charset_" value="utf-8" />
		<input type="hidden" name="returnurl" value="<?php echo esc_attr( $return_url ); ?>"/>

		<center>
			<button style="cursor:pointer;">
				<img src="<?php echo plugins_url('/images/molpay.png', __FILE__); ?>" style='width:50px;height:15px;' />
			</button>
		<!-- <input type="submit" class="btn_orange" value="<?php _e('Continue &rsaquo;&rsaquo;', MOL_TD); ?>" /> -->
		</center>
		<script type="text/javascript"> setTimeout("document.paymentform.submit();", 500); </script>
	</form>
<?php
}

add_action('cp_action_payment_button','dashboard_button_molpay',10,1);

/**
* Process Ads Payment
**/

function molpay_to_merchant(){
	global $wpdb, $app_abbr;

	$vkey = get_option($app_abbr.'_molpay_verify_key');

	$_POST['treq'] = "1";

	$skey = $_POST['skey'];
	$tranID = $_POST['tranID'];
	$domain = $_POST['domain'];
	$status = $_POST['status'];
	$amount = $_POST['amount'];
	$currency = $_POST['currency'];
	$paydate = $_POST['paydate'];
	$multiorder = $_POST['orderid'];
	$appcode = $_POST['appcode'];
	$error_code = $_POST['error_code'];
	$error_desc = $_POST['error_desc'];
	$channel = $_POST['channel'];

	$str1 = explode("U",$multiorder);
	$str2 = explode("P",$str1[1]);
	$orderid = $str1[0];
	// $usrid = $str2[0];
	$packid = $str2[1];

	//backend acknowledge method for IPN
	while(list($k,$v) = each($_POST)){
		$postData[] = $k."=".$v;
	}
	$postdata = implode("&",$postData);
	$url = "https://www.onlinepayment.com.my/MOLPAY/API/chkstat/returnipn.php";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
	$output = curl_exec($ch);
	curl_close($ch);

	$key0 = md5($tranID.$multiorder.$status.$domain.$amount.$currency);
	$key1 = md5($paydate.$domain.$key0.$appcode.$vkey);

	if(isset($_GET['molpay']) && !empty($_GET['_wpnonce'])){
		//step functions required to process orders
		include_once('wp-load.php');
		include_once(TEMPLATEPATH.'/includes/forms/step-functions.php');

		$pid = explode("_", $_GET['molpay']);

		if(!wp_verify_nonce($_GET['_wpnonce'],$pid[0]))
			return;

		$order_processed = false;
		$order = get_option("cp_order_".$pid[1]."_".$pid[0]);
		$the_user = get_userdata($pid[1]);

		if($status == "00"){
			if($skey != $key1){
				// Invalid transaction.
				$pay_status = "FAILED";
				$pending_reason = "";
			}else{
				// Success
				$pay_status = "SUCCESSFUL";
				$pending_reason = "";

				// make sure the order sent from payment gateway is logged in the database and that the current user created item_number
				if (isset($order['order_id']) && $order['order_id'] == $pid[0]){
					$the_user = get_userdata($order['user_id']);
					$order['order_id'] = $pid[0];

					//activated membership or renew/extend membership pack
					$order_processed = appthemes_process_membership_order($the_user, $order);
				}

				if(!$order_processed){
					$sql = $wpdb->prepare("SELECT p.ID, p.post_status
						FROM $wpdb->posts p, $wpdb->postmeta m
						WHERE p.ID = m.post_id
						AND p.post_status <> 'publish'
						AND m.meta_key = 'cp_sys_ad_conf_id'
						AND m.meta_value = %s
						", $pid[0]);

					$newadid = $wpdb->get_row($sql);
				}
			}
		}else if($status == "22"){
			// Pending transaction
			$pay_status = "PENDING";
			$pending_reason = "AWAITING FOR PAYMENT";
		}else{
			// status 11 which is failed
			$pay_status = "FAILED";
			$pending_reason = "";
		}

		// if the ad is found, then publish it
		if ( $newadid ) {
			$the_ad = array();
			$the_ad['ID'] = $newadid->ID;
			$the_ad['post_status'] = 'publish';
			$ad_id = wp_update_post($the_ad);

			$ad_length = get_post_meta($ad_id, 'cp_sys_ad_duration', true);
			$ad_length = empty($ad_length) ? get_option('cp_prun_period') : $ad_length;

			// set the ad listing expiration date
			$ad_expire_date = date_i18n('m/d/Y H:i:s', strtotime('+' . $ad_length . ' days')); // don't localize the word 'days'

			//now update the expiration date on the ad
			update_post_meta($ad_id, 'cp_sys_expire_date', $ad_expire_date);
			
			$the_ad = get_post( $ad_id );
			$tr_subject = __('Ad Purchase', MOL_TD);
			$tr_amount = get_post_meta($ad_id, 'cp_sys_total_ad_cost', true);
		}else{
			$tr_amount = $order['total_cost'];

			if($tr_amount == ""){
				$tr_subject = __('Ad Purchase', MOL_TD);
				$tr_amount = $amount;
			}else{
				$ad_id = '';
				$tr_subject = __('Membership Purchase', MOL_TD);
			}
		}

		$data = array(
			'ad_id' => appthemes_clean($ad_id),
			'user_id' => appthemes_clean($the_user->ID),
			'first_name' => appthemes_clean($the_user->first_name),
			'last_name' => appthemes_clean($the_user->last_name),
			'payer_email' => appthemes_clean($the_user->user_email),
			'residence_country' => '',
			'transaction_subject' => appthemes_clean($pid[0]),
			'item_name' => appthemes_clean($tr_subject),
			'item_number' => appthemes_clean($pid[0]),
			'payment_type' => $channel,
			'payer_status' => '',
			'payer_id' => '',
			'receiver_id' => '',
			'parent_txn_id' => '',
			'txn_id' => appthemes_clean($pid[0]),
			'mc_gross' => appthemes_clean($tr_amount),
			'mc_fee' => '',
			'payment_status' => $pay_status,
			'pending_reason' => $pending_reason,
			'txn_type' => '',
			'tax' => '',
			'mc_currency' => $currency,
			'reason_code' => $status,
			'custom' => appthemes_clean($pid[0]),
			'test_ipn' => '',
			'payment_date' => current_time('mysql'),
			'create_date' => current_time('mysql'),
		);

		// check and make sure this transaction hasn't already been added
		$item_number = $wpdb->get_var($wpdb->prepare("SELECT item_number FROM $wpdb->cp_order_info WHERE item_number = %s LIMIT 1", appthemes_clean($pid[0])));				
		$paystat = $wpdb->get_var($wpdb->prepare("SELECT payment_status FROM $wpdb->cp_order_info WHERE item_number = %s", $item_number));
		
		if($item_number){
			if($paystat == "SUCCESSFUL" && $status == "11"){
				return;
			}else{
				$wpdb->update($wpdb->cp_order_info,$data,array("item_number"=>$item_number)); 
			}
		}else{
			$wpdb->insert($wpdb->cp_order_info,$data);
		}
	}
}

add_action('init', 'molpay_to_merchant');

/*
 * This part is callback function for MOLPay
 */
function molpay_response_callback(){
	if(isset($_REQUEST['mode'])){
		if($_REQUEST['mode'] == "callback"){
			global $wpdb, $app_abbr;

			$vkey = get_option($app_abbr.'_molpay_verify_key');

			$nbcb = $_POST['nbcb'];
			$skey = $_POST['skey'];
			$tranID = $_POST['tranID'];
			$domain = $_POST['domain'];
			$status = $_POST['status'];
			$amount = $_POST['amount'];
			$currency = $_POST['currency'];
			$paydate = $_POST['paydate'];
			$multiorder = $_POST['orderid'];
			$appcode = $_POST['appcode'];
			$error_code = $_POST['error_code'];
			$error_desc = $_POST['error_desc'];
			$channel = $_POST['channel'];

			$str1 = explode("U",$multiorder);
			$str2 = explode("P",$str1[1]);
			$orderid = $str1[0];
			$_REQUEST['oid'] = $orderid;
			$usrid = $str2[0];
			$packid = $str2[1];

			$key0 = md5($tranID.$multiorder.$status.$domain.$amount.$currency);
			$key1 = md5($paydate.$domain.$key0.$appcode.$vkey);

			//step functions required to process orders
			include_once('wp-load.php');
			include_once(TEMPLATEPATH.'/includes/forms/step-functions.php');

			// $pid = explode("_", $_GET['molpay']);

			// if(!wp_verify_nonce($_GET['_wpnonce'],$pid[0]))
				// return;

			$order_processed = false;
			$order = get_option("cp_order_".$usrid."_".$orderid);		
			$the_user = get_userdata($usrid);

			if($status == "00"){
				if($skey != $key1){
					// Invalid transaction.
					$pay_status = "FAILED";
					$pending_reason = "";
				}else{
					// Success
					$pay_status = "SUCCESSFUL";
					$pending_reason = "";

					//add meta options for callback function only
					if(!is_array($order) || count($order) == 0){
						$pack = $wpdb->get_results("SELECT * FROM $wpdb->cp_ad_packs WHERE pack_id = ".$packid);

						$package = json_decode(json_encode($pack),true);
						$spack = array_pop($package);
						$opt_name = "cp_order_".$usrid."_".$orderid;
						$add_on = array(
							"user_id" => $usrid,
							"order_id" => $orderid,
							"option_order_id" => $opt_name,
							"total_cost" => $amount,
							);

						$addin = array_merge($add_on, $spack);
						$opt_val = serialize($addin);

						$opt_data = array(
							"option_name" => $opt_name,
							"option_value" => $opt_val,
							"autoload" => "yes",
							);

						$tblname = $wpdb->prefix.'options';
						$wpdb->insert($tblname,$opt_data);

						$order = unserialize($opt_val);
					}

					// make sure the order sent from payment gateway is logged in the database and that the current user created item_number
					if (isset($order['order_id']) && $order['order_id'] == $orderid){
						$the_user = get_userdata($order['user_id']);
						$order['order_id'] = $orderid;

						//activated membership or renew/extend membership pack
						$order_processed = appthemes_process_membership_order($the_user, $order);
					}

					if(!$order_processed){
						$sql = $wpdb->prepare("SELECT p.ID, p.post_status
							FROM $wpdb->posts p, $wpdb->postmeta m
							WHERE p.ID = m.post_id
							AND p.post_status <> 'publish'
							AND m.meta_key = 'cp_sys_ad_conf_id'
							AND m.meta_value = %s
							", $orderid);

						$newadid = $wpdb->get_row($sql);
					}
				}
			}else if($status == "22"){
				// Pending transaction
				$pay_status = "PENDING";
				$pending_reason = "AWAITING FOR PAYMENT";
			}else{
				// status 11 which is failed
				$pay_status = "FAILED";
				$pending_reason = "";
			}

			// if the ad is found, then publish it
			if ( $newadid ) {
				$the_ad = array();
				$the_ad['ID'] = $newadid->ID;
				$the_ad['post_status'] = 'publish';
				$ad_id = wp_update_post($the_ad);

				$ad_length = get_post_meta($ad_id, 'cp_sys_ad_duration', true);
				$ad_length = empty($ad_length) ? get_option('cp_prun_period') : $ad_length;

				// set the ad listing expiration date
				$ad_expire_date = date_i18n('m/d/Y H:i:s', strtotime('+' . $ad_length . ' days')); // don't localize the word 'days'

				//now update the expiration date on the ad
				update_post_meta($ad_id, 'cp_sys_expire_date', $ad_expire_date);
				
				$the_ad = get_post( $ad_id );
				$tr_subject = __('Ad Purchase', MOL_TD);
				$tr_amount = get_post_meta($ad_id, 'cp_sys_total_ad_cost', true);
			}else{
				$tr_amount = $order['total_cost'];

				if($tr_amount == ""){
					$tr_subject = __('Ad Purchase', MOL_TD);
					$tr_amount = $amount;
				}else{
					$ad_id = '';
					$tr_subject = __('Membership Purchase', MOL_TD);
				}
			}

			$data = array(
				'ad_id' => appthemes_clean($ad_id),
				'user_id' => appthemes_clean($the_user->ID),
				'first_name' => appthemes_clean($the_user->first_name),
				'last_name' => appthemes_clean($the_user->last_name),
				'payer_email' => appthemes_clean($the_user->user_email),
				'residence_country' => '',
				'transaction_subject' => appthemes_clean($orderid),
				'item_name' => appthemes_clean($tr_subject),
				'item_number' => appthemes_clean($orderid),
				'payment_type' => $channel,
				'payer_status' => '',
				'payer_id' => '',
				'receiver_id' => '',
				'parent_txn_id' => '',
				'txn_id' => appthemes_clean($orderid),
				'mc_gross' => appthemes_clean($tr_amount),
				'mc_fee' => '',
				'payment_status' => $pay_status,
				'pending_reason' => $pending_reason,
				'txn_type' => '',
				'tax' => '',
				'mc_currency' => $currency,
				'reason_code' => $status,
				'custom' => appthemes_clean($orderid),
				'test_ipn' => '',
				'payment_date' => current_time('mysql'),
				'create_date' => current_time('mysql'),
			);

			// check and make sure this transaction hasn't already been added
			$item_number = $wpdb->get_var($wpdb->prepare("SELECT item_number FROM $wpdb->cp_order_info WHERE item_number = %s LIMIT 1", appthemes_clean($orderid)));				
			$paystat = $wpdb->get_var($wpdb->prepare("SELECT payment_status FROM $wpdb->cp_order_info WHERE item_number = %s", $item_number));
			
			if($item_number){
				if($paystat == "SUCCESSFUL" && $status == "11"){
					return;
				}else{
					$wpdb->update($wpdb->cp_order_info,$data,array("item_number"=>$item_number)); 
				}
			}else{
				$wpdb->insert($wpdb->cp_order_info,$data);
			}

			if($nbcb == 1){
				echo "CBTOKEN:MPSTATOK";
				exit;
			}
		}
	}
}

add_action('init','molpay_response_callback');
?>