/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
TranslateInlineDialogVdeTest = TestCase('TranslateInlineDialogVdeTest');

TranslateInlineDialogVdeTest.prototype.testInit = function() {
    /*:DOC +=
        <script id="translate-inline-dialog-form-template" type="text/x-magento-template">
             <form id="<%= data.id %>" data-form="translate-inline-dialog-form">
                 <% _.each(data.items, function(item, i) { %>
                     <input id="perstore_<%= i %>" name="translate[<%= i %>][perstore]" type="hidden" value="0"/>
                     <input name="translate[<%= i %>][original]" type="hidden" value="<%= data.escape(item.original) %>"/>
                     <textarea id="custom_<%= i %>"
                         name="translate[<%= i %>][custom]"
                         data-translate-input-index="<%= i %>"><%= data.escape(item.translated) %></textarea>
                 <% }) %>
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
        <script id="translate-inline-dialog-form-template" type="text/x-magento-template">
             <form id="<%= data.id %>" data-form="translate-inline-dialog-form">
                 <% _.each(data.items, function(item, i) { %>
                    <input id="perstore_<%= i %>" name="translate[<%= i %>][perstore]" type="hidden" value="0"/>
                    <input name="translate[<%= i %>][original]" type="hidden" value="<%= data.escape(item.original) %>"/>
                    <textarea id="custom_<%= i %>"
                        name="translate[<%= i %>][custom]"
                        data-translate-input-index="<%= i %>"><%= data.escape(item.translated) %></textarea>
                 <% }) %>
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
        <script id="translate-inline-dialog-form-template" type="text/x-magento-template">
             <form id="<%= data.id %>" data-form="translate-inline-dialog-form">
                 <% _.each(data.items, function(item, i) { %>
                     <input id="perstore_<%= i %>" name="translate[<%= i %>][perstore]" type="hidden" value="0"/>
                     <input name="translate[<%= i %>][original]" type="hidden" value="<%= data.escape(item.original) %>"/>
                     <textarea id="custom_<%= i %>"
                         name="translate[<%= i %>][custom]"
                         data-translate-input-index="<%= i %>"><%= data.escape(item.translated) %></textarea>
                 <% }) %>
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
