/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "jquery/ui"
], function ($) {

    $.widget('mage.step-wizard', $.ui.tabs, {
        defaultStep: {
            force: function() {},
            back: function() {}
        },
        options: {
            collapsible: false,
            disabled: [],
            event: "click"
        },
        _create: function() {
            this._dialog();
            this._control();
            this.options.active = this.options.active >= 0 ? this.options.active : 0;
            this._super();
        },
        _dialog: function() {
            var dialog = this.element.parent();
            $('#dialog').dialog();
        },
        _control: function() {
            var self = this;
            this.element.find('.btn-wrap-next').on('click', function(event){
                self._activate(self.options.active + 1);
            });
            this.element.find('.btn-wrap-prev').on('click', function(event){
                self._activate(self.options.active - 1);
            })
        },
        load: function(index, event) {
            this._disabledTabs(index);
            this._handlerStep(index);
            this._super(index, event);
        },
        _handlerStep: function (index) {
            var script = this.panels.eq(index).find('[data-handler="step-wizard"]').first();
            if(script.text()) {
                var code = eval(script.text());
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
