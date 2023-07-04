define([
    "jquery"
], function($){

    /*
     * jQuery.multiselect plugin
     *
     * Form control: allow select several values from list and add new value(s) to list
     *
     * Licensed under the BSD License:
     *   http://www.opensource.org/licenses/bsd-license
     *
     * Version: 0.9.0
     *
     * @author Dmitry (dio) Levashov, dio@std42.ru
     * @example
     *  html: <select name="my-select" multiple="on"><option .... </select>
     * js   : $('select[name="my-select"]').multiselect()
     *  or
     * var opts = { ... };
     * $('select[name="my-select"]').multiselect(opts);
     */
    $.fn.multiselect = function(opts) {
        var o = $.extend({
            mselectHiddenClass: 'mselect-hidden',
            mselectItemNotEditableClass: 'mselect-list-item-not-editable',
            mselectItemNotRemovableClass: 'mselect-list-item-not-removable',
            mselectListClass: 'mselect-list',
            mselectItemsWrapperClass: 'mselect-items-wrapper',
            mselectButtonAddClass: 'mselect-button-add',
            mselectInputContainerClass: 'mselect-input-container',
            mselectInputClass: 'mselect-input',
            mselectButtonCancelClass: 'mselect-cancel',
            mselectButtonSaveClass: 'mselect-save',
            mselectListItemClass: 'mselect-list-item',
            mselectItemsWrapperOverflowClass: 'mselect-fixed',
            mselectDisabledClass: 'mselect-disabled',
            mselectCheckedClass: 'mselect-checked',
            layout: '<section class="block %mselectListClass%">'
                +'<div class="block-content"><div class="%mselectItemsWrapperClass%">'
                +'%items%'
                +'</div></div>'
                +'<footer class="block-footer">'
                +'<span class="action-add %mselectButtonAddClass%">%addText%</span>'
                +'</footer>'
                +'<div class="%mselectInputContainerClass%">'
                +'<input type="text" class="%mselectInputClass%" title="%inputTitle%"/>'
                +'<span class="%mselectButtonCancelClass%" title="%cancelText%"></span>'
                +'<span class="%mselectButtonSaveClass%" title="Add"></span>'
                +'</div>'
                +'</section>',
            item : '<div  class="%mselectListItemClass% %mselectDisabledClass% %iseditable% %isremovable%"><label><input type="checkbox" class="%mselectCheckedClass%" value="%value%" %checked% %disabled% /><span>%label%</span></label>' +
                '<span class="mselect-edit" title="Edit">Edit</span>' +
                '<span class="mselect-delete" title="Delete">Delete</span> ' +
                '</div>',
            addText: 'Add new value',
            cancelText: 'Cancel',
            inputTitle: 'Enter new option',
            size: 5,
            keyCodes: {
                Enter: 13,
                Esc: 27
            },
            toggleAddButton: true,
            // New option for callback
            mselectInputSubmitCallback: null,
            parse : function(v) { return v.split(/\s*,\s*/); }
        }, opts||{});

        return this.filter('select[multiple]:not(.' + o.mselectHiddenClass + ')').each(function() {
            var select = $(this).addClass(o.mselectHiddenClass).hide(),
                size = select.attr('size') > 0 ? select.attr('size') : o.size,
                items = (function() {
                    var str = '';

                    select.children('option').each(function(i, option) {
                        option = $(option);

                        str += o.item
                            .replace(/%value%/gi,  option.val())
                            .replace(/%checked%/gi, option.prop('selected') ? 'checked' : '')
                            .replace(/%mselectCheckedClass%/gi, option.prop('selected') ? ''+o.mselectCheckedClass+'' : '')
                            .replace(/%disabled%/gi, option.prop('disabled') ? 'disabled' : '')
                            .replace(/%mselectDisabledClass%/gi, option.prop('disabled') ? ''+o.mselectDisabledClass+'' : '')
                            .replace(/%mselectListItemClass%/gi, o.mselectListItemClass)
                            .replace(/%iseditable%/gi, option.attr('data-is-editable') ? ''+o.mselectItemNotEditableClass+'' : '')
                            .replace(/%isremovable%/i, option.attr('data-is-removable') ? ''+o.mselectItemNotRemovableClass+'' : '')
                            .replace(/%label%/gi,  option.html());
                    });

                    return str;
                })(),
                html = o.layout
                    .replace(/%items%/gi, items)
                    .replace(/%mselectListClass%/gi, o.mselectListClass)
                    .replace(/%mselectButtonAddClass%/gi, o.mselectButtonAddClass)
                    .replace(/%mselectButtonSaveClass%/gi, o.mselectButtonSaveClass)
                    .replace(/%mselectButtonCancelClass%/gi, o.mselectButtonCancelClass)
                    .replace(/%mselectItemsWrapperClass%/gi, o.mselectItemsWrapperClass)
                    .replace(/%mselectInputContainerClass%/gi, o.mselectInputContainerClass)
                    .replace(/%mselectInputClass%/gi, o.mselectInputClass)
                    .replace(/%addText%/gi, o.addText)
                    .replace(/%cancelText%/gi, o.cancelText)
                    .replace(/%inputTitle%/gi, o.inputTitle),
                widget = $(html)
                    .insertAfter(this)
                    .on('change.mselectCheck', '[type=checkbox]', function() {
                        var checkbox = $(this),
                            index = checkbox.closest('.' + o.mselectListItemClass + '').index();

                        select.find('option').eq(index).prop('selected', !!checkbox.prop('checked'));
                    }),
                list = widget.find('.' + o.mselectItemsWrapperClass + ''),
                buttonAdd = widget.find('.' + o.mselectButtonAddClass + '')
                    .on('click.mselectAdd', function(e) {
                        e.preventDefault();
                        o.toggleAddButton && buttonAdd.hide();
                        container.show();
                        input.trigger('focus');
                        if (input.parents(o.mselectListClass).length) {
                            list.scrollTop(list.height());
                        }
                    }),
                container = widget.find('.' + o.mselectInputContainerClass + ''),
                input = container.find('[type=text].' + o.mselectInputClass + '')
                    .on('blur.mselectReset', function() {
                        reset();
                    })
                    .on('keydown.mselectAddNewOption', function(e) {
                        var c = e.keyCode;

                        if (c == o.keyCodes.Enter || c == o.keyCodes.Esc) {
                            e.preventDefault();
                            c == o.keyCodes.Enter ? append(input.val())  : reset();
                        }
                    }),
                buttonSave = container.find('.' + o.mselectButtonSaveClass + '')
                    .on('mousedown.mselectSave', function(e) {
                        append(input.val());
                    }),
                buttonCancel = container.find('.' + o.mselectButtonCancelClass + '')
                    .on('mousedown.mdelectCancel', function(e) {
                        input.val('');
                    }),
                append = function(v) {
                    // Add ability to define custom handler for adding new values
                    if ($.isFunction(o.mselectInputSubmitCallback)) {
                        o.mselectInputSubmitCallback(v, o);
                        return;
                    }
                    // end of callback implementation
                    $.each(typeof(o.parse) == 'function' ? o.parse(v) : [$.trim(v)], function(i, v) {
                        var item;

                        if (v && !select.children('[value="' + v + '"]').length) {
                            item = $(o.item.replace(/%value%|%label%/gi, v)
                                .replace(/%mselectDisabledClass%|%iseditable%|%isremovable%/gi,'')
                                .replace(/%mselectListItemClass%/gi,o.mselectListItemClass))
                                .find('[type=checkbox]')
                                .addClass(o.mselectCheckedClass)
                                .prop('checked', true)
                                .end();

                            list.children('.' + o.mselectListItemClass + '').length
                                ? list.children('.' + o.mselectListItemClas ).last().after(item)
                                : list.prepend(item);

                            select.append('<option value="' + v + '" selected="selected">' + v + '</option>');
                        }
                    });

                    reset();
                    list.scrollTop(list.height());
                },
                reset = function() {
                    var ch = select.children();

                    input.val('');
                    container.hide();
                    buttonAdd.show();
                    list[list.children().length ? 'show' : 'hide']();

                    if (ch.length >= size && !list.hasClass(o.mselectItemsWrapperOverflowClass)) {
                        list.height(list.children('.' + o.mselectListItemClass)
                            .first()
                            .outerHeight(true) * size)
                            .addClass(o.mselectItemsWrapperOverflowClass);
                    }
                };
            reset();
        }).end();
    };
});
