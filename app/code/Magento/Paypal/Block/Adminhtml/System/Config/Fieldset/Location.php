<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Fieldset renderer for PayPal Merchant Location fieldset
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Paypal\Block\Adminhtml\System\Config\Fieldset;

class Location extends \Magento\Paypal\Block\Adminhtml\System\Config\Fieldset\Expanded
{
    /**
     * Render fieldset html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        $js = '
            require(["jquery", "prototype"], function(jQuery){
            jQuery("body").on("adminConfigDefined", function() {
                $$(".with-button button.button").each(function(configureButton) {
                    togglePaypalSolutionConfigureButton(configureButton, true);
                });
                var paypalConflictsObject = {
                    "isConflict": false,
                    "ecMissed": false,
                    sharePayflowEnabling: function(enabler, isEvent) {

                        var isPayflowLinkEnabled = !!$$(".paypal-payflowlink")[0],
                            isPayflowAdvancedEnabled = !!$$(".paypal-payflow-advanced")[0];
                        var ecPayflowEnabler = $$(".paypal-ec-payflow-enabler")[+isPayflowLinkEnabled];
                        if (typeof ecPayflowEnabler == "undefined") {
                            return;
                        }
                        var ecPayflowScopeElement = adminSystemConfig.getScopeElement(ecPayflowEnabler);

                        if (!enabler.enablerObject.ecPayflow) {
                            if ((!ecPayflowScopeElement || !ecPayflowScopeElement.checked) && isEvent
                                && enabler.value == 1
                            ) {
                                ecPayflowEnabler.value = 0;
                                fireEvent(ecPayflowEnabler, "change");
                            }
                            return;
                        }

                        var enablerScopeElement = adminSystemConfig.getScopeElement(enabler);
                        if (enablerScopeElement && ecPayflowScopeElement
                            && enablerScopeElement.checked != ecPayflowScopeElement.checked
                            && (isEvent || ecPayflowScopeElement.checked)
                        ) {
                            $(ecPayflowScopeElement).click();
                        }

                        var ecEnabler = $$(".paypal-ec-enabler")[0];
                        if (ecPayflowEnabler.value != enabler.value
                            && (isEvent || enabler.value == 1 && !isPayflowLinkEnabled)
                        ) {
                            ecPayflowEnabler.value = enabler.value;
                            paypalConflictsObject.checklessEventAction(ecPayflowEnabler, true);
                            if (ecPayflowEnabler.value == 1) {
                                if (typeof ecEnabler != "undefined") {
                                    var ecEnablerScopeElement = adminSystemConfig.getScopeElement(ecEnabler);
                                    ecEnabler.value = 0;
                                    if (ecEnablerScopeElement && ecEnablerScopeElement.checked) {
                                        paypalConflictsObject.checklessEventAction(ecEnablerScopeElement, false);
                                    }
                                    paypalConflictsObject.checklessEventAction(ecEnabler, true);
                                }
                            }
                        }
                        if (!isEvent && ecPayflowEnabler.value == 1 && typeof ecEnabler != "undefined") {
                            var ecSolution = $$(".pp-method-express")[0];
                            if (typeof ecSolution != "undefined" && !$(ecSolution).hasClassName("enabled")) {
                                ecSolution.addClassName("enabled");
                            }
                        }
                    },
                    onChangeEnabler: function(event) {
                        paypalConflictsObject.checkPaymentConflicts($(Event.element(event)), "change");
                    },
                    onClickEnablerScope: function(event) {
                        paypalConflictsObject.checkPaymentConflicts(
                            $(adminSystemConfig.getUpTr($(Event.element(event))).select(".paypal-enabler")[0]),
                            "click"
                        );
                    },
                    getSharedElements: function(element) {
                        var sharedElements = [];
                        adminSystemConfig.mapClasses(element, true, function(elementClassName) {
                            $$("." + elementClassName).each(function(sharedElement) {
                                if (sharedElements.indexOf(sharedElement) == -1) {
                                    sharedElements.push(sharedElement);
                                }
                            });
                        });
                        if (sharedElements.length == 0) {
                            sharedElements.push(element);
                        }
                        return sharedElements;
                    },
                    checklessEventAction: function(element, isChange) {
                        var action = isChange ? "change" : "click";
                        var handler = isChange
                            ? paypalConflictsObject.onChangeEnabler
                            : paypalConflictsObject.onClickEnablerScope;
                        paypalConflictsObject.getSharedElements(element).each(function(sharedElement) {
                            Event.stopObserving(sharedElement, action, handler);
                            if (isChange) {
                                sharedElement.value = element.value;
                                if ($(sharedElement).requiresObj) {
                                    $(sharedElement).requiresObj.indicateEnabled();
                                }
                            }
                        });
                        if (isChange) {
                            fireEvent(element, "change");
                        } else {
                            $(element).click();
                        }
                        paypalConflictsObject.getSharedElements(element).each(function(sharedElement) {
                            Event.observe(sharedElement, action, handler);
                        });
                    },
                    ecCheckAvailability: function() {
                        $$(".pp-method-express button.button").each(function(ecButton){
                            if (typeof ecButton == "undefined") {
                                return;
                            }
                            var couldBeConfigured = true;
                            $$(".paypal-enabler").each(function(enabler) {
                                if (enabler.enablerObject.ecEnabler || enabler.enablerObject.ecConflicts
                                    || enabler.enablerObject.ecSeparate
                                ) {
                                    return;
                                }
                                if (enabler.value == 1) {
                                    couldBeConfigured = false;
                                }
                            });
                            if (couldBeConfigured) {
                                togglePaypalSolutionConfigureButton(ecButton, true);
                            } else {
                                togglePaypalSolutionConfigureButton(ecButton, false);
                            }
                        });
                    },
                    // type could be "initial", "change", "click"
                    checkPaymentConflicts: function(enabler, type) {
                        if (!enabler.enablerObject) {
                            return;
                        }
                        var isEvent = (type != "initial");
                        var ecEnabler = $$(".paypal-ec-enabler")[0];

                        if (enabler.value == 0) {
                            if (!enabler.enablerObject.ecIndependent && type == "change") {
                                if (typeof ecEnabler != "undefined" && ecEnabler.value == 1) {
                                    var ecEnablerScopeElement = adminSystemConfig.getScopeElement(ecEnabler);
                                    if (!ecEnablerScopeElement || !ecEnablerScopeElement.checked) {
                                        ecEnabler.value = 0;
                                        paypalConflictsObject.checklessEventAction(ecEnabler, true);
                                    }
                                }
                            }
                            paypalConflictsObject.ecCheckAvailability();
                            paypalConflictsObject.sharePayflowEnabling(enabler, isEvent);
                            return;
                        }

                        var confirmationApproved = isEvent;
                        var confirmationShowed = false;
                        // check other solutions
                        $$(".paypal-enabler").each(function(anotherEnabler) {
                            var anotherEnablerScopeElement = adminSystemConfig.getScopeElement(anotherEnabler);
                            if (!confirmationApproved && isEvent || $(anotherEnabler) == enabler
                                || anotherEnabler.value == 0
                                && (!anotherEnablerScopeElement || !anotherEnablerScopeElement.checked)
                            ) {
                                return;
                            }
                            var conflict = enabler.enablerObject.ecConflicts && anotherEnabler.enablerObject.ecEnabler
                                || enabler.enablerObject.ecEnabler && anotherEnabler.enablerObject.ecConflicts
                                || !enabler.enablerObject.ecIndependent && anotherEnabler.enablerObject.ecConflicts
                                || !enabler.enablerObject.ecEnabler && !anotherEnabler.enablerObject.ecEnabler;

                            if (conflict && !confirmationShowed && anotherEnabler.value == 1) {
                                if (isEvent) {
                                    confirmationApproved = confirm(\'' .
            $this->escapeJsQuote(
                __('There is already another PayPal solution enabled. Enable this solution instead?')
            ) .
            '\');
                                } else {
                                    paypalConflictsObject.isConflict = true;
                                }
                                confirmationShowed = true;
                            }
                            if (conflict && confirmationApproved) {
                                anotherEnabler.value = 0;
                                if (anotherEnablerScopeElement && anotherEnablerScopeElement.checked && isEvent) {
                                    paypalConflictsObject.checklessEventAction(anotherEnablerScopeElement, false);
                                }
                                paypalConflictsObject.checklessEventAction(anotherEnabler, true);
                            }
                        });

                        if (!enabler.enablerObject.ecIndependent) {
                            if (!isEvent && (typeof ecEnabler == "undefined" || ecEnabler.value == 0)) {
                                if (!enabler.enablerObject.ecPayflow) {
                                    paypalConflictsObject.ecMissed = true;
                                }
                            } else if (isEvent && typeof ecEnabler != "undefined" && confirmationApproved) {
                                var ecEnablerScopeElement = adminSystemConfig.getScopeElement(ecEnabler);
                                if (ecEnablerScopeElement && ecEnablerScopeElement.checked) {
                                    paypalConflictsObject.checklessEventAction(ecEnablerScopeElement, false);
                                }
                                if (ecEnabler.value == 0 && !enabler.enablerObject.ecPayflow) {
                                    ecEnabler.value = 1;
                                    paypalConflictsObject.checklessEventAction(ecEnabler, true);
                                }
                            }
                        }

                        if (!confirmationApproved && isEvent) {
                            enabler.value = 0;
                            paypalConflictsObject.checklessEventAction(enabler, true);
                        }
                        paypalConflictsObject.ecCheckAvailability();
                        paypalConflictsObject.sharePayflowEnabling(enabler, isEvent);
                    },
                    handleBmlEnabler: function(event) {
                        required = Event.element(event);
                        var bml = $(required).bmlEnabler;
                        if (required.value == "1") {
                            bml.value = "1";
                        }
                        paypalConflictsObject.toggleBmlEnabler(required);
                    },

                    toggleBmlEnabler: function(required) {
                        var bml = $(required).bmlEnabler;
                        if (!bml) {
                            return;
                        }
                        if (required.value != "1") {
                            bml.value = "0";
                            $(bml).disable();
                        }
                        $(bml).requiresObj.indicateEnabled();
                    }
                };

                // fill enablers with conflict data
                $$(".paypal-enabler").each(function(enablerElement) {
                    var enablerObj = {
                        ecIndependent: false,
                        ecConflicts: false,
                        ecEnabler: false,
                        ecSeparate: false,
                        ecPayflow: false
                    };
                    $(enablerElement).classNames().each(function(className) {
                        switch (className) {
                            case "paypal-ec-conflicts":
                                enablerObj.ecConflicts = true;
                            case "paypal-ec-independent":
                                enablerObj.ecIndependent = true;
                                break;
                            case "paypal-ec-enabler":
                                enablerObj.ecEnabler = true;
                                enablerObj.ecIndependent = true;
                                break;
                            case "paypal-ec-separate":
                                enablerObj.ecSeparate = true;
                                enablerObj.ecIndependent = true;
                                break;
                            case "paypal-ec-pe":
                                enablerObj.ecPayflow = true;
                                break;
                        }
                    });
                    enablerElement.enablerObject = enablerObj;

                    Event.observe(enablerElement, "change", paypalConflictsObject.onChangeEnabler);
                    var enablerScopeElement = adminSystemConfig.getScopeElement(enablerElement);
                    if (enablerScopeElement) {
                        Event.observe(enablerScopeElement, "click", paypalConflictsObject.onClickEnablerScope);
                    }
                });

                // initially uncheck payflow
                var isPayflowLinkEnabled = !!$$(".paypal-payflowlink")[0],
                    isPayflowAdvancedEnabled = !!$$(".paypal-payflow-advanced")[0];
                var ecPayflowEnabler = $$(".paypal-ec-payflow-enabler")[+isPayflowLinkEnabled];
                if (typeof ecPayflowEnabler != "undefined") {
                    if (ecPayflowEnabler.value == 1 && !isPayflowLinkEnabled) {
                        ecPayflowEnabler.value = 0;
                        fireEvent(ecPayflowEnabler, "change");
                    }

                    var ecPayflowScopeElement = adminSystemConfig.getScopeElement(ecPayflowEnabler);
                    if (ecPayflowScopeElement && !ecPayflowScopeElement.checked) {
                        $(ecPayflowScopeElement).click();
                    }
                }
                $$(".paypal-bml").each(function(bmlEnabler) {
                    $(bmlEnabler).classNames().each(function(className) {
                        if (className.indexOf("requires-") !== -1) {
                            var required = $(className.replace("requires-", ""));
                            required.bmlEnabler = bmlEnabler;
                            Event.observe(required, "change", paypalConflictsObject.handleBmlEnabler);
                        }
                    });
                });

                $$(".paypal-enabler").each(function(enablerElement) {
                    paypalConflictsObject.checkPaymentConflicts(enablerElement, "initial");
                    paypalConflictsObject.toggleBmlEnabler(enablerElement);
                });
                if (paypalConflictsObject.isConflict || paypalConflictsObject.ecMissed) {
                    var notification = \'' .
            $this->escapeJsQuote(
                __('The following error(s) occured:')
            ) .
            '\';
                    if (paypalConflictsObject.isConflict) {
                        notification += "\\n  " + \'' .
            $this->escapeJsQuote(
                __('Some PayPal solutions conflict.')
            ) .
            '\';
                    }
                    if (paypalConflictsObject.ecMissed) {
                        notification += "\\n  " + \'' .
            $this->escapeJsQuote(
                __('PayPal Express Checkout is not enabled.')
            ) . '\';
                    }
                    notification += "\\n" + \'' .
                    $this->escapeJsQuote(
                        __('Please re-enable the previously enabled payment solutions.')
                    ) .
            '\';
                    setTimeout(function() {
                        alert(notification);
                    }, 1);
                }

                $$(".requires").each(function(dependent) {
                    var $dependent = $(dependent);
                    if ($dependent.hasClassName("paypal-ec-enabler") || $dependent.hasClassName("paypal-ec-payflow-enabler")) {
                        $dependent.requiresObj.callback = function(required) {
                            if ($(required).hasClassName("paypal-enabler") && required.value == 0) {
                                $dependent.disable();
                            }
                        }
                        $dependent.requiresObj.requires.each(function(required) {
                            $dependent.requiresObj.callback(required);
                        });
                    }
                });

                configForm.on(\'afterValidate\', function() {
                    var isPayflowLinkEnabled = !!$$(".paypal-payflowlink")[0],
                        isPayflowAdvancedEnabled = !!$$(".paypal-payflow-advanced")[0];
                    var ecPayflowEnabler = $$(".paypal-ec-payflow-enabler")[+isPayflowLinkEnabled];
                    if (typeof ecPayflowEnabler == "undefined") {
                        return;
                    }
                    var ecPayflowScopeElement = adminSystemConfig.getScopeElement(ecPayflowEnabler);
                    if ((typeof ecPayflowScopeElement == "undefined" || !ecPayflowScopeElement.checked)
                        && ecPayflowEnabler.value == 1
                    ) {
                        $$(".paypal-ec-enabler").each(function(ecEnabler) {
                            ecEnabler.value = 0;
                        });
                    }
                });
            });

            });
        ';
        return parent::render($element) . $this->_jsHelper->getScript($js);
    }
}
