/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui",
    "mage/translate",
    "jquery/file-uploader"
], function($){

    var parentWidget = ($.blueimpFP || $.blueimp).fileupload;
    
    $.widget("vde.quickStyleUploader", parentWidget, {
        options: {
            dataType: 'json',
            replaceFileInput:  true,
            sequentialUploads: true,
            hide_uploader:     true,
            url:               null,
            remove_url:        null,
            uploader_id:       null,
            value:             null,
            container:         null,
            tile_available:    null,

            /**
             * Add file
             * @param e
             * @param data
             */
            add: function (e, data) {
                // add the please wait indicator while the images loads
                $('body').loadingPopup({
                    timeout: false
                });
                data.submit();
            },

            /**
             * On done event
             * @param e
             * @param data
             */
            done: function (e, data) {
                if (data.result.error) {
                    alert(data.result.message);
                } else {
                    $(this).data('quickStyleUploader').setValue(data.result.content['name']);
                }
            },

            /**
             * Fail event
             * @param e
             * @param data
             */
            fail: function(e, data) {
                alert($.mage.__("We don't recognize or support this file extension type."));
            },

            /**
             * Always event, which is triggered once the upload completes (fail and success)
             * @param e
             * @param data
             */
            always: function (e, data) {
                // remove the please wait indicator since the image upload is complete
                $(this).trigger('hideLoadingPopup');
            }
        },

        /**
         * Init uploader
         * @param e
         * @param data
         */
        _init: function (e, data) {
            this._refreshControls();
        },

        /**
         * Remove file
         * @param event
         * @private
         */
        _remove: function (event) {
            $.ajax({
                type: 'POST',
                url: this.options.remove_url,
                data: { file_name: this.options.value, element: this.options.uploader_id },
                dataType: 'json',
                success: $.proxy(function(response) {
                    if (response.error) {
                        alert($.mage.__('Error') + ': "' + response.message + '".');
                    } else {
                        $(this._prepareId(this.options.uploader_id + '-image')).remove();
                        this.setValue(null);
                    }
                }, this),
                error: function() {
                    alert($.mage.__('Sorry, there was an unknown error.'));
                }
            });
        },

        /**
         * Set value
         * @param value
         */
        setValue: function (value) {
            this.options.value = value;
            this._refreshControls();
        },

        /**
         * Refresh controls
         * @protected
         */
        _refreshControls: function () {
            this.options.value ? this._displayUploadedFile() : this._displayUploader();
            this.element.trigger('refreshIframe');
        },

        /**
         * Display uploader
         * @protected
         */
        _displayUploader: function() {
            $(this._prepareId(this.options.uploader_id + '-container')).removeClass('no-display');
            $(this._prepareId(this.options.uploader_id + '-tile-container')).addClass('no-display');
        },

        /**
         * Display uploaded file
         * @protected
         */
        _displayUploadedFile: function() {
            $(this._prepareId(this.options.uploader_id + '-image')).remove();
            var removeId = 'remove-button-' + Math.floor(Math.random() * 999),
                fileTemplate = $(this._prepareId(this.options.uploader_id + '-template')).clone();

            fileTemplate
                .attr({'id': this.options.uploader_id + '-image'})
                .addClass('clone');

            var fileInfoHtml = fileTemplate.html().replace('{{name}}', this.options.value)
                .replace('{{remove-id}}', removeId);

            fileTemplate.html(fileInfoHtml) ;
            fileTemplate.removeClass('no-display');
            fileTemplate.prependTo($('.uploaded-file-wrapper', this._prepareId(this.options.container)));

            if (this.options.remove_url) {
                $(document).on('click.removeUploadedImage', '#' + removeId, $.proxy(function() {
                    this._remove();

                    return false;
                }, this));
            }

            if (this.options.hide_uploader == true) {
                $(this._prepareId(this.options.uploader_id + '-container')).addClass('no-display');
            }
            if (this.options.tile_available == true) {
                $(this._prepareId(this.options.uploader_id + '-tile-container')).removeClass('no-display');
            }
        },

        /**
         * Escape id
         * @param id
         * @return {String}
         */
        _prepareId: function(id) {
            return document.getElementById(id);
        }
    });

});