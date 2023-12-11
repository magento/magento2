/**
 * @file Jeditable - jQuery in place edit plugin
 * @home https://github.com/NicolasCARPi/jquery_jeditable
 * @author Mika Tuupola, Dylan Verheul, Nicolas CARPi
 * @copyright © 2006 Mika Tuupola, Dylan Verheul, Nicolas CARPi
 * @licence MIT (see LICENCE file)
 * @name Jquery-jeditable
 * @type  jQuery
 *
 * @param {String|Function} target - URL or Function to send edited content to. Can also be 'disable', 'enable', or 'destroy'
 * @param {Object} [options] - Additional options
 * @param {Object} [options.ajaxoptions] - jQuery Ajax options. See https://api.jquery.com/jQuery.ajax/
 * @param {Function} [options.before] - Function to be executed before going into edit mode
 * @param {Function} [options.callback] - function(result, settings, submitdata) Function to run after submitting edited content
 * @param {String} [options.cancel] - Cancel button value, empty means no button
 * @param {String} [options.cancelcssclass] - CSS class to apply to cancel button
 * @param {Number} [options.cols] - Number of columns if using textarea
 * @param {String} [options.cssclass] - CSS class to apply to input form; use 'inherit' to copy from parent
 * @param {String} [options.inputcssclass] - CSS class to apply to input. 'inherit' to copy from parent
 * @param {Function} [options.intercept] - Intercept the returned data so you have a chance to process it before returning it in the page
 * @param {String|Function} [options.data] - Content loaded in the form
 * @param {String} [options.event='click'] - jQuery event such as 'click' or 'dblclick'. See https://api.jquery.com/category/events/
 * @param {String} [options.formid] - Give an id to the form that is produced
 * @param {String|Number} [options.height='auto'] - Height of the element in pixels or 'auto' or 'none'
 * @param {String} [options.id='id'] - POST parameter name of edited div id
 * @param {String} [options.indicator] - Indicator html to show when saving
 * @param {String} [options.label] - Label for the form
 * @param {String} [options.list] - HTML5 attribute for text input. Will suggest from a datalist with id of the list option
 * @param {String|Function} [options.loaddata] - Extra parameters to pass when fetching content before editing
 * @param {String} [options.loadtext='Loading…'] - Text to display while loading external content
 * @param {String} [options.loadtype='GET'] - Request type for loadurl (GET or POST)
 * @param {String} [options.loadurl] - URL to fetch input content before editing
 * @param {Number} [options.max] - Maximum value for number type
 * @param {String} [options.maxlength] - The maximum number of character in the text field
 * @param {String} [options.method] - Method to use to send edited content (POST or PUT)
 * @param {Number} [options.min] - Minimum value for number type
 * @param {Boolean} [options.multiple] - Allow multiple selections in a select input
 * @param {String} [options.name='value'] - POST parameter name of edited content
 * @param {String|Function} [options.onblur='cancel'] - Use 'cancel', 'submit', 'ignore' or function. If function returns false, the form is cancelled.
 * @param {Function} [options.onedit] - function triggered upon edition; will cancel edition if it returns false
 * @param {Function} [options.onerror] - function(settings, original, xhr) { ... } called on error
 * @param {Function} [options.onreset] - function(settings, original) { ... } called before reset
 * @param {Function} [options.onsubmit] - function(settings, original) { ... } called before submit
 * @param {String} [options.pattern] - HTML5 attribute for text or URL input
 * @param {String} [options.placeholder='Click to edit'] - Placeholder text or html to insert when element is empty
 * @param {Number} [options.rows] - number of rows if using textarea
 * @param {Boolean} [options.select] - When true text is selected
 * @param {Function} [options.showfn]- Function that can animate the element when switching to edit mode
 * @param {String} [options.size] - The size of the text field
 * @param {String} [options.sortselectoptions] - Sort the options of a select form
 * @param {Number} [options.step] - Step size for number type
 * @param {String} [options.style] - Style to apply to input form; 'inherit' to copy from parent
 * @param {String} [options.submit] - submit button value, empty means no button
 * @param {String} [options.submitcssclass] - CSS class to apply to submit button
 * @param {Object|Function} [options.submitdata] - Extra parameters to send when submitting edited content. function(revert, settings, submitdata)
 * @param {String} [options.tooltip] - Tooltip text that appears on hover (via title attribute)
 * @param {String} [options.type='text'] - text, textarea, select, email, number, url (or any 3rd party input type)
 * @param {String|Number} [options.width='auto'] - The width of the element in pixels or 'auto' or 'none'
 *
 * @example <caption>Simple usage example:</caption>
 * $(".editable").editable("save.php", {
 *     cancel : 'Cancel',
 *     submit : 'Save',
 *     tooltip : "Click to edit...",
 * });
 */
