/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'underscore',
    'squire'
], function ($, _, Squire) {
    'use strict';

    let injector = new Squire(),
        component;

    describe('Magento_LoginAsCustomerAdminUi/js/confirmation-popup', function () {

        beforeEach(function (done) {
            // Clear global function
            window.lacConfirmationPopup = undefined;

            // Simple mocks - just spy functions
            injector.mock({
                'Magento_Ui/js/modal/confirm': jasmine.createSpy('confirm'),
                'Magento_Ui/js/modal/alert': jasmine.createSpy('alert'),
                'mage/translate': function (text) { return text; },
                'mage/template': function () { return '<div>Mock Template</div>'; },
                'text!Magento_LoginAsCustomerAdminUi/template/confirmation-popup/store-view-ptions.html':
                    '<div>Mock Template</div>'
            });

            injector.require([
                'Magento_LoginAsCustomerAdminUi/js/confirmation-popup'
            ], function (Component) {
                component = Component;
                done();
            });
        });

        afterEach(function () {
            window.lacConfirmationPopup = undefined;
            try {
                injector.clean();
                injector.remove();
                // eslint-disable-next-line no-unused-vars
            } catch (e) {
                // Ignore cleanup errors
            }
        });

        describe('Component initialization', function () {
            it('Should be defined', function () {
                expect(component).toBeDefined();
                expect(typeof component).toBe('function');
            });

            it('Should create lacConfirmationPopup global function after initialization', function () {
                const instance = new component({
                    title: 'Test Title',
                    content: 'Test Content'
                });

                instance.initialize();

                expect(window.lacConfirmationPopup).toBeDefined();
                expect(typeof window.lacConfirmationPopup).toBe('function');
            });

            it('Should return false when called', function () {
                const instance = new component({
                    title: 'Test Title',
                    content: 'Test Content'
                });

                instance.initialize();

                // eslint-disable-next-line one-var
                const result = window.lacConfirmationPopup('http://test.url');

                expect(result).toBe(false);
            });
        });

        describe('Modal configuration', function () {
            it('Should call confirm modal with correct configuration', function (done) {
                // Create a fresh injector for this test
                const testInjector = new Squire(),
                    confirmSpy = jasmine.createSpy('confirm');

                testInjector.mock({
                    'Magento_Ui/js/modal/confirm': confirmSpy,
                    'Magento_Ui/js/modal/alert': jasmine.createSpy('alert'),
                    'mage/translate': function (text) { return text; },
                    'mage/template': function () { return '<div>Mock Template</div>'; },
                    'text!Magento_LoginAsCustomerAdminUi/template/confirmation-popup/store-view-ptions.html':
                        '<div>Mock Template</div>'
                });

                testInjector.require([
                    'Magento_LoginAsCustomerAdminUi/js/confirmation-popup'
                ], function (TestComponent) {
                    let instance = new TestComponent({
                            title: 'Login as Customer',
                            content: 'Are you sure?'
                        }),
                        modalConfig;

                    instance.initialize();
                    window.lacConfirmationPopup('http://test.url');

                    expect(confirmSpy).toHaveBeenCalled();

                    modalConfig = confirmSpy.calls.argsFor(0)[0];
                    expect(modalConfig.title).toBe('Login as Customer');
                    expect(modalConfig.modalClass).toBe('confirm lac-confirm');
                    expect(modalConfig.content).toContain('<div class="message message-warning">Are you sure?</div>');
                    expect(modalConfig.buttons).toBeDefined();
                    expect(modalConfig.buttons.length).toBe(2);

                    testInjector.clean();
                    testInjector.remove();
                    done();
                });
            });
        });

        describe('AJAX functionality', function () {
            it('Should make AJAX request when confirm action is triggered', function (done) {
                // Mock AJAX FIRST, before creating the component
                const ajaxSpy = jasmine.createSpy('ajax'),
                    originalAjax = $.ajax,
                    testInjector = new Squire(), // Create a fresh injector for this test
                    confirmSpy = jasmine.createSpy('confirm').and.callFake(function (config) {
                        // Simulate user clicking confirm - trigger the confirm action
                        if (config.actions && config.actions.confirm) {
                            config.actions.confirm();
                        }
                    }),
                    mockJQuery = $; // Mock jQuery itself to ensure the component uses our mocked ajax

                $.ajax = ajaxSpy;
                mockJQuery.ajax = ajaxSpy;

                // Mock DOM elements
                $('body').append('<input name="form_key" value="test_form_key">');
                $('body').append(
                    '<select id="lac-confirmation-popup-store-id"><option value="2" selected>Store 2</option></select>'
                );

                testInjector.mock({
                    'jquery': mockJQuery,
                    'Magento_Ui/js/modal/confirm': confirmSpy,
                    'Magento_Ui/js/modal/alert': jasmine.createSpy('alert'),
                    'mage/translate': function (text) { return text; },
                    'mage/template': function () { return '<div>Mock Template</div>'; },
                    'text!Magento_LoginAsCustomerAdminUi/template/confirmation-popup/store-view-ptions.html':
                        '<div>Mock Template</div>'
                });

                testInjector.require([
                    'Magento_LoginAsCustomerAdminUi/js/confirmation-popup'
                ], function (TestComponent) {
                    const instance = new TestComponent({
                        title: 'Test Title',
                        content: 'Test Content'
                    });

                    instance.initialize();
                    window.lacConfirmationPopup('http://test.url/login');

                    // Verify confirm was called
                    expect(confirmSpy).toHaveBeenCalled();

                    // Verify AJAX was called
                    expect(ajaxSpy).toHaveBeenCalled();
                    expect(ajaxSpy.calls.argsFor(0)[0].url).toBe('http://test.url/login');
                    expect(ajaxSpy.calls.argsFor(0)[0].type).toBe('POST');
                    expect(ajaxSpy.calls.argsFor(0)[0].dataType).toBe('json');

                    // Verify form data
                    // eslint-disable-next-line one-var
                    const ajaxData = ajaxSpy.calls.argsFor(0)[0].data;

                    expect(ajaxData.form_key).toBe('test_form_key');
                    expect(ajaxData.store_id).toBe('2');

                    // Cleanup
                    $('input[name="form_key"]').remove();
                    $('#lac-confirmation-popup-store-id').remove();
                    $.ajax = originalAjax;
                    testInjector.clean();
                    testInjector.remove();
                    done();
                });
            });

            it('Should handle successful response with redirect URL', function (done) {
                const ajaxSpy = jasmine.createSpy('ajax'),
                    originalAjax = $.ajax;

                $.ajax = ajaxSpy;
                spyOn(window, 'open');

                // eslint-disable-next-line one-var
                const testInjector = new Squire(),
                    confirmSpy = jasmine.createSpy('confirm').and.callFake(function (config) {
                        if (config.actions && config.actions.confirm) {
                            config.actions.confirm();
                        }
                    }),
                    mockJQuery = $;

                // Mock AJAX to call success callback
                ajaxSpy.and.callFake(function (options) {
                    options.success({
                        redirectUrl: 'http://customer.frontend.url'
                    });
                });
                mockJQuery.ajax = ajaxSpy;

                testInjector.mock({
                    'jquery': mockJQuery,
                    'Magento_Ui/js/modal/confirm': confirmSpy,
                    'Magento_Ui/js/modal/alert': jasmine.createSpy('alert'),
                    'mage/translate': function (text) { return text; },
                    'mage/template': function () { return '<div>Mock Template</div>'; },
                    'text!Magento_LoginAsCustomerAdminUi/template/confirmation-popup/store-view-ptions.html':
                        '<div>Mock Template</div>'
                });

                testInjector.require([
                    'Magento_LoginAsCustomerAdminUi/js/confirmation-popup'
                ], function (TestComponent) {
                    const instance = new TestComponent({
                        title: 'Test Title',
                        content: 'Test Content'
                    });

                    instance.initialize();
                    window.lacConfirmationPopup('http://test.url');

                    expect(window.open).toHaveBeenCalledWith('http://customer.frontend.url');

                    // Cleanup
                    $.ajax = originalAjax;
                    testInjector.clean();
                    testInjector.remove();
                    done();
                });
            });

            it('Should handle error response', function (done) {
                const ajaxSpy = jasmine.createSpy('ajax'),
                    originalAjax = $.ajax,
                    alertSpy = jasmine.createSpy('alert'),
                    testInjector = new Squire(),
                    confirmSpy = jasmine.createSpy('confirm').and.callFake(function (config) {
                        if (config.actions && config.actions.confirm) {
                            config.actions.confirm();
                        }
                    });

                $.ajax = ajaxSpy;
                // Mock AJAX to call error callback
                ajaxSpy.and.callFake(function (options) {
                    options.error({
                        responseText: 'Error message',
                        status: 500
                    });
                });

                // Mock jQuery with our ajax spy
                // eslint-disable-next-line one-var
                const mockJQuery = $;

                mockJQuery.ajax = ajaxSpy;
                testInjector.mock({
                    'jquery': mockJQuery,
                    'Magento_Ui/js/modal/confirm': confirmSpy,
                    'Magento_Ui/js/modal/alert': alertSpy,
                    'mage/translate': function (text) { return text; },
                    'mage/template': function () { return '<div>Mock Template</div>'; },
                    'text!Magento_LoginAsCustomerAdminUi/template/confirmation-popup/store-view-ptions.html':
                        '<div>Mock Template</div>'
                });

                testInjector.require([
                    'Magento_LoginAsCustomerAdminUi/js/confirmation-popup'
                ], function (TestComponent) {
                    const instance = new TestComponent({
                        title: 'Test Title',
                        content: 'Test Content'
                    });

                    instance.initialize();
                    window.lacConfirmationPopup('http://test.url');

                    expect(alertSpy).toHaveBeenCalled();
                    expect(alertSpy.calls.argsFor(0)[0].content).toBe('Error message');

                    // Cleanup
                    $.ajax = originalAjax;
                    testInjector.clean();
                    testInjector.remove();
                    done();
                });
            });
        });

        describe('Button click handlers', function () {
            it('Should handle button clicks correctly', function (done) {
                const testInjector = new Squire(),
                    confirmSpy = jasmine.createSpy('confirm');

                testInjector.mock({
                    'Magento_Ui/js/modal/confirm': confirmSpy,
                    'Magento_Ui/js/modal/alert': jasmine.createSpy('alert'),
                    'mage/translate': function (text) { return text; },
                    'mage/template': function () { return '<div>Mock Template</div>'; },
                    'text!Magento_LoginAsCustomerAdminUi/template/confirmation-popup/store-view-ptions.html':
                        '<div>Mock Template</div>'
                });

                testInjector.require([
                    'Magento_LoginAsCustomerAdminUi/js/confirmation-popup'
                ], function (TestComponent) {
                    const instance = new TestComponent({title: 'Test Title', content: 'Test Content'}),
                        mockModal = { // Test cancel button
                            closeModal: jasmine.createSpy('closeModal')
                        };

                    instance.initialize();
                    window.lacConfirmationPopup('http://test.url');

                    // eslint-disable-next-line one-var
                    const modalConfig = confirmSpy.calls.argsFor(0)[0];

                    modalConfig.buttons[0].click.call(mockModal, {});
                    expect(mockModal.closeModal).toHaveBeenCalledWith({});

                    // Test confirm button
                    mockModal.closeModal.calls.reset();
                    modalConfig.buttons[1].click.call(mockModal, {});
                    expect(mockModal.closeModal).toHaveBeenCalledWith({}, true);

                    testInjector.clean();
                    testInjector.remove();
                    done();
                });
            });
        });
    });
});
