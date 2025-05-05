jQuery(document).ready(function($) {
    $('#clec-email-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            email: $('#clec-email').val(),
            first_name: $('#clec-first-name').length ? $('#clec-first-name').val() : '',
            last_name: $('#clec-last-name').length ? $('#clec-last-name').val() : '',
            phone: $('#clec-phone').length ? $('#clec-phone').val() : ''
        };
        
        var $form = $(this);
        var $message = $('#clec-message');
        var $wrapper = $form.closest('.content-locker-wrapper');
        
        $.ajax({
            url: clec_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'clec_submit_email',
                email: formData.email,
                first_name: formData.first_name,
                last_name: formData.last_name,
                phone: formData.phone,
                nonce: clec_ajax.nonce
            },
            beforeSend: function() {
                $form.find('button').prop('disabled', true).text('Processing...');
            },
            success: function(response) {
                if (response.success) {
                    $message.removeClass('error').addClass('success').text(response.data);
                    $wrapper.find('.locked-content').slideDown();
                    $form.slideUp();
                } else {
                    $message.removeClass('success').addClass('error').text(response.data);
                }
            },
            error: function() {
                $message.removeClass('success').addClass('error').text('An error occurred. Please try again.');
            },
            complete: function() {
                $form.find('button').prop('disabled', false).text('Unlock Content');
            }
        });
    });
});