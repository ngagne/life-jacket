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
            $('a[href="#panel_' + document.location.hash.replace('#', '') + '"]').click();
            tabs.selectTab($('#panel_' + document.location.hash.replace('#', '')));
        } else {
            $('.tabs-title:first-child a').click();
        }
    }

    // init YouTube input types
    var $inputYoutube = $('.input-youtube');
    if ($inputYoutube.length) {
        $inputYoutube.on('change.atc', function(){
            var regExp = /^.*(?:(?:youtu.be\/)|(?:v\/)|(?:\/u\/\w\/)|(?:embed\/)|(?:watch\?))\??v?=?([^#\&\?"]*).*/;
            var match = $(this).val().match(regExp);

            if (match) {
                $(this).val(match[1]);
            }
        });
    }

    // init repeater field groups
    var $fieldRepeaters = $('.field-repeater');
    if ($fieldRepeaters.length) {
        $fieldRepeaters.each(function() {
            var resetRepeaterRemoveButtons = function() {
                // remove existing "remove" buttons
                $('.field-repeater-remove', $group).remove();

                // add new "remove" buttons to all except the first items
                var $btnRemove = $('<button/>').addClass('field-repeater-remove alert button').html('&times; Remove').click(btnRemoveClick);
                $('>', $group).not($firstItem).append($btnRemove);
            };

            var btnRemoveClick = function(e){
                e.preventDefault();
                $(this).parent().remove();
            };

            var btnAddClick = function(e){
                e.preventDefault();

                var field = $firstItem.clone();
                field.find('input, select, textarea').val('');
                field.appendTo($group);

                resetRepeaterRemoveButtons();
            };

            var $group = $('.field-group-container', this),
                $firstItem = $('> :first-child', $group),
                $btnAdd = $('.field-repeater-add', this);

            $btnAdd.click(btnAddClick);
            resetRepeaterRemoveButtons();
        });
    }
});