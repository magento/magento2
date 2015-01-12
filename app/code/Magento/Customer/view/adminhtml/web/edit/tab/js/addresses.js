/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "jquery/ui",
    "jquery/template",
    "mage/backend/tabs"
], function($){

    $.widget('mage.addressTabs', $.mage.tabs, {
        options: {
            itemCount: 0,
            baseItemId: 'new_item',
            templatePrefix: '_templatePrefix_',
            regionsUrl: null,
            defaultCountries: [],
            optionalZipCountries: [],
            requiredStateForCountries: [],
            deleteConfirmPrompt: '',
            formTemplateSelector: '[data-template="address-form"]',
            tabTemplateSelector: '[data-template="address-tab"]',
            tabAddressTemplateSelector: '[data-template="tab-address-content"]',
            formsSelector: '[data-container="address-forms"]',
            addAddressSelector: '[data-container="add-address"]',
            formFirstNameSelector: ':input[data-ui-id="adminhtml-edit-tab-addresses-fieldset-element-text-address-template-firstname"]',
            accountFirstNameSelector: ':input[data-ui-id="adminhtml-edit-tab-account-fieldset-element-text-account-firstname"]',
            formLastNameSelector: ':input[data-ui-id="adminhtml-edit-tab-addresses-fieldset-element-text-address-template-lastname"]',
            accountLastNameSelector: ':input[data-ui-id="adminhtml-edit-tab-account-fieldset-element-text-account-lastname"]',
            accountWebsiteIdSelector: ':input[data-ui-id="store-switcher-form-renderer-fieldset-element-select-account-website-id"]',
            formCountrySelector: 'customer-edit-tab-addresses-fieldset-element-form-field-country-id',
            addAddressButtonSelector: ':button[data-ui-id="adminhtml-edit-tab-addresses-add-address-button"]'
        },

        /**
         * This method adds a new address - tab and form to the widget.
         */
        _addNewAddress: function () {
            this.options.itemCount++;

            // prevent duplication of ids
            while (this.element.find("div[data-item=" + this.options.itemCount + "]").length) {
                this.options.itemCount++;
            }

            var formName = this.options.baseItemId + this.options.itemCount;

            // add the new address form
            var formData = {
                formName: formName,
                itemCount: this.options.itemCount
            };
            var formTemplate = this.element.find(this.options.formTemplateSelector).tmpl(formData).html();
            this.element.find(this.options.formsSelector).append(this._prepareTemplate(formTemplate));

            var newForm = $('#form_' + formName);

            // Replace template attributes
            var itemData = {'itemId': this.options.itemCount};
            var itemTemplate = this.element.find(this.options.tabTemplateSelector).tmpl(itemData).html();

            // add the new address to the tabs list before the add new action list
            this.element.find(this.options.addAddressSelector).before(itemTemplate);

            // refresh the widget to pick up the newly added tab.
            this.refresh();

            // activate the newly added tab
            this.option('active', -1);

            this.element.trigger('contentUpdated', $(formName));

            // pre-fill form with account firstname, lastname, and country
            var firstname = newForm.find(this.options.formFirstNameSelector);
            firstname.val($(this.options.accountFirstNameSelector).val());
            newForm.find(this.options.formLastNameSelector).val($(this.options.accountLastNameSelector).val());

            var accountWebsiteId = $(this.options.accountWebsiteIdSelector).val();
            if (accountWebsiteId !== '' && undefined !== this.options.defaultCountries[accountWebsiteId]) {
                newForm.find(this.options.formCountrySelector).val(this.options.defaultCountries[accountWebsiteId]);
            }

            // .val does not trigger change event, so manually trigger. (Triggering change of any field will handle update of all fields.)
            firstname.trigger("change");

            this._bindCountryRegionRelation(newForm);
        },

        /**
         * This method is used to bind events associated with this widget.
         */
        _bind: function () {
            this._on(this.element.find(this.options.addAddressButtonSelector),{'click': '_addNewAddress'});
            this._on({'formchange': '_updateAddress', 'dataItemDelete': '_deleteItemPrompt'});
            this.element.find('.countries').addressCountry({
                regionsUrl: this.options.regionsUrl,
                optionalZipCountries: this.options.optionalZipCountries,
                requiredStateForCountries: this.options.requiredStateForCountries
            });
        },

        /**
         * Create, Initialize this widget.
         */
        _create: function () {
            this._super();
            this._bind();
        },

        /**
         * This method deletes the item in the list.
         * @private
         */
        _deleteItem: function(dataItem) {
            // remove the elements from the page
            this.element.find('[data-item="' + dataItem + '"]').remove();

            // refresh the widget to pick up the removed tab
            this.refresh();
        },

        /**
         * This method prompts the user to confirm the deletion of the item in the list.
         * @private
         */
        _deleteItemPrompt: function(event, data) {
            if(window.confirm(this.options.deleteConfirmPrompt)){
                this._deleteItem(data.item);
            }
        },

        /**
         * Initialize form template variables for the new address item.
         * @param {Element} template Address form html 'template'.
         * @private
         */
        _prepareTemplate: function (template) {
            var re = new RegExp(this.options.templatePrefix, "g");
            return template.replace(re, '_item' + this.options.itemCount);
        },

        /**
         * This method is used to grab the data from the form and display it nicely.
         * @param {Element} container Address form container.
         * @private
         */
        _syncFormData: function (container) {
            if (container) {
                var data = {};

                $(container).find(':input').each(function (index, inputField) {
                    var id = inputField.id;
                    if (id) {
                        id = id.replace(/^(_item)?[0-9]+/, '');
                        id = id.replace(/^(id)?[0-9]+/, '');
                        var value = inputField.getValue();
                        var tagName = inputField.tagName.toLowerCase();
                        if (tagName == 'select') {
                            if (inputField.multiple) {
                                var values = $([]);
                                var l = inputField.options.length;
                                for (j = 0; j < l; j++) {
                                    var o = inputField.options[j];
                                    if (o.selected === true) {
                                        values[values.length] = o.text.escapeHTML();
                                    }
                                }
                                data[id] = values.join(', ');
                            } else {
                                var option = inputField.options[inputField.selectedIndex],
                                    text = option.value == '0' || option.value === '' ? '' : option.text;
                                data[id] = text.escapeHTML();
                            }
                        } else if (value !== null) {
                            data[id] = value.escapeHTML();
                        }
                    }
                });

                // Set name of state to 'region' if list of states are in 'region_id' selectbox
                if (!data.region && data.region_id) {
                    data.region = data.region_id;
                    delete data.region_id;
                }

                // Set data to html
                var itemContainer = this.element.find("[aria-selected='true'] address");
                if (itemContainer.length && itemContainer[0]) {
                    itemContainer[0].innerHTML = $(this.options.tabAddressTemplateSelector).tmpl(data).html();
                }
            }
        },

        /**
         * This method processes the event associated with a form field changing.
         * @param event Event occurring.
         * @private
         */
        _updateAddress: function (event) {
            this._syncFormData(this._getFormContainer(event.target));
        },

        /**
         * This method returns the form containing this element.
         * @param {JQuery|Element} element JQuery object or DOM element.
         * @private
         */
        _getFormContainer: function(element) {
            if (!(element instanceof jQuery)) {
                element = $(element);
            }
            return element.closest('[data-item]');
        },

        /**
         * This method binds a country element on the given form to the addressCountry widget.
         * @param {JQuery} formElement The form containing the country.
         * @private
         */
        _bindCountryRegionRelation : function(formElement){
            $(formElement).find('.countries').addressCountry({
                regionsUrl: this.options.regionsUrl,
                optionalZipCountries: this.options.optionalZipCountries,
                requiredStateForCountries: this.options.requiredStateForCountries
            });
        }
    });

    $.widget('mage.addressCountry', {
        options: {
            regionsUrl: null,
            optionalZipCountries: [],
            requiredStateForCountries: [],
            countryElement: null,
            regionIdElement: null,
            regionElement: null
        },

        /**
         * This method is used to bind events associated with this widget.
         */
        _bind: function () {
            this._on({
                'change': '_onAddressCountryChange'
            });
        },

        /**
         * Create, Initialize this widget.
         */
         _create: function () {
            this._bind();
        },

        /**
         * This method updates country dependent fields; region input, and region and zipCode required indicator.
         * @param {Event} event Change event occurring.
         * @private
         */
        _onAddressCountryChange : function(event){
            var countryElement = event.target;
            this.options.countryElement = countryElement;

            var formElement = $(countryElement).closest('[data-item]');
            var fieldElement = $(formElement).find('.field-region');
            var regionElement =  $(fieldElement).find('.input-text');
            if ('select' == $(regionElement).prop("tagName").toLowerCase()) {
                this.options.regionIdElement = regionElement;
                this.options.regionElement = regionElement.next();
            }
            else {
                this.options.regionElement = regionElement;
                this.options.regionIdElement = regionElement.next();
            }

            if (countryElement.value) {
                // obtain regions for the country
                $.ajax({
                    url: this.options.regionsUrl,
                    type: 'post',
                    dataType: 'json',
                    showLoader: true,
                    data: {parent: countryElement.value},
                    context: this,
                    success: jQuery.proxy(this._refreshRegionField, this)
                });
            } else {
                // Set empty text field in region
                this._refreshRegionField({});
            }
            // set Zip optional/required
            this._setPostcodeOptional(countryElement);
        },

        /**
         * This method updates the region input from the server response.
         * @param {Object} serverResponse Regions (state/province) or empty if regions n/a for the country.
         * @private
         */
        _refreshRegionField : function(data){
            var regionField = $(this.options.regionElement).closest('div.field');
            var regionControl = regionField.find('.control');
            // clear current region input/select
            regionControl.empty();

            var regionInput = null;
            var regionIdInput = null;
            var newInput = null; // id of input that was added to a page - filled below

            if (data.length) {
                // Create visible selectbox 'region_id' and hidden 'region'
                regionIdInput = $('<select>').attr({
                    'name': this.options.regionIdElement.attr("name"),
                    'id': this.options.regionIdElement.attr("id"),
                    'class': "required-entry input-text select",
                    'title': this.options.regionIdElement.attr("title")
                }).appendTo(regionControl);

                var regionValue = this.options.regionElement.attr('value');
                $.each(data, function(idx, item) {
                    var regionOption = $("<option />").val(item.value).text(item.label);

                    if(regionValue && regionValue == item.label) {
                        regionOption.attr('selected', 'selected');
                    }

                    regionIdInput.append(regionOption);
                });

                regionInput = $('<input>').attr({
                    'name': this.options.regionElement.attr("name"),
                    'id': this.options.regionElement.attr("id"),
                    'type': "hidden"
                }).appendTo(regionControl);

                newInput = regionIdInput;
            }
            else {
                // Create visible text input 'region' and hidden 'region_id'
                regionInput = $('<input>').attr({
                    'type': "text",
                    'name': this.options.regionElement.attr("name"),
                    'id': this.options.regionElement.attr("id"),
                    'class': "input-text",
                    'title': this.options.regionElement.attr("title")
                }).appendTo(regionControl);

                regionIdInput = $('<input>').attr({
                    'type': "hidden",
                    'name': this.options.regionIdElement.attr("name"),
                    'id': this.options.regionIdElement.attr("id")
                }).appendTo(regionControl);

                newInput = regionInput;
            }

            this.options.regionElement = regionInput;
            this.options.regionIdElement = regionIdInput;

            // Updating in address info
            this.element.trigger('formchange');

            // bind region input change event
            newInput.on('change', $.proxy(this._triggerFormChange, this, newInput));

            this._checkRegionRequired([regionInput, regionIdInput], newInput, regionField);
        },

        /**
         * This method is used to trigger a change element for a given element.
         */
        _triggerFormChange: function (element) {
            element.trigger('formchange');
        },

        /**
         * This method updates the region input required/optional and validation classes.
         * @param {Array} elements Region elements
         * @param {Element} activeElement Active Region element
         * @param {Element} regionField Region section element
         * @private
         */
        _checkRegionRequired: function(elements, activeElement, regionField)
        {
            var regionRequired = this.options.requiredStateForCountries.indexOf(this.options.countryElement.value) >= 0;

            elements.each(function(currentElement) {
                var form = $(currentElement).closest("form");
                var validationInstance = form ? jQuery(form).data('validation') : null;

                if (validationInstance) {
                    validationInstance.clearError(currentElement);
                }

                if (!regionRequired) {
                    if (regionField.hasClass('required')) {
                        regionField.removeClass('required');
                    }
                    if (currentElement.hasClass('required-entry')) {
                        currentElement.removeClass('required-entry');
                    }
                    if ('select' == currentElement.prop("tagName").toLowerCase() &&
                        currentElement.hasClass('validate-select')) {
                        currentElement.removeClass('validate-select');
                    }
                } else {
                    if (regionField.hasClass('required') === false) {
                        regionField.addClass('required');
                    }
                    if (activeElement == currentElement) {
                        if (!currentElement.hasClass('required-entry')) {
                            currentElement.addClass('required-entry');
                        }
                        if ('select' == currentElement.prop("tagName").toLowerCase() &&
                            !currentElement.hasClass('validate-select')) {
                            currentElement.addClass('validate-select');
                        }
                    }
                }
            });
        },

        /**
         * This method shows/hides the zip/postalCode code required indicator.
         * @param {Element} countryElement
         * @private
         */
        _setPostcodeOptional: function(countryElement) {
            var formElement = $(countryElement).closest('[data-item]');
            var fieldElement = $(formElement).find('.field-postcode');
            var zipElement = $(fieldElement).find('.input-text');

            var zipField = $(zipElement).closest('.field-postcode');
            if (this.options.optionalZipCountries.indexOf(countryElement.value) != -1) {
                if ($(zipElement).hasClass('required-entry')) {
                    $(zipElement).removeClass('required-entry');
                }
                $(zipField).removeClass('required');
            } else {
                $(zipElement).addClass('required-entry');
                $(zipField).addClass('required');
            }
        }
    });

    $.widget('mage.observableInputs', {
        options: {
            name: ''
        },

        /**
         * This method is used to bind events associated with this widget.
         */
        _bind: function () {
            this._on(this.element.find(':input').not('.countries'), {'change': '_triggerChange'});
        },

        _create: function () {
            this._super();
            this._bind();
        },

        /**
         * This method is used to trigger a change element for a given entity.
         */
        _triggerChange: function (element) {
            // send the name of the captor and the field that changed
            this.element.trigger('formchange', {'name': this.options.name, 'element': element.target});
        }
    });

    /**
     * This widget is used to trigger a message to delete a data item (i.e. D of CRUD).
     */
    $.widget('mage.dataItemDeleteButton', {
        options: {
            item: ''
        },

        /**
         * This method is used to bind events associated with this widget.
         */
        _bind: function () {
            this._on(this.element.find('[data-role="delete"]'), {'click': '_triggerDelete'});
        },

        _create: function () {
            this._super();
            this._bind();

            // if the item was not specified, find the data-item element wrapper
            if (this.options.item.length === 0) {
                var dataItemContainer = this.element.parents('[data-item]');
                if (dataItemContainer.length === 1) {
                    this.options.item = dataItemContainer.attr("data-item");
                }
            }
        },

        /**
         * This method is used to trigger a delete message for this item.
         */
        _triggerDelete: function () {
            // send the name of the captor and the field that changed
            this.element.trigger('dataItemDelete', {'item': this.options.item});

            // we are handling the click, so stop processing
            return false;
        }
    });

    return {
        addressTabs:            $.mage.addressTabs,
        addressCountry:         $.mage.addressCountry,
        observableInputs:       $.mage.observableInputs,
        dataItemDeleteButton:   $.mage.dataItemDeleteButton
    };
});