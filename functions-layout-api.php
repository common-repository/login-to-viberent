<?php
global $viberent_api_url;
$viberent_api_url = 'https://viberent-api.azurewebsites.net/api/';
function viberent_get_page_id_by_title($title)
{
    $page = get_page_by_title($title);
    return $page->ID;
}
function viberent_create_page($title_of_the_page, $parent_id = NULL)
{
    $objPage = get_page_by_title($title_of_the_page, 'OBJECT', 'page');
    if (!empty($objPage)) {
        return $objPage->ID;
    }
    global $wpdb;
    if (null === $wpdb->get_row("SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = 'new-page-slug'", 'ARRAY_A')) {
        $page =  array(
            'comment_status' => 'close',
            'ping_status'    => 'close',
            'post_author'    => 1,
            'post_title'     => ucwords($title_of_the_page),
            'post_name'      => strtolower(str_replace(' ', '-', trim($title_of_the_page))),
            'post_status'    => 'publish',
            'post_content'   => '',
            'post_type'      => 'page',
            'post_parent'    =>  $parent_id
        );
        $page_id = wp_insert_post($page);
        return $page_id;
    }
}
function viberent_init()
{
    global $wpdb, $viberent_api_url;
    $charset_collate = $wpdb->get_charset_collate();
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $viberent_table_name = $wpdb->prefix . 'viberent_clients_company_info';
    $sql = "CREATE TABLE $viberent_table_name (
    companyID INTEGER NOT NULL,
    companyName TEXT NOT NULL,
    useName TEXT NOT NULL,
    isAdmin BOOLEAN NOT NULL,
    isMultiLocation BOOLEAN NOT NULL,
    dateFormat TEXT NOT NULL,
    currencysymbol TEXT NOT NULL,
    agreementText TEXT NOT NULL,
    logo MEDIUMTEXT NOT NULL,
    PRIMARY KEY (companyID)
  ) $charset_collate;";
    dbDelta($sql);

    $table_apikey = $wpdb->prefix . 'viberent_apikey';
    $sql = "CREATE TABLE $table_apikey (
    companyID INTEGER NOT NULL,
    apikey TEXT NOT NULL,
    PRIMARY KEY (companyID)
  ) $charset_collate;";
    dbDelta($sql);

    $table_layout = $wpdb->prefix . 'viberent_layout';
    $sql = "CREATE TABLE $table_layout (
    companyID INTEGER NOT NULL,
    useName TEXT NOT NULL,
    selected_layout TEXT NOT NULL,
    PRIMARY KEY (companyID)
  ) $charset_collate;";
    dbDelta($sql);

    $table_pagename = $wpdb->prefix . 'viberent_pagename';
    $sql = "CREATE TABLE $table_pagename (
    companyID INTEGER NOT NULL,
    useName TEXT NOT NULL,
    pagename TEXT NOT NULL,
    PRIMARY KEY (companyID)
  ) $charset_collate;";
    dbDelta($sql);

    $table_addTocart = $wpdb->prefix . 'viberent_tbl_product';
    $sql = "CREATE TABLE $table_addTocart (
    id int(11) NOT NULL AUTO_INCREMENT,
    sessionID varchar(255) NOT NULL,
    product_name varchar(255) NOT NULL,
    category_name varchar(255) NOT NULL,
    quantity int(10) NOT NULL,
    product_image varchar(255) NOT NULL,
    code varchar(255) NOT NULL,
    productAvailable varchar(255) NOT NULL,
    periodUnits Float(20) NOT NULL,
    GUID varchar(255) NOT NULL,
    hireTypeID varchar(255) NOT NULL,
    locationID varchar(255) NOT NULL,
    price double(10,2) NOT NULL,
    taxRate Float(10) NOT NULL,   
    rental_period varchar(255) NOT NULL,
    periodTypeId int(10) NOT NULL,
    startDate DATE NOT NULL DEFAULT '0000-00-00',
    endDate DATE NOT NULL DEFAULT '0000-00-00',
    PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);

    $QuoteNumber = $wpdb->prefix . 'viberent_quote_number';
    $sql = "CREATE TABLE $QuoteNumber (
    id int(11) NOT NULL AUTO_INCREMENT,
    QuoteNumber TEXT NOT NULL,
    PRIMARY KEY (id)
  ) $charset_collate;";
    dbDelta($sql);

    $ViberentPostArray = $wpdb->prefix . 'viberent_post_array';
    $sql = "CREATE TABLE $ViberentPostArray (
    id int(11) NOT NULL AUTO_INCREMENT,
    custoname TEXT NOT NULL,
    companyID TEXT NOT NULL,
    billing_address TEXT NOT NULL,
    city_bill TEXT NOT NULL,
    state_bill TEXT NOT NULL,
    postalCode_bill TEXT NOT NULL,
    country_bill TEXT NOT NULL,
    email_bill TEXT NOT NULL,
    phone_bill TEXT NOT NULL,
    shipping_address TEXT,
    city_ship TEXT,
    state_ship TEXT,
    postalCode_ship TEXT,
    country_ship TEXT,
    email_ship TEXT,
    phone_ship TEXT,
    PRIMARY KEY (id)
  ) $charset_collate;";
    dbDelta($sql);
        
