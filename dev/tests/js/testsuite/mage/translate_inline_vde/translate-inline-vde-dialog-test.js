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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
TranslateInlineDialogVdeTest = TestCase('TranslateInlineDialogVdeTest');

TranslateInlineDialogVdeTest.prototype.testInit = function() {
    /*:DOC +=
        <script id="translate-inline-dialog-form-template" type="text/x-jQuery-tmpl">
            <form id="${id}" data-form="translate-inline-dialog-form">
                {{each(i, item) items}}
                <input id="perstore_${i}" name="translate[${i}][perstore]" type="hidden" value="0"/>
                <input name="translate[${i}][original]" type="hidden" value="${item.scope}::${escape(item.original)}"/>
                <textarea id="custom_${i}" name="translate[${i}][custom]" data-translate-input-index="${i}">${escape(item.translated)}</textarea>
                {{/each}}
            </form>
        </script>
        <div id="translate-dialog" data-role="translate-dialog"></div>
    */
    var translateInlineDialogVde = jQuery('#translate-dialog').translateInlineDialogVde();
    assertTrue(translateInlineDialogVde.is(':mage-translateInlineDialogVde'));
    translateInlineDialogVde.translateInlineDialogVde('destroy');
};
TranslateInlineDialogVdeTest.prototype.testWithTemplate = function() {
    /*:DOC +=
        <script id="translate-inline-dialog-form-template" type="text/x-jQuery-tmpl">
            <form id="${id}" data-form="translate-inline-dialog-form">
                {{each(i, item) items}}
                <input id="perstore_${i}" name="translate[${i}][perstore]" type="hidden" value="0"/>
                <input name="translate[${i}][original]" type="hidden" value="${item.scope}::${escape(item.original)}"/>
                <textarea id="custom_${i}" name="translate[${i}][custom]" data-translate-input-index="${i}">${escape(item.translated)}</textarea>
                {{/each}}
            </form>
        </script>
        <div id="translate-dialog" data-role="translate-dialog"></div>
    */
    var translateInlineDialogVde = jQuery('#translate-dialog').translateInlineDialogVde();
    assertEquals(true, translateInlineDialogVde.is(':mage-translateInlineDialogVde'));
    translateInlineDialogVde.translateInlineDialogVde('destroy');
};
TranslateInlineDialogVdeTest.prototype.testOpenAndClose = function() {
    /*:DOC += 
        <div id="randomElement" data-translate=""></div>
        <script id="translate-inline-dialog-form-template" type="text/x-jQuery-tmpl">
            <form id="${id}" data-form="translate-inline-dialog-form">
                {{each(i, item) items}}
                <input id="perstore_${i}" name="translate[${i}][perstore]" type="hidden" value="0"/>
                <input name="translate[${i}][original]" type="hidden" value="${item.scope}::${escape(item.original)}"/>
                <textarea id="custom_${i}" name="translate[${i}][custom]" data-translate-input-index="${i}">${escape(item.translated)}</textarea>
                {{/each}}
            </form>
        </script>
        <div id="translate-dialog" data-role="translate-dialog"></div>
    */
    var options = {
      textTranslations: jQuery('[data-translate-mode="text"]'),
      imageTranslations: jQuery('[data-translate-mode="alt"]'),
      scriptTranslations: jQuery('[data-translate-mode="script"]')
    };

    var translateInlineDialogVde = jQuery('#translate-dialog').translateInlineDialogVde(options);

    var widget = {
      element : jQuery('#randomElement')
    };

    jQuery('#translate-dialog').translateInlineDialogVde('openWithWidget', null, widget, function() { });
    assertTrue(jQuery('#translate-dialog').translateInlineDialogVde('isOpen'));

    jQuery('#translate-dialog').translateInlineDialogVde('close');
    assertFalse(jQuery('#translate-dialog').translateInlineDialogVde('isOpen'));

    jQuery('#translate-dialog').translateInlineDialogVde('destroy');
};