(function($) {

    'use strict';

    // Keyboard accessibility/WAI-ARIA - allow users to navigate to an editable element using TAB/Shift+TAB
    $.fn.editableAriaShim = function () {
        this.attr({
            role: 'button',
            tabindex: 0
        });
        return this; // <-- object chaining.
    };

    // EDITABLE function
    $.fn.editable = function(target, options) {

        if ('disable' === target) {
            $(this).data('disabled.editable', true);
            return;
        }
        if ('enable' === target) {
            $(this).data('disabled.editable', false);
            return;
        }
        if ('destroy' === target) {
            $(this)
                .off($(this).data('event.editable'))
                .removeData('disabled.editable')
                .removeData('event.editable');
            return;
        }
        var settings = $.extend({}, $.fn.editable.defaults, {target:target}, options);

        /* setup some functions */
        var plugin   = $.editable.types[settings.type].plugin || function() { };
        var submit   = $.editable.types[settings.type].submit || function() { };
        var buttons  = $.editable.types[settings.type].buttons || $.editable.types.defaults.buttons;
        var content  = $.editable.types[settings.type].content || $.editable.types.defaults.content;
        var element  = $.editable.types[settings.type].element || $.editable.types.defaults.element;
        var reset    = $.editable.types[settings.type].reset || $.editable.types.defaults.reset;
        var destroy  = $.editable.types[settings.type].destroy || $.editable.types.defaults.destroy;
        var callback = settings.callback || function() { };
        var intercept = settings.intercept || function(s) { return s; };
        var onedit   = settings.onedit   || function() { };
        var onsubmit = settings.onsubmit || function() { };
        var onreset  = settings.onreset  || function() { };
        var onerror  = settings.onerror  || reset;
        var before   = settings.before || false;

        // TOOLTIP
        if (settings.tooltip) {
            $(this).attr('title', settings.tooltip);
        }

        return this.each(function() {

            /* Save this to self because this changes when scope changes. */
            var self = this;

            /* Save so it can be later used by $.editable('destroy') */
            $(this).data('event.editable', settings.event);

            /* If element is empty add something clickable (if requested) */
            if (!$(this).html().trim()) {
                $(this).html(settings.placeholder);
            }

            if ('destroy' === target) {
                destroy.apply($(this).find('form'), [settings, self]);
                return;
            }

            // EVENT IS FIRED
            $(this).on(settings.event, function(e) {

                /* Abort if element is disabled. */
                if (true === $(this).data('disabled.editable')) {
                    return;
                }

                // do nothing if user press Tab again, just go to next element, not into edit mode
                if (e.which === 9) {
                    return;
                }

                /* Prevent throwing an exception if edit field is clicked again. */
                if (self.editing) {
                    return;
                }

                /* Abort if onedit hook returns false. */
                if (false === onedit.apply(this, [settings, self, e])) {
                    return;
                }

                /* execute the before function if any was specified */
                if (settings.before && (typeof settings.before === 'function')) {
                    settings.before(e);
                } else if (settings.before && !(typeof settings.before === 'function')) {
                    throw "The 'before' option needs to be provided as a function!";
                }

                /* Prevent default action and bubbling. */
                e.preventDefault();
                e.stopPropagation();

                /* Remove tooltip. */
                if (settings.tooltip) {
                    $(self).removeAttr('title');
                }

                /* Remove placeholder text, replace is here because of IE. */
                if ($(this).html().toLowerCase().replace(/(;|"|\/)/g, '') ===
                    settings.placeholder.toLowerCase().replace(/(;|"|\/)/g, '')) {
                    $(this).html('');
                }

                self.editing    = true;
                self.revert     = $(self).text();
                $(self).html('');

                /* Create the form object. */
                var form = $('<form />');

                /* Apply css or style or both. */
                if (settings.cssclass) {
                    if ('inherit' === settings.cssclass) {
                        form.attr('class', $(self).attr('class'));
                    } else {
                        form.attr('class', settings.cssclass);
                    }
                }

                if (settings.style) {
                    if ('inherit' === settings.style) {
                        form.attr('style', $(self).attr('style'));
                        /* IE needs the second line or display won't be inherited. */
                        form.css('display', $(self).css('display'));
                    } else {
                        form.attr('style', settings.style);
                    }
                }

                // add a label if it exists
                if (settings.label) {
                    form.append('<label>' + settings.label + '</label>');
                }

                // add an ID to the form
                if (settings.formid) {
                    form.attr('id', settings.formid);
                }

                /* Add main input element to form and store it in input. */
                var input = element.apply(form, [settings, self]);

                if (settings.inputcssclass) {
                    if ('inherit' === settings.inputcssclass) {
                        input.attr('class', $(self).attr('class'));
                    } else {
                        input.attr('class', settings.inputcssclass);
                    }
                }

                /* Set input content via POST, GET, given data or existing value. */
                var input_content;

                // timeout function
                var t;
                var isSubmitting = false;

                if (settings.loadurl) {
                    t = self.setTimeout(function() {
                        input.disabled = true;
                    }, 100);
                    $(self).html(settings.loadtext);

                    var loaddata = {};
                    loaddata[settings.id] = self.id;
                    if (typeof settings.loaddata === 'function') {
                        $.extend(loaddata, settings.loaddata.apply(self, [self.revert, settings]));
                    } else {
                        $.extend(loaddata, settings.loaddata);
                    }
                    $.ajax({
                        type : settings.loadtype,
                        url  : settings.loadurl,
                        data : loaddata,
                        async: false,
                        cache : false,
                        success: function(result) {
                            self.clearTimeout(t);
                            input_content = result;
                            input.disabled = false;
                        }
                    });
                } else if (settings.data) {
                    input_content = settings.data;
                    if (typeof settings.data === 'function') {
                        input_content = settings.data.apply(self, [self.revert, settings]);
                    }
                } else {
                    input_content = self.revert;
                }
                content.apply(form, [input_content, settings, self]);

                input.attr('name', settings.name);

                /* adjust the width of the element to account for the margin/padding/border */
                if (settings.width !== 'none') {
                    var adj_width = settings.width - (input.outerWidth(true) - settings.width);
                    input.width(adj_width);
                }

                /* Add buttons to the form. */
                buttons.apply(form, [settings, self]);

                /* Add created form to self. */
                if (settings.showfn && (typeof settings.showfn === 'function')) {
                    form.hide();
                }

                // clear the loadtext that we put here before
                $(self).html('');

                $(self).append(form);

                // execute the showfn
                if (settings.showfn && (typeof settings.showfn === 'function')) {
                    settings.showfn(form);
                }

                /* Attach 3rd party plugin if requested. */
                plugin.apply(form, [settings, self]);

                /* Focus to first visible form element. */
                form.find(':input:visible:enabled:first').trigger('focus');

                /* Highlight input contents when requested. */
                if (settings.select) {
                    input.trigger('select');
                }

                /* discard changes if pressing esc */
                $(this).on('keydown', function(e) {
                    if (e.which === 27) {
                        e.preventDefault();
                        reset.apply(form, [settings, self]);
                        /* allow shift+enter to submit form (required for textarea) */
                    } else if (e.which == 13 && e.shiftKey){
                        e.preventDefault();
                        form.trigger('submit');
                    }
                });

                /* Discard, submit or nothing with changes when clicking outside. */
                /* Do nothing is usable when navigating with tab. */
                if ('cancel' === settings.onblur) {
                    input.on('blur', function(e) {
                        /* Prevent canceling if submit was clicked. */
                        t = self.setTimeout(function() {
                            reset.apply(form, [settings, self]);
                        }, 500);
                    });
                } else if ('submit' === settings.onblur) {
                    input.on('blur', function(e) {
                        /* Prevent double submit if submit was clicked. */
                        t = self.setTimeout(function() {
                            form.trigger('submit');
                        }, 200);
                    });
                } else if (typeof settings.onblur === 'function') {
                    input.on('blur', function(e) {
                        // reset the form if the onblur function returns false
                        if (false === settings.onblur.apply(self, [input.val(), settings, form])) {
                            reset.apply(form, [settings, self]);
                        }
                    });
                }

                form.on('submit', function(e) {

                    /* Do no submit. */
                    e.preventDefault();
                    e.stopPropagation();

                    if (isSubmitting) {
                        // we are already submitting! Stop right here.
                        return false;
                    } else {
                        isSubmitting = true;
                    }

                    if (t) {
                        self.clearTimeout(t);
                    }

                    /* Call before submit hook. */
                    /* If it returns false abort submitting. */
                    isSubmitting = false !== onsubmit.apply(form, [settings, self]);
                    if (isSubmitting) {
                        /* Custom inputs call before submit hook. */
                        /* If it returns false abort submitting. */
                        isSubmitting = false !== submit.apply(form, [settings, self]);
                        if (isSubmitting) {

                            /* Check if given target is function */
                            if (typeof settings.target === 'function') {
                                /* Callback function to handle the target response */
                                var responseHandler = function(value, complete) {
                                    isSubmitting = false;
                                    if (false !== complete) {
                                        $(self).html(value);
                                        self.editing = false;
                                        callback.apply(self, [self.innerText, settings]);
                                        if (!$(self).html().trim()) {
                                            $(self).html(settings.placeholder);
                                        }
                                    }
                                };
                                /* Call the user target function */
                                var userTarget = settings.target.apply(self, [input.val(), settings, responseHandler]);
                                /* Handle the target function return for compatibility */
                                if (false !== userTarget && undefined !== userTarget) {
                                    responseHandler(userTarget, userTarget);
                                }

                            } else {
                                /* Add edited content and id of edited element to POST. */
                                var submitdata = {};
                                submitdata[settings.name] = input.val();
                                submitdata[settings.id] = self.id;
                                /* Add extra data to be POST:ed. */
                                if (typeof settings.submitdata === 'function') {
                                    $.extend(submitdata, settings.submitdata.apply(self, [self.revert, settings, submitdata]));
                                } else {
                                    $.extend(submitdata, settings.submitdata);
                                }

                                /* Quick and dirty PUT support. */
                                if ('PUT' === settings.method) {
                                    submitdata._method = 'put';
                                }

                                // SHOW INDICATOR
                                $(self).html(settings.indicator);

                                /* Defaults for ajaxoptions. */
                                var ajaxoptions = {
                                    type    : 'POST',
                                    complete: function (xhr, status) {
                                        isSubmitting = false;
                                    },
                                    data    : submitdata,
                                    dataType: 'html',
                                    url     : settings.target,
                                    success : function(result, status) {

                                        // INTERCEPT
                                        result = intercept.apply(self, [result, status]);

                                        if (ajaxoptions.dataType === 'html') {
                                            $(self).html(result);
                                        }
                                        self.editing = false;
                                        callback.apply(self, [result, settings, submitdata]);
                                        if (!$(self).html().trim()) {
                                            $(self).html(settings.placeholder);
                                        }
                                    },
                                    error   : function(xhr, status, error) {
                                        onerror.apply(form, [settings, self, xhr]);
                                    }
                                };

                                /* Override with what is given in settings.ajaxoptions. */
                                $.extend(ajaxoptions, settings.ajaxoptions);
                                $.ajax(ajaxoptions);
                            }
                        }
                    }

                    /* Show tooltip again. */
                    $(self).attr('title', settings.tooltip);
                    return false;
                });
            });

            // PRIVILEGED METHODS

            // RESET
            self.reset = function(form) {
                /* Prevent calling reset twice when blurring. */
                if (self.editing) {
                    /* Before reset hook, if it returns false abort resetting. */
                    if (false !== onreset.apply(form, [settings, self])) {
                        $(self).text(self.revert);
                        self.editing   = false;
                        if (!$(self).html().trim()) {
                            $(self).html(settings.placeholder);
                        }
                        /* Show tooltip again. */
                        if (settings.tooltip) {
                            $(self).attr('title', settings.tooltip);
                        }
                    }
                }
            };

            // DESTROY
            self.destroy = function(form) {
                $(self)
                    .off($(self).data('event.editable'))
                    .removeData('disabled.editable')
                    .removeData('event.editable');

                self.clearTimeouts();

                if (self.editing) {
                    reset.apply(form, [settings, self]);
                }
            };

            // CLEARTIMEOUT
            self.clearTimeout = function(t) {
                var timeouts = $(self).data('timeouts');
                clearTimeout(t);
                if(timeouts) {
                    var i = timeouts.indexOf(t);
                    if(i > -1) {
                        timeouts.splice(i, 1);
                        if(timeouts.length <= 0) {
                            $(self).removeData('timeouts');
                        }
                    } else {
                        console.warn('jeditable clearTimeout could not find timeout '+t);
                    }
                }
            };

            // CLEAR ALL TIMEOUTS
            self.clearTimeouts = function () {
                var timeouts = $(self).data('timeouts');
                if(timeouts) {
                    for(var i = 0, n = timeouts.length; i < n; ++i) {
                        clearTimeout(timeouts[i]);
                    }
                    timeouts.length = 0;
                    $(self).removeData('timeouts');
                }
            };

            // SETTIMEOUT
            self.setTimeout = function(callback, time) {
                var timeouts = $(self).data('timeouts');
                var t = setTimeout(function() {
                    callback();
                    self.clearTimeout(t);
                }, time);
                if(!timeouts) {
                    timeouts = [];
                    $(self).data('timeouts', timeouts);
                }
                timeouts.push(t);
                return t;
            };
        });
    };

    var _supportInType = function (type) {
        var i = document.createElement('input');
        i.setAttribute('type', type);
        return i.type !== 'text' ? type : 'text';
    };


    $.editable = {
        types: {
            defaults: {
                element : function(settings, original) {
                    var input = $('<input type="hidden"></input>');
                    $(this).append(input);
                    return(input);
                },
                content : function(string, settings, original) {
                    $(this).find(':input:first').val(string);
                },
                reset : function(settings, original) {
                    original.reset(this);
                },
                destroy: function(settings, original) {
                    original.destroy(this);
                },
                buttons : function(settings, original) {
                    var form = this;
                    var submit;
                    if (settings.submit) {
                        /* If given html string use that. */
                        if (settings.submit.match(/>$/)) {
                            submit = $(settings.submit).on('click', function() {
                                if (submit.attr('type') !== 'submit') {
                                    form.trigger('submit');
                                }
                            });
                            /* Otherwise use button with given string as text. */
                        } else {
                            submit = $('<button type="submit" />');
                            submit.html(settings.submit);
                            if (settings.submitcssclass) {
                                submit.addClass(settings.submitcssclass);
                            }
                        }
                        $(this).append(submit);
                    }
                    if (settings.cancel) {
                        var cancel;
                        /* If given html string use that. */
                        if (settings.cancel.match(/>$/)) {
                            cancel = $(settings.cancel);
                            /* otherwise use button with given string as text */
                        } else {
                            cancel = $('<button type="cancel" />');
                            cancel.html(settings.cancel);
                            if (settings.cancelcssclass) {
                                cancel.addClass(settings.cancelcssclass);
                            }
                        }
                        $(this).append(cancel);

                        $(cancel).on('click', function(event) {
                            var reset;
                            if (typeof $.editable.types[settings.type].reset === 'function') {
                                reset = $.editable.types[settings.type].reset;
                            } else {
                                reset = $.editable.types.defaults.reset;
                            }
                            reset.apply(form, [settings, original]);
                            return false;
                        });
                    }
                }
            },
            text: {
                element : function(settings, original) {
                    var input = $('<input />').attr({
                        autocomplete: 'off',
                        list: settings.list,
                        maxlength: settings.maxlength,
                        pattern: settings.pattern,
                        placeholder: settings.placeholder,
                        tooltip: settings.tooltip,
                        type: 'text'
                    });

                    if (settings.width  !== 'none') {
                        input.css('width', settings.width);
                    }

                    if (settings.height !== 'none') {
                        input.css('height', settings.height);
                    }

                    if (settings.size) {
                        input.attr('size', settings.size);
                    }

                    if (settings.maxlength) {
                        input.attr('maxlength', settings.maxlength);
                    }

                    $(this).append(input);
                    return(input);
                }
            },

            // TEXTAREA
            textarea: {
                element : function(settings, original) {
                    var textarea = $('<textarea></textarea>');
                    if (settings.rows) {
                        textarea.attr('rows', settings.rows);
                    } else if (settings.height !== 'none') {
                        textarea.height(settings.height);
                    }
                    if (settings.cols) {
                        textarea.attr('cols', settings.cols);
                    } else if (settings.width !== 'none') {
                        textarea.width(settings.width);
                    }

                    if (settings.maxlength) {
                        textarea.attr('maxlength', settings.maxlength);
                    }

                    $(this).append(textarea);
                    return(textarea);
                }
            },

            // SELECT
            select: {
                element : function(settings, original) {
                    var select = $('<select />');

                    if (settings.multiple) {
                        select.attr('multiple', 'multiple');
                    }

                    $(this).append(select);
                    return(select);
                },
                content : function(data, settings, original) {
                    var json;
                    // If it is string assume it is json
                    if (String === data.constructor) {
                        json = JSON.parse(data);
                    } else {
                        // Otherwise assume it is a hash already
                        json = data;
                    }

                    // Create tuples for sorting
                    var tuples = [];
                    var key;

                    if (Array.isArray(json) && json.every(Array.isArray)) {
                        // Process list of tuples
                        tuples = json // JSON already contains list of [key, value]
                        json = {};
                        tuples.forEach(function(e) {
                            json[e[0]] = e[1]; // Recreate json object to comply with following code
                        });
                    } else {
                        // Process object
                        for (key in json) {
                            tuples.push([key, json[key]]); // Store: [key, value]
                        }
                    }

                    if (settings.sortselectoptions) {
                        // sort it
                        tuples.sort(function (a, b) {
                            a = a[1];
                            b = b[1];
                            return a < b ? -1 : (a > b ? 1 : 0);
                        });
                    }
                    // now add the options to our select
                    var option;
                    for (var i = 0; i < tuples.length; i++) {
                        key = tuples[i][0];
                        var value = tuples[i][1];

                        if (!json.hasOwnProperty(key)) {
                            continue;
                        }

                        if (key !== 'selected') {
                            option = $('<option />').val(key).append(value);

                            // add the selected prop if it's the same as original or if the key is 'selected'
                            if (json.selected === key || key === String.prototype.trim.call(original.revert == null ? "" : original.revert)) {
                                $(option).prop('selected', 'selected');
                            }

                            $(this).find('select').append(option);
                        }
                    }

                    // submit on change if no submit button defined
                    if (!settings.submit) {
                        var form = this;
                        $(this).find('select').change(function() {
                            form.trigger('submit');
                        });
                    }
                }
            },

            // NUMBER
            number: {
                element: function (settings, original) {
                    var input = $('<input />').attr({
                        maxlength: settings.maxlength,
                        placeholder: settings.placeholder,
                        min : settings.min,
                        max : settings.max,
                        step: settings.step,
                        tooltip: settings.tooltip,
                        type: _supportInType('number')
                    });
                    if (settings.width  !== 'none') {
                        input.css('width', settings.width);
                    }
                    $(this).append(input);
                    return input;
                }
            },

            // EMAIL
            email: {
                element: function (settings, original) {
                    var input = $('<input />').attr({
                        maxlength: settings.maxlength,
                        placeholder: settings.placeholder,
                        tooltip: settings.tooltip,
                        type: _supportInType('email')
                    });
                    if (settings.width  !== 'none') {
                        input.css('width', settings.width);
                    }
                    $(this).append(input);
                    return input;
                }
            },

            // URL
            url: {
                element: function (settings, original) {
                    var input = $('<input />').attr({
                        maxlength: settings.maxlength,
                        pattern: settings.pattern,
                        placeholder: settings.placeholder,
                        tooltip: settings.tooltip,
                        type: _supportInType('url')
                    });
                    if (settings.width  !== 'none') {
                        input.css('width', settings.width);
                    }
                    $(this).append(input);
                    return input;
                }
            }
        },

        // add new input type
        addInputType: function(name, input) {
            $.editable.types[name] = input;
        }
    };

    /* Publicly accessible defaults. */
    $.fn.editable.defaults = {
        name       : 'value',
        id         : 'id',
        type       : 'text',
        width      : 'auto',
        height     : 'auto',
        // Keyboard accessibility - use mouse click OR press any key to enable editing
        event      : 'click.editable keydown.editable',
        onblur     : 'cancel',
        tooltip    : 'Click to edit',
        loadtype   : 'GET',
        loadtext   : 'Loading...',
        placeholder: 'Click to edit',
        sortselectoptions: false,
        loaddata   : {},
        submitdata : {},
        ajaxoptions: {}
    };

})(jQuery);