$result = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "viberent_clients_company_info");
$viberent_mylayout = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "viberent_layout");
$viberent_mypagename = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "viberent_pagename");
?>
<div id="full_page_viberent_plugin">
    <div>
        <form method="post" id="viberent_plugin_login_form">
            <div class="viberent_login_logo">
                <img id="viberent_logo" src="<?php echo esc_url( plugin_dir_url(__FILE__) . 'assets/images/VibeRent-LOGO.png' ); ?>">
            </div>
            <div id="LoginForm" class="container">
                <h3 class="viberent_login_heading">LOGIN</h3>
                <label for="password"><b>Api Key</b></label>
                <input type="password" placeholder="Enter Api Key" name="password" id="password" required>
                <div class="viberent_login_button">
                    <button type="submit" id="login" name="submit">LOGIN</button>
                </div>
            </div>
        </form>
    </div>
    <?php
    $no_of_rows = $wpdb->get_results("SELECT companyID from " . $wpdb->prefix . "viberent_clients_company_info WHERE `companyID` IS NOT NULL");
    if (count($no_of_rows) != 0) {
    ?>
        <script language="javascript">
            document.getElementById("LoginForm").style.display = "none";
            setTimeout(function() {
                document.getElementById("chosen_message").style.display = "block";
            }, 1500);
        </script>
        <?php
        if (isset($viberent_mylayout[0])) {
            $viberent_mylayout = $viberent_mylayout[0]->selected_layout;
        }
        $viberent_mylayout = isset($_POST['chosen_layout']) ? (sanitize_text_field($_POST['chosen_layout'])) : sanitize_text_field($viberent_mylayout);
        echo "<b>Company Name:</b> ";
        echo esc_html($result[0]->companyName);
        ?>
        <form method="post" id="layout_options_form" name="layout_options_form">
            <p id="plz_choos_layout">Please choose the layout for your shop:</p>
            <div id="layout_options">
                <div>
                    <input type="radio" id="radio_item_based" name="chosen_layout" value="item-based" <?php if ($viberent_mylayout == 'item-based') { echo "checked"; } ?>>
                    <label for="radio_item_based">
                        Item-based listing
                        <img id="layout_option_img" src="<?php echo esc_url( plugin_dir_url(__FILE__) . 'assets/images/item.png' ); ?>">

                    </label>
                </div>
                <div>
                    <input type="radio" id="radio_category_based" name="chosen_layout" value="category-based" <?php if ($viberent_mylayout == 'category-based') { echo "checked"; } ?>>
                    <label for="radio_category_based">
                        Category-based listing
                        <img id="layout_option_img" src="<?php echo esc_url( plugin_dir_url(__FILE__) . 'assets/images/category.png' ); ?>">
                    </label>
                </div>
            </div>
            <div class="pagename">
                <?php
                if (empty($viberent_mypagename)) {
                ?>
                <label for="pagename">Please enter the page name for your shop<span class="redText">*</span></label>
                    <input type="text" id="pagename" class="viberent_pagename" name="pagename" required>
                <?php
                }
                ?>
                <button type="submit" id="radio_layout_submit" name="radio_layout_submit">Save</button>
            </div>
        </form>
        <form method="post">
            <button class="btn_logout" name="logout">
                LOGOUT
            </button>
        </form>
        <?php
        $viberent_mylayout = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "viberent_layout");
        if (!empty($viberent_mylayout)) {
            if (!empty($viberent_mypagename)) {
                $page_id_one = viberent_create_page('My cart');
                update_post_meta($page_id_one, '_wp_page_template', 'templates/my_cart.php');
                $page_id_two = viberent_create_page('Place my order');
                update_post_meta($page_id_two, '_wp_page_template', 'templates/place_my_order.php');
                $page_id_three = viberent_create_page('Thank shopping');
                update_post_meta($page_id_three, '_wp_page_template', 'templates/thank_shopping.php');

                if ($viberent_mylayout[0]->selected_layout == "item-based") {
                    $page_id = viberent_create_page($viberent_mypagename[0]->pagename);
                    update_post_meta($page_id, '_wp_page_template', 'templates/item_based.php');
                }
                if ($viberent_mylayout[0]->selected_layout == "category-based") {
                    $page_id = viberent_create_page($viberent_mypagename[0]->pagename);
                    update_post_meta($page_id, '_wp_page_template', 'templates/category_based.php');
                }
            }
        }
    }
    if (isset($_POST["logout"])) {
        $page_id = viberent_get_page_id_by_title($viberent_mypagename[0]->pagename, $post_type = 'page');
        $page_id_one = viberent_get_page_id_by_title('My cart', $post_type = 'page');
        $page_id_two = viberent_get_page_id_by_title('Place my order', $post_type = 'page');
        $page_id_three = viberent_get_page_id_by_title('Thank shopping', $post_type = 'page');

        $wpdb->delete($wpdb->prefix  . 'posts', array('ID' => $page_id));
        $wpdb->delete($wpdb->prefix  . 'posts', array('ID' => $page_id_one));
        $wpdb->delete($wpdb->prefix  . 'posts', array('ID' => $page_id_two));
        $wpdb->delete($wpdb->prefix  . 'posts', array('ID' => $page_id_three));

        $delete = $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix  . "viberent_clients_company_info");
        $deleteapikey = $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix  . "viberent_apikey");
        ?>
        <script>
            window.location.reload();
        </script>
        <?php
    }
    if (isset($_POST["submit"])) {
        $password = sanitize_text_field($_POST["password"]);
        $response = wp_remote_get($viberent_api_url . 'Customer/login-details?ApiSecretKey=' . $password);
        $body     = wp_remote_retrieve_body($response);
        $resp = json_decode($body, 1);
        $mypassword = array(
            "companyID" => $resp["companyID"],
            "apikey" => $password
        );
        if (isset($resp["companyID"])) {
            $num_of_rows = $wpdb->get_results("SELECT companyID from " . $wpdb->prefix . "viberent_clients_company_info WHERE `companyID` IS NOT NULL");
            if (count($num_of_rows) == 0) {
                $wpdb->insert($wpdb->prefix . 'viberent_clients_company_info', $resp);
                $wpdb->insert($wpdb->prefix . 'viberent_apikey', $mypassword);
        ?>
                <script language="javascript">
                    document.getElementById("LoginForm").style.display = "none";
                    setTimeout(function() {
                        document.getElementById("chosen_message").style.display = "block";
                    }, 1500);
                </script>
                <?php
                $result = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "viberent_clients_company_info");
                if (isset($viberent_mylayout[0])) {
                    $viberent_mylayout = $viberent_mylayout[0]->selected_layout;
                }
                $viberent_mylayout = isset($_POST['chosen_layout']) ? sanitize_text_field($_POST['chosen_layout']) : sanitize_text_field($viberent_mylayout);
                echo "<b>Company Name:</b> ";
                echo esc_html($result[0]->companyName);
                ?>
                <form method="post" id="layout_options_form">
                    <p>Please choose the layout for your shop:</p>
                    <div id="layout_options">
                        <div>
                            <input type="radio" id="radio_item_based" name="chosen_layout" value="item-based" <?php if ($viberent_mylayout == 'item-based') { echo "checked"; } ?>>
                            <label for="radio_item_based">
                                Item-based listing
                                <img id="layout_option_img" src="<?php echo esc_url( plugin_dir_url(__FILE__) . 'assets/images/item.png' ); ?>">
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="radio_category_based" name="chosen_layout" value="category-based" <?php if ($viberent_mylayout == 'category-based') { echo "checked";} ?>>
                            <label for="radio_category_based">
                                Category-based listing
                                <img id="layout_option_img" src="<?php echo esc_url( plugin_dir_url(__FILE__) . 'assets/images/category.png' ); ?>">
                            </label>
                        </div>
                    </div>
                    <button type="submit" id="radio_layout_submit" name="radio_layout_submit">Save</button>
                </form>
                <form method="post">
                    <button class="btn_logout" name="logout">
                        LOGOUT
                    </button>
                </form>
        <?php
            }
            if (!empty($viberent_mylayout)) {
                $viberent_mylayout = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "viberent_layout");
                if (!empty($viberent_mypagename)) {
                    $page_id_one = viberent_create_page('My cart');
                    update_post_meta($page_id_one, '_wp_page_template', 'templates/my_cart.php');
                    $page_id_two = viberent_create_page('Place my order');
                    update_post_meta($page_id_two, '_wp_page_template', 'templates/place_my_order.php');
                    $page_id_three = viberent_create_page('Thank shopping');
                    update_post_meta($page_id_three, '_wp_page_template', 'templates/thank_shopping.php');
                    if ($viberent_mylayout[0]->selected_layout == "item-based") {
                        $page_id = viberent_create_page($viberent_mypagename[0]->pagename);
                        update_post_meta($page_id, '_wp_page_template', 'templates/item_based.php');
                    }
                    if ($viberent_mylayout[0]->selected_layout == "category-based") {
                        $page_id = viberent_create_page($viberent_mypagename[0]->pagename);
                        update_post_meta($page_id, '_wp_page_template', 'templates/category_based.php');
                    }
                }
            }
        } else {
            echo '<script>alert("The Api Key provided is incorrect")</script>';
        }
    }
    if (isset($_POST["radio_layout_submit"])) {
        $item_layout = array(
            "companyID" => $result[0]->companyID,
            "useName" => $result[0]->useName,
            "selected_layout" => sanitize_text_field($_POST["chosen_layout"])
        );
        if (empty($viberent_mylayout)) {
            $wpdb->insert($wpdb->prefix . 'viberent_layout', $item_layout);
        } else {
            $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix  . "viberent_layout");
            $wpdb->insert($wpdb->prefix . 'viberent_layout', $item_layout);
        }
        if (isset($_POST["pagename"])) {
            $mypagearr = array(
                "companyID" => $result[0]->companyID,
                "useName" => $result[0]->useName,
                "pagename" => sanitize_text_field($_POST["pagename"])
            );
            $wpdb->insert($wpdb->prefix . 'viberent_pagename', $mypagearr);
        }
        ?>
        <script>
            window.location.reload();
        </script>
        <?php
    }
    $rows = $wpdb->get_results("SELECT companyID from " . $wpdb->prefix . "viberent_layout WHERE `companyID` IS NOT NULL");
    $my_selected_layout = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "viberent_layout WHERE `companyID` IS NOT NULL");
    if (count($rows) != 0) {
        if (!empty($viberent_mypagename)) {
            $slug_name = sanitize_title($viberent_mypagename[0]->pagename);
        ?>
            <div id="chosen_message">
                <a href="<?php echo esc_url( site_url() . "/" . $slug_name ); ?>" type="button" target="_blank" id="change_selected_layout" name="change_selected_layout">Go to the <?php echo esc_html( $my_selected_layout[0]->selected_layout ); ?> layout </a>
            </div>
    <?php
        }
    }
    ?>
