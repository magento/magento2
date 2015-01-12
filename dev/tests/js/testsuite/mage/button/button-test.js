/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
ButtonTest = TestCase('ButtonTest');
ButtonTest.prototype.testInit = function() {
    /*:DOC += <button id="test-button"></button>*/
    assertTrue(jQuery('#test-button').button().is(':ui-button'));
};
ButtonTest.prototype.testBind = function() {
    /*:DOC += <button id="test-button"></button><div id="event-target"></div>*/
    var testEventTriggered = false;
    jQuery('#event-target').on('testEvent', function(e) {
        testEventTriggered = true;
    });
    jQuery('#test-button').button({
        event: 'testEvent',
        target: '#event-target'
    }).click();
    assertTrue(testEventTriggered);
};
