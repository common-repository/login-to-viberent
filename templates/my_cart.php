<?php
/* Template name: Viberent my-cart */
session_start();
get_header();
global $wpdb;

if ( !empty($_SESSION["cart_item"]) && isset($_POST['sessionID']) && isset($_POST["viberent_cart_nonce"]) ) {
    wp_verify_nonce($_POST["viberent_cart_nonce"], 'viberent_cart_nonce');
    $session_ID = sanitize_text_field($_POST['sessionID']);
    $productByCode = $wpdb->get_results( $wpdb->prepare( "SELECT * from " . $wpdb->prefix . "viberent_tbl_product WHERE sessionID = %s", $session_ID ) );
    foreach (sanitize_post($_SESSION["cart_item"]) as $k => $v) {
        if ($productByCode[0]->sessionID == $k) {
            if (empty($_SESSION["cart_item"][$k]["quantity"])) {
                $_SESSION["cart_item"][$k]["quantity"] = 0;
            }
            $quan = isset($_POST['quan']) ? sanitize_text_field($_POST['quan']) : "";
            if ($quan <= $v["productAvailble"]) {
                $_SESSION["cart_item"][$k]["quantity"] = $quan;
            }
        }
    }
}
if ( !empty($_GET["action"]) ) {
    wp_verify_nonce('viberent_cart_nonce');
    switch (sanitize_text_field($_GET["action"])) {
        case "remove":
            if (!empty($_SESSION["cart_item"])) {
                foreach (sanitize_post($_SESSION["cart_item"]) as $k => $v) {
                    if (sanitize_text_field($_GET["sessionID"]) == $k)
                        unset($_SESSION["cart_item"][$k]);
                    $delete_id = trim(sanitize_text_field($_GET["sessionID"]));
                    $wpdb->delete($wpdb->prefix . 'viberent_tbl_product', array('sessionID' => $delete_id));
                    if (empty($_SESSION["cart_item"]))
                        unset($_SESSION["cart_item"]);
                }
            }
            break;
        case "empty":
            unset($_SESSION["cart_item"]);
            $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix  . "viberent_tbl_product");
            break;
    }
}
$viberent_mypagename = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "viberent_pagename");
$slug_name = sanitize_title($viberent_mypagename[0]->pagename);
?>

