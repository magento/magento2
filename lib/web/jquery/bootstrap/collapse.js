/**
 * --------------------------------------------------------------------------
 * Bootstrap (v5.1.3): collapse.js and base-component.js
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/main/LICENSE)
 * --------------------------------------------------------------------------
 */

define([
    "jquery",
    "./util/index",
    "./dom/data",
    "./dom/event-handler",
    "./dom/manipulator",
    "./dom/selector-engine"
], function($, Util, Data, EventHandler, Manipulator, SelectorEngine) {
    'use strict';

    var defineJQueryPlugin = Util.defineJQueryPlugin;
    var executeAfterTransition = Util.executeAfterTransition;
    var getElement = Util.getElement;
    var getSelectorFromElement = Util.getSelectorFromElement;
    var getElementFromSelector = Util.getElementFromSelector;
    var reflow = Util.reflow;
    var typeCheckConfig = Util.typeCheckConfig;

    /**
     * ------------------------------------------------------------------------
     * Constants
     * ------------------------------------------------------------------------
     */

    var VERSION = '5.1.3'
    var NAME = 'collapse'
    var DATA_KEY = 'bs.collapse'
    var EVENT_KEY = `.${DATA_KEY}`
    var DATA_API_KEY = '.data-api'

    var Default = {
        toggle: true,
        parent: null
    }

    var DefaultType = {
        toggle: 'boolean',
        parent: '(null|element)'
    }

    var EVENT_SHOW = `show${EVENT_KEY}`
    var EVENT_SHOWN = `shown${EVENT_KEY}`
    var EVENT_HIDE = `hide${EVENT_KEY}`
    var EVENT_HIDDEN = `hidden${EVENT_KEY}`
    var EVENT_CLICK_DATA_API = `click${EVENT_KEY}${DATA_API_KEY}`

    var CLASS_NAME_SHOW = 'show'
    var CLASS_NAME_COLLAPSE = 'collapse'
    var CLASS_NAME_COLLAPSING = 'collapsing'
    var CLASS_NAME_COLLAPSED = 'collapsed'
    var CLASS_NAME_DEEPER_CHILDREN = `:scope .${CLASS_NAME_COLLAPSE} .${CLASS_NAME_COLLAPSE}`
    var CLASS_NAME_HORIZONTAL = 'collapse-horizontal'

    var WIDTH = 'width'
    var HEIGHT = 'height'

    var SELECTOR_ACTIVES = '.collapse.show, .collapse.collapsing'
    var SELECTOR_DATA_TOGGLE = '[data-bs-toggle="collapse"]'

    /**
     * ------------------------------------------------------------------------
     * Class Definition
     * ------------------------------------------------------------------------
     */

    var Collapse = function(element, config) {
        element = getElement(element)

        if (!element) {
            return
        }

        this._element = element
        Data.set(this._element, DATA_KEY, this)

        this._isTransitioning = false
        this._config = this._getConfig(config)
        this._triggerArray = []

        var toggleList = SelectorEngine.find(SELECTOR_DATA_TOGGLE)

        var self = this;
        for (var i = 0, len = toggleList.length; i < len; i++) {
            var elem = toggleList[i]
            var selector = getSelectorFromElement(elem)
            var filterElement = SelectorEngine.find(selector)
                .filter(function(foundElem) {
                    return foundElem === self._element
                })

            if (selector !== null && filterElement.length) {
                this._selector = selector
                this._triggerArray.push(elem)
            }
        }

        this._initializeChildren()

        if (!this._config.parent) {
            this._addAriaAndCollapsedClass(this._triggerArray, this._isShown())
        }

        if (this._config.toggle) {
            this.toggle()
        }
    }

    // Getters

    Collapse.VERSION = VERSION;

    Collapse.Default = Default;

    Collapse.NAME = NAME;

    Collapse.DATA_KEY = 'bs.' + Collapse.NAME;

    Collapse.EVENT_KEY = '.' + Collapse.DATA_KEY;

    // Public

    Collapse.prototype.dispose = function() {
        Data.remove(this._element, Collapse.DATA_KEY)
        EventHandler.off(this._element, Collapse.EVENT_KEY)

        Object.getOwnPropertyNames(this).forEach(function(propertyName) {
            this[propertyName] = null
        })
    }

    Collapse.prototype._queueCallback = function(callback, element, isAnimated = true) {
        executeAfterTransition(callback, element, isAnimated)
    }

    Collapse.prototype.toggle = function() {
        if (this._isShown()) {
            this.hide()
        } else {
            this.show()
        }
    }

    Collapse.prototype.show = function() {
        if (this._isTransitioning || this._isShown()) {
            return
        }

        var actives = []
        var activesData

        if (this._config.parent) {
            var children = SelectorEngine.find(CLASS_NAME_DEEPER_CHILDREN, this._config.parent)
            actives = SelectorEngine.find(SELECTOR_ACTIVES, this._config.parent).filter(function(elem) {
                return !children.includes(elem)
            }) // remove children if greater depth
        }

        var container = SelectorEngine.findOne(this._selector)
        if (actives.length) {
            var tempActiveData = actives.find(function(elem) {
                return container !== elem
            })
            activesData = tempActiveData ? Collapse.getInstance(tempActiveData) : null

            if (activesData && activesData._isTransitioning) {
                return
            }
        }

        var startEvent = EventHandler.trigger(this._element, EVENT_SHOW)
        if (startEvent.defaultPrevented) {
            return
        }

        actives.forEach(function(elemActive) {
            if (container !== elemActive) {
                Collapse.getOrCreateInstance(elemActive, {toggle: false}).hide()
            }

            if (!activesData) {
                Data.set(elemActive, DATA_KEY, null)
            }
        })

        var dimension = this._getDimension()

        this._element.classList.remove(CLASS_NAME_COLLAPSE)
        this._element.classList.add(CLASS_NAME_COLLAPSING)

        this._element.style[dimension] = 0

        this._addAriaAndCollapsedClass(this._triggerArray, true)
        this._isTransitioning = true

        var self = this;
        var complete = function() {
            self._isTransitioning = false

            self._element.classList.remove(CLASS_NAME_COLLAPSING)
            self._element.classList.add(CLASS_NAME_COLLAPSE, CLASS_NAME_SHOW)

            self._element.style[dimension] = ''

            EventHandler.trigger(self._element, EVENT_SHOWN)
        }

        var capitalizedDimension = dimension[0].toUpperCase() + dimension.slice(1)
        var scrollSize = `scroll${capitalizedDimension}`

        this._queueCallback(complete, this._element, true)
        this._element.style[dimension] = `${this._element[scrollSize]}px`
    }

    Collapse.prototype.hide = function() {
        if (this._isTransitioning || !this._isShown()) {
            return
        }

        var startEvent = EventHandler.trigger(this._element, EVENT_HIDE)
        if (startEvent.defaultPrevented) {
            return
        }

        var dimension = this._getDimension()

        this._element.style[dimension] = `${this._element.getBoundingClientRect()[dimension]}px`

        reflow(this._element)

        this._element.classList.add(CLASS_NAME_COLLAPSING)
        this._element.classList.remove(CLASS_NAME_COLLAPSE, CLASS_NAME_SHOW)

        var triggerArrayLength = this._triggerArray.length
        for (var i = 0; i < triggerArrayLength; i++) {
            var trigger = this._triggerArray[i]
            var elem = getElementFromSelector(trigger)

            if (elem && !this._isShown(elem)) {
                this._addAriaAndCollapsedClass([trigger], false)
            }
        }

        this._isTransitioning = true

        var self = this;
        var complete = function() {
            self._isTransitioning = false
            self._element.classList.remove(CLASS_NAME_COLLAPSING)
            self._element.classList.add(CLASS_NAME_COLLAPSE)
            EventHandler.trigger(self._element, EVENT_HIDDEN)
        }

        this._element.style[dimension] = ''

        this._queueCallback(complete, this._element, true)
    }

    Collapse.prototype._isShown = function(element = this._element) {
        return element.classList.contains(CLASS_NAME_SHOW)
    }

    // Private

    Collapse.prototype._getConfig = function(config) {
        config = {
            ...Default,
            ...Manipulator.getDataAttributes(this._element),
            ...config
        }
        config.toggle = Boolean(config.toggle) // Coerce string values
        config.parent = getElement(config.parent)
        typeCheckConfig(NAME, config, DefaultType)
        return config
    }

    Collapse.prototype._getDimension = function() {
        return this._element.classList.contains(CLASS_NAME_HORIZONTAL) ? WIDTH : HEIGHT
    }

    Collapse.prototype._initializeChildren = function() {
        if (!this._config.parent) {
            return
        }

        var children = SelectorEngine.find(CLASS_NAME_DEEPER_CHILDREN, this._config.parent)
        SelectorEngine.find(SELECTOR_DATA_TOGGLE, this._config.parent).filter(function(elem) {
            return !children.includes(elem)
        })
        .forEach(function(element) {
            var selected = getElementFromSelector(element)

            if (selected) {
                this._addAriaAndCollapsedClass([element], this._isShown(selected))
            }
        })
    }

    Collapse.prototype._addAriaAndCollapsedClass = function(triggerArray, isOpen) {
        if (!triggerArray.length) {
            return
        }

        triggerArray.forEach(function(elem) {
            if (isOpen) {
                elem.classList.remove(CLASS_NAME_COLLAPSED)
            } else {
                elem.classList.add(CLASS_NAME_COLLAPSED)
            }

            elem.setAttribute('aria-expanded', isOpen)
        })
    }

    // Static

    Collapse.getInstance = function(element) {
        return Data.get(getElement(element), this.DATA_KEY)
    }

    Collapse.getOrCreateInstance = function(element, config = {}) {
        return this.getInstance(element) || new this(element, typeof config === 'object' ? config : null)
    }

    Collapse.jQueryInterface = function(config) {
        return this.each(function () {
            var _config = {}
            if (typeof config === 'string' && /show|hide/.test(config)) {
                _config.toggle = false
            }

            var data = Collapse.getOrCreateInstance(this, _config)

            if (typeof config === 'string') {
                if (typeof data[config] === 'undefined') {
                    throw new TypeError(`No method named "${config}"`)
                }

                data[config]()
            }
        })
    }

    /**
     * ------------------------------------------------------------------------
     * Data Api implementation
     * ------------------------------------------------------------------------
     */

    EventHandler.on(document, EVENT_CLICK_DATA_API, SELECTOR_DATA_TOGGLE, function (event) {
        // preventDefault only for <a> elements (which change the URL) not inside the collapsible element
        if (event.target.tagName === 'A' || (event.delegateTarget && event.delegateTarget.tagName === 'A')) {
            event.preventDefault()
        }

        var selector = getSelectorFromElement(this)
        var selectorElements = SelectorEngine.find(selector)

        selectorElements.forEach(function(element) {
            Collapse.getOrCreateInstance(element, {toggle: false}).toggle()
        })
    })

    /**
     * ------------------------------------------------------------------------
     * jQuery
     * ------------------------------------------------------------------------
     * add .Collapse to jQuery only if jQuery is present
     */

    defineJQueryPlugin(Collapse)

    return Collapse;
});
