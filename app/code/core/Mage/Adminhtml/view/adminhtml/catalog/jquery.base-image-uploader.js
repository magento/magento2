/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
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
 * @category    Mage
 * @package     js
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
function BaseImageUploader(id, maxFileSize) {
    (function ($) {
        $('#' + id + '_upload').fileupload({
            dataType: 'json',
            dropZone: '#' + id + '_image',
            acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
            maxFileSize: maxFileSize,
            done: function (e, data) {
                if (!data.result) {
                    return;
                }
                if (!data.result.error) {
                    $('#' + id + '_image').attr({src: data.result.url,
                        title: data.result.url,
                        alt: data.result.url});
                    $('#' + id).val(data.result.file);
                    if (typeof media_gallery_contentJsObject != 'undefined') {
                        media_gallery_contentJsObject.handleUploadComplete(data.result);
                        media_gallery_contentJsObject.imagesValues.image = data.result.file;
                        media_gallery_contentJsObject.updateImages();
                    }
                } else {
                    alert(jQuery.mage.__('File extension not known or unsupported type.'));
                }
            },
            add: function(e, data) {
                $(this).fileupload('process', data).done(function () {
                    data.submit();
                });
            }
        });
    })(jQuery);
}