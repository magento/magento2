define([
    'ko',
    './abstract'
], function (ko, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            caption: 'Select...',
            options: [
                {
                    label: '1 column',
                    value: '1column'
                }, {
                    label: '2 column',
                    value: "2column"
                }, {
                    label: '3 column',
                    value: '3column'
                }
            ],
            listVisible: false,
            multiselectFocus: false,
            selectedCounter: 0,
            hoveredElementIndex: null
        },

        initialize: function () {
            this._super();

            if (this.customEntry) {
                this.initInput();
            }

            if (this.filterBy) {
                this.initFilter();
            }

            return this;
        },

        /**
         * Calls 'initObservable' of parent, initializes 'options' and 'initialOptions'
         *     properties, calls 'setOptions' passing options to it
         *
         * @returns {Select} Chainable.
         */
        initObservable: function () {
            var i = 0,
                length = this.options.length,
                curOption;

            this._super();
            this.initialOptions = this.options;

            this.observe('multiselectFocus');
            this.observe('caption');
            //this.observe('selectedCounter');

            for (i; i<length; i++) {
                curOption = this.options[i];
                curOption.visible = ko.observable(false);
                curOption.hovered = ko.observable(false);
            }

            this.observe('listVisible');


            return this;
        },
        proxyClick: function () {
            this.toggleListVisible();
        },
        onMouseEnter: function(data, index) {
            this.hoveredElementIndex = index;
            data.hovered(true);
        },
        onMouseLeave: function(data) {
            data.hovered(false);
        },
        onFocusIn: function () {
            this.multiselectFocus(true);
            //this._set().focus(true);
        },
        keydownSwitcher: function (data, event) {
            var enterBtn = 13,
                escapeBtn = 27,
                pageDownBtn = 40,
                pageUpBtn = 38,
                spaceBtn = 32,
                handlers = this.keydownHandlers();

            switch (event.keyCode) {
                case enterBtn: {
                    handlers.enter();
                    break;
                }
                case spaceBtn: {
                    handlers.enter();
                    break;
                }
                case escapeBtn: {
                    handlers.escape();
                    break;
                }
                case pageDownBtn: {
                    handlers.pageDown();
                    break;
                }
                case pageUpBtn: {
                    handlers.pageUp();
                    break;
                }
                default: {
                    return true;
                }
            }
        },
        keydownHandlers: function () {
            var t = this,
                is = t._is(),
                set = t._set();

            return {
                enter: function () {
                    if (is.listVisible()) {
                        t.hoveredElementIndex !== null ?
                            t.proxyOptionsClick(t.options[t.hoveredElementIndex]) : false;
                    } else {
                        set.listVisible(true);
                    }
                },
                escape: function () {
                    is.listVisible() ? set.listVisible(false) : false;
                },
                pageDown: function () {
                    if (t.hoveredElementIndex !== null) {
                        t.onMouseLeave(t.options[t.hoveredElementIndex]);
                        if (t.hoveredElementIndex !== t.options.length-1) {
                            t.hoveredElementIndex++;
                            t.onMouseEnter(t.options[t.hoveredElementIndex], t.hoveredElementIndex);
                        } else {
                            t.onMouseEnter(t.options[0], 0);
                        }
                    } else {
                        t.onMouseEnter(t.options[0], 0);
                    }
                },
                pageUp: function () {
                    if (t.hoveredElementIndex !== null) {
                        t.onMouseLeave(t.options[t.hoveredElementIndex]);
                        if (t.hoveredElementIndex !== 0) {
                            t.hoveredElementIndex--;
                            t.onMouseEnter(t.options[t.hoveredElementIndex], t.hoveredElementIndex);
                        } else {
                            t.onMouseEnter(t.options[t.options.length-1], t.options.length-1);
                        }
                    } else {
                        t.onMouseEnter(t.options[t.options.length-1], t.options.length-1);
                    }
                }
            };
        },
        onFocusOut: function () {
            var set = this._set(),
                is = this._is();

            //this._set().focus(false);
            this.multiselectFocus(false);

            if (is.listVisible()) {
                set.listVisible(false);
            }
        },
        toggleFocus: function () {
            this.multiselectFocus(!this.multiselectFocus());
        },
        toggleListVisible: function () {
            var visible = !this.listVisible();

            if (!visible && this.hoveredElementIndex) {
                this.onMouseLeave(this.options[this.hoveredElementIndex]);
                this.hoveredElementIndex = null;
            }

            this.listVisible(!this.listVisible());

            return this;
        },
        _get: function () {
            var t = this;

            return {
                selected: function () {
                    var i = 0,
                        length = t.options.length,
                        cur,
                        array = [];

                    for (i; i < length; i++) {
                        cur = t.options[i];
                        cur.visible() ? array.push(cur) : false;
                    }

                    return array;
                }
            };
        },
        _set: function () {
            var t = this;

            return {
                listVisible: function (value) {
                    if (!value && t.hoveredElementIndex) {
                        t.onMouseLeave(t.options[t.hoveredElementIndex]);
                        t.hoveredElementIndex = null;
                    }
                    t.listVisible(value);
                },
                focus: function (value) {
                    t.multiselectFocus(value);
                },
                caption: function () {
                    t.selectedCounter === 0 ? t.caption('Select...')
                        : t.selectedCounter === 1 ? t.caption(t._get().selected()[0].label)
                            : t.caption(t.selectedCounter + ' Selected');
                },
                hovered: function (obj, value) {
                    obj.hovered(value);
                }
            };
        },
        _is: function () {
            var t = this;

            return {
                listVisible: function () {
                    return t.listVisible();
                }
            };
        },
        proxyOptionsClick: function (data) {
            var visible = !data.visible();

            data.visible(visible);
            visible ? this.selectedCounter++ : this.selectedCounter--;
            this._set().caption(data);

        }
    });
});