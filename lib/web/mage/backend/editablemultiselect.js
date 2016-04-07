/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    "jquery/editableMultiselect/js/jquery.editable",
    "jquery/editableMultiselect/js/jquery.multiselect"
], function($, alert, confirm){


    /**
     * Editable multiselect wrapper for multiselects
     *
     * This class is defined in global scope ('var' is not needed)
     *
     * @param String settings[add_button_caption] caption of the 'Add New Value' button
     * @param String settings[new_url] URL to which new request has to be submitted
     * @param String settings[save_url] URL to which save request has to be submitted
     * @param String settings[delete_url] URL to which delete request has to be submitted
     * @param String settings[delete_confirm_message] confirmation message that is shown to user during delete operation
     * @param String settings[target_select_id] HTML ID of target select element
     * @param Hash settings[submit_data] extra parameters to send with new/edit/delete requests
     * @param String settings[entity_value_name] name of the request parameter that represents select option text
     * @param String settings[entity_id_name] name of the request parameter that represents select option value
     * @param Boolean settings[is_entry_editable] flag that shows if user can add/edit/remove data
     *
     * @constructor
     */
    EditableMultiselect = function(settings) {

        this.settings = settings || {};
        this.addButtonCaption = this.settings.add_button_caption || 'Add new value';
        this.newUrl = this.settings.new_url;
        this.saveUrl = this.settings.save_url;
        this.deleteUrl = this.settings.delete_url;
        this.deleteConfirmMessage = this.settings.delete_confirm_message;
        this.targetSelectId = this.settings.target_select_id;
        this.submitData = this.settings.submit_data || {};
        this.entityIdName = this.settings.entity_id_name || 'entity_id';
        this.entityValueName = this.settings.entity_value_name || 'entity_value';
        this.isEntityEditable = this.settings.is_entity_editable || false;

        /**
         * Initialize editable multiselect (make it visible in UI)
         */
        EditableMultiselect.prototype.init = function() {
            var self = this;
            var mselectOptions = {
                addText: this.addButtonCaption,
                mselectInputSubmitCallback: function (value, options) {
                    self.createEntity(value, options);
                }
            };

            if (!this.isEntityEditable) {
                // Override default layout of editable multiselect
                mselectOptions.layout = '<section class="block %mselectListClass%">' +
                    '<div class="block-content"><div class="%mselectItemsWrapperClass%">' +
                    '%items%' +
                    '</div></div>' +
                    '<div class="%mselectInputContainerClass%">' +
                    '<input type="text" class="%mselectInputClass%" title="%inputTitle%"/>' +
                    '<span class="%mselectButtonCancelClass%" title="%cancelText%"></span>' +
                    '<span class="%mselectButtonSaveClass%" title="Add"></span>' +
                    '</div>' +
                    '</section>';
            }

            $('#' + this.targetSelectId).multiselect(mselectOptions);

            // Make multiselect editable if needed
            if (this.isEntityEditable) {
                this.makeMultiselectEditable();

                // Root element of HTML markup that represents select element in UI
                var mselectList = $('#' + this.targetSelectId).next();
                this.attachEventsToControls(mselectList);
            }
        };

        /**
         * Attach required event handlers to control elements of editable multiselect
         *
         * @param mselectList
         */
        EditableMultiselect.prototype.attachEventsToControls = function (mselectList)
        {
            mselectList.on("click.mselect-delete", '.mselect-delete', {container: this}, function(event) {
                // Pass the clicked button to container
                event.data.container.deleteEntity({delete_button: this});
            });

            mselectList.on('click.mselect-checked', '.mselect-list-item input', {container: this}, function (event) {
                var el = $(this),
                    checkedClassName = 'mselect-checked';
                el[el.is(':checked') ? 'addClass' : 'removeClass'](checkedClassName);
                event.data.container.makeMultiselectEditable();
            });

            mselectList.on('click.mselect-edit', '.mselect-edit', {container: this}, function (event) {
                event.data.container.makeMultiselectEditable();
                $(this).parent().find('label span').trigger("dblclick");
            });
        };

        /**
         * Make multiselect editable
         */
        EditableMultiselect.prototype.makeMultiselectEditable = function() {
            var entityIdName = this.entityIdName,
                entityValueName = this.entityValueName,
                selectList = $('#' + this.targetSelectId).next();
            selectList.find('.mselect-list-item:not(.mselect-list-item-not-editable) label span').editable(this.saveUrl,
            {
                type: 'text',
                submit: '<button class="mselect-save" title="Save" type="submit" />',
                cancel: '<span class="mselect-cancel" title="Cancel"></span>',
                event: 'dblclick',
                placeholder: '',
                isChecked: function(settings) {
                    var that = $(this);
                    if (!that.closest('.mselect-list-item').hasClass('mselect-disabled')) {
                        var checked = that.parent().find('[type=checkbox]').prop('disabled');
                        that.parent().find('[type=checkbox]').prop({
                            disabled: !checked
                        });
                    }
                },
                data: function(value, settings) {
                    settings.isChecked.apply(this, [settings]);
                    if (typeof value === 'string') {
                        var retval = value.unescapeHTML();
                        return (retval);
                    }
                    return value;
                },
                submitdata: this.submitData,
                onblur: 'cancel',
                name: entityValueName,
                ajaxoptions: {
                    dataType: 'json'
                },

                onsubmit: function (settings, original) {
                    var select = $(original).closest('.mselect-list').prev(),
                        current = $(original).closest('.mselect-list-item').index(),
                        entityId = select.find('option').eq(current).val();
                    // Add entity ID to AJAX request params
                    var entityInfo = {};
                    entityInfo[entityIdName] = entityId;
                    settings.submitdata = $.extend(settings.submitdata || {}, entityInfo);
                },

                callback: function (result, settings) {
                    settings.isChecked.apply(this, [settings]);
                    var select = $(this).closest('.mselect-list').prev(),
                        current = $(this).closest('.mselect-list-item').index();
                    if (result.success) {
                        if (typeof result[entityValueName] === 'string') {
                            select.find('option').eq(current).val(result[entityIdName]).text(result[entityValueName]);
                            $(this).html(result[entityValueName].escapeHTML());
                        }
                    } else {
                        alert({
                            content: result.error_message
                        });
                    }
                }
            });
        };

        /**
         * Callback function that is called when admin adds new value to select
         *
         * @param value
         * @param options - list of settings of multiselect
         */
        EditableMultiselect.prototype.createEntity = function(value, options) {
            if (!value) {
                return;
            }
            var select = $('#' + this.targetSelectId),
                entityIdName = this.entityIdName,
                entityValueName = this.entityValueName,
                entityInfo = {};
            entityInfo[entityIdName] = null;
            entityInfo[entityValueName] = value;

            var postData = $.extend(entityInfo, this.submitData);

            var ajaxOptions = {
                type: 'POST',
                data: postData,
                dataType: 'json',
                url: this.newUrl,
                success: function(result, status) {
                    if (result.success) {
                        var resultEntityValueName="";
                        if (typeof result[entityValueName] === 'string') {
                            resultEntityValueName=result[entityValueName].escapeHTML();
                        } else {
                            resultEntityValueName=result[entityValueName];
                        }
                        // Add item to initial select element
                        select.append('<option value="' + result[entityIdName] + '" selected="selected">' +
                        resultEntityValueName + '</option>');
                        // Add editable multiselect item
                        var mselectItemHtml = $(options.item.replace(/%value%|%label%/gi, resultEntityValueName)
                                .replace(/%mselectDisabledClass%|%iseditable%|%isremovable%/gi, '')
                                .replace(/%mselectListItemClass%/gi, options.mselectListItemClass))
                                .find('[type=checkbox]')
                                .attr('checked', true)
                                .addClass(options.mselectCheckedClass)
                                .end(),
                            sectionBlock = select.nextAll('section.block:first'),
                            itemsWrapper = sectionBlock.find('.' + options.mselectItemsWrapperClass + '');
                        if( itemsWrapper.children('.' + options.mselectListItemClass + '').length ) {
                            itemsWrapper.children('.' + options.mselectListItemClass + ':last').after(mselectItemHtml);
                        } else {
                            itemsWrapper.prepend(mselectItemHtml);
                        }
                        // Trigger blur event on input field, that is used to add new value, to hide it
                        var inputSelector = '.' + options.mselectInputContainerClass + ' [type=text].' +
                            options.mselectInputClass + '';
                        sectionBlock.find(inputSelector).trigger('blur');
                    } else {
                        alert({
                            content: result.error_message
                        });
                    }
                }
            };
            $.ajax(ajaxOptions);
        };

        /**
         * Callback function that is called when user tries to delete value from select
         *
         * @param options
         */
        EditableMultiselect.prototype.deleteEntity = function(options) {
            var self = this;

            if (options.delete_button) {
                confirm({
                    content: this.deleteConfirmMessage,
                    actions: {
                        confirm: function() {
                            // Button that has been clicked
                            var deleteButton = $(options.delete_button),
                                index = deleteButton.parent().index(),
                                select = deleteButton.closest('.mselect-list').prev(),
                                entityId = select.find('option').eq(index).val(),
                                entityInfo = {};
                            entityInfo[self.entityIdName] = entityId;
                            var postData = $.extend(entityInfo, self.submitData);

                            var ajaxOptions = {
                                type: 'POST',
                                data: postData,
                                dataType: 'json',
                                url: self.deleteUrl,
                                success: function(result, status) {
                                    if (result.success) {
                                        deleteButton.parent().remove();
                                        select.find('option').eq(index).remove();
                                    } else {
                                        alert({
                                            content: result.error_message
                                        });
                                    }
                                }
                            };
                            $.ajax(ajaxOptions);
                        }
                    }
                });
            }
        };
    };
});