</div>
<?php
}
add_action('init', 'viberent_register_styles');
function viberent_register_styles()
{
    wp_register_style('fontawesome', plugins_url('assets/css/all.css', __FILE__));
    wp_register_style('customCss', plugins_url('assets/css/custom.css', __FILE__));
    wp_register_style('Bootstrap', plugins_url('assets/css/bootstrap.css', __FILE__));
    wp_register_style('mycart', plugins_url('assets/css/my_cart.css', __FILE__));
    wp_register_style('PlaceOrder', plugins_url('assets/css/place_order.css', __FILE__));
    wp_register_style('thanks', plugins_url('assets/css/thank.css', __FILE__));
    wp_register_style('viberent_item', plugins_url('assets/css/item.css', __FILE__));
    wp_register_style('viberent_category', plugins_url('assets/css/category.css', __FILE__));

    wp_register_script('viberent_style', includes_url('js/dist/vendor/moment.min.js', __FILE__));
    wp_register_script('jqueryJs', includes_url('js/jquery/jquery.js', __FILE__));
    wp_register_script('bootstrap', plugins_url('assets/js/bootstrap.js', __FILE__));
}
add_action('wp_enqueue_scripts', 'viberent_enqueue_styles');
function viberent_enqueue_styles()
{
    global $wpdb;
    $viberent_mypagename = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "viberent_pagename");
    $page_id = viberent_create_page($viberent_mypagename[0]->pagename);
    $page_id_one = viberent_create_page('My cart');
    $page_id_two = viberent_create_page('Place my order');
    $page_id_three = viberent_create_page('Thank shopping');
    if (is_page($page_id) || is_page($page_id_one) || is_page($page_id_two) || is_page($page_id_three)) {
        wp_enqueue_style('fontawesome');
        wp_enqueue_style('customCss');
        wp_enqueue_style('Bootstrap');
        wp_enqueue_script('viberent_style');
        wp_enqueue_script('jqueryJs');
        wp_enqueue_script('bootstrap');
    }
    if (is_page($page_id)) {
        wp_enqueue_style('viberent_item');
        wp_enqueue_style('viberent_category');
    }
    if (is_page($page_id_one)) {
        wp_enqueue_style('mycart');
    }
    if (is_page($page_id_two)) {
        wp_enqueue_style('PlaceOrder');
    }
    if (is_page($page_id_three)) {
        wp_enqueue_style('thanks');
    }
}
add_action('wp_head', 'viberent_add_script_wp_head');
function viberent_add_script_wp_head()
{
    global $wpdb;
    $viberent_mypagename = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "viberent_pagename");
    $page_id = viberent_create_page($viberent_mypagename[0]->pagename);
    $page_id_one = viberent_create_page('My cart');
    $page_id_two = viberent_create_page('Place my order');
    $page_id_three = viberent_create_page('Thank shopping');
    if (is_page($page_id) || is_page($page_id_one) || is_page($page_id_two) || is_page($page_id_three)) {
       wp_register_script('templateCustom', plugins_url('assets/js/templateCustom.js', __FILE__));
       wp_enqueue_script('templateCustom');
    }
}

add_action('wp_enqueue_scripts', 'viberent_ajax_enqueue_scripts');
function viberent_ajax_enqueue_scripts()
{
    wp_register_script(
        'ajaxHandle',
        plugins_url('assets/js/layout-admin-ajax.js', __FILE__),
        array(),
        false,
        true
    );
    wp_enqueue_script('ajaxHandle');
    wp_localize_script(
        'ajaxHandle',
        'ajax_object',
        array('ajaxurl' => admin_url('admin-ajax.php'))
    );
}


include_once(VIBERENT__PLUGIN_DIR . 'templates/layout_page_ajax.php');

?>