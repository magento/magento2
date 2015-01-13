/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// also depends on a mage/adminhtml/tools.js for Base64 encoding
define([
    "jquery",
    "mage/mage",
    "prototype",
    "mage/adminhtml/form",
    "mage/adminhtml/events" 
], function(jQuery){


window.varienGrid = new Class.create();

varienGrid.prototype = {
    initialize:function (containerId, url, pageVar, sortVar, dirVar, filterVar) {
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
    initGrid:function () {
        if (this.preInitCallback) {
            this.preInitCallback(this);
        }
        if ($(this.containerId + this.tableSufix)) {
            this.rows = $$('#' + this.containerId + this.tableSufix + ' tbody tr');
            for (var row = 0; row < this.rows.length; row++) {
                if (row % 2 == 0) {
                    Element.addClassName(this.rows[row], 'even');
                }

                Event.observe(this.rows[row], 'mouseover', this.trOnMouseOver);
                Event.observe(this.rows[row], 'mouseout', this.trOnMouseOut);
                Event.observe(this.rows[row], 'click', this.trOnClick);
                Event.observe(this.rows[row], 'dblclick', this.trOnDblClick);
            }
        }
        if (this.sortVar && this.dirVar) {
            var columns = $$('#' + this.containerId + this.tableSufix + ' thead a');

            for (var col = 0; col < columns.length; col++) {
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
                if (window.console) {
                    console.log(e);
                }
            }
        }
        jQuery('#' + this.containerId).trigger('gridinit', this)
    },
    initGridAjax:function () {
        this.initGrid();
        this.initGridRows();
    },
    initGridRows:function () {
        if (this.initRowCallback) {
            for (var row = 0; row < this.rows.length; row++) {
                try {
                    this.initRowCallback(this, this.rows[row]);
                } catch (e) {
                    if (window.console) {
                        console.log(e);
                    }
                }
            }
        }
    },
    rowMouseOver:function (event) {
        var element = Event.findElement(event, 'tr');

        if (!element.title) return;

        Element.addClassName(element, 'on-mouse');

        if (!Element.hasClassName('pointer')
            && (this.rowClickCallback !== openGridRow || element.title)) {
            if (element.title) {
                Element.addClassName(element, 'pointer');
            }
        }
    },
    rowMouseOut:function (event) {
        var element = Event.findElement(event, 'tr');
        Element.removeClassName(element, 'on-mouse');
    },
    rowMouseClick:function (event) {
        if (this.rowClickCallback) {
            try {
                this.rowClickCallback(this, event);
            }
            catch (e) {
            }
        }
        varienGlobalEvents.fireEvent('gridRowClick', event);
    },
    rowMouseDblClick:function (event) {
        varienGlobalEvents.fireEvent('gridRowDblClick', event);
    },
    keyPress:function (event) {

    },
    doSort:function (event) {
        var element = Event.findElement(event, 'a');

        if (element.name && element.title) {
            this.addVarToUrl(this.sortVar, element.name);
            this.addVarToUrl(this.dirVar, element.title);
            this.reload(this.url);
        }
        Event.stop(event);
        return false;
    },
    loadByElement:function (element) {
        if (element && element.name) {
            this.reload(this.addVarToUrl(element.name, element.value));
        }
    },

    _onAjaxSeccess: function(data, textStatus, transport) {
        try {
            var responseText = transport.responseText;

            if (transport.responseText.isJSON()) {
                var response = transport.responseText.evalJSON()
                if (response.error) {
                    alert(response.message);
                }
                if (response.ajaxExpired && response.ajaxRedirect) {
                    setLocation(response.ajaxRedirect);
                }
            } else {
                /**
                 * For IE <= 7.
                 * If there are two elements, and first has name, that equals id of second.
                 * In this case, IE will choose one that is above
                 *
                 * @see https://prototype.lighthouseapp.com/projects/8886/tickets/994-id-selector-finds-elements-by-name-attribute-in-ie7
                 */
                var divId = $(this.containerId);
                if (divId.id == this.containerId) {
                    divId.update(responseText);
                } else {
                    $$('div[id="' + this.containerId + '"]')[0].update(responseText);
                }
            }
        } catch (e) {
            var divId = $(this.containerId);
            if (divId.id == this.containerId) {
                divId.update(responseText);
            } else {
                $$('div[id="' + this.containerId + '"]')[0].update(responseText);
            }
        }
        jQuery('#' + this.containerId).trigger('contentUpdated');
    },

    reload:function (url, onSuccessCallback) {
        this.reloadParams = this.reloadParams || {};
        this.reloadParams.form_key = FORM_KEY;
        url = url || this.url;
        if (this.useAjax) {
            var ajaxSettings = {
                url: url + (url.match(new RegExp('\\?')) ? '&ajax=true' : '?ajax=true' ),
                showLoader: true,
                method: 'post',
                context: jQuery('#' + this.containerId),
                data: this.reloadParams,
                error: this._processFailure.bind(this),
                complete: this.initGridAjax.bind(this),
                dataType: 'html',
                success: function(data, textStatus, transport) {
                    this._onAjaxSeccess(data, textStatus, transport);
                    if (onSuccessCallback && typeof(onSuccessCallback) === "function") {
                        // execute the callback, passing parameters as necessary
                        onSuccessCallback();
                    }
                }.bind(this)
            };
            jQuery('#' + this.containerId).trigger('gridajaxsettings', ajaxSettings);
            var ajaxRequest = jQuery.ajax(ajaxSettings);
            jQuery('#' + this.containerId).trigger('gridajax', ajaxRequest);
            return ajaxRequest;
        } else {
            if (this.reloadParams) {
                $H(this.reloadParams).each(function(pair) {
                    url = this.addVarToUrl(pair.key, pair.value);
                }.bind(this));
            }
            location.href = url;
        }
    },
    /*_processComplete : function(transport){
        console.log(transport);
        if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        if (response.ajaxExpired && response.ajaxRedirect) {
            location.href = response.ajaxRedirect;
            return false;
        }
        this.initGrid();
    },*/
    _processFailure:function (transport) {
        location.href = BASE_URL;
    },
    _addVarToUrl:function (url, varName, varValue) {
        var re = new RegExp('\/(' + varName + '\/.*?\/)');
        var parts = url.split(new RegExp('\\?'));
        url = parts[0].replace(re, '/');
        url += varName + '/' + varValue + '/';
        if (parts.size() > 1) {
            url += '?' + parts[1];
        }
        return url;
    },
    addVarToUrl:function (varName, varValue) {
        this.url = this._addVarToUrl(this.url, varName, varValue);
        return this.url;
    },
    doExport : function(){
        if($(this.containerId+'_export')){
            var exportUrl = $(this.containerId+'_export').value;
            if(this.massaction && this.massaction.checkedString) {
                exportUrl = this._addVarToUrl(exportUrl, this.massaction.formFieldNameInternal, this.massaction.checkedString);
            }
            location.href = exportUrl;
        }
    },
    bindFilterFields : function(){
        var filters = $$('#'+this.containerId+' .filter input', '#'+this.containerId+' .filter select');
        for (var i=0; i<filters.length; i++) {
            Event.observe(filters[i],'keypress',this.filterKeyPress.bind(this));
        }
    },
    bindFieldsChange : function(){
        if (!$(this.containerId)) {
            return;
        }
//        var dataElements = $(this.containerId+this.tableSufix).down('.data tbody').select('input', 'select');
        var dataElements = $(this.containerId+this.tableSufix).down('tbody').select('input', 'select');
        for(var i=0; i<dataElements.length;i++){
            Event.observe(dataElements[i], 'change', dataElements[i].setHasChanges.bind(dataElements[i]));
        }
    },
    bindSortable: function(){
        if (jQuery('#' + this.containerId).find('.draggable-handle').length) {
            jQuery('#' + this.containerId).find('tbody').sortable({
                axis: 'y',
                handle: '.draggable-handle',
                helper: function(event, ui) {
                    ui.children().each(function() {
                        jQuery(this).width(jQuery(this).width());
                    });
                    return ui;
                },
                update: this.sortableUpdateCallback ? this.sortableUpdateCallback : function(){},
                tolerance: 'pointer'
            });
        }
    },
    filterKeyPress:function (event) {
        if (event.keyCode == Event.KEY_RETURN) {
            this.doFilter();
        }
    },
    doFilter:function (callback) {
        var filters = $$('#' + this.containerId + ' .filter input', '#' + this.containerId + ' .filter select');
        var elements = [];
        for (var i in filters) {
            if (filters[i].value && filters[i].value.length) elements.push(filters[i]);
        }
        if (!this.doFilterCallback || (this.doFilterCallback && this.doFilterCallback())) {
            this.reload(this.addVarToUrl(this.filterVar, Base64.encode(Form.serializeElements(elements))), callback);
        }
    },
    resetFilter:function (callback) {
        this.reload(this.addVarToUrl(this.filterVar, ''), callback);
    },
    checkCheckboxes:function (element) {
        elements = Element.select($(this.containerId), 'input[name="' + element.name + '"]');
        for (var i = 0; i < elements.length; i++) {
            this.setCheckboxChecked(elements[i], element.checked);
        }
    },
    setCheckboxChecked:function (element, checked) {
        element.checked = checked;
        jQuery(element).trigger('change');
        element.setHasChanges({});
        if (this.checkboxCheckCallback) {
            this.checkboxCheckCallback(this, element, checked);
        }
    },
    inputPage:function (event, maxNum) {
        var element = Event.element(event);
        var keyCode = event.keyCode || event.which;
        if (keyCode == Event.KEY_RETURN) {
            this.setPage(element.value);
        }
        /*if(keyCode>47 && keyCode<58){

         }
         else{
         Event.stop(event);
         }*/
    },
    setPage:function (pageNumber) {
        this.reload(this.addVarToUrl(this.pageVar, pageNumber));
    }
};

function openGridRow(grid, event){
    var element = Event.findElement(event, 'tr');
    if(['a', 'input', 'select', 'option'].indexOf(Event.element(event).tagName.toLowerCase())!=-1) {
        return;
    }

    if(element.title){
        setLocation(element.title);
    }
}
window.openGridRow = openGridRow;

window.varienGridMassaction = Class.create();
varienGridMassaction.prototype = {
    /* Predefined vars */
    checkedValues: $H({}),
    checkedString: '',
    oldCallbacks: {},
    errorText:'',
    items: {},
    gridIds: [],
    useSelectAll: false,
    currentItem: false,
    lastChecked: { left: false, top: false, checkbox: false },
    fieldTemplate: new Template('<input type="hidden" name="#{name}" value="#{value}" />'),
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
    setUseAjax: function(flag) {
        this.useAjax = flag;
    },
    setUseSelectAll: function(flag) {
        this.useSelectAll = flag;
    },
    initMassactionElements: function() {
        this.container      = $(this.containerId);
        this.count          = $(this.containerId + '-count');
        this.formHiddens    = $(this.containerId + '-form-hiddens');
        this.formAdditional = $(this.containerId + '-form-additional');
        this.select         = $(this.containerId + '-select');
        this.form           = this.prepareForm();
        jQuery(this.form).mage('validation');
        this.select.observe('change', this.onSelectChange.bindAsEventListener(this));
        this.lastChecked    = { left: false, top: false, checkbox: false };
        this.initMassSelect();
    },
    prepareForm: function() {
        var form = $(this.containerId + '-form'), formPlace = null,
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
            formPlace = formElement.parentNode.parentNode;
            formPlace.parentNode.appendChild(form);
            form.appendChild(formPlace);
        }

        return form;
    },
    setGridIds: function(gridIds) {
        this.gridIds = gridIds;
        this.updateCount();
    },
    getGridIds: function() {
        return this.gridIds;
    },
    setItems: function(items) {
        this.items = items;
        this.updateCount();
    },
    getItems: function() {
        return this.items;
    },
    getItem: function(itemId) {
        if(this.items[itemId]) {
            return this.items[itemId];
        }
        return false;
    },
    getOldCallback: function (callbackName) {
        return this.oldCallbacks[callbackName] ? this.oldCallbacks[callbackName] : Prototype.emptyFunction;
    },
    setOldCallback: function (callbackName, callback) {
        this.oldCallbacks[callbackName] = callback;
    },
    onGridPreInit: function(grid) {
        this.initMassactionElements();
        this.getOldCallback('pre_init')(grid);
    },
    onGridInit: function(grid) {
        this.initCheckboxes();
        this.checkCheckboxes();
        this.updateCount();
        this.getOldCallback('init')(grid);
    },
    onGridRowInit: function(grid, row) {
        this.getOldCallback('init_row')(grid, row);
    },
    onGridRowClick: function(grid, evt) {
        var tdElement = Event.findElement(evt, 'td');
        var trElement = Event.findElement(evt, 'tr');

        if(!$(tdElement).down('input')) {
            if($(tdElement).down('a') || $(tdElement).down('select')) {
                return;
            }
            if (trElement.title && trElement.title.strip() != '#') {
                this.getOldCallback('row_click')(grid, evt);
            }
            else{
                var checkbox = Element.select(trElement, 'input');
                var isInput  = Event.element(evt).tagName == 'input';
                var checked = isInput ? checkbox[0].checked : !checkbox[0].checked;

                if(checked) {
                    this.checkedString = varienStringArray.add(checkbox[0].value, this.checkedString);
                } else {
                    this.checkedString = varienStringArray.remove(checkbox[0].value, this.checkedString);
                }
                this.grid.setCheckboxChecked(checkbox[0], checked);
                this.updateCount();
            }
            return;
        }

        if(Event.element(evt).isMassactionCheckbox) {
           this.setCheckbox(Event.element(evt));
        } else if (checkbox = this.findCheckbox(evt)) {
           checkbox.checked = !checkbox.checked;
           jQuery(checkbox).trigger('change');
           this.setCheckbox(checkbox);
        }
    },
    onSelectChange: function(evt) {
        var item = this.getSelectedItem();
        if(item) {
            this.formAdditional.update($(this.containerId + '-item-' + item.id + '-block').innerHTML);
        } else {
            this.formAdditional.update('');
        }
        jQuery(this.form).data('validator').resetForm();
    },
    findCheckbox: function(evt) {
        if(['a', 'input', 'select'].indexOf(Event.element(evt).tagName.toLowerCase())!==-1) {
            return false;
        }
        checkbox = false;
        Event.findElement(evt, 'tr').select('.massaction-checkbox').each(function(element){
            if(element.isMassactionCheckbox) {
                checkbox = element;
            }
        }.bind(this));
        return checkbox;
    },
    initCheckboxes: function() {
        this.getCheckboxes().each(function(checkbox) {
           checkbox.isMassactionCheckbox = true;
        }.bind(this));
    },
    checkCheckboxes: function() {
        this.getCheckboxes().each(function(checkbox) {
            checkbox.checked = varienStringArray.has(checkbox.value, this.checkedString);
            jQuery(checkbox).trigger('change');
        }.bind(this));
    },
    selectAll: function() {
        this.setCheckedValues((this.useSelectAll ? this.getGridIds() : this.getCheckboxesValuesAsString()));
        this.checkCheckboxes();
        this.updateCount();
        this.clearLastChecked();
        return false;
    },
    unselectAll: function() {
        this.setCheckedValues('');
        this.checkCheckboxes();
        this.updateCount();
        this.clearLastChecked();
        return false;
    },
    selectVisible: function() {
        this.setCheckedValues(this.getCheckboxesValuesAsString());
        this.checkCheckboxes();
        this.updateCount();
        this.clearLastChecked();
        return false;
    },
    unselectVisible: function() {
        this.getCheckboxesValues().each(function(key){
            this.checkedString = varienStringArray.remove(key, this.checkedString);
        }.bind(this));
        this.checkCheckboxes();
        this.updateCount();
        this.clearLastChecked();
        return false;
    },
    setCheckedValues: function(values) {
        this.checkedString = values;
    },
    getCheckedValues: function() {
        return this.checkedString;
    },
    getCheckboxes: function() {
        var result = [];
        this.grid.rows.each(function(row){
            var checkboxes = row.select('.massaction-checkbox');
            checkboxes.each(function(checkbox){
                result.push(checkbox);
            });
        });
        return result;
    },
    getCheckboxesValues: function() {
        var result = [];
        this.getCheckboxes().each(function(checkbox) {
            result.push(checkbox.value);
        }.bind(this));
        return result;
    },
    getCheckboxesValuesAsString: function() {
        return this.getCheckboxesValues().join(',');
    },
    setCheckbox: function(checkbox) {
        if(checkbox.checked) {
            this.checkedString = varienStringArray.add(checkbox.value, this.checkedString);
        } else {
            this.checkedString = varienStringArray.remove(checkbox.value, this.checkedString);
        }
        this.updateCount();
    },
    updateCount: function() {
        this.count.update(varienStringArray.count(this.checkedString));
        if(!this.grid.reloadParams) {
            this.grid.reloadParams = {};
        }
        this.grid.reloadParams[this.formFieldNameInternal] = this.checkedString;
    },
    getSelectedItem: function() {
        if(this.getItem(this.select.value)) {
            return this.getItem(this.select.value);
        } else {
            return false;
        }
    },
    apply: function() {
        if(varienStringArray.count(this.checkedString) == 0) {
                alert(this.errorText);
                return;
            }

        var item = this.getSelectedItem();
        if(!item) {
            jQuery(this.form).valid();
            return;
        }
        this.currentItem = item;
        var fieldName = (item.field ? item.field : this.formFieldName);
        var fieldsHtml = '';

        if(this.currentItem.confirm && !window.confirm(this.currentItem.confirm)) {
            return;
        }

        this.formHiddens.update('');
        new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({name: fieldName, value: this.checkedString}));
        new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({name: 'massaction_prepare_key', value: fieldName}));

        if(!jQuery(this.form).valid()) {
            return;
        }

        if(this.useAjax && item.url) {
            new Ajax.Request(item.url, {
                'method': 'post',
                'parameters': this.form.serialize(true),
                'onComplete': this.onMassactionComplete.bind(this)
            });
        } else if(item.url) {
            this.form.action = item.url;
            this.form.submit();
        }
    },
    onMassactionComplete: function(transport) {
        if(this.currentItem.complete) {
            try {
                var listener = this.getListener(this.currentItem.complete) || Prototype.emptyFunction;
                listener(this.grid, this, transport);
            } catch (e) {}
       }
    },
    getListener: function(strValue) {
        return eval(strValue);
    },
    initMassSelect: function() {
        $$('input[class~="massaction-checkbox"]').each(
            function(element) {
                element.observe('click', this.massSelect.bind(this));
            }.bind(this)
            );
    },
    clearLastChecked: function() {
        this.lastChecked = {
            left: false,
            top: false,
            checkbox: false
        };
    },
    massSelect: function(evt) {
        if(this.lastChecked.left !== false
            && this.lastChecked.top !== false
            && evt.button === 0
            && evt.shiftKey === true
        ) {
            var currentCheckbox = Event.element(evt);
            var lastCheckbox = this.lastChecked.checkbox;
            if (lastCheckbox != currentCheckbox) {
                var start = this.getCheckboxOrder(lastCheckbox);
                var finish = this.getCheckboxOrder(currentCheckbox);
                if (start !== false && finish !== false) {
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
    getCheckboxOrder: function(curCheckbox) {
        var order = false;
        this.getCheckboxes().each(function(checkbox, key){
            if (curCheckbox == checkbox) {
                order = key;
            }
        });
        return order;
    },
    selectCheckboxRange: function(start, finish, isChecked){
        this.getCheckboxes().each((function(checkbox, key){
            if (key >= start && key <= finish) {
                checkbox.checked = isChecked;
                this.setCheckbox(checkbox);
            }
        }).bind(this));
    }
};

window.varienGridAction = {
    execute: function(select) {
        if(!select.value || !select.value.isJSON()) {
            return;
        }

        var config = select.value.evalJSON();
        if(config.confirm && !window.confirm(config.confirm)) {
            select.options[0].selected = true;
            return;
        }

        if(config.popup) {
            var win = window.open(config.href, 'action_window', 'width=500,height=600,resizable=1,scrollbars=1');
            win.focus();
            select.options[0].selected = true;
        } else {
            setLocation(config.href);
        }
    }
};

window.varienStringArray = {
    remove: function(str, haystack)
    {
        haystack = ',' + haystack + ',';
        haystack = haystack.replace(new RegExp(',' + str + ',', 'g'), ',');
        return this.trimComma(haystack);
    },
    add: function(str, haystack)
    {
        haystack = ',' + haystack + ',';
        if (haystack.search(new RegExp(',' + str + ',', 'g'), haystack) === -1) {
            haystack += str + ',';
        }
        return this.trimComma(haystack);
    },
    has: function(str, haystack)
    {
        haystack = ',' + haystack + ',';
        if (haystack.search(new RegExp(',' + str + ',', 'g'), haystack) === -1) {
            return false;
        }
        return true;
    },
    count: function(haystack)
    {
        if (typeof haystack != 'string') {
            return 0;
        }
        if (match = haystack.match(new RegExp(',', 'g'))) {
            return match.length + 1;
        } else if (haystack.length != 0) {
            return 1;
        }
        return 0;
    },
    each: function(haystack, fnc)
    {
        var haystack = haystack.split(',');
        for (var i=0; i<haystack.length; i++) {
            fnc(haystack[i]);
        }
    },
    trimComma: function(string)
    {
        string = string.replace(new RegExp('^(,+)','i'), '');
        string = string.replace(new RegExp('(,+)$','i'), '');
        return string;
    }
};

window.serializerController = Class.create();
serializerController.prototype = {
    oldCallbacks: {},
    initialize: function(hiddenDataHolder, predefinedData, inputsToManage, grid, reloadParamName){
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
        this.grid.reloadParams[this.reloadParamName+'[]'] = this.getDataForReloadParam();
        this.grid.rowClickCallback = this.rowClick.bind(this);
        this.grid.initRowCallback = this.rowInit.bind(this);
        this.grid.checkboxCheckCallback = this.registerData.bind(this);
        this.grid.rows.each(this.eachRow.bind(this));
    },
    setOldCallback: function (callbackName, callback) {
        this.oldCallbacks[callbackName] = callback;
    },
    getOldCallback: function (callbackName) {
        return this.oldCallbacks[callbackName] ? this.oldCallbacks[callbackName] : Prototype.emptyFunction;
    },
    registerData : function(grid, element, checked) {
        if(this.multidimensionalMode){
            if(checked){
                 if(element.inputElements) {
                     this.gridData.set(element.value, {});
                     for(var i = 0; i < element.inputElements.length; i++) {
                         element.inputElements[i].disabled = false;
                         this.gridData.get(element.value)[element.inputElements[i].name] = element.inputElements[i].value;
                     }
                 }
            }
            else{
                if(element.inputElements){
                    for(var i = 0; i < element.inputElements.length; i++) {
                        element.inputElements[i].disabled = true;
                    }
                }
                this.gridData.unset(element.value);
            }
        }
        else{
            if(checked){
                this.gridData.set(element.value, element.value);
            }
            else{
                this.gridData.unset(element.value);
            }
        }

        this.hiddenDataHolder.value = this.serializeObject();
        this.grid.reloadParams = {};
        this.grid.reloadParams[this.reloadParamName+'[]'] = this.getDataForReloadParam();
        this.getOldCallback('checkbox_check')(grid, element, checked);
    },
    eachRow : function(row) {
        this.rowInit(this.grid, row);
    },
    rowClick : function(grid, event) {
        var trElement = Event.findElement(event, 'tr');
        var isInput   = Event.element(event).tagName == 'INPUT';
        if(trElement){
            var checkbox = Element.select(trElement, 'input');
            if(checkbox[0] && !checkbox[0].disabled){
                var checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                this.grid.setCheckboxChecked(checkbox[0], checked);
            }
        }
        this.getOldCallback('row_click')(grid, event);
    },
    inputChange : function(event) {
        var element = Event.element(event);
        if(element && element.checkboxElement && element.checkboxElement.checked){
            this.gridData.get(element.checkboxElement.value)[element.name] = element.value;
            this.hiddenDataHolder.value = this.serializeObject();
        }
    },
    rowInit : function(grid, row) {
        if(this.multidimensionalMode){
            var checkbox = $(row).select('.checkbox')[0];
            var selectors = this.inputsToManage.map(function (name) { return ['input[name="' + name + '"]', 'select[name="' + name + '"]']; });
            var inputs = $(row).select.apply($(row), selectors.flatten());
            if(checkbox && inputs.length > 0) {
                checkbox.inputElements = inputs;
                for(var i = 0; i < inputs.length; i++) {
                    inputs[i].checkboxElement = checkbox;
                    if(this.gridData.get(checkbox.value) && this.gridData.get(checkbox.value)[inputs[i].name]) {
                        inputs[i].value = this.gridData.get(checkbox.value)[inputs[i].name];
                    }
                    inputs[i].disabled = !checkbox.checked;
                    inputs[i].tabIndex = this.tabIndex++;
                    Event.observe(inputs[i],'keyup', this.inputChange.bind(this));
                    Event.observe(inputs[i],'change', this.inputChange.bind(this));
                }
            }
        }
        this.getOldCallback('init_row')(grid, row);
    },

    //Stuff methods
    getGridDataHash: function (_object){
        return $H(this.multidimensionalMode ? _object : this.convertArrayToObject(_object))
    },
    getDataForReloadParam: function(){
        return this.multidimensionalMode ? this.gridData.keys() : this.gridData.values();
    },
    serializeObject: function(){
        if(this.multidimensionalMode){
            var clone = this.gridData.clone();
            clone.each(function(pair) {
                clone.set(pair.key, Base64.encode(Object.toQueryString(pair.value)));
            });
            return clone.toQueryString();
        }
        else{
            return this.gridData.values().join('&');
        }
    },
    convertArrayToObject: function (_array){
        var _object = {};
        for(var i = 0, l = _array.length; i < l; i++){
            _object[_array[i]] = _array[i];
        }
        return _object;
    }
};

});