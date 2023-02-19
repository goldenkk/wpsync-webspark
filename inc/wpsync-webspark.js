jQuery( function ( $ ) {
    $('.startParse').on('click', function (e) {
        e.preventDefault();
        var data = {
            action: 'start_parse_process',
            startParse: true
        };

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: data,
            success: function (response) {
                if (response) {
                    $('.start-parse-wrap').html('<br><p>In process...</p>');
                }
            }
        });
    });
});