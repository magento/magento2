/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "jquery/ui"
], function ($) {
    "use strict";

    var getWizardBySteps = function (steps, element) {
        var deferred = new $.Deferred();
        require(steps, function () {
            deferred.resolve(new Wizard(arguments, element));
        });
        return deferred.promise();
    };

    var Wizard = function(steps, element) {
        this.index = 0;
        this.step = steps[this.index];
        this.element = element;
        this.move = function(newIndex, tab) {
            if (newIndex > this.index) {
                this.next(newIndex);
            } else if (newIndex < this.index) {
                this.prev(newIndex);
            }
            this.render(tab);
        };
        this.next = function() {
            this.step = steps[++this.index];
            this.step.force(this);
        };
        this.prev = function() {
            this.step = steps[--this.index];
            this.step.back(this);
        };
        this.render = function(tab) {
            this.step.render(tab);
        };
    };

    $.widget('mage.step-wizard', $.ui.tabs, {
        wizard: {},
        options: {
            collapsible: false,
            disabled: [],
            event: "click",
            buttonNextElement: '[data-role="step-wizard-next"]',
            buttonPrevElement: '[data-role="step-wizard-prev"]'
        },
        _create: function() {
            this._control();
            this.wizard = getWizardBySteps(this.options.steps, this.element);
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
            this._super(index, event);
            this._handlerStep(index);
        },
        _handlerStep: function (index) {
            var tab = this.panels.eq(index);
            this.wizard.done(function (wizard) {
                wizard.move(index, tab);
            });
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
