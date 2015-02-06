/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
TranslateInlineVdeTest = TestCase('TranslateInlineVdeTest');
TranslateInlineVdeTest.prototype.testInit = function() {
    /*:DOC += <div data-translate="true">text</div>
    <script data-template="translate-inline-icon" type="text/x-magento-template">
        <img src="<%= data.img %>" height="16" width="16">
    </script>
    */
    var translateInlineVde = jQuery('[data-translate]').translateInlineVde();
    assertTrue(translateInlineVde.is(':mage-translateInlineVde'));
    translateInlineVde.translateInlineVde('destroy');
};
TranslateInlineVdeTest.prototype.testCreate = function() {
    /*:DOC += <div data-translate="true">text</div>
    <script data-template="translate-inline-icon" type="text/x-magento-template">
        <img src="<%= data.img %>" height="16" width="16">
    </script>
    */
    assertEquals(0, jQuery('[data-translate] > img').size());
    var translateInlineVde = jQuery('[data-translate]').translateInlineVde();
    assertEquals(1, jQuery('[data-translate] > img').size());
    translateInlineVde.translateInlineVde('destroy');
};
TranslateInlineVdeTest.prototype.testHideAndShow = function() {
    /*:DOC += <div data-translate="true">text</div>
    <script data-template="translate-inline-icon" type="text/x-magento-template">
        <img src="<%= data.img %>" height="16" width="16">
    </script>
    */
    var translateInlineVde = jQuery('[data-translate]').translateInlineVde(),
        iconImg = jQuery('[data-translate] > img');
    assertFalse(iconImg.is('.hidden'));

    translateInlineVde.translateInlineVde('hide');
    assertTrue(iconImg.is('.hidden') );

    translateInlineVde.translateInlineVde('show');
    assertFalse(iconImg.is('.hidden') );
    assertFalse(jQuery('[data-translate]').is(':hidden') );

    translateInlineVde.translateInlineVde('destroy');
};
TranslateInlineVdeTest.prototype.testReplaceTextNormal = function() {
    /*:DOC += <div id="translateElem"
      data-translate="[{&quot;shown&quot; : &quot;Some value&quot;, &quot;translated&quot; : &quot;Translated value&quot;}]">text</div>
    <script data-template="translate-inline-icon" type="text/x-magento-template">
        <img src="<%= data.img %>" height="16" width="16">
    </script>
    */
    var translateInlineVde = jQuery('[data-translate]').translateInlineVde();
    var newValue = 'New value';

    jQuery('[data-translate]').translateInlineVde('replaceText', 0, newValue);

    var translateData = jQuery('#translateElem').data('translate');
    assertEquals(newValue, translateData[0]['shown']);
    assertEquals(newValue, translateData[0]['translated']);

    translateInlineVde.translateInlineVde('destroy');
};
TranslateInlineVdeTest.prototype.testReplaceTextNullOrBlank = function() {
    /*:DOC += <div id="translateElem"
      data-translate="[{&quot;shown&quot; : &quot;Some value&quot;, &quot;translated&quot; : &quot;Translated value&quot;}]">text</div>
    <script data-template="translate-inline-icon" type="text/x-magento-template">
        <img src="<%= data.img %>" height="16" width="16">
    </script>
    */
    var translateInlineVde = jQuery('[data-translate]').translateInlineVde();
    var newValue = null;

    jQuery('[data-translate]').translateInlineVde('replaceText', 0, newValue);

    var translateData = jQuery('#translateElem').data('translate');
    assertEquals('&nbsp;', translateData[0]['shown']);
    assertEquals('&nbsp;', translateData[0]['translated']);

    newValue = 'Some value';
    jQuery('[data-translate]').translateInlineVde('replaceText', 0, newValue);

    translateData = jQuery('#translateElem').data('translate');
    assertEquals(newValue, translateData[0]['shown']);
    assertEquals(newValue, translateData[0]['translated']);

    newValue = '';
    jQuery('[data-translate]').translateInlineVde('replaceText', 0, newValue);

    translateData = jQuery('#translateElem').data('translate');
    assertEquals('&nbsp;', translateData[0]['shown']);
    assertEquals('&nbsp;', translateData[0]['translated']);

    translateInlineVde.translateInlineVde('destroy');
};
TranslateInlineVdeTest.prototype.testClick = function() {
    /*:DOC += <div id="translateElem" data-translate="[]">text</div>
    <script data-template="translate-inline-icon" type="text/x-magento-template">
        <img src="<%= data.img %>" height="16" width="16">
    </script>
    */
    var counter = 0;
    var callback = function() {
      counter++;
    };
    var translateInlineVde = jQuery('[data-translate]').translateInlineVde({
      onClick: callback
      }),
      iconImg = jQuery('[data-translate] > img');

    iconImg.trigger('click');
    assertEquals(1, counter);
    assertTrue(jQuery('#translateElem').hasClass('invisible'));

    translateInlineVde.translateInlineVde('destroy');
};
TranslateInlineVdeTest.prototype.testDblClick = function() {
    /*:DOC += <div id="translateElem" data-translate="[]">text</div>
    <script data-template="translate-inline-icon" type="text/x-magento-template">
        <img src="<%= data.img %>" height="16" width="16">
    </script>
    */
    var counter = 0;
    var callback = function() {
      counter++;
    };
    var translateInlineVde = jQuery('[data-translate]').translateInlineVde({
      onClick: callback
      }),
      iconImg = jQuery('[data-translate] > img');

    assertEquals(1, jQuery('#translateElem').find('img').size());

    translateInlineVde.trigger('dblclick');
    assertEquals(1, counter);

    assertEquals(0, jQuery('#translateElem').find('img').size());
    assertTrue(jQuery('#translateElem').hasClass('invisible'));

    translateInlineVde.translateInlineVde('destroy');
};
