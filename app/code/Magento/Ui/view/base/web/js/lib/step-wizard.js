/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "uiRegistry",
    "jquery",
    "underscore",
    "mage/backend/notification"
], function (uiRegistry, $, _) {
    "use strict";
    var stepComponents;
    var getStep = _.memoize(function(step) {
        return _.findWhere(stepComponents, {name: step});
    });
    var Wizard = function (steps, element, uiWizard) {
        this.steps = steps;
        this.index = 0;
        this.data = {};
        this.element = element;
        this.uiWizard = uiWizard;
        this.nextLabel = uiWizard.nextLabel.text();
        this.prevLabel = uiWizard.prevLabel.text();
        $(this.element).notification();
        this.move = function (newIndex) {
            if (newIndex > this.index) {
                this._next(newIndex);
            } else if (newIndex < this.index) {
                this._prev(newIndex);
            }
        };
        this._next = function () {
            try {
                this.force();
            } catch (e) {
                this.notifyMessage(e.message, true);
                throw new Error(e);
            }
            this.index++;
            this.updateLabels(this.getStep());
            this.render();
        };
        this.getStep = function(stepIndex) {
            return getStep(this.steps[stepIndex || this.index]);
        };
        this.force = function() {
            this.getStep().force(this);
        };
        this._prev = function (newIndex) {
            this.updateLabels(this.getStep(this.index - 1));
            this.getStep().back(this);
            this.index = newIndex;
        };
        this.notifyMessage = function (message, error) {
            $(this.element).notification('clear').notification('add', {
                error: error,
                message: $.mage.__(message)
            });
        };
        this.updateLabels = function(step) {
            this.uiWizard.nextLabel.text(step.nextLabel || this.nextLabel);
            this.uiWizard.prevLabel.text(step.prevLabel || this.prevLabel);
        };
        this.render = function() {
            $(this.element).notification('clear');
            this.getStep().render(this);
        };
    };

    $.widget('mage.step-wizard', $.ui.tabs, {
        wizard: undefined,
        options: {
            collapsible: false,
            disabled: [],
            event: "click",
            buttonNextElement: '[data-role="step-wizard-next"]',
            buttonPrevElement: '[data-role="step-wizard-prev"]',
            stepRegistryComponent: null,
            steps: null
        },
        _create: function () {
            this._control();
            this._super();
            this.options.beforeActivate = this._handlerStep.bind(this);
        },
        _control: function () {
            var self = this;
            this.prev = this.element.find(this.options.buttonPrevElement);
            this.prevLabel = $('button', this.prev);
            this.next = this.element.find(this.options.buttonNextElement);
            this.nextLabel = $('button', this.next);

            this.next.on('click.' + this.eventNamespace, function (event) {
                // TODO: try to avoid ui.tabs for simplify logic
                if ((self.options.active+1) == (self.options.steps.length)) {
                    self.wizard.force();
                }
                self._activate(self.options.active + 1);
            });
            this.prev.on('click.' + this.eventNamespace, function (event) {
                self._activate(self.options.active - 1);
            });
        },
        load: function (index, event) {
            this._disabledTabs(index);
            this._actionControl(index);
            this._super(index, event);
        },
        _handlerStep: function (event, ui) {
            try {
                var index = this.tabs.index(ui.newTab[0]);
                var tab = this.panels.eq(index);
                var steps =  uiRegistry.async(this.options.stepRegistryComponent);

                steps(function(component) {
                    if (this.wizard === undefined) {
                        this.wizard = new Wizard(this.options.steps, tab, this);
                        stepComponents = component.steps;
                    }
                    this.wizard.move(index);
                }.bind(this));
            } catch (e) {
                return false;
            }
        },
        _way: function (index) {
            return this.options.selected > index ? 'back' : 'force';
        },
        _actionControl: function (index) {
            if (index < 1) {
                this.prev.find('button').addClass("disabled");
            }
            if (index === 1 && this._way(index) === 'force') {
                this.prev.find('button').removeClass("disabled");
            }
        },
        _disabledTabs: function (index) {
            this._setupDisabled(_.range(index + 2, this.tabs.length));
        }

    });

    return $.mage["step-wizard"];

});
