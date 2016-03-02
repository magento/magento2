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
        tooltipsCollection = {},
        isTouchDevice = (function () {
            return 'ontouchstart' in document.documentElement;
        })(),
        CLICK_EVENT = (function () {
            return isTouchDevice ? 'touchstart' : 'click';
        })();

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

        map: {
            horizontal: {
                s: 'w',
                p: 'left'
            },
            vertical: {
                s: 'h',
                p: 'top'
            }
        },

        /**
         * Wrapper function to get tooltip data (position, className, etc)
         *
         * @param {Object} s - object with sizes and positions elements
         * @returns {Object} tooltip data (position, className, etc)
         */
        top: function (s) {
            return positions._topLeftChecker(s, positions.map, 'vertical', '_bottom', 'top', 'right');
        },

        /**
         * Wrapper function to get tooltip data (position, className, etc)
         *
         * @param {Object} s - object with sizes and positions elements
         * @returns {Object} tooltip data (position, className, etc)
         */
        left: function (s) {
            return positions._topLeftChecker(s, positions.map, 'horizontal', '_right', 'left', 'top');
        },

        _topLeftChecker: function (s, map, direction, className, side, delegate) {
            var result = {
                    position: {}
                },
                config = tooltip.getTooltip(tooltipData.currentID),
                startPosition = !config.strict ? s.eventPosition : s.elementPosition,
                changedDirection;

            checkedPositions[side] = true;

            if (startPosition[map[direction].p] - s.tooltipSize[map[direction].s] - config.step > s.scrollPosition[map[direction].p]) {
                result.position[map[direction].p] = startPosition[map[direction].p] - s.tooltipSize[map[direction].s] - config.step;
                result.className = className;
                result.side = side;
                changedDirection = direction === 'vertical' ? 'horizontal' : 'vertical';
                result = positions._normalize(s, result, config, delegate, map, changedDirection);
            } else if (!checkedPositions[delegate]) {
                result = positions[delegate].apply(null, arguments);
            } else {
                result = positions._positionCenter(s, result, config);
            }

            return result;
        },

        _bottomRightChecker: function (s, map, direction, className, side, delegate) {
            var result = {
                    position: {}
                },
                config = tooltip.getTooltip(tooltipData.currentID),
                startPosition = !config.strict ? s.eventPosition : {
                    top: s.elementPosition.top + s.elementSize.h,
                    left: s.elementPosition.left + s.elementSize.w
                },
                changedDirection;

            checkedPositions[side] = true;

            if (startPosition[map[direction].p] + s.tooltipSize[map[direction].s] + config.step < s.scrollPosition[map[direction].p] + s.windowSize[map[direction].s]) {
                result.position[map[direction].p] = startPosition[map[direction].p] + config.step;
                result.className = className;
                result.side = side;
                changedDirection = direction === 'vertical' ? 'horizontal' : 'vertical';
                result = positions._normalize(s, result, config, delegate, map, changedDirection);
            } else if (!checkedPositions[delegate]) {
                result = positions[delegate].apply(null, arguments);
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
            return positions._bottomRightChecker(s, positions.map, 'vertical', '_top', 'bottom', 'left');
        },

        /**
         * Wrapper function to get tooltip data (position, className, etc)
         *
         * @param {Object} s - object with sizes and positions elements
         * @returns {Object} tooltip data (position, className, etc)
         */
        right: function (s) {
            return positions._bottomRightChecker(s, positions.map, 'horizontal', '_left', 'right', 'bottom');
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

        _normalize: function (s, data, config, delegate, map, direction) {
            var startPosition = !config.center ? s.eventPosition : {
                    left: s.elementPosition.left + s.elementSize.w / 2,
                    top: s.elementPosition.top + s.elementSize.h / 2
                },
                depResult;

            if (startPosition[map[direction].p] - s.tooltipSize[map[direction].s] / 2 > s.scrollPosition[map[direction].p] &&
                startPosition[map[direction].p] + s.tooltipSize[map[direction].s] / 2 < s.scrollPosition[map[direction].p] + s.windowSize[map[direction].s]) {
                data.position[map[direction].p] = startPosition[map[direction].p] - s.tooltipSize[map[direction].s] / 2;
            } else {

                /*eslint-disable no-lonely-if*/
                if (!checkedPositions[delegate]) {
                    depResult = positions[delegate].apply(null, arguments);

                    if (depResult.hasOwnProperty('className')) {
                        data = depResult;
                    } else {
                        data.tail = {};

                        if (s.tooltipSize[map[direction].s] < s.windowSize[map[direction].s]) {

                            if (startPosition[map[direction].p] > s.windowSize[map[direction].s] / 2 + s.scrollPosition[map[direction].p]) {
                                data.position[map[direction].p] = s.windowSize[map[direction].s] + s.scrollPosition[map[direction].p] - s.tooltipSize[map[direction].s];
                                data.tail[map[direction].p] = startPosition[map[direction].p] - s.tooltipSize[map[direction].s] / 2 - data.position[map[direction].p];
                            } else {
                                data.position[map[direction].p] = s.scrollPosition[map[direction].p];
                                data.tail[map[direction].p] = startPosition[map[direction].p] - s.tooltipSize[map[direction].s] / 2 - data.position[map[direction].p];
                            }
                        } else {
                            data.position[map[direction].p] = s.scrollPosition[map[direction].p];
                            data.tail[map[direction].p] = s.eventPosition[map[direction].p] - s.windowSize[map[direction].s] / 2;
                            data.tooltipSize = {};
                            data.tooltipSize[map[direction].s] = s.windowSize[map[direction].s];
                        }
                    }
                } else {
                    data.tail = {};

                    if (s.tooltipSize[map[direction].s] < s.windowSize[map[direction].s]) {

                        if (startPosition[map[direction].p] > s.windowSize[map[direction].s] / 2 + s.scrollPosition[map[direction].p]) {
                            data.position[map[direction].p] = s.windowSize[map[direction].s] + s.scrollPosition[map[direction].p] - s.tooltipSize[map[direction].s];
                            data.tail[map[direction].p] = startPosition[map[direction].p] - s.tooltipSize[map[direction].s] / 2 - data.position[map[direction].p];

                        } else {
                            data.position[map[direction].p] = s.scrollPosition[map[direction].p];
                            data.tail[map[direction].p] = startPosition[map[direction].p] - s.tooltipSize[map[direction].s] / 2 - data.position[map[direction].p];
                        }
                    } else {
                        data.position[map[direction].p] = s.scrollPosition[map[direction].p];
                        data.tail[map[direction].p] = s.eventPosition[map[direction].p] - s.windowSize[map[direction].s] / 2;
                        data.tooltipSize = {};
                        data.tooltipSize[map[direction].s] = s.windowSize[map[direction].s];
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
        _normalizeHorizontal: function (s, data, config, delegate) {
            var startPosition = !config.center ? s.eventPosition : {
                    left: s.elementPosition.left + s.elementSize.w / 2,
                    top: s.elementPosition.top
                },
                depResult;

            if (startPosition.left - s.tooltipSize.w / 2 > s.scrollPosition.left &&
                startPosition.left + s.tooltipSize.w / 2 < s.scrollPosition.left + s.windowSize.w) {
                data.position.left = startPosition.left - s.tooltipSize.w / 2;
            } else {

                /*eslint-disable no-lonely-if*/
                if (!checkedPositions[delegate]) {
                    depResult = positions[delegate].apply(null, arguments);

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
        _normalizeVertical: function (s, data, config, delegate) {
            var startPosition = !config.center ? s.eventPosition : {
                    top: s.elementPosition.top + s.elementSize.h / 2,
                    left: s.elementPosition.left
                },
                depResult;

            if (startPosition.top - s.tooltipSize.h / 2 > s.scrollPosition.top &&
                startPosition.top + s.tooltipSize.h / 2 < s.scrollPosition.top + s.windowSize.h) {
                data.position.top = startPosition.top - s.tooltipSize.h / 2;
            } else {

                /*eslint-disable no-lonely-if*/
                if (!checkedPositions[delegate]) {
                    depResult = positions[delegate].apply(null, arguments);

                    if (depResult.hasOwnProperty('className')) {
                        data = depResult;
                    } else {
                        data.tail = {};

                        if (s.tooltipSize.h < s.windowSize.h) {

                            if (startPosition.top > s.windowSize.h / 2 + s.scrollPosition.top) {
                                data.position.top = s.windowSize.h + s.scrollPosition.top - s.tooltipSize.h;
                                data.tail.top = startPosition.top - s.tooltipSize.h / 2 - data.position.top;
                            } else {
                                data.position.top = s.scrollPosition.top;
                                data.tail.top = data.position.top - (startPosition.top - s.tooltipSize.h / 2);
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

                        if (startPosition.top >  s.windowSize.h / 2 + s.scrollPosition.top) {
                            data.position.top = s.windowSize.h + s.scrollPosition.top - s.tooltipSize.h;
                            data.tail.top = startPosition.top - s.tooltipSize.h / 2 - data.position.top;
                        } else {
                            data.position.top = s.scrollPosition.top;
                            data.tail.top = data.position.top - (startPosition.top - s.tooltipSize.h / 2);

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
                body = $('body');

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

            }, config.delay);
        },

        /**
         * Set position to current tooltip
         *
         * @param {Object} tooltipElement - tooltip element
         * @param {String} id - tooltip id
         */
        setPosition: function (tooltipElement, id) {
            var config = tooltip.getTooltip(id),
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
                    left: tooltipData.event.originalEvent.pageX,
                    top: tooltipData.event.originalEvent.pageY
                }
            };

            _.extend(positionData, positions[config.position](tooltip.sizeData));
            checkedPositions = {};
            tooltipElement.css(positionData.position);
            tooltipElement.addClass(positionData.className);
            $('body').css('position', 'relative');

            if (positionData.tooltipSize) {
                positionData.tooltipSize.w ?
                    tooltipElement.css('width', positionData.tooltipSize.w) :
                    tooltipElement.css('height', positionData.tooltipSize.h);
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
            var tooltipElement = $(event.target).parents(defaults.tooltipWrapper)[0],
                isTrigger = event.target === tooltipData.trigger[0] || $.contains(tooltipData.trigger[0], event.target);

            if (tooltipData.showed && tooltipElement !== tooltipData.element[0] && !isTrigger) {
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
            var config = tooltip.getTooltip(id);

            if (config.track) {
                tooltipData.trigger.on('mousemove.track', tooltip.track);
            }

            if (config.action === 'click') {
                $(window).on(CLICK_EVENT + '.outerClick', tooltip.outerClick.bind(null, id));
            }

            if (config.closeButton) {
                $('.' + config.closeButtonClass).on('click.closeButton', tooltip.destroy.bind(null, id));
            }

            document.addEventListener('scroll', tooltip.destroy, true);
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
            tooltipData.element = $(config.tooltipWrapper);

            return tooltipData.element;
        },

        /**
         * Check action and clean timeout
         *
         * @param {String} id - tooltip id
         */
        clearTimeout: function (id) {
            var config = tooltip.getTooltip(id);

            if (config.action === 'hover') {
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
            document.removeEventListener('scroll', tooltip.destroy, true);
            $(window).off(CLICK_EVENT + '.outerClick');
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
        },

        mergingConfig: function (config) {
            return _.extend({}, defaults, config);
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
            var config = tooltip.mergingConfig(valueAccessor()),
                $parentScope = config.parentScope ? $(config.parentScope) : $(elem).parent(),
                tooltipId;

            $(elem).addClass('hidden');

            if (isTouchDevice) {
                config.action = 'click';
            }
            
            tooltipId = tooltip.setTooltip(config);

            if (config.action === 'hover') {
                $parentScope.on('mouseenter', config.trigger, tooltip.setContent.bind(null, elem, viewModel, tooltipId, bindingCtx));
                $parentScope.on('mouseleave', config.trigger, tooltip.checkPreviousTooltip.bind(null, tooltipId));
            } else if (config.action === 'click') {
                $parentScope.on('click', config.trigger, tooltip.toggleTooltip.bind(null, elem, viewModel, tooltipId, bindingCtx));
            }

            return {
                controlsDescendantBindings: true
            };
        }
    };

    renderer.addAttribute('tooltip');
});
