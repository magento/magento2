/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable */
/* jscs:disable */
define([
    "jquery",
    'Magento_Ui/js/modal/alert',
    "mage/translate",
    "prototype"
], function(jQuery){

    var VarienRulesForm = new Class.create();

    VarienRulesForm.prototype = {
        initialize : function(parent, newChildUrl){
            this.parent = $(parent);
            this.newChildUrl  = newChildUrl;
            this.shownElement = null;
            this.updateElement = null;
            this.chooserSelectedItems = $H({});
            this.readOnly = false;

            var elems = this.parent.getElementsByClassName('rule-param');
            for (var i=0; i<elems.length; i++) {
                this.initParam(elems[i]);
            }
        },

        setReadonly: function (readonly){
            this.readOnly = readonly;
            var elems = this.parent.getElementsByClassName('rule-param-remove');
            for (var i=0; i<elems.length; i++) {
                var element = elems[i];
                    if (this.readOnly) {
                        element.hide();
                    } else {
                        element.show();
                    }
            }

            var elems = this.parent.getElementsByClassName('rule-param-new-child');
            for (var i=0; i<elems.length; i++) {
                var element = elems[i];
                if (this.readOnly) {
                    element.hide();
                } else {
                    element.show();
                }
            }

            var elems = this.parent.getElementsByClassName('rule-param');
            for (var i=0; i<elems.length; i++) {
                var container = elems[i];
                var label = Element.down(container, '.label');
                if (label) {
                    if (this.readOnly) {
                        label.addClassName('label-disabled');
                    } else {
                        label.removeClassName('label-disabled');
                    }
                }
            }
        },

        initParam: function (container) {
            container.rulesObject = this;
            var label = Element.down(container, '.label');
            if (label) {
                Event.observe(label, 'click', this.showParamInputField.bind(this, container));
            }

            var elem = Element.down(container, '.element');
            if (elem) {
                var trig = elem.down('.rule-chooser-trigger');
                if (trig) {
                    Event.observe(trig, 'click', this.toggleChooser.bind(this, container));
                }

                var apply = elem.down('.rule-param-apply');
                if (apply) {
                    Event.observe(apply, 'click', this.hideParamInputField.bind(this, container));
                } else {
                    elem = elem.down('.element-value-changer');
                    elem.container = container;
                    if (!elem.multiple) {
                        Event.observe(elem, 'change', this.hideParamInputField.bind(this, container));
                    }
                    Event.observe(elem, 'blur', this.hideParamInputField.bind(this, container));
                }
            }

            var remove = Element.down(container, '.rule-param-remove');
            if (remove) {
                Event.observe(remove, 'click', this.removeRuleEntry.bind(this, container));
            }
        },

        showChooserElement: function (chooser) {
            this.chooserSelectedItems = $H({});
            if (chooser.hasClassName('no-split')) {
                this.chooserSelectedItems.set(this.updateElement.value, 1);
            } else {
                var values = this.updateElement.value.split(','), s = '';
                for (i=0; i<values.length; i++) {
                    s = values[i].strip();
                    if (s!='') {
                       this.chooserSelectedItems.set(s,1);
                    }
                }
            }
            new Ajax.Request(chooser.getAttribute('url'), {
                evalScripts: true,
                parameters: {'form_key': FORM_KEY, 'selected[]':this.chooserSelectedItems.keys() },
                onSuccess: function(transport) {
                    if (this._processSuccess(transport)) {
                        $(chooser).update(transport.responseText);
                        this.showChooserLoaded(chooser, transport);
                        jQuery(chooser).trigger('contentUpdated');
                    }
                }.bind(this),
                onFailure: this._processFailure.bind(this)
            });
        },

        showChooserLoaded: function(chooser, transport) {
            chooser.style.display = 'block';
        },

        showChooser: function (container, event) {
            var chooser = container.up('li');
            if (!chooser) {
                return;
            }
            chooser = chooser.down('.rule-chooser');
            if (!chooser) {
                return;
            }
            this.showChooserElement(chooser);
        },

        hideChooser: function (container, event) {
            var chooser = container.up('li');
            if (!chooser) {
                return;
            }
            chooser = chooser.down('.rule-chooser');
            if (!chooser) {
                return;
            }
            chooser.style.display = 'none';
        },

        toggleChooser: function (container, event) {
            if (this.readOnly) {
                return false;
            }

            var chooser = container.up('li').down('.rule-chooser');
            if (!chooser) {
                return;
            }
            if (chooser.style.display=='block') {
                chooser.style.display = 'none';
                this.cleanChooser(container, event);
            } else {
                this.showChooserElement(chooser);
            }
        },

        cleanChooser: function (container, event) {
            var chooser = container.up('li').down('.rule-chooser');
            if (!chooser) {
                return;
            }
            chooser.innerHTML = '';
        },

        showParamInputField: function (container, event) {
            if (this.readOnly) {
                return false;
            }

            if (this.shownElement) {
                this.hideParamInputField(this.shownElement, event);
            }

            Element.addClassName(container, 'rule-param-edit');
            var elemContainer = Element.down(container, '.element');

            var elem = Element.down(elemContainer, 'input.input-text');
            if (elem) {
                elem.focus();
                if (elem && elem.id && elem.id.match(/__value$/)) {
                    this.updateElement = elem;
                }

            }

            var elem = Element.down(elemContainer, '.element-value-changer');
            if (elem) {
               elem.focus();
            }

            this.shownElement = container;
        },

        hideParamInputField: function (container, event) {
            Element.removeClassName(container, 'rule-param-edit');
            var label = Element.down(container, '.label'), elem;

            if (!container.hasClassName('rule-param-new-child')) {
                elem = Element.down(container, '.element-value-changer');
                if (elem && elem.options) {
                    var selectedOptions = [];
                    for (i=0; i<elem.options.length; i++) {
                        if (elem.options[i].selected) {
                            selectedOptions.push(elem.options[i].text);
                        }
                    }

                    var str = selectedOptions.join(', ');
                    label.innerHTML = str!='' ? str : '...';
                }

                elem = Element.down(container, 'input.input-text');
                if (elem) {
                    var str = elem.value.replace(/(^\s+|\s+$)/g, '');
                    elem.value = str;
                    if (str=='') {
                        str = '...';
                    } else if (str.length>30) {
                        str = str.substr(0, 30)+'...';
                    }
                    label.innerHTML = str.escapeHTML();
                }
            } else {
                elem = container.down('.element-value-changer');
                if (elem.value) {
                    this.addRuleNewChild(elem);
                }
                elem.value = '';
            }

            if (elem && elem.id && elem.id.match(/__value$/)) {
                this.hideChooser(container, event);
                this.updateElement = null;
            }

            this.shownElement = null;
        },

        addRuleNewChild: function (elem) {
            var parent_id = elem.id.replace(/^.*__(.*)__.*$/, '$1');
            var children_ul_id = elem.id.replace(/__/g, ':').replace(/[^:]*$/, 'children').replace(/:/g, '__');
            var children_ul = $(this.parent).select('#' + children_ul_id)[0];
            var max_id = 0, i;
            var children_inputs = Selector.findChildElements(children_ul, $A(['input.hidden']));
            if (children_inputs.length) {
                children_inputs.each(function(el){
                    if (el.id.match(/__type$/)) {
                        i = 1 * el.id.replace(/^.*__.*?([0-9]+)__.*$/, '$1');
                        max_id = i > max_id ? i : max_id;
                    }
                });
            }
            var new_id = parent_id + '--' + (max_id + 1);
            var new_type = elem.value;
            var new_elem = document.createElement('LI');
            new_elem.className = 'rule-param-wait';
            new_elem.innerHTML = jQuery.mage.__('This won\'t take long . . .');
            children_ul.insertBefore(new_elem, $(elem).up('li'));

            new Ajax.Request(this.newChildUrl, {
                evalScripts: true,
                parameters: {form_key: FORM_KEY, type:new_type.replace('/','-'), id:new_id },
                onComplete: this.onAddNewChildComplete.bind(this, new_elem),
                onSuccess: function(transport) {
                    if(this._processSuccess(transport)) {
                        $(new_elem).update(transport.responseText);
                    }
                }.bind(this),
                onFailure: this._processFailure.bind(this)
            });
        },

        _processSuccess : function(transport) {
            if (transport.responseText.isJSON()) {
                var response = transport.responseText.evalJSON()
                if (response.error) {
                    alert(response.message);
                }
                if(response.ajaxExpired && response.ajaxRedirect) {
                    setLocation(response.ajaxRedirect);
                }
                return false;
            }
            return true;
        },

        _processFailure : function(transport) {
            location.href = BASE_URL;
        },

        onAddNewChildComplete: function (new_elem) {
            if (this.readOnly) {
                return false;
            }

            $(new_elem).removeClassName('rule-param-wait');
            var elems = new_elem.getElementsByClassName('rule-param');
            for (var i=0; i<elems.length; i++) {
                this.initParam(elems[i]);
            }
        },

        removeRuleEntry: function (container, event) {
            var li = Element.up(container, 'li');
            li.parentNode.removeChild(li);
        },

        chooserGridInit: function (grid) {
            //grid.reloadParams = {'selected[]':this.chooserSelectedItems.keys()};
        },

        chooserGridRowInit: function (grid, row) {
            if (!grid.reloadParams) {
                grid.reloadParams = {'selected[]':this.chooserSelectedItems.keys()};
            }
        },

        chooserGridRowClick: function (grid, event) {
            var trElement = Event.findElement(event, 'tr');
            var isInput = Event.element(event).tagName == 'INPUT';
            if (trElement) {
                var checkbox = Element.select(trElement, 'input');
                if (checkbox[0]) {
                    var checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                    grid.setCheckboxChecked(checkbox[0], checked);

                }
            }
        },

        chooserGridCheckboxCheck: function (grid, element, checked) {
            if (checked) {
                if (!element.up('th')) {
                    this.chooserSelectedItems.set(element.value,1);
                }
            } else {
                this.chooserSelectedItems.unset(element.value);
            }
            grid.reloadParams = {'selected[]':this.chooserSelectedItems.keys()};
            this.updateElement.value = this.chooserSelectedItems.keys().join(', ');
        }
    };

    return VarienRulesForm;
});