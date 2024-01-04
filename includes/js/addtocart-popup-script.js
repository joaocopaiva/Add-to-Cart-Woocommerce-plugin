jQuery(document).ready(function($) {

    // Listen for click events on elements with the class 'popup-close'
    $(document).on('click', '.popup-close', function() {
        // Close the notification
        $(this).closest('.addtocart-popup').fadeOut(300);
    });

    // Listen for the click event on the "Add to Cart" button
    $('.add_to_cart_button').on('click', function(e) {
        e.preventDefault();

        // Check if there is an open notification
        if ($('.addtocart-popup').length > 0) {
            // Close the existing notification
            $('.addtocart-popup').fadeOut(300, function() {});
        }
        // When there's no open notification, proceed to open a new one
        openNewNotification(this);
    });

    function openNewNotification(clickedElement) {
        // Get the product data
        var productContainer = $(clickedElement).closest('.product');
        var productImage = productContainer.find('img').attr('src');
        var productName = productContainer.find('.wp-block-post-title').text();
        var productPrice = productContainer.find('.woocommerce-Price-amount.amount:last').text();
        var viewCartText = productContainer.find('.added_to_cart.wc_forward').text();

        // Make an AJAX request to notify the server
        $.ajax({
            url: addToCartPopupSettings.ajaxurl,
            type: 'POST',
            data: {
                action: 'add_to_cart_notification',
                product_image: productImage,
                product_name: productName,
                product_price: productPrice,
                view_cart_text: viewCartText,
            },
            success: function(response) {
                // Append the notification content to the body and slide it in
                $('body').append(response);
                $('.addtocart-popup').css('right', '10px');

                // Close the popup after a specified number of seconds
                setTimeout(function(){
                    $('.addtocart-popup').fadeOut(300);
                }, addToCartPopupSettings.closeAfterSeconds * 1000); // Adjust the time as needed
            },
            error: function(error) {
                console.error('Error:', error);
            }
        });
    };
});
