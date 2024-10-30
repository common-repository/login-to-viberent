jQuery(document).ready(function ($) {
    $('form.ajax-layoutbase-form').submit(function (e) {
        e.preventDefault();
        var ajaxItembaseForm = $(this),
            formData = {
                requireQuantity: $(this).find(".product-quantity").val(),
                rentalratesprice: $(this).find(".rentalratesvalue").val(),
                productimage: $(this).find(".productimage").val(),
                productAvailable: $(this).find(".productAvailable").val(),
                periodUnits: $(this).find(".periodUnits").val(),
                itemCode: $(this).find(".itemCode").val(),
                itemGUID: $(this).find(".itemGUID").val(),
                hireTypeID: $(this).find(".hireTypeID").val(),
                locationID: $(this).find(".locationID").val(),
                categoryName: $(this).find(".categoryName").val(),
                itemName: $(this).find(".itemName").val(),
                rentalratesName: $(this).find(".rentalratesName").val(),
                periodTypeId: $(this).find(".periodTypeId").val(),
                startDate: $(this).find(".startDate").val(),
                endDate: $(this).find(".endDate").val(),
                taxRate: parseFloat($(this).find(".taxrate").val()),
                sessionID: $(this).find(".sessionID").val(),
            };
        ajaxItembaseForm.find(".loading").show();
        ajaxItembaseForm.find(".add_to_cart_icon").hide();
        ajaxItembaseForm.find(".btnAddAction").hide();
        $.ajax({
            data: {
                action: 'viberent_layoutBased_form',
                formData: formData,
                actionRequest: "addToCart",
            },
            type: 'post',
            url: ajax_object.ajaxurl,
            success: function (response) {
                console.log(response);
                if (response) {
                    ajaxItembaseForm.find(".loading").hide();
                    ajaxItembaseForm.find(".add_to_cart_icon").show();
                    var result = $.parseJSON(response);
                    ajaxItembaseForm.find('.product-quantity-message').empty().append('<div class="itemQuantityAvailablediv"><b>item(s) added to cart</b></div>');
                    
                    if (result['cart_count'] > 0) {
                        $(".btn_mycart").find("span.has-badge").attr('data-count', result['cart_count']);
                    } else {
                        $(".btn_mycart").find("span.has-badge").attr('data-count', '0');
                    }
                }
            },
            error: function (data) {
            }
        })
    });
});