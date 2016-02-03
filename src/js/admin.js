jQuery(function($){
    // init WYSIWYG editors
    $('.input-wysiwyg').tinymce({
        plugins: 'link',
        toolbar: 'undo redo | bold italic | cut copy paste | link unlink ',
        menubar: false,
        skin: 'light',
        toolbar_items_size: 'small'
    });

    // init tabs
    if ($('#adminTabs').length) {
        // init foundation tabs
        $('[data-tabs]').on('change.zf.tabs', function (e, tab) {
            document.location.hash = $(tab).find('>a').attr('href');
        });
        var tabs = new Foundation.Tabs($('#adminTabs'), {
            autoFocus: true
        });
        if (document.location.hash != '') {
            tabs.selectTab($(document.location.hash));
        }
    }

    // init YouTube input types
    if ($('.input-youtube').length) {
        $('.input-youtube').on('change.atc', function(){
            var regExp = /^.*(?:(?:youtu.be\/)|(?:v\/)|(?:\/u\/\w\/)|(?:embed\/)|(?:watch\?))\??v?=?([^#\&\?"]*).*/;
            var match = $(this).val().match(regExp);

            if (match) {
                $(this).val(match[1]);
            }
        });
    }
});