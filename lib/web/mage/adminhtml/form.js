/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    "jquery",
    "prototype",
    "mage/adminhtml/events"
], function(jQuery){
/*
 * @TODO Need to be removed after refactoring all dependent of the form the components
 */
(function($) {
    $(document).ready(function() {
        $(document).on("beforeSubmit", function(e) {
            if (typeof varienGlobalEvents !== 'undefined') {
                varienGlobalEvents.fireEvent("formSubmit", $(e.target).attr('id'));
            }
        });
    });
})(jQuery);


/**
 *  Additional elements methods
 */
var varienElementMethods = {
    setHasChanges : function(element, event){
        if($(element) && $(element).hasClassName('no-changes')) return;
        var elm = element;
        while(elm && elm.tagName != 'BODY') {
            if(elm.statusBar)
                Element.addClassName($(elm.statusBar), 'changed')
            elm = elm.parentNode;
        }
    },
    setHasError : function(element, flag, form){
        var elm = element;
        while(elm && elm.tagName != 'BODY') {
            if(elm.statusBar){
                if(form.errorSections.keys().indexOf(elm.statusBar.id)<0)
                    form.errorSections.set(elm.statusBar.id, flag);
                if(flag){
                    Element.addClassName($(elm.statusBar), 'error');
                    if(form.canShowError && $(elm.statusBar).show){
                        form.canShowError = false;
                        $(elm.statusBar).show();
                    }
                    form.errorSections.set(elm.statusBar.id, flag);
                }
                else if(!form.errorSections.get(elm.statusBar.id)){
                    Element.removeClassName($(elm.statusBar), 'error')
                }
            }
            elm = elm.parentNode;
        }
        this.canShowElement = false;
    }
}

Element.addMethods(varienElementMethods);

// Global bind changes
varienWindowOnloadCache = {};
function varienWindowOnload(useCache){
    var dataElements = $$('input', 'select', 'textarea');
    for(var i=0; i<dataElements.length;i++){
        if(dataElements[i] && dataElements[i].id){
            if ((!useCache) || (!varienWindowOnloadCache[dataElements[i].id])) {
                Event.observe(dataElements[i], 'change', dataElements[i].setHasChanges.bind(dataElements[i]));
                if (useCache) {
                    varienWindowOnloadCache[dataElements[i].id] = true;
                }
            }
        }
    }
}
Event.observe(window, 'load', varienWindowOnload);

RegionUpdater = Class.create();
RegionUpdater.prototype = {
    initialize: function (countryEl, regionTextEl, regionSelectEl, regions, disableAction, clearRegionValueOnDisable)
    {
        this.isRegionRequired = true;
        this.countryEl = $(countryEl);
        this.regionTextEl = $(regionTextEl);
        this.regionSelectEl = $(regionSelectEl);
        this.config = regions['config'];
        delete regions.config;
        this.regions = regions;
        this.disableAction = (typeof disableAction=='undefined') ? 'hide' : disableAction;
        this.clearRegionValueOnDisable = (typeof clearRegionValueOnDisable == 'undefined') ? false : clearRegionValueOnDisable;

        if (this.regionSelectEl.options.length<=1) {
            this.update();
        }
        else {
            this.lastCountryId = this.countryEl.value;
        }

        this.countryEl.changeUpdater = this.update.bind(this);

        Event.observe(this.countryEl, 'change', this.update.bind(this));
    },

    _checkRegionRequired: function()
    {
        if (!this.isRegionRequired) {
            return;
        }

        var label, wildCard;
        var elements = [this.regionTextEl, this.regionSelectEl];
        var that = this;
        if (typeof this.config == 'undefined') {
            return;
        }
        var regionRequired = this.config.regions_required.indexOf(this.countryEl.value) >= 0;

        elements.each(function(currentElement) {
            if(!currentElement) {
                return;
            }
            var form = currentElement.form,
                validationInstance = form ? jQuery(form).data('validation') : null,
                field = currentElement.up('.field') || new Element('div');

            if (validationInstance) {
                validationInstance.clearError(currentElement);
            }
            label = $$('label[for="' + currentElement.id + '"]')[0];
            if (label) {
                wildCard = label.down('em') || label.down('span.required');
                var topElement = label.up('tr') || label.up('li');
                if (!that.config.show_all_regions && topElement) {
                    if (regionRequired) {
                        topElement.show();
                    } else {
                        topElement.hide();
                    }
                }
            }

            if (label && wildCard) {
                if (!regionRequired) {
                    wildCard.hide();
                } else {
                    wildCard.show();
                }
            }

            //compute the need for the required fields
            if (!regionRequired || !currentElement.visible()) {
                if (field.hasClassName('required')) {
                    field.removeClassName('required');
                }
                if (currentElement.hasClassName('required-entry')) {
                    currentElement.removeClassName('required-entry');
                }
                if ('select' == currentElement.tagName.toLowerCase() &&
                    currentElement.hasClassName('validate-select')
                ) {
                    currentElement.removeClassName('validate-select');
                }
            } else {
                if (!field.hasClassName('required')) {
                    field.addClassName('required');
                }
                if (!currentElement.hasClassName('required-entry')) {
                    currentElement.addClassName('required-entry');
                }
                if ('select' == currentElement.tagName.toLowerCase() &&
                    !currentElement.hasClassName('validate-select')
                ) {
                    currentElement.addClassName('validate-select');
                }
            }
        });
    },

    disableRegionValidation: function()
    {
        this.isRegionRequired = false;
    },

    update: function()
    {
        if (this.regions[this.countryEl.value]) {

            if (this.lastCountryId!=this.countryEl.value) {
                var i, option, region, def;

                def = this.regionSelectEl.getAttribute('defaultValue');
                if (this.regionTextEl) {
                    if (!def) {
                        def = this.regionTextEl.value.toLowerCase();
                    }
                    this.regionTextEl.value = '';
                }

                this.regionSelectEl.options.length = 1;
                for (regionId in this.regions[this.countryEl.value]) {
                    region = this.regions[this.countryEl.value][regionId];

                    option = document.createElement('OPTION');
                    option.value = regionId;
                    option.text = region.name.stripTags();
                    option.title = region.name;

                    if (this.regionSelectEl.options.add) {
                        this.regionSelectEl.options.add(option);
                    } else {
                        this.regionSelectEl.appendChild(option);
                    }

                    if (regionId==def || region.name.toLowerCase()==def || region.code.toLowerCase()==def) {
                        this.regionSelectEl.value = regionId;
                    }
                }
            }

            if (this.disableAction=='hide') {
                if (this.regionTextEl) {
                    this.regionTextEl.style.display = 'none';
                    this.regionTextEl.style.disabled = true;
                }
                this.regionSelectEl.style.display = '';
                this.regionSelectEl.disabled = false;
            } else if (this.disableAction=='disable') {
                if (this.regionTextEl) {
                    this.regionTextEl.disabled = true;
                }
                this.regionSelectEl.disabled = false;
            }
            this.setMarkDisplay(this.regionSelectEl, true);

            this.lastCountryId = this.countryEl.value;
        } else {
            if (this.disableAction=='hide') {
                if (this.regionTextEl) {
                    this.regionTextEl.style.display = '';
                    this.regionTextEl.style.disabled = false;
                }
                this.regionSelectEl.style.display = 'none';
                this.regionSelectEl.disabled = true;
            } else if (this.disableAction=='disable') {
                if (this.regionTextEl) {
                    this.regionTextEl.disabled = false;
                }
                this.regionSelectEl.disabled = true;
                if (this.clearRegionValueOnDisable) {
                    this.regionSelectEl.value = '';
                }
            } else if (this.disableAction=='nullify') {
                this.regionSelectEl.options.length = 1;
                this.regionSelectEl.value = '';
                this.regionSelectEl.selectedIndex = 0;
                this.lastCountryId = '';
            }
            this.setMarkDisplay(this.regionSelectEl, false);

//            // clone required stuff from select element and then remove it
//            this._regionSelectEl.className = this.regionSelectEl.className;
//            this._regionSelectEl.name      = this.regionSelectEl.name;
//            this._regionSelectEl.id        = this.regionSelectEl.id;
//            this._regionSelectEl.innerHTML = this.regionSelectEl.innerHTML;
//            Element.remove(this.regionSelectEl);
//            this.regionSelectEl = null;
        }
        varienGlobalEvents.fireEvent("address_country_changed", this.countryEl);
        this._checkRegionRequired();
    },

    setMarkDisplay: function(elem, display){
        if(elem.parentNode.parentNode){
            var marks = Element.select(elem.parentNode.parentNode, '.required');
            if(marks[0]){
                display ? marks[0].show() : marks[0].hide();
            }
        }
    }
};

regionUpdater = RegionUpdater;

/**
 * Fix errorrs in IE
 */
Event.pointerX = function(event){
    try{
        return event.pageX || (event.clientX +(document.documentElement.scrollLeft || document.body.scrollLeft));
    }
    catch(e){

    }
}
Event.pointerY = function(event){
    try{
        return event.pageY || (event.clientY +(document.documentElement.scrollTop || document.body.scrollTop));
    }
    catch(e){

    }
}

/**
 * Observer that watches for dependent form elements
 * If an element depends on 1 or more of other elements, it should show up only when all of them gain specified values
 */
FormElementDependenceController = Class.create();
FormElementDependenceController.prototype = {
    /**
     * Structure of elements: {
     *     'id_of_dependent_element' : {
     *         'id_of_master_element_1' : 'reference_value',
     *         'id_of_master_element_2' : 'reference_value'
     *         'id_of_master_element_3' : ['reference_value1', 'reference_value2']
     *         ...
     *     }
     * }
     * @param object elementsMap
     * @param object config
     */
    initialize : function (elementsMap, config)
    {
        if (config) {
            this._config = config;
        }
        for (var idTo in elementsMap) {
            for (var idFrom in elementsMap[idTo]) {
                if ($(idFrom)) {
                    Event.observe($(idFrom), 'change', this.trackChange.bindAsEventListener(this, idTo, elementsMap[idTo]));
                    this.trackChange(null, idTo, elementsMap[idTo]);
                } else {
                    this.trackChange(null, idTo, elementsMap[idTo]);
                }
            }
        }
    },

    /**
     * Misc. config options
     * Keys are underscored intentionally
     */
    _config : {
        levels_up : 1 // how many levels up to travel when toggling element
    },

    /**
     * Define whether target element should be toggled and show/hide its row
     *
     * @param object e - event
     * @param string idTo - id of target element
     * @param valuesFrom - ids of master elements and reference values
     * @return
     */
    trackChange : function(e, idTo, valuesFrom)
    {
        // define whether the target should show up
        var shouldShowUp = true;
        for (var idFrom in valuesFrom) {
            var from = $(idFrom);
            if (from) {
                var values = valuesFrom[idFrom]['values'];
                var isInArray = values.indexOf(from.value) != -1;
                var isNegative = valuesFrom[idFrom]['negative'];
                if (!from || isInArray && isNegative || !isInArray && !isNegative) {
                    shouldShowUp = false;
                }
            }
        }

        // toggle target row
        var headElement = $(idTo + '-head'),
            isInheritCheckboxChecked = $(idTo + '_inherit') && $(idTo + '_inherit').checked,
            target = $(idTo);

        // Target won't always exist (for example, if field type is "label")
        if (target) {
            var inputs = target.up(this._config.levels_up).select('input', 'select', 'td'),
                isAnInputOrSelect = ['input', 'select'].indexOf(target.tagName.toLowerCase()) != -1;
        } else {
            var inputs = false,
                isAnInputOrSelect = false;
        }
        if (shouldShowUp) {
            var currentConfig = this._config;
            if (inputs) {
                inputs.each(function (item) {
                    // don't touch hidden inputs (and Use Default inputs too), bc they may have custom logic
                    if ((!item.type || item.type != 'hidden') && !($(item.id+'_inherit') && $(item.id+'_inherit').checked)
                        && !(currentConfig.can_edit_price != undefined && !currentConfig.can_edit_price)) {
                        item.disabled = false;
                        jQuery(item).removeClass('ignore-validate');
                    }
                });
            }
            if (headElement) {
                headElement.show();
                if (headElement.hasClassName('open') && target) {
                    target.show();
                } else if (target) {
                    target.hide();
                }
            } else {
                if (target) {
                    target.show();
                }
                if (isAnInputOrSelect && !isInheritCheckboxChecked) {
                    if (target) {
                        target.disabled = false;
                    }
                    jQuery('#' + idTo).removeClass('ignore-validate');
                }
            }
        } else {
            if (inputs) {
                inputs.each(function (item){
                    // don't touch hidden inputs (and Use Default inputs too), bc they may have custom logic
                    if ((!item.type || item.type != 'hidden') && !($(item.id+'_inherit') && $(item.id+'_inherit').checked)) {
                        item.disabled = true;
                        jQuery(item).addClass('ignore-validate');
                    }
                });
            }
            if (headElement) {
                headElement.hide();
            }
            if (target) {
                target.hide();
            }
            if (isAnInputOrSelect && !isInheritCheckboxChecked) {
                if (target) {
                    target.disabled = true;
                }
                jQuery('#' + idTo).addClass('ignore-validate');
            }

        }
        var rowElement = $('row_' + idTo);
        if (rowElement == undefined && target) {
            rowElement = target.up(this._config.levels_up);
        }
        if (rowElement) {
            if (shouldShowUp) {
                rowElement.show();
            } else {
                rowElement.hide();
            }
        }
    }
};


    window.varienWindowOnload = varienWindowOnload;
    window.varienElementMethods = varienElementMethods;
});
