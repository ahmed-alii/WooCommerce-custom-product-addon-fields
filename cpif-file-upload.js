jQuery(document).ready(function($) {
    $('input[type="file"]').on('change', function() {
        var fileInput = $(this);
        var file = fileInput[0].files[0];
        var formData = new FormData();
        formData.append('file', file);
        formData.append('action', 'cpif_upload_file');
        formData.append('nonce', cpif_nonce.nonce);

        $.ajax({
            url: cpif_nonce.ajax_url,
            type: 'POST',
            processData: false,
            contentType: false,
            data: formData,
            success: function(response) {
                if (response.success) {
                    fileInput.after('<input type="hidden" name="' + fileInput.attr('data-name') + '_url" value="' + response.data.url + '">');
                } else {
                    alert(response.data.error);
                }
            }
        });
    });
});
