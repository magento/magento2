/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'ko',
    'underscore',
    'mage/template',
    'text!ui/template/tooltip/tooltip.html',
    '../template/renderer'
], function ($, ko, _, template, tooltipTmpl, renderer) {
    'use strict';

    var tooltip,
        defaults,
        positions,
        transformProp,
        checkedPositions = {},
        iterator = 0,
        previousTooltip,
        tooltipData,
        positionData = {},
        tooltipsCollection = {};

    defaults = {
        tooltipWrapper: '[data-tooltip=tooltip-wrapper]',
        tooltipContentBlock: 'data-tooltip-content',
        closeButtonClass: 'action-close',
        tailClass: 'data-tooltip-tail',
        action: 'click',
        delay: 0,
        track: false,
        step: 20,
        position: 'top',
        closeButton: false,
        showed: false,
        strict: true,
        center: false
    };

    tooltipData = {
        trigger: false,
        timeout: 0,
        element: false,
        event: false,
        targetElement: {},
        showed: false,
        currentID: 0
    };

    /**
     * Polyfill for css transform
     */
    transformProp = (function () {
        var style = document.createElement('div').style,
            base = 'Transform',
            vendors = ['webkit', 'moz', 'ms', 'o'],
            vi = vendors.length,
            property;

        if (typeof style.transform != 'undefined') {
            return 'transform';
        }

        while (vi--) {
            property = vendors[vi] + base;

            if (typeof style[property] != 'undefined') {
                return property;
            }
        }
    })();

    positions = {

        /*eslint max-depth: [0, 0]*/

        /**
         * Wrapper function to get tooltip data (position, className, etc)
         *
         * @param {Object} s - object with sizes and positions elements
         * @returns {Object} tooltip data (position, className, etc)
         */
        top: function (s) {
            var result = {
                    position: {}
                },
                config = tooltip.getTooltip(tooltipData.currentID),
                strict = !_.isUndefined(config.strict) ? config.strict : defaults.strict,
                step = !_.isUndefined(config.step) ? config.step : defaults.step,
                startPosition = !strict ? s.eventPosition : s.elementPosition;

            checkedPositions.top = true;

            if (startPosition.top - s.tooltipSize.h - step > s.scrollPosition.top) {
                result.position.top = startPosition.top - s.tooltipSize.h - step;
                result.className = '_bottom';
                result.side = 'top';
                result = positions._normalizeTop(s, result, config);
            } else if (!checkedPositions.right) {
                result = positions.right.apply(null, arguments);
            } else {
                result = positions._positionCenter(s, result, config);
            }

            return result;
        },

        /**
         * Centered tooltip if tooltip does not fit in window
         *
         * @param {Object} s - object with sizes and positions elements
         * @param {Object} data - current data (position, className, etc)
         * @returns {Object} tooltip data (position, className, etc)
         */
        _positionCenter: function (s, data) {
            if (s.tooltipSize.w < s.windowSize.w) {
                data.position.left = (s.windowSize.w - s.tooltipSize.w) / 2 + s.scrollPosition.left;
            } else {
                data.position.left = s.scrollPosition.left;
                data.tooltipSize = {
                    width: s.windowSize.w
                };
            }

            if (s.tooltipSize.h < s.windowSize.h) {
                data.position.top = (s.windowSize.h - s.tooltipSize.h) / 2 + s.scrollPosition.top;
            } else {
                data.position.top = s.scrollPosition.top;
                data.tooltipSize = {
                    height: s.windowSize.h
                };
            }

            return data;
        },

        /**
         * Normalize horizontal position if element can be setted in vertical position
         *
         * @param {Object} s - object with sizes and positions elements
         * @param {Object} data - current data (position, className, etc)
         * @param {Object} config - tooltip config
         * @returns {Object} tooltip data (position, className, etc)
         */
        _normalizeTop: function (s, data, config) {
            var center = !_.isUndefined(config.center) ? config.center : defaults.center,
                startPosition = !center ? s.eventPosition : {
                    left: s.elementPosition.left + s.elementSize.w / 2,
                    top: s.elementPosition.top
                },
                depResult;

            if (startPosition.left + s.tooltipSize.w / 2 < s.windowSize.w + s.scrollPosition.left &&
                startPosition.left - s.tooltipSize.w / 2  > s.scrollPosition.left) {
                data.position.left = startPosition.left - s.tooltipSize.w / 2;
            } else {

                /*eslint-disable no-lonely-if*/
                if (!checkedPositions.right) {
                    depResult = positions.right.apply(null, arguments);

                    if (depResult.hasOwnProperty('className')) {
                        data = depResult;
                    } else {
                        data.tail = {};

                        if (s.tooltipSize.w < s.windowSize.w) {

                            if (startPosition.left > s.windowSize.w / 2 + s.scrollPosition.left) {
                                data.position.left = s.windowSize.w + s.scrollPosition.left - s.tooltipSize.w;
                                data.tail.left = startPosition.left - s.tooltipSize.w / 2 - data.position.left;
                            } else {
                                data.position.left = s.scrollPosition.left;
                                data.tail.left = data.position.left - (startPosition.left - s.tooltipSize.w / 2);
                            }
                        } else {
                            data.position.left = s.scrollPosition.left;
                            data.tail.left = s.eventPosition.left - s.windowSize.w / 2;
                            data.tooltipSize = {
                                width: s.windowSize.w
                            };
                        }
                    }
                } else {
                    data.tail = {};

                    if (s.tooltipSize.w < s.windowSize.w) {

                        if (startPosition.left > s.windowSize.w / 2 + s.scrollPosition.left) {
                            data.position.left = s.windowSize.w + s.scrollPosition.left - s.tooltipSize.w;
                            data.tail.left = startPosition.left - s.tooltipSize.w / 2 - data.position.left;

                        } else {
                            data.position.left = s.scrollPosition.left;
                            data.tail.left = data.position.left - (startPosition.left - s.tooltipSize.w / 2);
                        }
                    } else {
                        data.position.left = s.scrollPosition.left;
                        data.tail.left = s.eventPosition.left - s.windowSize.w / 2;
                        data.tooltipSize = {
                            width: s.windowSize.w
                        };
                    }
                }
            }

            return data;
        },

        /**
         * Normalize horizontal position if element can be setted in vertical position
         *
         * @param {Object} s - object with sizes and positions elements
         * @param {Object} data - current data (position, className, etc)
         * @param {Object} config - tooltip config
         * @returns {Object} tooltip data (position, className, etc)
         */
        _normalizeBottom: function (s, data, config) {
            var center = !_.isUndefined(config.center) ? config.center : defaults.center,
                startPosition = !center ? s.eventPosition : {
                    left: s.elementPosition.left + s.elementSize.w / 2,
                    top: s.elementPosition.top
                },
                depResult;

            if (startPosition.left + s.tooltipSize.w / 2 < s.windowSize.w + s.scrollPosition.left &&
                startPosition.left - s.tooltipSize.w / 2  > s.scrollPosition.left) {
                data.position.left = startPosition.left - s.tooltipSize.w / 2;
            } else {

                /*eslint-disable no-lonely-if*/
                if (!checkedPositions.left) {
                    depResult = positions.left.apply(null, arguments);

                    if (depResult.hasOwnProperty('className')) {
                        data = depResult;
                    } else {
                        data.tail = {};

                        if (s.tooltipSize.w < s.windowSize.w) {

                            if (startPosition.left > s.windowSize.w / 2 + s.scrollPosition.left) {
                                data.position.left = s.windowSize.w + s.scrollPosition.left - s.tooltipSize.w;
                                data.tail.left = startPosition.left - s.tooltipSize.w / 2 - data.position.left;
                            } else {
                                data.position.left = s.scrollPosition.left;
                                data.tail.left = data.position.left - (startPosition.left - s.tooltipSize.w / 2);
                            }
                        } else {
                            data.position.left = s.scrollPosition.left;
                            data.tail.left = s.eventPosition.left - s.windowSize.w / 2;
                            data.tooltipSize = {
                                width: s.windowSize.w
                            };
                        }
                    }
                } else {
                    data.tail = {};

                    if (s.tooltipSize.w < s.windowSize.w) {

                        if (startPosition.left > s.windowSize.w / 2 + s.scrollPosition.left) {
                            data.position.left = s.windowSize.w + s.scrollPosition.left - s.tooltipSize.w;
                            data.tail.left = startPosition.left - s.tooltipSize.w / 2 - data.position.left;
                        } else {
                            data.position.left = s.scrollPosition.left;
                            data.tail.left = data.position.left - (startPosition.left - s.tooltipSize.w / 2);
                        }
                    } else {
                        data.position.left = s.scrollPosition.left;
                        data.tail.left = s.eventPosition.left - s.windowSize.w / 2;
                        data.tooltipSize = {
                            width: s.windowSize.w
                        };
                    }
                }
            }

            return data;
        },

        /**
         * Normalize vertical position if element can be setted in horizontal position
         *
         * @param {Object} s - object with sizes and positions elements
         * @param {Object} data - current data (position, className, etc)
         * @param {Object} config - tooltip config
         * @returns {Object} tooltip data (position, className, etc)
         */
        _normalizeRight: function (s, data, config) {
            var center = !_.isUndefined(config.center) ? config.center : defaults.center,
                startPosition = !center ? s.eventPosition : {
                    top: s.elementPosition.top + s.elementSize.h / 2,
                    left: s.elementPosition.left
                },
                depResult;

            if (startPosition.top - s.tooltipSize.h / 2 > s.scrollPosition.top &&
                startPosition.top + s.tooltipSize.h / 2 < s.scrollPosition.top + s.windowSize.h) {
                data.position.top = startPosition.top - s.tooltipSize.h / 2;
            } else {

                /*eslint-disable no-lonely-if*/
                if (!checkedPositions.bottom) {
                    depResult = positions.bottom.apply(null, arguments);

                    if (depResult.hasOwnProperty('className')) {
                        data = depResult;
                    } else {
                        data.tail = {};

                        if (s.tooltipSize.h < s.windowSize.h) {

                            if (startPosition.top > s.scrollPosition.top + s.windowSize.h / 2) {
                                data.position.top = s.windowSize.h + s.scrollPosition.top - s.tooltipSize.h;
                                data.tail.top = startPosition.top - s.tooltipSize.h / 2 - data.position.top;
                            } else {
                                data.position.top = s.scrollPosition.top;
                                data.tail.top = startPosition.top - s.tooltipSize.h / 2 - data.position.top;
                            }
                        } else {
                            data.position.top = s.scrollPosition.top;
                            data.tail.top = s.eventPosition.top - s.scrollPosition.top - s.windowSize.h / 2;
                            data.tooltipSize = {
                                height: s.windowSize.h
                            };
                        }
                    }
                } else {
                    data.tail = {};

                    if (s.tooltipSize.h < s.windowSize.h) {

                        if (startPosition.top > s.scrollPosition.top + s.windowSize.h / 2) {
                            data.position.top = s.scrollPosition.top;
                            data.tail.top = data.position.top - (startPosition.top - s.tooltipSize.h / 2);
                        } else {
                            data.position.top = s.windowSize.h + s.scrollPosition.top - s.tooltipSize.h;
                            data.tail.top = startPosition.top - s.tooltipSize.h / 2 - data.position.left;
                        }
                    } else {
                        data.position.top = s.scrollPosition.top;
                        data.tail.top = s.eventPosition.top - s.scrollPosition.top - s.windowSize.h / 2;
                        data.tooltipSize = {
                            height: s.windowSize.h
                        };
                    }
                }
            }

            return data;
        },

        /**
         * Normalize vertical position if element can be setted in horizontal position
         *
         * @param {Object} s - object with sizes and positions elements
         * @param {Object} data - current data (position, className, etc)
         * @param {Object} config - tooltip config
         * @returns {Object} tooltip data (position, className, etc)
         */
        _normalizeLeft: function (s, data, config) {
            var center = !_.isUndefined(config.center) ? config.center : defaults.center,
                startPosition = !center ? s.eventPosition : {
                    top: s.elementPosition.top + s.elementSize.h / 2,
                    left: s.elementPosition.left
                },
                depResult;

            if (startPosition.top - s.tooltipSize.h / 2 > s.scrollPosition.top &&
                startPosition.top + s.tooltipSize.h / 2 < s.scrollPosition.top + s.windowSize.h) {
                data.position.top = startPosition.top - s.tooltipSize.h / 2;
            } else {

                /*eslint-disable no-lonely-if*/
                if (!checkedPositions.top) {
                    depResult = positions.top.apply(null, arguments);

                    if (depResult.hasOwnProperty('className')) {
                        data = depResult;
                    } else {
                        data.tail = {};

                        if (s.tooltipSize.h < s.windowSize.h) {

                            if (startPosition.top > s.scrollPosition.top + s.windowSize.h / 2) {
                                data.position.top = s.windowSize.h + s.scrollPosition.top - s.tooltipSize.h;
                                data.tail.top = startPosition.top - s.tooltipSize.h / 2 - data.position.top;
                            } else {
                                data.position.top = s.scrollPosition.top;
                                data.tail.top = startPosition.top - s.tooltipSize.h / 2 - data.position.top;
                            }
                        } else {
                            data.position.top = s.scrollPosition.top;
                            data.tail.top = s.eventPosition.top - s.scrollPosition.top - s.windowSize.h / 2;
                            data.tooltipSize = {
                                height: s.windowSize.h
                            };
                        }
                    }
                } else {
                    data.tail = {};

                    if (s.tooltipSize.h < s.windowSize.h) {

                        if (startPosition.top > s.scrollPosition.top + s.windowSize.h / 2) {
                            data.position.top = s.scrollPosition.top;
                            data.tail.top = data.position.top - (startPosition.top - s.tooltipSize.h / 2);
                        } else {
                            data.position.top = s.windowSize.h + s.scrollPosition.top - s.tooltipSize.h;
                            data.tail.top = startPosition.top - s.tooltipSize.h / 2 - data.position.left;
                        }
                    } else {
                        data.position.top = s.scrollPosition.top;
                        data.tail.top = s.eventPosition.top - s.scrollPosition.top - s.windowSize.h / 2;
                        data.tooltipSize = {
                            height: s.windowSize.h
                        };
                    }
                }
            }

            return data;
        },

        /**
         * Wrapper function to get tooltip data (position, className, etc)
         *
         * @param {Object} s - object with sizes and positions elements
         * @returns {Object} tooltip data (position, className, etc)
         */
        left: function (s) {
            var result = {
                    position: {}
                },
                config = tooltip.getTooltip(tooltipData.currentID),
                strict = !_.isUndefined(config.strict) ? config.strict : defaults.strict,
                step = config.step || defaults.step,
                startPosition = !strict ? s.eventPosition : s.elementPosition;

            checkedPositions.left = true;

            if (startPosition.left - s.tooltipSize.w - step > s.scrollPosition.left) {
                result.position.left = startPosition.left - s.tooltipSize.w - step;
                result.className = '_right';
                result.side = 'left';
                result = positions._normalizeLeft(s, result, config);
            } else if (!checkedPositions.top) {
                result = positions.top.apply(null, arguments);
            } else {
                result = positions._positionCenter(s, result, config);
            }

            return result;
        },

        /**
         * Wrapper function to get tooltip data (position, className, etc)
         *
         * @param {Object} s - object with sizes and positions elements
         * @returns {Object} tooltip data (position, className, etc)
         */
        bottom: function (s) {
            var result = {
                    position: {}
                },
                config = tooltip.getTooltip(tooltipData.currentID),
                strict = !_.isUndefined(config.strict) ? config.strict : defaults.strict,
                step = config.step || defaults.step,
                startPosition = !strict ? s.eventPosition : {
                    top: s.elementPosition.top + s.elementSize.h,
                    left: s.elementPosition.left
                };

            checkedPositions.bottom = true;

            if (startPosition.top + s.tooltipSize.h + step < s.scrollPosition.top + s.windowSize.h) {
                result.position.top = startPosition.top + step;
                result.className = '_top';
                result.side = 'bottom';
                result = positions._normalizeBottom(s, result, config);
            } else if (!checkedPositions.left) {
                result = positions.left.apply(null, arguments);
            } else {
                result = positions._positionCenter(s, result, config);
            }

            return result;
        },

        /**
         * Wrapper function to get tooltip data (position, className, etc)
         *
         * @param {Object} s - object with sizes and positions elements
         * @returns {Object} tooltip data (position, className, etc)
         */
        right: function (s) {
            var result = {
                    position: {}
                },
                config = tooltip.getTooltip(tooltipData.currentID),
                strict = !_.isUndefined(config.strict) ? config.strict : defaults.strict,
                step = config.step || defaults.step,
                startPosition = !strict ? s.eventPosition : {
                    left: s.elementPosition.left + s.elementSize.w,
                    top: s.elementPosition.top
                };

            checkedPositions.right = true;

            if (startPosition.left + s.tooltipSize.w + step < s.windowSize.w + s.scrollPosition.left) {
                result.position.left = startPosition.left + step;
                result.className = '_left';
                result.side = 'right';
                result = positions._normalizeRight(s, result, config);
            } else if (!checkedPositions.bottom) {
                result = positions.bottom.apply(null, arguments);
            } else {
                result = positions._positionCenter(s, result, config);
            }

            return result;
        }
    };

    tooltip = {

        /**
         * Set new tooltip to tooltipCollection, save config, and add unic id
         *
         * @param {Object} config - tooltip config
         * @returns {String} tooltip id
         */
        setTooltip: function (config) {
            var property = 'id-' + iterator;

            tooltipsCollection[property] = config;
            iterator++;

            return property;
        },

        /**
         * Get tooltip config by id
         *
         * @param {String} id - tooltip id
         * @returns {Object} tooltip config
         */
        getTooltip: function (id) {
            return tooltipsCollection[id];
        },

        /**
         * Set content to current tooltip
         *
         * @param {Object} tooltipElement - tooltip element
         * @param {Object} viewModel - tooltip view model
         * @param {String} id - tooltip id
         * @param {Object} bindingCtx - tooltip context
         * @param {Object} event - action event
         */
        setContent: function (tooltipElement, viewModel, id, bindingCtx, event) {
            var html = $(tooltipElement).html(),
                config = tooltip.getTooltip(id),
                body = $('body'),
                delay = !_.isUndefined(config.delay) ? config.delay : defaults.delay;

            tooltipData.currentID = id;
            tooltipData.trigger = $(event.currentTarget);
            tooltip.setTargetData(event);
            body.on('mousemove.setTargetData', tooltip.setTargetData);
            tooltip.clearTimeout(id);

            tooltipData.timeout = _.delay(function () {
                body.off('mousemove.setTargetData', tooltip.setTargetData);

                if (tooltipData.trigger[0] === tooltipData.targetElement) {
                    tooltip.destroy(id);
                    event.stopPropagation();
                    tooltipElement = tooltip.createTooltip(id);
                    tooltipElement.find('.' + defaults.tooltipContentBlock).append(html);
                    tooltipElement.applyBindings(bindingCtx);
                    tooltip.setHandlers(id);
                    tooltip.setPosition(tooltipElement, id);
                    previousTooltip = id;
                }

            }, delay);
        },

        /**
         * Set position to current tooltip
         *
         * @param {Object} tooltipElement - tooltip element
         * @param {String} id - tooltip id
         */
        setPosition: function (tooltipElement, id) {
            var config = tooltip.getTooltip(id),
                position = config.position || defaults.position,
                tail,
                tailMargin;

            tooltip.sizeData = {
                windowSize: {
                    h: $(window).outerHeight(),
                    w: $(window).outerWidth()
                },
                scrollPosition: {
                    top: $(window).scrollTop(),
                    left: $(window).scrollLeft()
                },
                tooltipSize: {
                    h: tooltipElement.outerHeight(),
                    w: tooltipElement.outerWidth()
                },
                elementSize: {
                    h: tooltipData.trigger.outerHeight(),
                    w: tooltipData.trigger.outerWidth()
                },
                elementPosition: tooltipData.trigger.offset(),
                eventPosition: {
                    left: tooltipData.event.pageX,
                    top: tooltipData.event.pageY
                }
            };

            _.extend(positionData, positions[position](tooltip.sizeData));
            checkedPositions = {};
            tooltipElement.css(positionData.position);
            tooltipElement.addClass(positionData.className);
            $('body').css('position', 'relative');

            if (positionData.tooltipSize) {
                tooltipElement.css(positionData.tooltipSize);
            }

            if (positionData.tail) {
                tail = tooltipElement.find('.' + defaults.tailClass);

                if (positionData.tail.left) {
                    tailMargin = parseInt(tail.css('margin-left'), 10);
                    tail.css('margin-left', tailMargin + positionData.tail.left);
                } else {
                    tailMargin = parseInt(tail.css('margin-top'), 10);
                    tail.css('margin-top', tailMargin + positionData.tail.top);
                }
            }
        },

        /**
         * Close tooltip if action happened outside handler and tooltip element
         *
         * @param {String} id - tooltip id
         * @param {Object} event - action event
         */
        outerClick: function (id, event) {
            var tooltipElement = $(event.target).parents(defaults.tooltipWrapper)[0];

            if (tooltipData.showed && tooltipElement !== tooltipData.element[0]) {
                tooltip.destroy(id);
            }
        },

        /**
         * Parse keydown event and if event trigger is escape key - close tooltip
         *
         * @param {Object} event - action event
         */
        keydownHandler: function (event) {
            if (tooltipData.showed && event.keyCode === 27) {
                tooltip.destroy(tooltipData.currentID);
            }
        },

        /**
         * Change tooltip position when track is enabled
         *
         * @param {Object} event - current event
         */
        track: function (event) {
            var inequality = {};

            if (positionData.side === 'bottom' || positionData.side === 'top') {
                inequality.x = event.pageX - (positionData.position.left + tooltipData.element.outerWidth() / 2);

                if (positionData.position.left + inequality.x + tooltip.sizeData.tooltipSize.w >
                    tooltip.sizeData.windowSize.w + tooltip.sizeData.scrollPosition.left ||
                    inequality.x + positionData.position.left < tooltip.sizeData.scrollPosition.left) {

                    return false;
                }

                tooltipData.element[0].style[transformProp] = 'translateX(' + inequality.x + 'px)';
            } else if (positionData.side === 'left' || positionData.side === 'right') {
                inequality.y = event.pageY - (positionData.position.top + tooltipData.element.outerHeight() / 2);

                if (positionData.position.top + inequality.x + tooltip.sizeData.tooltipSize.h >
                    tooltip.sizeData.windowSize.h + tooltip.sizeData.scrollPosition.top ||
                    inequality.h + positionData.position.top < tooltip.sizeData.scrollPosition.top) {

                    return false;
                }

                tooltipData.element[0].style[transformProp] = 'translateY(' + inequality.y + 'px)';
            }
        },

        /**
         * Set handlers to tooltip
         *
         * @param {String} id - tooltip id
         */
        setHandlers: function (id) {
            var config = tooltip.getTooltip(id),
                action = config.action || defaults.action,
                closeButton = !_.isUndefined(config.closeButton) ? config.closeButton : defaults.closeButton,
                track = !_.isUndefined(config.track) ? config.track : defaults.track;

            if (track) {
                tooltipData.trigger.on('mousemove.track', tooltip.track);
            }

            if (action === 'click') {
                $(window).on('click.outerClick', tooltip.outerClick.bind(null, id));
            }

            if (closeButton) {
                $('.' + defaults.closeButtonClass).on('click.closeButton', tooltip.destroy.bind(null, id));
            }

            $(window).on('keydown.tooltip', tooltip.keydownHandler);
            $(window).on('scroll.tooltip', tooltip.outerClick.bind(null, id));
            $(window).on('resize.outerClick', tooltip.outerClick.bind(null, id));
        },

        /**
         * Toggle tooltip
         *
         * @param {Object} tooltipElement - tooltip element
         * @param {Object} viewModel - tooltip view model
         * @param {String} id - tooltip id
         */
        toggleTooltip: function (tooltipElement, viewModel, id) {
            if (previousTooltip === id && tooltipData.showed) {
                tooltip.destroy(id);

                return false;
            }

            tooltip.setContent.apply(null, arguments);
        },

        /**
         * Create tooltip and append to DOM
         *
         * @param {String} id - tooltip id
         * @returns {Object} tooltip element
         */
        createTooltip: function (id) {
            var body = $('body'),
                config = tooltip.getTooltip(id);

            $(template(tooltipTmpl, {
                data: config
            })).appendTo(body);

            tooltipData.showed = true;
            tooltipData.element = $(defaults.tooltipWrapper);

            return tooltipData.element;
        },

        /**
         * Check action and clean timeout
         *
         * @param {String} id - tooltip id
         */
        clearTimeout: function (id) {
            var config = tooltip.getTooltip(id),
                action = config.action || defaults.action;

            if (action === 'hover') {
                clearTimeout(tooltipData.timeout);
            }
        },

        /**
         * Check previous tooltip
         */
        checkPreviousTooltip: function () {
            if (!tooltipData.timeout) {
                tooltip.destroy();
            }
        },

        /**
         * Destroy tooltip instance
         */
        destroy: function () {
            if (tooltipData.element) {
                tooltipData.element.remove();
                tooltipData.showed = false;
            }

            $('body').css('position', 'static');
            positionData = {};
            tooltipData.timeout = false;
            tooltip.removeHandlers();
        },

        /**
         * Remove tooltip handlers
         */
        removeHandlers: function () {
            $('.' + defaults.closeButtonClass).off('click.closeButton');
            tooltipData.trigger.off('mousemove.track');
            $(window).off('click.outerClick');
            $(window).off('scroll.tooltip');
            $(window).off('keydown.tooltip');
            $(window).off('resize.outerClick');
        },

        /**
         * Set target element
         *
         * @param {Object} event - current event
         */
        setTargetData: function (event) {
            tooltipData.event = event;
            tooltipData.targetElement = event.type === 'mousemove' ?
                                        event.target : event.currentTarget;
        }
    };

    ko.bindingHandlers.tooltip = {

        /**
         * Initialize tooltip
         *
         * @param {Object} elem - tooltip DOM element
         * @param {Function} valueAccessor - ko observable property, tooltip data
         * @param {Object} allBindings - all bindings on current element
         * @param {Object} viewModel - current element viewModel
         * @param {Object} bindingCtx - current element binding context
         */
        init: function (elem, valueAccessor, allBindings, viewModel, bindingCtx) {
            var config = valueAccessor(),
                trigger = config.trigger,
                action = config.action,
                $parentScope =  $(elem).addClass('hidden').parent(),
                id;

            if (config.parentScope) {
                $parentScope = $(config.parentScope);
            }

            id = tooltip.setTooltip(valueAccessor());

            if (action === 'hover') {
                $parentScope.on('mouseenter', trigger, tooltip.setContent.bind(null, elem, viewModel, id, bindingCtx));
                $parentScope.on('mouseleave', trigger, tooltip.checkPreviousTooltip.bind(null, id));
            } else if (action === 'click') {
                $parentScope.on('click', trigger, tooltip.toggleTooltip.bind(null, elem, viewModel, id, bindingCtx));
            }

            return {
                controlsDescendantBindings: true
            };
        }
    };

    renderer.addAttribute('tooltip');
});
