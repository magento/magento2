/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/

define([
    'jquery',
    'Magento_Ui/js/form/element/url-input'
], function ($, UrlInput) {
    'use strict';

    describe('Magento_Ui/js/form/element/url-input', function () {
        var component;

        beforeEach(function () {
            var params = {
                dataScope: 'urlInput',
                urlTypes: {
                    url: {
                        label: 'Test label',
                        component: 'Magento_Ui/js/form/element/abstract',
                        template: 'ui/form/element/input',
                        sortOrder: 40
                    },
                    testUrl: {
                        label: 'Test label 2',
                        component: 'Magento_Ui/js/form/element/abstract',
                        template: 'ui/form/element/input',
                        sortOrder: 10
                    }
                }
            };

            component = new UrlInput(params);
        });

        describe('processLinkTypes method', function () {
            it('check url types were set', function () {
                expect(component.urlTypes).toBeDefined();
                expect(component.urlTypes.hasOwnProperty('url'));
                expect(component.urlTypes.hasOwnProperty('testUrl'));
            });
        });

        describe('setOptions method', function () {
            it('check that optons were set', function () {
                var expectedOptions = [
                    {
                        value: 'testUrl',
                        label: 'Test label 2',
                        sortOrder: 10
                    },
                    {
                        value: 'url',
                        label: 'Test label',
                        sortOrder: 40
                    }
                ];

                expect(component.options()).toEqual(expectedOptions);
            });
        });

        describe('createChildUrlInputComponent method', function () {
            it('check linked element was set', function () {
                expect(component.linkedElementInstances.url).not.toBeDefined();
                component.createChildUrlInputComponent('url');
                expect(component.linkedElementInstances.url).toBeDefined();
                expect(component.getLinkedElementName()).toEqual(component.linkedElementInstances.url);
            });
        });

        describe('Parent config properties are propagated', function () {
            it('sets the disabled property on the child element', function () {
                var params = {
                    dataScope: 'urlInput',
                    disabled: true,
                    urlTypes: {
                        url: {
                            label: 'Test label',
                            component: 'Magento_Ui/js/form/element/abstract',
                            template: 'ui/form/element/input',
                            sortOrder: 40
                        }
                    }
                };

                component = new UrlInput(params);
                expect(component.disabled()).toBe(true);
                expect(component.urlTypes.url.disabled).toBe(true);
            });

            it('sets the visible property on the child element', function () {
                var params = {
                    dataScope: 'urlInput',
                    visible: false,
                    urlTypes: {
                        url: {
                            label: 'Test label',
                            component: 'Magento_Ui/js/form/element/abstract',
                            template: 'ui/form/element/input',
                            sortOrder: 40
                        }
                    }
                };

                component = new UrlInput(params);
                expect(component.visible()).toBe(false);
                expect(component.urlTypes.url.visible).toBe(false);
            });
        });

    });
});
