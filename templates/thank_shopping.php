<?php
/* Template name: Viberent thank-shopping */
session_start();
get_header();
?>
<script>
  jQuery(document).ready(function() {
    localStorage.clear();
  });
</script>
<?php
if (isset($_SESSION["cart_item"])) {
  $total_quantity = 0;
  $total_price = 0;
  $result = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "viberent_clients_company_info");
  $currencysymbol = $result[0]->currencysymbol;
  $dateFormatfromAPi = $result[0]->dateFormat;
  if ($dateFormatfromAPi == "dd/MM/yyyy") {
    $dateFormat = "j/m/Y";
  } else if ($dateFormatfromAPi == "MM/dd/yyyy") {
    $dateFormat = "m/j/Y";
  } else if ($dateFormatfromAPi == "MM-dd-yyyy") {
    $dateFormat = "m-j-Y";
  }
  $all_items = array();
  $companyID = $result[0]->companyID;
  $resapikey = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "viberent_apikey");
  $apikey = $resapikey[0]->apikey;
  $api_args = array(
    'timeout' => 10,
    'headers'     => array(
    'ApiKey' => $apikey,
    'CompanyId' => $companyID
    )
  );
  foreach ($_SESSION["cart_item"] as $item) {
    $getcode = $item["code"];
    $mystartDate = gmdate($dateFormat, strtotime($item["startDate"]));
    $myendDate = gmdate($dateFormat, strtotime($item["endDate"]));
    $rentalp = $item["rental_period"];
    if ($item["productAvailble"] >= $item["quantity"]) {
      $myquanti = $item["quantity"];
    } else {
      $myquanti = $item["productAvailble"];
    }
    $responseperiod = wp_remote_get($viberent_api_url . 'item/rental-periodtype?companyid=' . $companyID, $api_args);
    if (is_wp_error($responseperiod) || wp_remote_retrieve_response_code($responseperiod) != 200) {
      return false;
    }
    $responsbody = wp_remote_retrieve_body($responseperiod);
    $respperiod = json_decode($responsbody, 1);
    foreach ($respperiod as $myresp) {
      if ($myresp["name"] == $item["rental_period"]) {
        $myHireID = $myresp["periodTypeId"];
      }
    }
    $each_item = array("from" => $item["startDate"], "to" => $item["endDate"], "itemGUID" => $item["GUID"], "itemCode" => $item["code"], "price" => $item["price"], "itemHireTypeID" => $myHireID, "quantity" => $myquanti, "PeriodUnits" => $item["periodUnits"], "location" => $item["locationID"]);
    array_push($all_items, $each_item);
  }
  $resulty = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "viberent_post_array WHERE `custoname` IS NOT NULL ORDER BY ID DESC LIMIT 1");
  if (!empty($resulty)) {
    $data = array('customerName' => $resulty[0]->custoname, 'companyid' => $resulty[0]->companyID, "billingAddresses" => array("isBilling" => true, "addressType" => "BillTo", "billAddrDtls" => $resulty[0]->billing_address, "city" => $resulty[0]->city_bill, "state" => $resulty[0]->state_bill, "postalCode" => $resulty[0]->postalCode_bill, "country" => $resulty[0]->country_bill, "email" => $resulty[0]->email_bill, "phone" => $resulty[0]->phone_bill, "contactName" => $resulty[0]->custoname), "shipingAddresses" => array("isBilling" => true, "addressType" => "ShipTo", "billAddrDtls" => $resulty[0]->shipping_address, "city" => $resulty[0]->city_ship, "state" => $resulty[0]->state_ship, "postalCode" => $resulty[0]->postalCode_ship, "country" => $resulty[0]->country_ship, "email" => $resulty[0]->email_ship, "phone" => $resulty[0]->phone_ship, "contactName" => $resulty[0]->custoname), "items" => $all_items);

    $curlUrl = wp_remote_post($viberent_api_url . 'Quote/create', array(
      'headers'     => array('Content-Type' => 'application/json; charset=utf-8', 'ApiKey' => $apikey, 'CompanyId' => $companyID),
      'body'        => json_encode($data),
      'method'      => 'POST',
      'data_format' => 'body',
    ));


    if (is_wp_error($curlUrl) || wp_remote_retrieve_response_code($curlUrl) != 200) {
      return false;
    }
    $response = wp_remote_retrieve_body($curlUrl);
    $respquote = json_decode($response, 1);


    if (isset($respquote['QuoteNumber'])) {
      $wpdb->insert($wpdb->prefix . 'viberent_quote_number', $respquote);
    }
  }
}
$result = $wpdb->get_results("SELECT QuoteNumber from " . $wpdb->prefix . "viberent_quote_number WHERE `id` IS NOT NULL");
if (isset($result[0]->QuoteNumber)) { ?>
<div class="viberent-thanku-section">
  <div id="thanku" class="thanku-section">
    <h1><i>Thank you for shopping with us!</i></h1>
    <h4>Your order was successfully placed</h4>
   <div class="quoteNumer">
        <p>Please note your Quote Number for future reference</p><?php
        echo "<h3><b>Your Quote Number </b><span class='vibe_quote_num' style='margin:20px 0;'>";
        echo esc_html($result[0]->QuoteNumber) . "</span></h3";
        $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix  . "viberent_quote_number");
        unset($_SESSION["cart_item"]);
        $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix  . "viberent_tbl_product");
        $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix  . "viberent_post_array");
  
        $viberent_mypagename = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "viberent_pagename");
        $slug_name = sanitize_title($viberent_mypagename[0]->pagename);
    ?>
  </div>
    <div id="continue_btn">
      <a href="<?php echo esc_url(site_url() . "/" . $slug_name); ?>" id="btn_shop_now">Continue Shopping</a>
    </div>
  </div>
</div>
<?php } ?>
<?php get_footer(); ?>