<div class="viberent_my_cart">
    <div class="container mb-0 vibs_topdiv">
        <div class="d-flex py-3" id="vibfontop">
            <div class="d-flex">
                <div class="text-muted d-flex">
                    <a href="<?php echo esc_url(site_url() . "/" . $slug_name); ?>">
                        Home
                    </a>
                </div>
                <div class="px-3"> | </div>
                <div class="text-dark"> Shopping Cart</div>
            </div>
        </div>
        <div class="d-flex justify-content-between vibshopheading">
            <h3 class="pb-1 m-0 vibshophead"> SHOPPING CART </h3>
            <?php         
            if (isset($_SESSION["cart_item"])) {
            ?>
                <div id="empty_cart">
                    <a id="btnEmpty" href="#">Empty Cart</a>
                </div>
            <?php         
            }
            ?>
        </div>
    </div>

    <div class="container vibs_maindiv">
    <div class="row belowheadgap">
        <?php
        if (isset($_SESSION["cart_item"])) {
            $total_quantity = 0;
            $total_price = 0;
            $cart_count = 0;
        ?>
    <div class="cart_page col-md-9" id="shopping-cart">
            <table class="tbl-cart table-responsive m-0" cellpadding="10" cellspacing="1">
                <tbody>
                    <tr>
                        <th class="text-start" width="27%">ITEMS</th>
                        <th class="text-start" width="10%">PERIOD TYPE</th>
                        <th class="text-start" width="10%">START DATE</th>
                        <th class="text-start" width="10%">END DATE</th>
                        <th class="text-center" width="8%">QTY</th>
                        <th class="text-center" width="10%">PERIOD UNIT</th>
                        <th class="text-end" width="12%">PRICE</th>
                        <th class="text-end" width="13%">TOTAL</th>
                    </tr>
                    <?php
                    $result = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "viberent_clients_company_info");
                    $companyID = sanitize_text_field($result[0]->companyID);
                    $currencysymbol = sanitize_text_field($result[0]->currencysymbol);
                    $dateFormatfromAPi = sanitize_text_field($result[0]->dateFormat);
                    if ($dateFormatfromAPi == "dd/MM/yyyy") {
                        $dateFormat = "j/m/Y";
                    } else if ($dateFormatfromAPi == "MM/dd/yyyy") {
                        $dateFormat = "m/j/Y";
                    } else if ($dateFormatfromAPi == "MM-dd-yyyy") {
                        $dateFormat = "m-j-Y";
                    }
                    $resapikey = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "viberent_apikey");
                    $apikey = $resapikey[0]->apikey;
                    $api_args = array(
                        'timeout' => 10,
                        'headers'     => array(
                            'ApiKey' => $apikey,
                            'CompanyId' => $companyID
                        )
                    );
                    foreach ($_SESSION["cart_item"] as $key => $item) {
                        $responseperiod = wp_remote_get($viberent_api_url . 'item/rental-periodtype?companyid=' . $companyID, $api_args);
                        if (is_wp_error($responseperiod) || wp_remote_retrieve_response_code($responseperiod) != 200) {
                            return false;
                        }
                        $responsbody = wp_remote_retrieve_body($responseperiod);
                        $respperiod = json_decode($responsbody, 1);
                        foreach ($respperiod as $retrieved_period) {
                            if ($item["rental_period"] == $retrieved_period["name"]) {
                                //echo $viberent_api_url . 'Item/item-availability?itemGUID=' . $item["GUID"] . '&companyid=' . $companyID . '&fromDate=' . $item["startDate"] . '&todate=' . $item["endDate"] . '&PeriodTypeId=' . $retrieved_period["periodTypeId"] . '&locationID=' . $item["locationID"], $api_args;
                                $curlavail = wp_remote_get($viberent_api_url . 'Item/item-availability?itemGUID=' . $item["GUID"] . '&companyid=' . $companyID . '&fromDate=' . $item["startDate"] . '&todate=' . $item["endDate"] . '&PeriodTypeId=' . $retrieved_period["periodTypeId"] . '&locationID=' . $item["locationID"], $api_args);
                                if (is_wp_error($curlavail) || wp_remote_retrieve_response_code($curlavail) != 200) {
                                    return false;
                                }
                            }
                        }
            
                        $responseavail = wp_remote_retrieve_body($curlavail);
        
                        $respavail = json_decode($responseavail, 1);

                        $getcode = $item['GUID'];
                        $taxRate = $item['taxRate'];
          
                        if ($item["productAvailble"] >= $item["quantity"]) {
                            $productQuantity = $item["quantity"];
                        } else {
                            $productQuantity = $item["productAvailble"];
                        }

                        if ( isset($_POST["viberent_cart_nonce"]) ) {
                            wp_verify_nonce('viberent_cart_nonce');
                            $pID = isset($_POST['pID']) ? sanitize_text_field($_POST['pID']) : sanitize_text_field($getcode);
                            $quan = isset($_POST['quan']) ? sanitize_text_field($_POST['quan']) : sanitize_text_field($productAvailable);
                            $wpdb->query($wpdb->prepare("UPDATE " . $wpdb->prefix . "viberent_tbl_product
                                 SET quantity= %s
                                 WHERE code= %s", $quan, $pID));
                        }
                        if (isset($productQuantity) && isset($item["price"]) && isset($respavail[0]["periodUnits"])) {
                            $item_price = (int)$productQuantity * (float)$item["price"] * (float)$respavail[0]["periodUnits"];
                            $item_total_price = ($item["price"] * $respavail[0]["periodUnits"]) * $productQuantity;
                        }
                    ?>
                        <tr>
                            <td class="text-start"><img src="<?php echo esc_url($item["product_image"]); ?>" class="cart-item-image" />
                                <div class="d-block">
                                <p class="my-auto">
                                    <?php echo esc_html($item["product_name"]); ?>
                                </p>
                                <a href='#' class="btnRemoveAction" data-value="<?php echo esc_attr($item['sessionID']); ?>">
                                    <i class="fa fa-times" aria-hidden="true"></i>
                                    Remove
                                </a>
                            </div>
                            </td>
                            <td class="text-start"><?php echo esc_html($item["rental_period"]); ?></td>
                            <td class="text-start"><?php echo esc_html(gmdate($dateFormat, strtotime($item["startDate"]))); ?></td>
                            <td class="text-start"><?php echo esc_html(gmdate($dateFormat, strtotime($item["endDate"]))); ?></td>
                            <td class="text-end item-row p-0">
                                <form class="viberent_cart_quantity" action="<?php echo esc_attr( site_url() . "/my-cart/" ); ?>" method="post">
                                    <input type='hidden' value="<?php echo esc_attr($item['sessionID']); ?>" name='sessionID'>
                                    <input type='hidden' class="product_available" value="<?php echo esc_attr($item['productAvailble']); ?>">
                                    <input type="hidden" class="viberent_cart_nonce" name="viberent_cart_nonce" value="wp_create_nonce( 'viberent_cart_nonce' )" />
                                    <input type="number" min="1" class="productQuantity" name="quan" value="<?php echo esc_attr($productQuantity); ?>">
                                    <input type="submit" class="quantity-submit" name="quantity-submit">
                                </form>
                            </td>
                            <td class="text-center"><?php echo esc_html($respavail[0]["periodUnits"]); ?></td>
                            <td class="text-end"><?php echo esc_html($currencysymbol . " " . $item["price"]); ?></td>
                            <td class="text-end"><?php echo esc_html($currencysymbol . " " . number_format($item_total_price, 2)); ?></td>
                        </tr>
                    <?php
                        (int)$total_quantity += (int)$productQuantity;
                        (float)$total_price += (float)($item["price"] * (int)$productQuantity) * (float)$respavail[0]["periodUnits"];
                        $cart_count = count(array_keys($_SESSION["cart_item"]));
                        $tax_val +=  isset($_SESSION["cart_item"]) ? (float)($taxRate/100 * $item_total_price) : 0;
                    }
                    (float)$totalPrice += (float)$total_price + (float)$tax_val;
                    ?>
                    <input type="hidden" id="totalQuantity" value="<?php echo esc_attr($cart_count); ?>">
                    <tr>
                        <td colspan="8" class="text-end">
                            Subtotal (<?php echo esc_html($total_quantity) . " items): "; ?><strong><?php echo esc_html($currencysymbol . " " . number_format($total_price, 2) ); ?></strong>
                        </td>
                    </tr>
                </tbody>
            </table>
    </div>

    <div class="col-md-3">
        <div class="cart-box sticky-top">
            <div class="cart-box-sticky">
                <div class="order-summary">
                    <p>ORDER SUMMARY</p>
                </div>
                <div class="subtotal-details d-flex justify-content-between">
                    <div>SUBTOTAL</div>
                    <div>
                        <?php echo esc_html($currencysymbol . " " . number_format($total_price, 2) ); ?>
                    </div>
                </div>
                <div class="tax-details d-flex justify-content-between">
                    <div>GST</div>
                    <div>
                        <?php echo esc_html($currencysymbol . " " . number_format($tax_val, 2) ); ?>
                    </div>
                </div>
                <div class="total-details d-flex justify-content-between">
                    <div>TOTAL</div>
                    <div>
                        <?php echo esc_html($currencysymbol . " " . number_format($totalPrice, 2) ); ?>
                    </div>
                </div>
                <div class="checkout-btns">
                <a href="<?php echo esc_url( site_url() . "/place-my-order/" ) ?>">
                    <button type="submit" name="my-place-order" id="btn_place_order">
                        <h5 class="m-0 p-2">PROCEED TO CHECKOUT</h5>
                    </button>
                </a>
                </div>
            </div>
        </div>
        <div class="continue_shopping_viberent text-end py-2">
            <a href="<?php echo esc_url(site_url() . "/" . $slug_name); ?>">
                <i class="viberent_back_button text-dark fas fa-arrow-left fa-stack-1x text-left"></i>
                CONTINUE SHOPPING
            </a>
        </div>
    </div>
        <?php
        } else {
        ?>
            <div class="no-records">
                <p class="mb-2">Your Cart is Empty!</p>
                <p class="mb-3">Please add items to place an enquiry</p>
                <a href="<?php echo esc_url( site_url() . "/" . $slug_name ); ?>" class="viberent_shop_now text-center text-white m-auto btn btn-primary border-0 h4 p-1 px-3 rounded">Shop Now</a>
            </div>
            <?php
        }
        ?>
</div>
</div>
<?php get_footer(); ?>