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

    const defineJQueryPlugin = Util.defineJQueryPlugin;
    const executeAfterTransition = Util.executeAfterTransition;
    const getElement = Util.getElement;
    const getSelectorFromElement = Util.getSelectorFromElement;
    const getElementFromSelector = Util.getElementFromSelector;
    const reflow = Util.reflow;
    const typeCheckConfig = Util.typeCheckConfig;

    /**
     * ------------------------------------------------------------------------
     * Constants
     * ------------------------------------------------------------------------
     */

    const VERSION = '5.1.3';
    const NAME = 'collapse';
    const DATA_KEY = 'bs.collapse';
    const EVENT_KEY = `.${DATA_KEY}`;
    const DATA_API_KEY = '.data-api';

    const Default = {
        toggle: true,
        parent: null
    };

    const DefaultType = {
        toggle: 'boolean',
        parent: '(null|element)'
    };

    const EVENT_SHOW = `show${EVENT_KEY}`;
    const EVENT_SHOWN = `shown${EVENT_KEY}`;
    const EVENT_HIDE = `hide${EVENT_KEY}`;
    const EVENT_HIDDEN = `hidden${EVENT_KEY}`;
    const EVENT_CLICK_DATA_API = `click${EVENT_KEY}${DATA_API_KEY}`;

    const CLASS_NAME_SHOW = 'show';
    const CLASS_NAME_COLLAPSE = 'collapse';
    const CLASS_NAME_COLLAPSING = 'collapsing';
    const CLASS_NAME_COLLAPSED = 'collapsed';
    const CLASS_NAME_DEEPER_CHILDREN = `:scope .${CLASS_NAME_COLLAPSE} .${CLASS_NAME_COLLAPSE}`;
    const CLASS_NAME_HORIZONTAL = 'collapse-horizontal';

    const WIDTH = 'width';
    const HEIGHT = 'height';

    const SELECTOR_ACTIVES = '.collapse.show, .collapse.collapsing';
    const SELECTOR_DATA_TOGGLE = '[data-bs-toggle="collapse"]';

    /**
     * ------------------------------------------------------------------------
     * Class Definition
     * ------------------------------------------------------------------------
     */

    var Collapse = function(element, config) {
        element = getElement(element);

        if (!element) {
            return;
        }

        this._element = element;
        Data.set(this._element, DATA_KEY, this);

        this._isTransitioning = false;
        this._config = this._getConfig(config);
        this._triggerArray = [];

        const toggleList = SelectorEngine.find(SELECTOR_DATA_TOGGLE);

        for (let i = 0, len = toggleList.length; i < len; i++) {
            const elem = toggleList[i];
            const selector = getSelectorFromElement(elem);
            const filterElement = SelectorEngine.find(selector)
                .filter(foundElem => foundElem === this._element);

            if (selector !== null && filterElement.length) {
                this._selector = selector;
                this._triggerArray.push(elem);
            }
        }

        this._initializeChildren();

        if (!this._config.parent) {
            this._addAriaAndCollapsedClass(this._triggerArray, this._isShown());
        }

        if (this._config.toggle) {
            this.toggle();
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
        Data.remove(this._element, this.constructor.DATA_KEY);
        EventHandler.off(this._element, this.constructor.EVENT_KEY);

        Object.getOwnPropertyNames(this).forEach(propertyName => {
            this[propertyName] = null;
        })
    }

    Collapse.prototype._queueCallback = function(callback, element, isAnimated = true) {
        executeAfterTransition(callback, element, isAnimated);
    }

    Collapse.prototype.toggle = function() {
        if (this._isShown()) {
            this.hide();
        } else {
            this.show();
        }
    }

    Collapse.prototype.show = function() {
        if (this._isTransitioning || this._isShown()) {
            return;
        }

        let actives = [];
        let activesData;

        if (this._config.parent) {
            const children = SelectorEngine.find(CLASS_NAME_DEEPER_CHILDREN, this._config.parent);
            actives = SelectorEngine.find(SELECTOR_ACTIVES, this._config.parent).filter(elem => !children.includes(elem)); // remove children if greater depth
        }

        const container = SelectorEngine.findOne(this._selector);
        if (actives.length) {
            const tempActiveData = actives.find(elem => container !== elem);
            activesData = tempActiveData ? Collapse.getInstance(tempActiveData) : null;

            if (activesData && activesData._isTransitioning) {
                return;
            }
        }

        const startEvent = EventHandler.trigger(this._element, EVENT_SHOW);
        if (startEvent.defaultPrevented) {
            return;
        }

        actives.forEach(elemActive => {
            if (container !== elemActive) {
                Collapse.getOrCreateInstance(elemActive, {toggle: false}).hide();
            }

            if (!activesData) {
                Data.set(elemActive, DATA_KEY, null);
            }
        })

        const dimension = this._getDimension();

        this._element.classList.remove(CLASS_NAME_COLLAPSE);
        this._element.classList.add(CLASS_NAME_COLLAPSING);

        this._element.style[dimension] = 0;

        this._addAriaAndCollapsedClass(this._triggerArray, true);
        this._isTransitioning = true;

        const complete = () => {
            this._isTransitioning = false;

            this._element.classList.remove(CLASS_NAME_COLLAPSING);
            this._element.classList.add(CLASS_NAME_COLLAPSE, CLASS_NAME_SHOW);

            this._element.style[dimension] = '';

            EventHandler.trigger(this._element, EVENT_SHOWN);
        };

        const capitalizedDimension = dimension[0].toUpperCase() + dimension.slice(1);
        const scrollSize = `scroll${capitalizedDimension}`;

        this._queueCallback(complete, this._element, true);
        this._element.style[dimension] = `${this._element[scrollSize]}px`;
    }

    Collapse.prototype.hide = function() {
        if (this._isTransitioning || !this._isShown()) {
            return;
        }

        const startEvent = EventHandler.trigger(this._element, EVENT_HIDE);
        if (startEvent.defaultPrevented) {
            return;
        }

        const dimension = this._getDimension();

        this._element.style[dimension] = `${this._element.getBoundingClientRect()[dimension]}px`;

        reflow(this._element);

        this._element.classList.add(CLASS_NAME_COLLAPSING);
        this._element.classList.remove(CLASS_NAME_COLLAPSE, CLASS_NAME_SHOW);

        const triggerArrayLength = this._triggerArray.length;
        for (let i = 0; i < triggerArrayLength; i++) {
            const trigger = this._triggerArray[i];
            const elem = getElementFromSelector(trigger);

            if (elem && !this._isShown(elem)) {
                this._addAriaAndCollapsedClass([trigger], false);
            }
        }

        this._isTransitioning = true;

        const complete = () => {
            this._isTransitioning = false;
            this._element.classList.remove(CLASS_NAME_COLLAPSING);
            this._element.classList.add(CLASS_NAME_COLLAPSE);
            EventHandler.trigger(this._element, EVENT_HIDDEN);
        };

        this._element.style[dimension] = '';

        this._queueCallback(complete, this._element, true);
    }

    Collapse.prototype._isShown = function(element = this._element) {
        return element.classList.contains(CLASS_NAME_SHOW);
    }

    // Private

    Collapse.prototype._getConfig = function(config) {
        config = {
            ...Default,
            ...Manipulator.getDataAttributes(this._element),
            ...config
        };
        config.toggle = Boolean(config.toggle); // Coerce string values
        config.parent = getElement(config.parent);
        typeCheckConfig(NAME, config, DefaultType);
        return config;
    }

    Collapse.prototype._getDimension = function() {
        return this._element.classList.contains(CLASS_NAME_HORIZONTAL) ? WIDTH : HEIGHT;
    }

    Collapse.prototype._initializeChildren = function() {
        if (!this._config.parent) {
            return;
        }

        const children = SelectorEngine.find(CLASS_NAME_DEEPER_CHILDREN, this._config.parent);
        SelectorEngine.find(SELECTOR_DATA_TOGGLE, this._config.parent).filter(elem => !children.includes(elem))
            .forEach(element => {
                const selected = getElementFromSelector(element);

                if (selected) {
                    this._addAriaAndCollapsedClass([element], this._isShown(selected));
                }
            })
    }

    Collapse.prototype._addAriaAndCollapsedClass = function(triggerArray, isOpen) {
        if (!triggerArray.length) {
            return;
        }

        triggerArray.forEach(elem => {
            if (isOpen) {
                elem.classList.remove(CLASS_NAME_COLLAPSED);
            } else {
                elem.classList.add(CLASS_NAME_COLLAPSED);
            }

            elem.setAttribute('aria-expanded', isOpen);
        })
    }

    // Static

    Collapse.getInstance = function(element) {
        return Data.get(getElement(element), this.DATA_KEY);
    }

    Collapse.getOrCreateInstance = function(element, config = {}) {
        return this.getInstance(element) || new this(element, typeof config === 'object' ? config : null);
    }

    Collapse.jQueryInterface = function(config) {
        return this.each(function () {
            const _config = {};
            if (typeof config === 'string' && /show|hide/.test(config)) {
                _config.toggle = false;
            }

            const data = Collapse.getOrCreateInstance(this, _config);

            if (typeof config === 'string') {
                if (typeof data[config] === 'undefined') {
                    throw new TypeError(`No method named "${config}"`);
                }

                data[config]();
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
            event.preventDefault();
        }

        const selector = getSelectorFromElement(this);
        const selectorElements = SelectorEngine.find(selector);

        selectorElements.forEach(element => {
            Collapse.getOrCreateInstance(element, {toggle: false}).toggle();
        })
    })

    /**
     * ------------------------------------------------------------------------
     * jQuery
     * ------------------------------------------------------------------------
     * add .Collapse to jQuery only if jQuery is present
     */

    defineJQueryPlugin(Collapse);

    return Collapse;
});
