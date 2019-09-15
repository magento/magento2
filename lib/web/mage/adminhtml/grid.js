/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// also depends on a mage/adminhtml/tools.js for Base64 encoding
/* global varienGrid, setLocation, varienGlobalEvents, FORM_KEY,
    BASE_URL, Base64, varienGridMassaction, varienStringArray, serializerController
*/
/* eslint-disable strict */
define([
    'jquery',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'mage/mage',
    'prototype',
    'mage/adminhtml/form',
    'mage/adminhtml/events'
], function (jQuery, mageTemplate, alert, confirm) {
    /**
     * @param {*} grid
     * @param {*} event
     */
    function openGridRow(grid, event) {
        var element = Event.findElement(event, 'tr');

        if (['a', 'input', 'select', 'option'].indexOf(Event.element(event).tagName.toLowerCase()) !== -1) {
            return;
        }

        if (element.title) {
            setLocation(element.title);
        }
    }
    window.openGridRow = openGridRow;

    window.varienGrid = new Class.create();

    varienGrid.prototype = {
        /**
         * @param {String} containerId
         * @param {String} url
         * @param {*} pageVar
         * @param {*} sortVar
         * @param {*} dirVar
         * @param {*} filterVar
         */
        initialize: function (containerId, url, pageVar, sortVar, dirVar, filterVar) {
            this.containerId = containerId;
            jQuery('#' + containerId).data('gridObject', this);
            this.url = url;
            this.pageVar = pageVar || false;
            this.sortVar = sortVar || false;
            this.dirVar = dirVar || false;
            this.filterVar = filterVar || false;
            this.tableSufix = '_table';
            this.useAjax = false;
            this.rowClickCallback = false;
            this.checkboxCheckCallback = false;
            this.preInitCallback = false;
            this.initCallback = false;
            this.initRowCallback = false;
            this.doFilterCallback = false;
            this.sortableUpdateCallback = false;

            this.reloadParams = false;

            this.trOnMouseOver = this.rowMouseOver.bindAsEventListener(this);
            this.trOnMouseOut = this.rowMouseOut.bindAsEventListener(this);
            this.trOnClick = this.rowMouseClick.bindAsEventListener(this);
            this.trOnDblClick = this.rowMouseDblClick.bindAsEventListener(this);
            this.trOnKeyPress = this.keyPress.bindAsEventListener(this);

            this.thLinkOnClick = this.doSort.bindAsEventListener(this);
            this.initGrid();
        },

        /**
         * Init grid.
         */
        initGrid: function () {
            var row, columns, col;

            if (this.preInitCallback) {
                this.preInitCallback(this);
            }

            if ($(this.containerId + this.tableSufix)) {
                this.rows = $$('#' + this.containerId + this.tableSufix + ' tbody tr');

                for (row = 0; row < this.rows.length; row++) {
                    if (row % 2 == 0) { //eslint-disable-line eqeqeq, max-depth
                        Element.addClassName(this.rows[row], 'even');
                    }

                    Event.observe(this.rows[row], 'mouseover', this.trOnMouseOver);
                    Event.observe(this.rows[row], 'mouseout', this.trOnMouseOut);
                    Event.observe(this.rows[row], 'click', this.trOnClick);
                    Event.observe(this.rows[row], 'dblclick', this.trOnDblClick);
                }
            }

            if (this.sortVar && this.dirVar) {
                columns = $$('#' + this.containerId + this.tableSufix + ' thead [data-sort]');

                for (col = 0; col < columns.length; col++) {
                    Event.observe(columns[col], 'click', this.thLinkOnClick);
                }
            }
            this.bindFilterFields();
            this.bindFieldsChange();

            if (this.initCallback) {
                try {
                    this.initCallback(this);
                }
                catch (e) {
                    if (window.console) { //eslint-disable-line max-depth
                        console.log(e);
                    }
                }
            }
            jQuery('#' + this.containerId).trigger('gridinit', this);
        },

        /**
         * Init grid ajax.
         */
        initGridAjax: function () {
            this.initGrid();
            this.initGridRows();
        },

        /**
         * Init grid rows.
         */
        initGridRows: function () {
            var row;

            if (this.initRowCallback) {
                for (row = 0; row < this.rows.length; row++) {
                    try { //eslint-disable-line max-depth
                        this.initRowCallback(this, this.rows[row]);
                    } catch (e) {
                        if (window.console) { //eslint-disable-line max-depth
                            console.log(e);
                        }
                    }
                }
            }
        },

        /**
         * @param {*} event
         */
        rowMouseOver: function (event) {
            var element = Event.findElement(event, 'tr');

            if (!element.title) {
                return;
            }

            Element.addClassName(element, 'on-mouse');

            if (!Element.hasClassName('_clickable') && (this.rowClickCallback !== openGridRow || element.title)) {
                if (element.title) {
                    Element.addClassName(element, '_clickable');
                }
            }
        },

        /**
         * @param {*} event
         */
        rowMouseOut: function (event) {
            var element = Event.findElement(event, 'tr');

            Element.removeClassName(element, 'on-mouse');
        },

        /**
         * @param {*} event
         */
        rowMouseClick: function (event) {
            if (this.rowClickCallback) {
                try {
                    this.rowClickCallback(this, event);
                }
                catch (e) {
                }
            }
            varienGlobalEvents.fireEvent('gridRowClick', event);
        },

        /**
         * @param {*} event
         */
        rowMouseDblClick: function (event) {
            varienGlobalEvents.fireEvent('gridRowDblClick', event);
        },

        /**
         * Key press.
         */
        keyPress: function () {},

        /**
         * @param {*} event
         * @return {Boolean}
         */
        doSort: function (event) {
            var element = Event.findElement(event, 'th');

            if (element.readAttribute('data-sort') && element.readAttribute('data-direction')) {
                this.addVarToUrl(this.sortVar, element.readAttribute('data-sort'));
                this.addVarToUrl(this.dirVar, element.readAttribute('data-direction'));
                this.reload(this.url);
            }
            Event.stop(event);

            return false;
        },

        /**
         * @param {Object} element
         */
        loadByElement: function (element) {
            if (element && element.name) {
                this.reload(this.addVarToUrl(element.name, element.value));
            }
        },

        /**
         * @param {*} data
         * @param {*} textStatus
         * @param {*} transport
         * @private
         */
        _onAjaxSeccess: function (data, textStatus, transport) {
            var responseText, response, divId;

            /* eslint-disable max-depth */
            try {
                responseText = transport.responseText;

                if (transport.responseText.isJSON()) {
                    response = transport.responseText.evalJSON();

                    if (response.error) {
                        alert({
                            content: response.message
                        });
                    }

                    if (response.ajaxExpired && response.ajaxRedirect) {
                        setLocation(response.ajaxRedirect);
                    }
                } else {

                    /*eslint-disable max-len*/
                    /**
                     * For IE <= 7.
                     * If there are two elements, and first has name, that equals id of second.
                     * In this case, IE will choose one that is above
                     *
                     * @see https://prototype.lighthouseapp.com/projects/8886/tickets/994-id-selector-finds-elements-by-name-attribute-in-ie7
                     */

                    /*eslint-enable max-len*/
                    divId = $(this.containerId);

                    if (divId.id == this.containerId) { //eslint-disable-line eqeqeq
                        divId.update(responseText);
                    } else {
                        $$('div[id="' + this.containerId + '"]')[0].update(responseText);
                    }
                }
            } catch (e) {
                divId = $(this.containerId);

                if (divId.id == this.containerId) { //eslint-disable-line eqeqeq
                    divId.update(responseText);
                } else {
                    $$('div[id="' + this.containerId + '"]')[0].update(responseText);
                }
            }

            /* eslint-enable max-depth */
            jQuery('#' + this.containerId).trigger('contentUpdated');
        },

        /**
         * @param {*} url
         * @param {Function} onSuccessCallback
         * @return {*}
         */
        reload: function (url, onSuccessCallback) {
            var ajaxSettings, ajaxRequest;

            this.reloadParams = this.reloadParams || {};
            this.reloadParams['form_key'] = FORM_KEY;
            url = url || this.url;

            if (this.useAjax) {
                ajaxSettings = {
                    url: url + (url.match(new RegExp('\\?')) ? '&ajax=true' : '?ajax=true'),
                    showLoader: true,
                    method: 'post',
                    context: jQuery('#' + this.containerId),
                    data: this.reloadParams,
                    error: this._processFailure.bind(this),
                    complete: this.initGridAjax.bind(this),
                    dataType: 'html',

                    /**
                     * Success callback.
                     */
                    success: function (data, textStatus, transport) {
                        this._onAjaxSeccess(data, textStatus, transport);

                        if (onSuccessCallback && typeof onSuccessCallback === 'function') {
                            // execute the callback, passing parameters as necessary
                            onSuccessCallback();
                        }
                    }.bind(this)
                };
                jQuery('#' + this.containerId).trigger('gridajaxsettings', ajaxSettings);
                ajaxRequest = jQuery.ajax(ajaxSettings);
                jQuery('#' + this.containerId).trigger('gridajax', ajaxRequest);

                return ajaxRequest;
            }

            if (this.reloadParams) {
                $H(this.reloadParams).each(function (pair) {
                    url = this.addVarToUrl(pair.key, pair.value);
                }.bind(this));
            }
            location.href = url;
        },

        /**
         * @private
         */
        _processFailure: function () {
            location.href = BASE_URL;
        },

        /**
         * @param {*} url
         * @param {*} varName
         * @param {*} varValue
         * @return {String|*}
         * @private
         */
        _addVarToUrl: function (url, varName, varValue) {
            var re = new RegExp('\/(' + varName + '\/.*?\/)'),
                parts = url.split(new RegExp('\\?'));

            url = parts[0].replace(re, '/');
            url += varName + '/' + varValue + '/';

            if (parts.size() > 1) {
                url += '?' + parts[1];
            }

            return url;
        },

        /**
         * Builds the form with fields containing the and submits
         *
         * @param {String} url
         * @param {String} varName
         * @param {String} varValue
         * @private
         */
        _buildFormAndSubmit: function (url, varName, varValue) {
            var re = new RegExp('\/(' + varName + '\/.*?\/)'),
                parts = url.split(new RegExp('\\?')),
                form = jQuery('<form/>'),
                inputProps = [
                    {
                        name: varName,
                        value: varValue
                    },
                    {
                        name: 'form_key',
                        value: window.FORM_KEY
                    }
                ],
                input;

            url = parts[0].replace(re, '/');

            if (parts.size() > 1) {
                url += '?' + parts[1];
            }

            form.attr('action', url);
            form.attr('method', 'POST');

            inputProps.forEach(function (item) {
                input = jQuery('<input/>');
                input.attr('name', item.name);
                input.attr('type', 'hidden');
                input.val(item.value);
                form.append(input);
            });
            jQuery('[data-container="body"]').append(form);
            form.submit();
            form.remove();
        },

        /**
         * @param {*} varName
         * @param {*} varValue
         * @return {*|String}
         */
        addVarToUrl: function (varName, varValue) {
            this.url = this._addVarToUrl(this.url, varName, varValue);

            return this.url;
        },

        /**
         * Do export.
         */
        doExport: function () {
            var exportUrl;

            if ($(this.containerId + '_export')) {
                exportUrl = $(this.containerId + '_export').value;

                if (this.massaction && this.massaction.checkedString) {
                    this._buildFormAndSubmit(
                        exportUrl,
                        this.massaction.formFieldNameInternal,
                        this.massaction.checkedString
                    );
                } else {
                    location.href = exportUrl;
                }
            }
        },

        /**
         * Bind filter fields.
         */
        bindFilterFields: function () {
            var filters = $$(
                    '#' + this.containerId + ' [data-role="filter-form"] input',
                    '#' + this.containerId + ' [data-role="filter-form"] select'
                ),
                i;

            for (i = 0; i < filters.length; i++) {
                Event.observe(filters[i], 'keypress', this.filterKeyPress.bind(this));
            }
        },

        /**
         * Bind field change.
         */
        bindFieldsChange: function () {
            var dataElements, i;

            if (!$(this.containerId)) {
                return;
            }
            //var dataElements = $(this.containerId+this.tableSufix).down('.data tbody').select('input', 'select');
            dataElements = $(this.containerId + this.tableSufix).down('tbody').select('input', 'select');

            for (i = 0; i < dataElements.length; i++) {
                Event.observe(dataElements[i], 'change', dataElements[i].setHasChanges.bind(dataElements[i]));
            }
        },

        /**
         * Bind sortable.
         */
        bindSortable: function () {
            if (jQuery('#' + this.containerId).find('.draggable-handle').length) {
                jQuery('#' + this.containerId).find('tbody').sortable({
                    axis: 'y',
                    handle: '.draggable-handle',

                    /**
                     * @param {*} event
                     * @param {*} ui
                     * @return {*}
                     */
                    helper: function (event, ui) {
                        ui.children().each(function () {
                            jQuery(this).width(jQuery(this).width());
                        });

                        return ui;
                    },
                    update: this.sortableUpdateCallback ? this.sortableUpdateCallback : function () {},
                    tolerance: 'pointer'
                });
            }
        },

        /**
         * @param {Object} event
         */
        filterKeyPress: function (event) {
            if (event.keyCode == Event.KEY_RETURN) { //eslint-disable-line eqeqeq
                this.doFilter();
            }
        },

        /**
         * @param {Function} callback
         */
        doFilter: function (callback) {
            var filters = $$(
                    '#' + this.containerId + ' [data-role="filter-form"] input',
                    '#' + this.containerId + ' [data-role="filter-form"] select'
                ),
                elements = [],
                i;

            for (i in filters) {
                if (filters[i].value && filters[i].value.length) {
                    elements.push(filters[i]);
                }
            }

            if (!this.doFilterCallback || this.doFilterCallback && this.doFilterCallback()) {
                this.reload(
                    this.addVarToUrl(this.filterVar, Base64.encode(Form.serializeElements(elements))),
                    callback
                );
            }
        },

        /**
         * @param {Function} callback
         */
        resetFilter: function (callback) {
            this.reload(this.addVarToUrl(this.filterVar, ''), callback);
        },

        /**
         * @param {Object} element
         */
        checkCheckboxes: function (element) {
            var elements = Element.select($(this.containerId), 'input[name="' + element.name + '"]'),
                i;

            for (i = 0; i < elements.length; i++) {
                this.setCheckboxChecked(elements[i], element.checked);
            }

            /*eslint-enable no-undef*/
        },

        /**
         *
         * @param {HTMLElement} element
         * @param {*} checked
         */
        setCheckboxChecked: function (element, checked) {
            element.checked = checked;
            jQuery(element).trigger('change');
            element.setHasChanges({});

            if (this.checkboxCheckCallback) {
                this.checkboxCheckCallback(this, element, checked);
            }
        },

        /**
         * @param {Object} event
         * @param {*} lastId
         */
        inputPage: function (event, lastId) {
            var element = Event.element(event),
                keyCode = event.keyCode || event.which,
                enteredValue = parseInt(element.value, 10),
                pageId = parseInt(lastId, 10);

            if (keyCode == Event.KEY_RETURN) { //eslint-disable-line eqeqeq
                if (enteredValue > pageId) {
                    this.setPage(pageId);
                } else {
                    this.setPage(enteredValue);
                }
            }

            /*if(keyCode>47 && keyCode<58){

             }
             else{
             Event.stop(event);
             }*/
        },

        /**
         * @param {*} pageNumber
         */
        setPage: function (pageNumber) {
            this.reload(this.addVarToUrl(this.pageVar, pageNumber));
        }
    };

    window.varienGridMassaction = Class.create();
    varienGridMassaction.prototype = {
        /* Predefined vars */
        checkedValues: $H({}),
        checkedString: '',
        oldCallbacks: {},
        errorText: '',
        items: {},
        gridIds: [],
        useSelectAll: false,
        currentItem: false,
        lastChecked: {
            left: false,
            top: false,
            checkbox: false
        },
        fieldTemplate: mageTemplate('<input type="hidden" name="<%- name %>" value="<%- value %>" />'),

        /**
         * @param {*} containerId
         * @param {*} grid
         * @param {*} checkedValues
         * @param {*} formFieldNameInternal
         * @param {*} formFieldName
         */
        initialize: function (containerId, grid, checkedValues, formFieldNameInternal, formFieldName) {
            this.setOldCallback('row_click', grid.rowClickCallback);
            this.setOldCallback('init',      grid.initCallback);
            this.setOldCallback('init_row',  grid.initRowCallback);
            this.setOldCallback('pre_init',  grid.preInitCallback);

            this.useAjax        = false;
            this.grid           = grid;
            this.grid.massaction = this;
            this.containerId    = containerId;
            this.initMassactionElements();

            this.checkedString          = checkedValues;
            this.formFieldName          = formFieldName;
            this.formFieldNameInternal  = formFieldNameInternal;

            this.grid.initCallback      = this.onGridInit.bind(this);
            this.grid.preInitCallback   = this.onGridPreInit.bind(this);
            this.grid.initRowCallback   = this.onGridRowInit.bind(this);
            this.grid.rowClickCallback  = this.onGridRowClick.bind(this);
            this.initCheckboxes();
            this.checkCheckboxes();
        },

        /**
         * @param {*} flag
         */
        setUseAjax: function (flag) {
            this.useAjax = flag;
        },

        /**
         * @param {*} flag
         */
        setUseSelectAll: function (flag) {
            this.useSelectAll = flag;
        },

        /**
         * Init massaction elements.
         */
        initMassactionElements: function () {
            this.container      = $(this.containerId);
            this.multiselect    = $(this.containerId + '-mass-select');
            this.count          = $(this.containerId + '-count');
            this.formHiddens    = $(this.containerId + '-form-hiddens');
            this.formAdditional = $(this.containerId + '-form-additional');
            this.select         = $(this.containerId + '-select');
            this.form           = this.prepareForm();
            jQuery(this.form).mage('validation');
            this.select.observe('change', this.onSelectChange.bindAsEventListener(this));
            this.lastChecked    = {
                left: false,
                top: false,
                checkbox: false
            };
            this.select.addClassName(this.select.value ? '_selected' : '');
            this.initMassSelect();
        },

        /**
         * @return {jQuery|*|HTMLElement}
         */
        prepareForm: function () {
            var form = $(this.containerId + '-form'),
                formPlace = null,
                formElement = this.formHiddens || this.formAdditional;

            if (!formElement) {
                formElement = this.container.getElementsByTagName('button')[0];
                formElement && formElement.parentNode;
            }

            if (!form && formElement) {
                /* fix problem with rendering form in FF through innerHTML property */
                form = document.createElement('form');
                form.setAttribute('method', 'post');
                form.setAttribute('action', '');
                form.id = this.containerId + '-form';
                formPlace = formElement.parentNode;
                formPlace.parentNode.appendChild(form);
                form.appendChild(formPlace);
            }

            return form;
        },

        /**
         * @param {Array} gridIds
         */
        setGridIds: function (gridIds) {
            this.gridIds = gridIds;
            this.updateCount();
        },

        /**
         * @return {Array}
         */
        getGridIds: function () {
            return this.gridIds;
        },

        /**
         * @param {*} items
         */
        setItems: function (items) {
            this.items = items;
            this.updateCount();
        },

        /**
         * @return {Object}
         */
        getItems: function () {
            return this.items;
        },

        /**
         * @param {*} itemId
         * @return {*}
         */
        getItem: function (itemId) {
            if (this.items[itemId]) {
                return this.items[itemId];
            }

            return false;
        },

        /**
         * @param {String} callbackName
         * @return {Function}
         */
        getOldCallback: function (callbackName) {
            return this.oldCallbacks[callbackName] ? this.oldCallbacks[callbackName] : Prototype.emptyFunction;
        },

        /**
         * @param {String} callbackName
         * @param {Function} callback
         */
        setOldCallback: function (callbackName, callback) {
            this.oldCallbacks[callbackName] = callback;
        },

        /**
         * @param {*} grid
         */
        onGridPreInit: function (grid) {
            this.initMassactionElements();
            this.getOldCallback('pre_init')(grid);
        },

        /**
         * @param {*} grid
         */
        onGridInit: function (grid) {
            this.initCheckboxes();
            this.checkCheckboxes();
            this.updateCount();
            this.getOldCallback('init')(grid);
        },

        /**
         * @param {*} grid
         * @param {*} row
         */
        onGridRowInit: function (grid, row) {
            this.getOldCallback('init_row')(grid, row);
        },

        /**
         * @param {Object} evt
         */
        isDisabled: function (evt) {
            var target = jQuery(evt.target),
                tr,
                checkbox;

            tr = target.is('tr') ? target : target.closest('tr');
            checkbox = tr.find('input[type="checkbox"]');

            return checkbox.is(':disabled');
        },

        /**
         * @param {*} grid
         * @param {*} evt
         * @return {*}
         */
        onGridRowClick: function (grid, evt) {
            var tdElement = Event.findElement(evt, 'td'),
                trElement = Event.findElement(evt, 'tr'),
                checkbox, isInput, checked;

            if (this.isDisabled(evt)) {
                return false;
            }

            if (!$(tdElement).down('input')) {
                if ($(tdElement).down('a') || $(tdElement).down('select')) {
                    return; //eslint-disable-line
                }

                if (trElement.title && trElement.title.strip() != '#') { //eslint-disable-line eqeqeq
                    this.getOldCallback('row_click')(grid, evt);
                } else {
                    checkbox = Element.select(trElement, 'input');
                    isInput  = Event.element(evt).tagName == 'input'; //eslint-disable-line eqeqeq
                    checked = isInput ? checkbox[0].checked : !checkbox[0].checked;

                    if (checked) { //eslint-disable-line max-depth
                        this.checkedString = varienStringArray.add(checkbox[0].value, this.checkedString);
                    } else {
                        this.checkedString = varienStringArray.remove(checkbox[0].value, this.checkedString);
                    }
                    this.grid.setCheckboxChecked(checkbox[0], checked);
                    this.updateCount();
                }

                return; //eslint-disable-line
            }

            if (Event.element(evt).isMassactionCheckbox) {
                this.setCheckbox(Event.element(evt));
            } else if (checkbox = this.findCheckbox(evt)) { //eslint-disable-line no-cond-assign
                checkbox.checked = !checkbox.checked;
                jQuery(checkbox).trigger('change');
                this.setCheckbox(checkbox);
            }
        },

        /**
         * @param {Object} evt
         */
        onSelectChange: function (evt) {
            var item = this.getSelectedItem();

            if (item) {
                this.formAdditional.update($(this.containerId + '-item-' + item.id + '-block').innerHTML);
                evt.target.addClassName('_selected');
            } else {
                this.formAdditional.update('');
                evt.target.removeClassName('_selected');
            }
            jQuery(this.form).data('validator').resetForm();
        },

        /**
         * @param {Object} evt
         * @return {*}
         */
        findCheckbox: function (evt) {
            if (['a', 'input', 'select'].indexOf(Event.element(evt).tagName.toLowerCase()) !== -1) {
                return false;
            }
            checkbox = false; //eslint-disable-line no-undef
            Event.findElement(evt, 'tr').select('[data-role="select-row"]').each(function (element) { //eslint-disable-line
                if (element.isMassactionCheckbox) {
                    checkbox = element; //eslint-disable-line no-undef
                }
            });

            return checkbox; //eslint-disable-line no-undef
        },

        /**
         * Init checkobox.
         */
        initCheckboxes: function () {
            this.getCheckboxes().each(function (checkbox) { //eslint-disable-line no-extra-bind
                checkbox.isMassactionCheckbox = true; //eslint-disable-line no-undef
            });
        },

        /**
         * Check checkbox.
         */
        checkCheckboxes: function () {
            this.getCheckboxes().each(function (checkbox) {
                checkbox.checked = varienStringArray.has(checkbox.value, this.checkedString);
                jQuery(checkbox).trigger('change');
            }.bind(this));
        },

        /**
         * @return {Boolean}
         */
        selectAll: function () {
            this.setCheckedValues(this.useSelectAll ? this.getGridIds() : this.getCheckboxesValuesAsString());
            this.checkCheckboxes();
            this.updateCount();
            this.clearLastChecked();

            return false;
        },

        /**
         * @return {Boolean}
         */
        unselectAll: function () {
            this.setCheckedValues('');
            this.checkCheckboxes();
            this.updateCount();
            this.clearLastChecked();

            return false;
        },

        /**
         * @return {Boolean}
         */
        selectVisible: function () {
            this.setCheckedValues(this.getCheckboxesValuesAsString());
            this.checkCheckboxes();
            this.updateCount();
            this.clearLastChecked();

            return false;
        },

        /**
         * @return {Boolean}
         */
        unselectVisible: function () {
            this.getCheckboxesValues().each(function (key) {
                this.checkedString = varienStringArray.remove(key, this.checkedString);
            }.bind(this));
            this.checkCheckboxes();
            this.updateCount();
            this.clearLastChecked();

            return false;
        },

        /**
         * @param {*} values
         */
        setCheckedValues: function (values) {
            this.checkedString = values;
        },

        /**
         * @return {String}
         */
        getCheckedValues: function () {
            return this.checkedString;
        },

        /**
         * @return {Array}
         */
        getCheckboxes: function () {
            var result = [];

            this.grid.rows.each(function (row) {
                var checkboxes = row.select('[data-role="select-row"]');

                checkboxes.each(function (checkbox) {
                    result.push(checkbox);
                });
            });

            return result;
        },

        /**
         * @return {Array}
         */
        getCheckboxesValues: function () {
            var result = [];

            this.getCheckboxes().each(function (checkbox) { //eslint-disable-line no-extra-bind
                result.push(checkbox.value);
            });

            return result;
        },

        /**
         * @return {String}
         */
        getCheckboxesValuesAsString: function () {
            return this.getCheckboxesValues().join(',');
        },

        /**
         * @param {Object} checkbox
         */
        setCheckbox: function (checkbox) {
            if (checkbox.checked) {
                this.checkedString = varienStringArray.add(checkbox.value, this.checkedString);
            } else {
                this.checkedString = varienStringArray.remove(checkbox.value, this.checkedString);
            }
            this.updateCount();
        },

        /**
         * Update count.
         */
        updateCount: function () {
            var checkboxesTotal = varienStringArray.count(
                this.useSelectAll ? this.getGridIds() : this.getCheckboxesValuesAsString()
                ),
                checkboxesChecked = varienStringArray.count(this.checkedString);

            jQuery('[data-role="counter"]', this.count).html(checkboxesChecked);

            if (!checkboxesTotal) {
                this.multiselect.addClassName('_disabled');
            } else {
                this.multiselect.removeClassName('_disabled');
            }

            if (checkboxesChecked == checkboxesTotal && checkboxesTotal != 0) { //eslint-disable-line eqeqeq
                this.count.removeClassName('_empty');
                this.multiselect.addClassName('_checked').removeClassName('_indeterminate');
            } else if (checkboxesChecked == 0) { //eslint-disable-line eqeqeq
                this.count.addClassName('_empty');
                this.multiselect.removeClassName('_checked').removeClassName('_indeterminate');
            } else {
                this.count.removeClassName('_empty');
                this.multiselect.addClassName('_checked').addClassName('_indeterminate');
            }

            if (!this.grid.reloadParams) {
                this.grid.reloadParams = {};
            }
            this.grid.reloadParams[this.formFieldNameInternal] = this.checkedString;
        },

        /**
         * @return {*}
         */
        getSelectedItem: function () {
            if (this.getItem(this.select.value)) {
                return this.getItem(this.select.value);
            }

            return false;
        },

        /**
         * Apply.
         */
        apply: function () {
            var item, fieldName;

            if (varienStringArray.count(this.checkedString) == 0) { //eslint-disable-line eqeqeq
                alert({
                    content: this.errorText
                });

                return;
            }

            item = this.getSelectedItem();

            if (!item) {
                jQuery(this.form).valid();

                return;
            }
            this.currentItem = item;
            fieldName = item.field ? item.field : this.formFieldName;

            if (this.currentItem.confirm) {
                confirm({
                    content: this.currentItem.confirm,
                    actions: {
                        confirm: this.onConfirm.bind(this, fieldName, item)
                    }
                });
            } else {
                this.onConfirm(fieldName, item);
            }
        },

        /**
         * @param {*} fieldName
         * @param {*} item
         */
        onConfirm: function (fieldName, item) {
            this.formHiddens.update('');
            new Insertion.Bottom(this.formHiddens, this.fieldTemplate({
                name: fieldName,
                value: this.checkedString
            }));
            new Insertion.Bottom(this.formHiddens, this.fieldTemplate({
                name: 'massaction_prepare_key',
                value: fieldName
            }));

            if (!jQuery(this.form).valid()) {
                return;
            }

            if (this.useAjax && item.url) {
                new Ajax.Request(item.url, {
                    'method': 'post',
                    'parameters': this.form.serialize(true),
                    'onComplete': this.onMassactionComplete.bind(this)
                });
            } else if (item.url) {
                this.form.action = item.url;
                this.form.submit();
            }
        },

        /**
         * @param {*} transport
         */
        onMassactionComplete: function (transport) {
            var listener;

            if (this.currentItem.complete) {
                try {
                    listener = this.getListener(this.currentItem.complete) || Prototype.emptyFunction;
                    listener(this.grid, this, transport);
                } catch (e) {}
            }
        },

        /**
         * @param {*} strValue
         * @return {Object}
         */
        getListener: function (strValue) {
            return eval(strValue); //eslint-disable-line no-eval
        },

        /**
         * Init mass select.
         */
        initMassSelect: function () {
            $$('input[data-role="select-row"]').each(function (element) {
                element.observe('click', this.massSelect.bind(this));
            }.bind(this));
        },

        /**
         * Clear last checked.
         */
        clearLastChecked: function () {
            this.lastChecked = {
                left: false,
                top: false,
                checkbox: false
            };
        },

        /**
         * @param {Object} evt
         */
        massSelect: function (evt) {
            var currentCheckbox, lastCheckbox, start, finish;

            if (this.lastChecked.left !== false &&
                this.lastChecked.top !== false &&
                evt.button === 0 &&
                evt.shiftKey === true
            ) {
                currentCheckbox = Event.element(evt);
                lastCheckbox = this.lastChecked.checkbox;

                if (lastCheckbox != currentCheckbox) { //eslint-disable-line eqeqeq
                    start = this.getCheckboxOrder(lastCheckbox);
                    finish = this.getCheckboxOrder(currentCheckbox);

                    if (start !== false && finish !== false) { //eslint-disable-line max-depth
                        this.selectCheckboxRange(
                            Math.min(start, finish),
                            Math.max(start, finish),
                            currentCheckbox.checked
                        );
                    }
                }
            }

            this.lastChecked = {
                left: Event.element(evt).viewportOffset().left,
                top: Event.element(evt).viewportOffset().top,
                checkbox: Event.element(evt) // "boundary" checkbox
            };
        },

        /**
         * @param {*} curCheckbox
         * @return {Boolean}
         */
        getCheckboxOrder: function (curCheckbox) {
            var order = false;

            this.getCheckboxes().each(function (checkbox, key) {
                if (curCheckbox == checkbox) { //eslint-disable-line eqeqeq
                    order = key;
                }
            });

            return order;
        },

        /**
         * @param {*} start
         * @param {*} finish
         * @param {*} isChecked
         */
        selectCheckboxRange: function (start, finish, isChecked) {
            this.getCheckboxes().each(function (checkbox, key) {
                if (key >= start && key <= finish) {
                    checkbox.checked = isChecked;
                    this.setCheckbox(checkbox);
                }
            }.bind(this));
        }
    };

    window.varienGridAction = {
        /**
         * @param {Object} select
         */
        execute: function (select) {
            var config, win;

            if (!select.value || !select.value.isJSON()) {
                return;
            }

            config = select.value.evalJSON();

            if (config.confirm && !window.confirm(config.confirm)) { //eslint-disable-line no-alert
                select.options[0].selected = true;

                return;
            }

            if (config.popup) {
                win = window.open(config.href, 'action_window', 'width=500,height=600,resizable=1,scrollbars=1');
                win.focus();
                select.options[0].selected = true;
            } else {
                setLocation(config.href);
            }
        }
    };

    window.varienStringArray = {
        /**
         * @param {*} str
         * @param {*} haystack
         * @return {*}
         */
        remove: function (str, haystack) {
            haystack = ',' + haystack + ',';
            haystack = haystack.replace(new RegExp(',' + str + ',', 'g'), ',');

            return this.trimComma(haystack);
        },

        /**
         * @param {*} str
         * @param {*} haystack
         * @return {*}
         */
        add: function (str, haystack) {
            haystack = ',' + haystack + ',';

            if (haystack.search(new RegExp(',' + str + ',', 'g'), haystack) === -1) {
                haystack += str + ',';
            }

            return this.trimComma(haystack);
        },

        /**
         * @param {*} str
         * @param {*} haystack
         * @return {Boolean}
         */
        has: function (str, haystack) {
            haystack = ',' + haystack + ',';

            if (haystack.search(new RegExp(',' + str + ',', 'g'), haystack) === -1) {
                return false;
            }

            return true;
        },

        /**
         * @param {*} haystack
         * @return {*}
         */
        count: function (haystack) {
            var match;

            if (typeof haystack != 'string') {
                return 0;
            }

            /* eslint-disable no-undef, no-cond-assign, eqeqeq */
            if (match = haystack.match(new RegExp(',', 'g'))) {
                return match.length + 1;
            } else if (haystack.length != 0) {
                return 1;
            }

            /* eslint-enable no-undef, no-cond-assign, eqeqeq */
            return 0;
        },

        /**
         * @param {*} haystack
         * @param {*} fnc
         */
        each: function (haystack, fnc) {
            var i;

            haystack = haystack.split(',');

            for (i = 0; i < haystack.length; i++) {
                fnc(haystack[i]);
            }
        },

        /**
         * @param {String} string
         * @return {String}
         */
        trimComma: function (string) {
            string = string.replace(new RegExp('^(,+)','i'), '');
            string = string.replace(new RegExp('(,+)$','i'), '');

            return string;
        }
    };

    window.serializerController = Class.create();
    serializerController.prototype = {
        oldCallbacks: {},

        /**
         * @param {*} hiddenDataHolder
         * @param {*} predefinedData
         * @param {*} inputsToManage
         * @param {*} grid
         * @param {*} reloadParamName
         */
        initialize: function (hiddenDataHolder, predefinedData, inputsToManage, grid, reloadParamName) {
            //Grid inputs
            this.tabIndex = 1000;
            this.inputsToManage       = inputsToManage;
            this.multidimensionalMode = inputsToManage.length > 0;

            //Hash with grid data
            this.gridData             = this.getGridDataHash(predefinedData);

            //Hidden input data holder
            this.hiddenDataHolder     = $(hiddenDataHolder);
            this.hiddenDataHolder.value = this.serializeObject();

            this.grid = grid;

            // Set old callbacks
            this.setOldCallback('row_click', this.grid.rowClickCallback);
            this.setOldCallback('init_row', this.grid.initRowCallback);
            this.setOldCallback('checkbox_check', this.grid.checkboxCheckCallback);

            //Grid
            this.reloadParamName = reloadParamName;
            this.grid.reloadParams = {};
            this.grid.reloadParams[this.reloadParamName + '[]'] = this.getDataForReloadParam();
            this.grid.rowClickCallback = this.rowClick.bind(this);
            this.grid.initRowCallback = this.rowInit.bind(this);
            this.grid.checkboxCheckCallback = this.registerData.bind(this);
            this.grid.rows.each(this.eachRow.bind(this));
        },

        /**
         * @param {String} callbackName
         * @param {Function} callback
         */
        setOldCallback: function (callbackName, callback) {
            this.oldCallbacks[callbackName] = callback;
        },

        /**
         * @param {String} callbackName
         * @return {Prototype.emptyFunction}
         */
        getOldCallback: function (callbackName) {
            return this.oldCallbacks[callbackName] ? this.oldCallbacks[callbackName] : Prototype.emptyFunction;
        },

        /**
         * @param {*} grid
         * @param {*} element
         * @param {*} checked
         */
        registerData: function (grid, element, checked) {
            var i;

            if (this.multidimensionalMode) {
                if (checked) {
                    /*eslint-disable max-depth*/
                    if (element.inputElements) {
                        this.gridData.set(element.value, {});

                        for (i = 0; i < element.inputElements.length; i++) {
                            element.inputElements[i].disabled = false;
                            this.gridData.get(element.value)[element.inputElements[i].name] =
                                element.inputElements[i].value;
                        }
                    }
                } else {
                    if (element.inputElements) {
                        for (i = 0; i < element.inputElements.length; i++) {
                            element.inputElements[i].disabled = true;
                        }
                    }
                    this.gridData.unset(element.value);
                }
            } else {
                if (checked) { //eslint-disable-line no-lonely-if
                    this.gridData.set(element.value, element.value);
                } else {
                    this.gridData.unset(element.value);
                }
            }

            this.hiddenDataHolder.value = this.serializeObject();
            this.grid.reloadParams = {};
            this.grid.reloadParams[this.reloadParamName + '[]'] = this.getDataForReloadParam();
            this.getOldCallback('checkbox_check')(grid, element, checked);

            /*eslint-enable max-depth*/
        },

        /**
         * @param {*} row
         */
        eachRow: function (row) {
            this.rowInit(this.grid, row);
        },

        /**
         * @param {*} grid
         * @param {*} event
         */
        rowClick: function (grid, event) {
            var trElement = Event.findElement(event, 'tr'),
                isInput   = Event.element(event).tagName == 'INPUT', //eslint-disable-line eqeqeq
                checkbox, checked;

            if (trElement) {
                checkbox = Element.select(trElement, 'input');

                if (checkbox[0] && !checkbox[0].disabled) {
                    checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                    this.grid.setCheckboxChecked(checkbox[0], checked);
                }
            }
            this.getOldCallback('row_click')(grid, event);
        },

        /**
         * @param {*} event
         */
        inputChange: function (event) {
            var element = Event.element(event);

            if (element && element.checkboxElement && element.checkboxElement.checked) {
                this.gridData.get(element.checkboxElement.value)[element.name] = element.value;
                this.hiddenDataHolder.value = this.serializeObject();
            }
        },

        /**
         * @param {*} grid
         * @param {*} row
         */
        rowInit: function (grid, row) {
            var checkbox, selectors, inputs, i;

            if (this.multidimensionalMode) {
                checkbox = $(row).select('.checkbox')[0];
                selectors = this.inputsToManage.map(function (name) {
                    return ['input[name="' + name + '"]', 'select[name="' + name + '"]'];
                });
                inputs = $(row).select.apply($(row), selectors.flatten());

                if (checkbox && inputs.length > 0) {
                    checkbox.inputElements = inputs;

                    /* eslint-disable max-depth */
                    for (i = 0; i < inputs.length; i++) {
                        inputs[i].checkboxElement = checkbox;

                        if (this.gridData.get(checkbox.value) && this.gridData.get(checkbox.value)[inputs[i].name]) {
                            inputs[i].value = this.gridData.get(checkbox.value)[inputs[i].name];
                        }
                        inputs[i].disabled = !checkbox.checked;
                        inputs[i].tabIndex = this.tabIndex++;
                        Event.observe(inputs[i], 'keyup', this.inputChange.bind(this));
                        Event.observe(inputs[i], 'change', this.inputChange.bind(this));
                    }
                }
            }

            /* eslint-enable max-depth */
            this.getOldCallback('init_row')(grid, row);
        },

        /**
         * Stuff methods.
         *
         * @param {*} _object
         * @return {*}
         */
        getGridDataHash: function (_object) {
            return $H(this.multidimensionalMode ? _object : this.convertArrayToObject(_object));
        },

        /**
         * @return {*}
         */
        getDataForReloadParam: function () {
            return this.multidimensionalMode ? this.gridData.keys() : this.gridData.values();
        },

        /**
         * @return {*}
         */
        serializeObject: function () {
            var clone;

            if (this.multidimensionalMode) {
                clone = this.gridData.clone();
                clone.each(function (pair) {
                    clone.set(pair.key, Base64.encode(Object.toQueryString(pair.value)));
                });

                return clone.toQueryString();
            }

            return this.gridData.values().join('&');
        },

        /**
         * @param {Array} _array
         * @return {Object}
         */
        convertArrayToObject: function (_array) {
            var _object = {},
                i, l;

            for (i = 0, l = _array.length; i < l; i++) {
                _object[_array[i]] = _array[i];
            }

            return _object;
        }
    };
});
