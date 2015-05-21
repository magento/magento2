/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "jquery/ui"
], function ($) {
    "use strict";

    $.widget('mage.step-wizard', $.ui.tabs, {
        defaultStep: {
            force: function() {},
            back: function() {}
        },
        options: {
            collapsible: false,
            disabled: [],
            event: "click",
            buttonNextElement: '[data-role="step-wizard-next"]',
            buttonPrevElement: '[data-role="step-wizard-prev"]',
            selectorScript: '[data-handler="step-wizard"]'
        },
        _create: function() {
            this._control();
            this._super();
        },
        _control: function() {
            var self = this;
            this.element.find(this.options.buttonNextElement).on('click', function(event){
                self._activate(self.options.active + 1);
            });
            this.element.find(this.options.buttonPrevElement).on('click', function(event){
                self._activate(self.options.active - 1);
            });
        },
        load: function(index, event) {
            this._disabledTabs(index);
            this._handlerStep(index);
            this._super(index, event);
        },
        _handlerStep: function (index) {
            var script = this.panels.eq(index).find(this.options.selectorScript).first();
            if (script.text()) {
                eval(script.text());
                this.step = step;
            } else {
                this.step = this.defaultStep;
            }
            this.step[this._way(index)]();
        },
        _way: function(index) {
            return this.options.selected > index ? 'back' : 'force';
        },
        _disabledTabs: function(index) {
            var disabled = [];
            for(var i=0; this.tabs.length >= i; i++) {
                if([index, index + 1, index - 1].indexOf(i) > -1) {
                    continue;
                }
                disabled.push(i);
            }
            this._setupDisabled(disabled);
        }
    });

    return $.mage["step-wizard"];
});
