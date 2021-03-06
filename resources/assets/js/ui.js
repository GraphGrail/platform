$(document).ready(function () {
    $('div.component-field-repeatable .add-repeatable').on('click', function () {
        let fields = $(this).parents('.component-field-repeatable');
        let clone = fields.clone(true);
        clone.find('.remove-repeatable').removeClass('m--hide');

        fields.parent().append(clone);
        $(this).addClass('m--hide');
        fields.find('.remove-repeatable').removeClass('m--hide');
    });
    $('div.component-field-repeatable .remove-repeatable').on('click', function () {
        let fields = $(this).parents('.component-field-repeatable');
        let parent = fields.parent();
        fields.remove();

        let last = parent.find('.component-field-repeatable').last();

        parent.find('.add-repeatable').addClass('m--hide');
        last.find('.add-repeatable').removeClass('m--hide');

        fields = parent.find('.component-field-repeatable');
        fields.find('.remove-repeatable').removeClass('m--hide');
        if (fields.length === 1) {
            parent.find('.remove-repeatable').addClass('m--hide')
        }
    });
    $(".ui-sortable").sortable({
        handle: 'div.m-portlet__head:not(.m-portlet__head--disabled)',
        connectWith: ".ui-sortable",
        cancel: ".sortable-item--disabled",
        placeholder: "ui-sortable-drop-area",
        forcePlaceholderSize: true,
        start: function (a,ui) {
            let parent = ui.item.closest('.ui-sortable')[0];
            let another = $('.ui-sortable').not('.' + (parent.className.split(' ')[1]))
            another.addClass('prepare-drop');
        },
        stop: function (a, ui) {
            $('.right-sortable.prepare-drop').removeClass('prepare-drop');
        },
        receive: function( event, ui ) {
            let head = ui.item.children('.m-portlet__head').next();
            if (ui.item.parent().hasClass('left-sortable')) {
                head.slideUp('fast');
                ui.item.children('.m-portlet__head').removeClass('toggled');
            } else {
                if ((head).css('display') === 'none') {
                    head.slideToggle('fast');
                    ui.item.children('.m-portlet__head').toggleClass('toggled');
                }
            }
        }
    }).disableSelection();

    $('.ui-sortable .m-portlet__head').click(function() {
        $(this).next().slideToggle('fast');
        $(this).toggleClass('toggled');
        return false;
    }).next().hide();


    $('.stop-words.select-language').change(function () {
        let lang = $(this).val();
        let set = $('.stop-words.stop-sets.lang-' + lang).val();

        let textarea = $('.stop-words.stop-words-remover');
        if (!set) {
            textarea.val('');
            textarea.removeAttr('disabled');
            return;
        }

        textarea.val(set);
        textarea.attr('disabled', 'disabled');
    });

    $('#dataset').change(function () {
        let field = $('.dataset-classes');
        let countField = field.find('.dataset-classes-items');
        let datasetId = $(this).val() - 0;

        field.addClass('m--hide');
        countField.html('');

        if (!datasetId) {
            return;
        }

        axios.get('/labels/json/' + datasetId)
            .then(function (response) {
                if (!response.data) {
                    return;
                }
                field.removeClass('m--hide');
                countField.html(response.data.length);
            })
            .catch(function (error) {
                console.log(error);
            });
    });

});
