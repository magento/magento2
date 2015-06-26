/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global FORM_KEY*/
define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore',
    'Magento_Ui/js/lib/collapsible',
    'mage/template',
    "jquery/file-uploader"
], function (Component, $, ko, _, Collapsible, mageTemplate) {
    'use strict';

    //TODO: where unique id for options
    var viewModel;
    viewModel = Component.extend({
        attributes: ko.observableArray([]),
        newProductsCount: ko.observable(),
        imagesSection: ko.observableArray([
            {
                label: 'Images',
                type: ko.observable('none'),
                value: ko.observable(),
                attribute: ko.observable()
            }
        ]),
        pricingSection: ko.observableArray([
            {
                label: 'Pricing',
                type: ko.observable('none'),
                value: ko.observable(),
                attribute: ko.observable()
            }
        ]),
        inventorySection: ko.observableArray([
            {
                label: 'Inventory',
                type: ko.observable('none'),
                value: ko.observable(),
                attribute: ko.observable()
            }
        ]),
        sections: ko.observableArray([
            {
                label: 'images',
                type: ko.observable('none'),
                value: ko.observable(),
                attribute: ko.observable()
            },
            {
                label: 'pricing',
                type: ko.observable('none'),
                value: ko.observable(),
                attribute: ko.observable()
            },
            {
                label: 'inventory',
                type: ko.observable('none'),
                value: ko.observable(),
                attribute: ko.observable()
            }
        ]),
        render: function (wizard) {
            this.attributes(wizard.data.attributes());
            var count = 1;
            this.attributes.each(function (attribute) {
                count *= attribute.chosen.length;
                attribute.chosen.each(function (option) {
                    option.sections = ko.observable({images:'',pricing:'',inventory:''});
                });
            });
            this.newProductsCount(count);
            this.bindGalleries();
        },
        force: function (wizard) {
            wizard.data.sections = this.sections;
        },
        back: function (wizard) {
        },
        bindGalleries: function () {
            var baseElement = $('[data-role=bulk-step]');
            baseElement.find('[data-role=gallery]').each(function (index, element) {
                var gallery = $(element),
                    uploadInput = $(gallery.find('[name=image]'));

                if (!gallery.data('gallery-initialized')) {
                    gallery.mage('productGallery', {
                        template: '[data-template=gallery-content]',
                        types: {
                            "image":{
                                "code":"image",
                                "value":null,
                                "label":"Base Image",
                                "scope":"<br\/>[STORE VIEW]",
                                "name":"product[image]"
                            },
                            "small_image":{
                                "code":"small_image",
                                "value":null,
                                "label":"Small Image",
                                "scope":"<br\/>[STORE VIEW]",
                                "name":"product[small_image]"
                            },
                            "thumbnail": {
                                "code":"thumbnail",
                                "value":null,
                                "label":"Thumbnail",
                                "scope":"<br\/>[STORE VIEW]",
                                "name":"product[thumbnail]"
                            }
                        }
                    });

                    uploadInput.fileupload({
                        dataType: 'json',
                        formData: {form_key: FORM_KEY},
                        sequentialUploads: true,
                        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                        add: function (e, data) {
                            var progressTmpl = mageTemplate('[data-template=uploader]'),
                                fileSize,
                                tmpl;

                            $.each(data.files, function (index, file) {
                                fileSize = typeof file.size == "undefined" ?
                                    $.mage.__('We could not detect a size.') :
                                    byteConvert(file.size);

                                data.fileId = Math.random().toString(33).substr(2, 18);

                                tmpl = progressTmpl({
                                    data: {
                                        name: file.name,
                                        size: fileSize,
                                        id: data.fileId
                                    }
                                });

                                $(tmpl).appendTo(gallery.find('[data-role=uploader]'));
                            });

                            $(this).fileupload('process', data).done(function () {
                                data.submit();
                            });
                        },
                        done: function (e, data) {
                            if (data.result && !data.result.error) {
                                gallery.trigger('addItem', data.result);
                            } else {
                                $('#' + data.fileId)
                                    .delay(2000)
                                    .hide('highlight');
                                alert($.mage.__('We don\'t recognize or support this file extension type.'));
                            }
                            $('#' + data.fileId).remove();
                        },
                        progress: function (e, data) {
                            var progress = parseInt(data.loaded / data.total * 100, 10);
                            var progressSelector = '#' + data.fileId + ' .progressbar-container .progressbar';
                            $(progressSelector).css('width', progress + '%');
                        },
                        fail: function (e, data) {
                            var progressSelector = '#' + data.fileId;
                            $(progressSelector).removeClass('upload-progress').addClass('upload-failure')
                                .delay(2000)
                                .hide('highlight')
                                .remove();
                        }
                    });
                    uploadInput.fileupload('option', {
                        process: [{
                            action: 'load',
                            fileTypes: /^image\/(gif|jpeg|png)$/
                        }, {
                            action: 'resize',
                            maxWidth: 1920 ,
                            maxHeight: 1200
                        }, {
                            action: 'save'
                        }]
                    });
                    gallery.data('gallery-initialized', 1);
                }
            });
        }
    });
    return viewModel;
});
