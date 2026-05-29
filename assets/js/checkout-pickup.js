jQuery(document).ready(function($) {
    
    var labelAddress = wcps_labels?.address_label || '📍 Address';
    var labelHours = wcps_labels?.hours_label || '🕒 Business Hours';
    var labelPhone = wcps_labels?.phone_label || '📞 Phone';
    var labelEmail = wcps_labels?.email_label || '📧 Email';
    
    function displayStoreDetails() {
        var selectedOption = $('#pickup_store_id option:selected');
        var storeId = $('#pickup_store_id').val();
        
        if (storeId && storeId !== '') {
            var address = selectedOption.data('address');
            var hours = selectedOption.data('hours');
            var phone = selectedOption.data('phone');
            var email = selectedOption.data('email');
            
            var html = '';
            
            if (address && address !== '') {
                html += '<p><strong>' + labelAddress + ':</strong><br>' + address.replace(/\n/g, '<br>') + '</p>';
            }
            
            if (hours && hours !== '') {
                html += '<p><strong>' + labelHours + ':</strong><br>' + hours.replace(/\n/g, '<br>') + '</p>';
            }
            
            if (phone && phone !== '') {
                html += '<p><strong>' + labelPhone + ':</strong> ' + phone + '</p>';
            }
            
            if (email && email !== '') {
                html += '<p><strong>' + labelEmail + ':</strong> ' + email + '</p>';
            }
            
            $('#wcps-store-details').html(html).slideDown(200);
        } else {
            $('#wcps-store-details').slideUp(200);
        }
    }
    
    // Funcție pentru a muta selectorul DUPĂ #payment
    function moveSelectorAfterPayment() {
        var $wrapper = $('.wcps-store-wrapper');
        var $payment = $('#payment');
        
        if ($wrapper.length && $payment.length) {
            // Mută wrapper-ul după #payment
            $wrapper.insertAfter($payment);
            console.log('Moved store selector after #payment');
        }
    }
    
    $(document.body).on('updated_checkout', function() {
        setTimeout(function() {
            moveSelectorAfterPayment();
            displayStoreDetails();
        }, 100);
    });
    
    $(document.body).on('change', '#pickup_store_id', function() {
        displayStoreDetails();
    });
    
    // Inițializare
    setTimeout(function() {
        moveSelectorAfterPayment();
        displayStoreDetails();
    }, 300);
});