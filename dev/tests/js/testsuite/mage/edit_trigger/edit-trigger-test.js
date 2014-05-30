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
EditTriggerTest = TestCase('EditTriggerTest');
EditTriggerTest.prototype.testInit = function() {
    var editTrigger = jQuery(document).editTrigger();
    assertEquals(true, editTrigger.is(':mage-editTrigger'));
    editTrigger.editTrigger('destroy');
};
EditTriggerTest.prototype.testCreate = function() {
    /*:DOC += <script id="translate-inline-icon" type="text/x-jQuery-tmpl">
          <img alt="${alt}" src="${img}" height="16" width="16" class="translate-edit-icon">
      </script>
     */
    var options = {
            img: 'img.gif',
            alt: 'translate'
        },
        editTrigger = jQuery(document).editTrigger(options);
    var trigger = jQuery('.translate-edit-icon');
    assertNotNull(trigger);
    assertTrue(trigger.is('img'));
    assertEquals(true, trigger.attr('src') === options.img);
    assertEquals(true, trigger.attr('alt') === options.alt);
    assertEquals(true, trigger.is(':hidden'));
    editTrigger.editTrigger('destroy');
};
EditTriggerTest.prototype.testShowHideOnMouseMove = function() {
    /*:DOC += <div class="container" style="height:100px;">
     <div class="edit">text</div>
     </div>
     <script id="translate-inline-icon" type="text/x-jQuery-tmpl">
          <img alt="${alt}" src="${img}" height="16" width="16" class="translate-edit-icon">
      </script>
     */
    var editTrigger = jQuery(document).editTrigger({
            editSelector: '.edit',
            delay: 0
        }),
        trigger = jQuery('.translate-edit-icon'),
        editElement = jQuery('.edit'),
        container = jQuery('.container');
    editElement.trigger('mousemove');
    assertEquals(true, trigger.is(':visible'));
    container.trigger('mousemove');
    assertEquals(true, trigger.is(':hidden'));
    editTrigger.editTrigger('destroy');
};
EditTriggerTest.prototype.testTriggerClick = function() {
    /*:DOC += <div class="edit">text</div>
     <script id="translate-inline-icon" type="text/x-jQuery-tmpl">
          <img alt="${alt}" src="${img}" height="16" width="16" class="translate-edit-icon">
      </script>
    */
    var editTrigger = jQuery(document).editTrigger({
            editSelector: '.edit'
        }),
        trigger = jQuery('.translate-edit-icon'),
        editElement = jQuery('.edit'),
        editTriggered = false;
    $(document).on('edit.editTrigger', function() { editTriggered = true; });
    editElement.trigger('mousemove');
    trigger.trigger('click');
    assertEquals(true, editTriggered);
    editTrigger.editTrigger('destroy');
};
EditTriggerTest.prototype.testDestroy = function() {
    var editTrigger = jQuery(document).editTrigger(),
        editTriggerExist = editTrigger.is(':mage-editTrigger'),
        editProcessed = false,
        mousemoveProcessed = false;
    $(document)
        .on('edit.editTrigger', function() {editProcessed = true;})
        .on('mousemove.editTrigger', function() {mousemoveProcessed = true;});
    editTrigger.editTrigger('destroy');
    assertEquals(false, editTriggerExist === editTrigger.is(':mage-editTrigger'));
    $(document).trigger('edit.editTrigger');
    assertEquals(false, editProcessed);
    $(document).trigger('mousemove.editTrigger');
    assertEquals(false, mousemoveProcessed);
};
var EditTriggerTestAsync = AsyncTestCase('EditTriggerTestAsync');
EditTriggerTestAsync.prototype.testHideEditTriggerWithDelay = function(queue) {
    /*:DOC += <div class="container" style="height:100px;">
     <div class="edit">text</div>
     </div>
     <script id="translate-inline-icon" type="text/x-jQuery-tmpl">
          <img alt="${alt}" src="${img}" height="16" width="16" class="translate-edit-icon">
     </script>
     */
    var editTrigger = jQuery(document).editTrigger({
            editSelector: '.edit',
            delay: 1000
        }),
        trigger = jQuery('.translate-edit-icon'),
        editElement = jQuery('.edit'),
        container = jQuery('.container'),
        visibleOnMouseout,
        hiddenAfterDelay;
    editElement.trigger('mousemove');
    container.trigger('mousemove');
    queue.call('Step 1: Start hiding with delay', function(callbacks) {
        visibleOnMouseout = trigger.is(':visible');
        setTimeout(callbacks.add(function() {
            hiddenAfterDelay = trigger.is(':hidden');
        }), 1050);
    });
    queue.call('Step 2: Check is trigger are hidden after delay', function() {
        assertEquals(true, visibleOnMouseout);
        assertEquals(true, hiddenAfterDelay);
        editTrigger.editTrigger('destroy');
    });
};
