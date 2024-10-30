<?php

add_action('wp_ajax_viberent_layoutBased_form', 'viberent_layoutBased_form');
add_action('wp_ajax_nopriv_viberent_layoutBased_form', 'viberent_layoutBased_form');

function viberent_layoutBased_form()
{ 
    // echo "<pre>";
    // print_r($_POST);
    // die();
    global $wpdb, $viberent_api_url;
    session_start();
    wp_verify_nonce('viberent_nonce');
    $start_from_date = gmdate('Y-m-d', strtotime(sanitize_text_field($_POST['formData']['startDate'])));
    $end_to_date = gmdate('Y-m-d', strtotime(sanitize_text_field($_POST['formData']['endDate'])));
    $splitInPeak = 0;
    $result = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "viberent_clients_company_info");
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
    $responseperiod = wp_remote_get($viberent_api_url . 'item/rental-periodtype?companyid=' . $companyID, $api_args);
    if (is_wp_error($responseperiod) || wp_remote_retrieve_response_code($responseperiod) != 200) {
        return false;
    }
    $responsbody = wp_remote_retrieve_body($responseperiod);
    $respperiod = json_decode($responsbody, 1);
    $countresp = 0;
    foreach ($respperiod as $myresp) {
        if ($countresp == 0) {
            $firstRental_period = $myresp["name"];
        }
        $countresp = $countresp + 1;
    }
    if (isset($_POST['formData']['rentalratesName'])) {
        $rental_period = sanitize_text_field($_POST['formData']['rentalratesName']);
    } else {
        $rental_period = sanitize_text_field($firstRental_period);
    }

    if (isset($_POST['formData']['categoryName'])) {
        $categoryName = sanitize_text_field($_POST['formData']['categoryName']);
    } else {
        $categoryName = "all";
    }

    $actionReqest = sanitize_text_field($_POST['actionRequest']);
    $sessionId = sanitize_text_field($_POST['formData']['sessionID'] . $_POST['formData']['startDate']);

    $sessionId = str_replace('+', 'plus', $sessionId);

    $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "viberent_tbl_product WHERE sessionID = %s", $sessionId ) );
    $GUID = array();
    $rentalPeriod = array();
    foreach ($results as $val) {
        $GUID = sanitize_text_field($val->GUID);
        $rentalPeriod = sanitize_text_field($val->rental_period);
        $sessionID = sanitize_text_field($val->sessionID);

        $sessionID = str_replace('+', 'plus', $sessionID);

    }

    if ($GUID != $_POST['formData']['itemGUID'] || $rentalPeriod != $rental_period || $sessionID != $sessionId) {

        $mynewsess = str_replace('+', 'plus', sanitize_text_field($_POST['formData']['sessionID'] . $_POST['formData']['startDate']));
       
        if(isset($_POST['formData']['periodUnits'])){
            $periodUnits = sanitize_text_field($_POST['formData']['periodUnits']);
        }else{
            $periodUnits = 1;
        }

        $item_layout = array(
            "product_name" => sanitize_text_field($_POST['formData']['itemName']),
            "product_image" => sanitize_text_field($_POST['formData']['productimage']),
            "price" => sanitize_text_field($_POST['formData']['rentalratesprice']),
            "taxrate" => sanitize_text_field(number_format($_POST['formData']['taxRate'], 2)),
            "quantity" => sanitize_text_field($_POST['formData']['requireQuantity']),
            "category_name" => $categoryName,
            "code" => sanitize_text_field($_POST['formData']['itemCode']),
            "GUID" => sanitize_text_field($_POST['formData']['itemGUID']),
            "hireTypeID" => sanitize_text_field($_POST['formData']['hireTypeID']),
            "locationID" => sanitize_text_field($_POST['formData']['locationID']),
            "productAvailable" => sanitize_text_field($_POST['formData']['productAvailable']),
            "periodUnits" => $periodUnits,
            "rental_period" => $rental_period,
            "periodTypeId" => sanitize_text_field($_POST['formData']['periodTypeId']),
            "startDate" => $start_from_date,
            "endDate" =>  $end_to_date,
            "sessionID" => $mynewsess
        );

        $wpdb->insert($wpdb->prefix . 'viberent_tbl_product', $item_layout);
    }
    viberentCheckAction($actionReqest, $sessionId);

    $cartCount = is_array($_SESSION['cart_item']) ? count($_SESSION['cart_item']) : 0;
    $productByCode = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "viberent_tbl_product WHERE sessionID = %s", $sessionId ) );
    $count = 0;
    if (isset($_SESSION["cart_item"])) {
        foreach ($_SESSION["cart_item"] as $k => $item) {
            if ($productByCode[0]->GUID == $item['GUID'] && $productByCode[0]->rental_period == $item['rental_period']) {
                $count++;
            }
        }
    }
    $var1 = $cartCount;
    $var2 = $count;
    $varArray = array("cart_count" => $var1, "item_count" => $var2);
    echo json_encode($varArray);
    wp_die();
}

function viberentCheckAction($action, $sessionId)
{
    global $wpdb;
    session_start();
    wp_verify_nonce('viberent_nonce');
    if (!empty($action)) {
        switch ($action) {
            case "addToCart":
                $productByCode = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "viberent_tbl_product WHERE sessionID = %s", $sessionId ) );
                $itemArray = array($productByCode[0]->sessionID => array(
                    'product_name' => $productByCode[0]->product_name,
                    'code' => $productByCode[0]->code,
                    "GUID" => $productByCode[0]->GUID,
                    "hireTypeID" => $productByCode[0]->hireTypeID,
                    "locationID" => $productByCode[0]->locationID,
                    'quantity' => $productByCode[0]->quantity,
                    'price' => $productByCode[0]->price,
                    'product_image' => $productByCode[0]->product_image,
                    'rental_period' => $productByCode[0]->rental_period,
                    'periodTypeId' => $productByCode[0]->periodTypeId,
                    'startDate' => $productByCode[0]->startDate,
                    'productAvailble' => $productByCode[0]->productAvailable,
                    'periodUnits' => $productByCode[0]->periodUnits,
                    'endDate' => $productByCode[0]->endDate,
                    'taxRate' =>  $productByCode[0]->taxRate,
                    'sessionID' => $productByCode[0]->sessionID
                ));
                if (!empty($_SESSION["cart_item"])) {
                    if (in_array($productByCode[0]->sessionID, array_keys($_SESSION["cart_item"]))) {
                        foreach ($_SESSION["cart_item"] as $k => $v) {
                            if ($productByCode[0]->sessionID == $k) {
                                if (empty($_SESSION["cart_item"][$k]["quantity"])) {
                                    $_SESSION["cart_item"][$k]["quantity"] = 0;
                                }
                                $_SESSION["cart_item"][$k]["quantity"] += sanitize_text_field($_POST['formData']['requireQuantity']);
                            }
                        }
                    } else {
                        $_SESSION["cart_item"] = array_merge($_SESSION["cart_item"], $itemArray);
                    }
                } else {
                    $_SESSION["cart_item"] = $itemArray;
                }
                break;
            case "empty":
                unset($_SESSION["cart_item"]);
                $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix  . "viberent_tbl_product");
                break;
        }
    }
}
