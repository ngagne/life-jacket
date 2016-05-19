$(document).foundation();

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
        $('.tabs-content, #adminTabs').addClass('loaded');
        var tabs = new Foundation.Tabs($('#adminTabs'));

        // keep track of selected tab
        $('[data-tabs]').on('change.zf.tabs', function (e, tab) {
            document.location.hash = $(tab).find('>a').attr('href').replace('panel_', '');
        });

        if (document.location.hash != '') {
            var anchor = $('a[href="#panel_' + document.location.hash.replace('#', '') + '"]');
            if (anchor.length){
                anchor.click();
                tabs.selectTab($('#panel_' + document.location.hash.replace('#', '')));
            } else {
                $('.tabs-title:first-child a').click();
            }

        } else {
            $('.tabs-title:first-child a').click();
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