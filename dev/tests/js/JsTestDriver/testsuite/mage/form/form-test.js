/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
FormTest = TestCase('FormTest');
FormTest.prototype.setUp = function() {
    /*:DOC += <form id="form" action="action/url" method="GET", target="_self" ></form>*/
};
FormTest.prototype.tearDown = function() {
    var formInstance = jQuery('#form').data('form');
    if(formInstance && formInstance.destroy) {
        formInstance.destroy();
    }
};
FormTest.prototype.testInit = function() {
    var form = jQuery('#form').form();
    assertTrue(form.is(':mage-form'));
};
FormTest.prototype.testRollback = function() {
    var form = jQuery('#form').form(),
        initialFormAttrs = {
            action: form.prop('action'),
            target: form.prop('target'),
            method: form.prop('method')
        };

    form.data("form").oldAttributes = initialFormAttrs;
    form.prop({
        action: 'new/action/url',
        target: '_blank',
        method: 'POST'
    });

    assertNotEquals(form.prop('action'), initialFormAttrs.action);
    assertNotEquals(form.prop('target'), initialFormAttrs.target);
    assertNotEquals(form.prop('method'), initialFormAttrs.method);
    form.data("form")._rollback();
    assertEquals(form.prop('action'), initialFormAttrs.action);
    assertEquals(form.prop('target'), initialFormAttrs.target);
    assertEquals(form.prop('method'), initialFormAttrs.method);
};
FormTest.prototype.testGetHandlers = function() {
    var form = jQuery('#form').form(),
        handlersData = form.form('option', 'handlersData'),
        handlers = [];
    $.each(handlersData, function(key) {
        handlers.push(key);
    });
    assertEquals(handlers.join(' '), form.data("form")._getHandlers().join(' '));
};
FormTest.prototype.testStoreAttribute = function() {
    var form = jQuery('#form').form(),
        initialFormAttrs = {
            action: form.attr('action'),
            target: form.attr('target'),
            method: form.attr('method')
        };
    form.data("form")._storeAttribute('action');
    form.data("form")._storeAttribute('target');
    form.data("form")._storeAttribute('method');

    assertEquals(form.data("form").oldAttributes.action, initialFormAttrs.action);
    assertEquals(form.data("form").oldAttributes.target, initialFormAttrs.target);
    assertEquals(form.data("form").oldAttributes.method, initialFormAttrs.method);
};
FormTest.prototype.testBind = function() {
    var form = jQuery('#form').form(),
        submitted = false,
        handlersData = form.form('option', 'handlersData');

    form.on('submit', function(e) {
        submitted = true;
        e.stopImmediatePropagation();
        e.preventDefault();
    });
    $.each(handlersData, function(key) {
        form.trigger(key);
        assertTrue(submitted);
        submitted = false;
    });
    form.off('submit');
};
FormTest.prototype.testGetActionUrl = function() {
    var form = jQuery('#form').form(),
        action = form.attr('action'),
        testUrl = 'new/action/url',
        testArgs = {
            args: {arg: 'value'}
        };

    form.data("form")._storeAttribute('action');
    assertEquals(form.data("form")._getActionUrl(testArgs), action + '/arg/value/');
    assertEquals(form.data("form")._getActionUrl(testUrl), testUrl);
    assertEquals(form.data("form")._getActionUrl(), action);
};
FormTest.prototype.testProcessData = function() {
    var form = jQuery('#form').form(),
        initialFormAttrs = {
            action: form.attr('action'),
            target: form.attr('target'),
            method: form.attr('method')
        },
        testSimpleData = {
            action: 'new/action/url',
            target: '_blank',
            method: 'POST'
        },
        testActionArgsData = {
            action: {
                args: {
                    arg: 'value'
                }
            }
        };
    var processedData = form.data("form")._processData(testSimpleData);

    assertEquals(form.data("form").oldAttributes.action, initialFormAttrs.action);
    assertEquals(form.data("form").oldAttributes.target, initialFormAttrs.target);
    assertEquals(form.data("form").oldAttributes.method, initialFormAttrs.method);

    assertEquals(processedData.action, testSimpleData.action);
    assertEquals(processedData.target, testSimpleData.target);
    assertEquals(processedData.method, testSimpleData.method);

    form.data("form")._rollback();

    processedData = form.data("form")._processData(testActionArgsData);
    form.data("form")._storeAttribute('action');
    var newActionUrl = form.data("form")._getActionUrl(testActionArgsData.action);

    assertEquals(processedData.action, newActionUrl);
};
FormTest.prototype.testBeforeSubmit = function() {
    /*:DOC += <form id="test-form"></form> */
    var testHandler = {
            action: {
                args: {
                    arg1: 'value1'
                }
            }
        },
        form = jQuery('#form').form({handlersData: {
                testHandler: testHandler
            }
        }),
        beforeSubmitData = {
            action: {
                args: {
                    arg2: 'value2'
                }
            },
            target: '_blank'
        },
        eventData = {
            method: 'POST'
        },
        resultData = $.extend(
            true,
            {},
            testHandler,
            beforeSubmitData,
            eventData
        );
    form.data("form")._storeAttribute('action');

    var testForm = jQuery('#test-form');
    resultData = form.data("form")._processData(resultData);
    testForm.prop(resultData);

    form.on('beforeSubmit', function(e, data) {
        jQuery.extend(data, beforeSubmitData);
    });
    form.on('submit', function(e) {
        e.stopImmediatePropagation();
        e.preventDefault();
    });
    form.data("form")._beforeSubmit('testHandler', eventData);

    assertEquals(testForm.prop('action'), form.prop('action'));
    assertEquals(testForm.prop('target'), form.prop('target'));
    assertEquals(testForm.prop('method'), form.prop('method'));
};
FormTest.prototype.testSubmit = function() {
    var form = jQuery('#form').form({
            handlersData: {
                save: {}
            }
        }),
        formSubmitted = false;

    form.data("form")._storeAttribute('action');
    form.data("form")._storeAttribute('target');
    form.data("form")._storeAttribute('method');
    form
        .on('submit', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            e.preventDefault();
            formSubmitted = true;
        })
        .prop({
            action: 'new/action/url',
            target: '_blank',
            method: 'POST'
        });

    form.data("form")._submit({type: 'save'});

    assertEquals(form.attr('action'), form.data("form").oldAttributes.action);
    assertEquals(form.attr('target'), form.data("form").oldAttributes.target);
    assertEquals(form.attr('method'), form.data("form").oldAttributes.method);
    assertTrue(formSubmitted);
    form.off('submit');
};
FormTest.prototype.testBuildURL = function() {
    var dataProvider = [
        {
            params: ['http://domain.com//', {'key[one]': 'value 1', 'key2': '# value'}],
            expected: 'http://domain.com/key[one]/value%201/key2/%23%20value/'
        },
        {
            params: ['http://domain.com', {'key[one]': 'value 1', 'key2': '# value'}],
            expected: 'http://domain.com/key[one]/value%201/key2/%23%20value/'
        },
        {
            params: ['http://domain.com?some=param', {'key[one]': 'value 1', 'key2': '# value'}],
            expected: 'http://domain.com?some=param&key[one]=value%201&key2=%23%20value'
        },
        {
            params: ['http://domain.com?some=param&', {'key[one]': 'value 1', 'key2': '# value'}],
            expected: 'http://domain.com?some=param&key[one]=value%201&key2=%23%20value'
        }
    ],
        method = jQuery.mage.form._proto._buildURL,
        quantity = dataProvider.length;

    expectAsserts(quantity);
    for (var i = 0; i < quantity; i++) {
        assertEquals(dataProvider[i].expected, method.apply(null, dataProvider[i].params));
    }
};
