/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
EditTriggerTest = TestCase('EditTriggerTest');
EditTriggerTest.prototype.testInit = function() {
    var editTrigger = jQuery('body').editTrigger();
    assertEquals(true, !!editTrigger.data('mageEditTrigger'));
    editTrigger.editTrigger('destroy');
};
EditTriggerTest.prototype.testCreate = function() {
    /*:DOC += <script id="translate-inline-icon" type="text/x-magento-template">
          <img alt="<%= data.alt %>" src="<%= data.img %>" height="16" width="16" class="translate-edit-icon">
      </script>
     */
    var options = {
            img: 'img.gif',
            alt: 'translate'
        },
        editTrigger = jQuery('body').editTrigger(options);
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
     <script id="translate-inline-icon" type="text/x-magento-template">
          <img alt="<%= data.alt %>" src="<%= data.img %>" height="16" width="16" class="translate-edit-icon">
      </script>
     */
    var editTrigger = jQuery('body').editTrigger({
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
     <script id="translate-inline-icon" type="text/x-magento-template">
          <img alt="<%= data.alt %>" src="<%= data.img %>" height="16" width="16" class="translate-edit-icon">
      </script>
    */
    var editTrigger = jQuery('body').editTrigger({
            editSelector: '.edit'
        }),
        trigger = jQuery('.translate-edit-icon'),
        editElement = jQuery('.edit'),
        editTriggered = false;
    $('body').on('edit.editTrigger', function() { editTriggered = true; });
    editElement.trigger('mousemove');
    trigger.trigger('click');
    assertEquals(true, editTriggered);
    editTrigger.editTrigger('destroy');
};
EditTriggerTest.prototype.testDestroy = function() {
    var editTrigger = jQuery('body').editTrigger(),
        editProcessed = false,
        mousemoveProcessed = false;

    $('body')
        .on('edit.editTrigger', function() {editProcessed = true;})
        .on('mousemove.editTrigger', function() {mousemoveProcessed = true;});

    editTrigger.editTrigger('destroy');
    assertEquals(false, !!editTrigger.data('mageEditTrigger'));

    $('body').trigger('edit.editTrigger');
    assertEquals(false, editProcessed);

    $('body').trigger('mousemove.editTrigger');
    assertEquals(false, mousemoveProcessed);
};
var EditTriggerTestAsync = AsyncTestCase('EditTriggerTestAsync');
EditTriggerTestAsync.prototype.testHideEditTriggerWithDelay = function(queue) {
    /*:DOC += <div class="container" style="height:100px;">
     <div class="edit">text</div>
     </div>
     <script id="translate-inline-icon" type="text/x-magento-template">
          <img alt="<%= data.alt %>" src="<%= data.img %>" height="16" width="16" class="translate-edit-icon">
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
