/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true */
(function (root, factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            "jquery",
            "mage/template",
            "jquery/ui",
            "mage/translate-inline",
            "mage/translate"
        ], factory);
    } else {
        factory(root.jQuery, root.mageTemplate);
    }
}(this, function ($, mageTemplate) {
    'use strict';

    /**
     * Widget for a dialog to edit translations.
     */
    $.widget("mage.translateInlineDialogVde", $.mage.translateInline, {
        options: {
            translateForm: {
                template: '#translate-inline-dialog-form-template',
                data: {
                    selector: '#translate-inline-dialog-form'
                }
            },
            dialogClass: "translate-dialog",
            draggable: false,
            modal: false,
            resizable: false,
            height: "auto",
            minHeight: 0,
            buttons: [{
                text: $.mage.__('Cancel'),
                "class" : "translate-dialog-cancel",
                click: function() {
                   $(this).translateInlineDialogVde('close');
                }
            },
            {
                text: $.mage.__('Save'),
                "class" : "translate-dialog-save",
                click: function(e) {
                    $(this).translateInlineDialogVde('submit');
                }
            }],

            area: "vde",
            ajaxUrl: null,
            textTranslations: null,
            imageTranslations: null,
            scriptTranslations: null,
            translateMode: null,
            translateModes : ["text", "script", "alt"]
        },

        /**
         * Identifies if the form is already being submitted.
         *
         * @type {boolean}
         */
        isSubmitting : false,

        /**
         * Identifies if inline text is being editied.  Only one element can be edited at a time.
         *
         * @type {boolean}
         */
        isBeingEdited : false,

        /**
         * Creates the translation dialog widget. Fulfills jQuery WidgetFactory _create hook.
         */
        _create: function() {
            this._super();
            // Unbind previously bound events that may be present from previous loads of vde container.
            if (parent && parent.jQuery) {
                parent.jQuery('[data-frame="editor"]')
                    .off('modeChange')
                    .on('modeChange', $.proxy(this._checkTranslateEditing, this));
            }
        },

        openWithWidget: function(e, widget, callback) {
            if (widget && callback) {
                this.callback = callback;
                this.element.html(this._prepareContent($(widget.element).data('translate')));
                this.triggerElement = widget.element;
                $(window).on('resize.translateInlineDialogVde', $.proxy(this._positionNearTarget, this));
                this._positionNearTarget();
            }
            this.open(arguments);
        },

        _positionNearTarget: function() {
            this.option('position', { of : this.triggerElement, my: "left top", at: "left-3 top-9" });
            this.option('width', $(this.triggerElement).width());
        },

        close: function() {
            this._super();
            this._onCancel();
            this.isBeingEdited = false;
            $(window).off('resize.translateInlineVdeDialog');
        },

        /**
         * Shows translate mode applicable css styles.
         */
        toggleStyle: function(mode) {
        // TODO: need to remove eventually
            this._toggleOutline(mode);

            this.options.textTranslations.translateInlineVde('toggleIcon', mode);
            this.options.imageTranslations.translateInlineImageVde('toggleIcon', mode);
            this.options.scriptTranslations.translateInlineScriptVde('toggleIcon', mode);
        },

        /**
         * Determine if user has modified inline translation text, but has not saved it.
         */
        _checkTranslateEditing: function(event, data) {
             if (this.isBeingEdited) {
                alert(data.alert_message);
                data.is_being_edited = true;
            }
            else {
                // Disable inline translation.
                var url = parent.jQuery('[data-frame="editor"]').attr('src');
                var dataDisable = {
                    frameUrl: url.substring(0, url.indexOf('translation_mode')),
                    mode: this.options.translateMode
                };
                parent.jQuery('[vde-translate]').trigger('disableInlineTranslation', dataDisable);

                // Inline translation text is not being edited.  Continue on.
                parent.jQuery('[data-frame="editor"]').trigger(data.next_action, data);
             }
        },

        _prepareContent: function() {
            var content = this._superApply(arguments);
            this._on(content.find('textarea[data-translate-input-index]'), {
                keydown: function(e) {
                    var keyCode = $.ui.keyCode;
                    switch (e.keyCode) {
                        case keyCode.ESCAPE:
                            e.preventDefault();
                            this.close();
                            break;
                        case keyCode.ENTER:
                            e.preventDefault();
                            this._formSubmit();
                            break;
                        default:
                            /* keep track of the fact that translate text has been changed */
                            this.isBeingEdited = true;
                    }
                }
            });
            this._on(content.find(this.options.translateForm.data.selector), {
                submit: function(e) {
                    e.preventDefault();
                    this._formSubmit();
                    return true;
                }
            });
            return content;
        },

        /**
         * Submits the form.
         */
        _formSubmit: function() {
            this._superApply(arguments);
            this.isBeingEdited = false;
        },

        /**
         * Callback for when the AJAX call in _formSubmit is completed.
         */
        _formSubmitComplete: function() {
         // TODO: need to replace with merged version
            var self = this;
            this.element.find('[data-translate-input-index]').each($.proxy(function(count, elem) {
                var index = $(elem).data('translate-input-index'),
                    value = $(elem).val() || '';
                self.callback(index, value);
                self = null;
            }, this));

            $(window).off('resize.translateInlineVdeDialog');
            this._onSubmitComplete();

            this._superApply(arguments);
            this.isSubmitting = false;
        },

        _toggleOutline: function(mode) {
        // TODO: need to remove eventually
            if (mode == null)
                mode = this.options.translateMode;
            else
            /* change translateMode */
                this.options.translateMode = mode;

            this.element.closest('[data-container="body"]').addClass('trnslate-inline-' + mode + '-area');
            var that = this;
            $.each(this.options.translateModes, function(){
                if (this != mode) {
                    that.element.closest('[data-container="body"]').removeClass('trnslate-inline-' + this + '-area');
                }
            });
        },

        _onCancel: function() {
        // TODO: need to remove eventually
            this._toggleOutline();
            this.options.textTranslations.translateInlineVde('show');
            this.options.imageTranslations.translateInlineImageVde('show');
            this.options.scriptTranslations.translateInlineScriptVde('show');
        },

        _onSubmitComplete: function() {
        // TODO: need to remove eventually
            this._toggleOutline();
            this.options.textTranslations.translateInlineVde('show');
            this.options.imageTranslations.translateInlineImageVde('show');
            this.options.scriptTranslations.translateInlineScriptVde('show');
        }
    });

    /**
     * Widget for an icon to be displayed indicating that text can be translated.
     */
    $.widget("mage.translateInlineVde", {
        options: {
            iconTemplateSelector: '[data-template="translate-inline-icon"]',
            img: null,
            imgHover: null,

            offsetLeft: -16,

            dataAttrName: "translate",
            translateMode: null,
            onClick: function(widget) {}
        },

        /**
         * Elements to wrap instead of just inserting a child element. This is
         * to work around some different behavior in Firefox vs. WebKit.
         *
         * @type {Array}
         */
        elementsToWrap : [ 'button' ],

        /**
         * Determines if the template is already appended to the element.
         *
         * @type {boolean}
         */
        isTemplateAttached : false,

        iconTemplate: null,
        iconWrapperTemplate: null,
        elementWrapperTemplate: null,

        /**
         * Determines if the element is suppose to be wrapped or just attached.
         *
         * @type {boolean}, null is unset, false/true is set
         */
        isElementWrapped : null,

        /**
         * Creates the icon widget to indicate text that can be translated.
         * Fulfills jQuery's WidgetFactory _create hook.
         */
        _create: function() {
            this.element.addClass('translate-edit-icon-container');
            this._initTemplates();
            this.show();
        },

        /**
         * Shows the widget.
         */
        show: function() {
            this._attachIcon();

            this.iconTemplate.removeClass('hidden');

            if (this.element.data('translateMode') != this.options.translateMode)
                this.iconTemplate.addClass('hidden');

            this.element.on("dblclick", $.proxy(this._invokeAction, this));
            this._disableElementClicks();
        },

        /**
         * Show edit icon for given translate mode.
         */
        toggleIcon: function(mode) {
            if (mode == this.element.data('translateMode'))
                this.iconTemplate.removeClass('hidden');
            else
                this.iconTemplate.addClass('hidden');

            this.options.translateMode = mode;
        },

        /**
         * Determines if the element should have an icon element wrapped around it or
         * if an icon element should be added as a child element.
         */
        _shouldWrap: function() {
            if (this.isElementWrapped !== null) {
                return this.isElementWrapped;
            }

            this.isElementWrapped = false;
            for (var c = 0; c < this.elementsToWrap.length; c++) {
                if (this.element.is(this.elementsToWrap[c])) {
                    this.isElementWrapped = true;
                    break;
                }
            }

            return this.isElementWrapped;
        },

        /**
         * Attaches an icon to the widget's element.
         */
       _attachIcon: function() {
            if (this._shouldWrap()) {
                if (!this.isTemplateAttached) {
                    this.iconWrapperTemplate = this.iconTemplate.wrap('<div/>').parent();
                    this.iconWrapperTemplate.addClass('translate-edit-icon-wrapper-text');

                    this.elementWrapperTemplate = this.element.wrap('<div/>').parent();
                    this.elementWrapperTemplate.addClass('translate-edit-icon-container');

                    this.iconTemplate.appendTo(this.iconWrapperTemplate);
                    this.iconWrapperTemplate.appendTo(this.elementWrapperTemplate);
                }
            } else {
                this.iconTemplate.appendTo(this.element);
                this.element.removeClass('invisible');
            }

            this.isTemplateAttached = true;
        },

        /**
         * Disables the element click from actually performing a click.
         */
        _disableElementClicks: function() {
            this.element.find('a').off('click');

            if (this.element.is('A')) {
                this.element.on('click', function(e) {
                    return false;
                });
            }
        },

        /**
         * Hides the widget.
         */
        hide: function() {
            this.element.off("dblclick");
            this.iconTemplate.addClass('hidden');
        },

        /**
         * Replaces the translated text inside the widget with the new value.
         */
        replaceText: function(index, value) {
            var translateData = this.element.data(this.options.dataAttrName),
                innerHtmlStr = this.element.html();

            if (value === null || value === '') {
                value = "&nbsp;";
            }

            innerHtmlStr =  innerHtmlStr.replace(translateData[index].shown, value);

            this.element.html(innerHtmlStr);

            translateData[index].shown = value;
            translateData[index].translated = value;
            this.element.data(this.options.dataAttrName, translateData);
        },

        /**
         * Initializes all the templates for the widget.
         */
        _initTemplates: function() {
            this._initIconTemplate();
            this.iconTemplate.addClass('translate-edit-icon-text');
        },

        /**
         * Changes depending on hover action.
         */
        _hoverIcon: function() {
            if (this.options.imgHover) {
                this.iconTemplate.prop('src', this.options.imgHover);
            }
        },

        /**
         * Changes depending on hover action.
         */
        _unhoverIcon: function() {
            if (this.options.imgHover) {
                this.iconTemplate.prop('src', this.options.img);
            }
        },

        /**
         * Initializes the icon template for the widget. Sets the widget up to
         * respond to events.
         */
        _initIconTemplate: function() {
            var self = this;

            this.iconTemplate = $(mageTemplate(this.options.iconTemplateSelector, {
                data: this.options
            }));

            this.iconTemplate.on("click", $.proxy(this._invokeAction, this))
                             .on("mouseover", $.proxy(this._hoverIcon, this))
                             .on("mouseout", $.proxy(this._unhoverIcon, this));
        },

        /**
         * Invokes the action (e.g. activate the inline dialog)
         */
        _invokeAction: function(event) {
            this._detachIcon();
            this.options.onClick(event, this);
        },

        /**
         * Destroys the widget. Fulfills jQuery's WidgetFactory _destroy hook.
         */
        _destroy: function() {
            this.iconTemplate.remove();
            this._detachIcon();
        },

        /**
         * Detaches an icon from the widget's element.
         */
        _detachIcon: function() {
            this._unhoverIcon();

            $(this.iconTemplate).detach();

            if (this._shouldWrap()) {
                this.iconWrapperTemplate.remove();
                this.element.unwrap();
                this.elementWrapperTemplate.remove();
            } else {
                this.element.addClass('invisible');
            }

            this.isTemplateAttached = false;
        }
    });

    $.widget("mage.translateInlineImageVde", $.mage.translateInlineVde, {
        _attachIcon: function() {
            if (!this.isTemplateAttached) {
                this.iconWrapperTemplate = this.iconTemplate.wrap('<div/>').parent();
                this.iconWrapperTemplate.addClass('translate-edit-icon-wrapper-image');

                this.elementWrapperTemplate = this.element.wrap('<div/>').parent();
                this.elementWrapperTemplate.addClass('translate-edit-icon-container');

                this.iconTemplate.appendTo(this.iconWrapperTemplate);
                this.iconWrapperTemplate.appendTo(this.elementWrapperTemplate);

                this.isTemplateAttached = true;
            }
        },

        _initTemplates: function() {
            this._initIconTemplate();
            this.iconTemplate.addClass('translate-edit-icon-image');
        },

        _detachIcon: function() {
            $(this.iconTemplate).detach();
            this.iconWrapperTemplate.remove();
            this.element.unwrap();
            this.elementWrapperTemplate.remove();

            this.isTemplateAttached = false;
        }
    });

    $.widget("mage.translateInlineScriptVde", $.mage.translateInlineVde, {
    });

    /*
     * @TODO move the "escapeHTML" method into the file with global utility functions
     */
    $.extend(true, $, {
        mage: {
            escapeHTML: function(str) {
                return str ? str.replace(/"/g, '&quot;') : "";
            }
        }
    });
}));
