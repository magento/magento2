/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint max-nested-callbacks: 0 */
define(['squire'], function (Squire) {
    'use strict';

    var injector = new Squire(),
        obj;

    beforeEach(function (done) {
        // injector.mock(mocks);
        injector.require(['Magento_Customer/js/section-config'], function (Constr) {
            obj = Constr;
            done();
        });
    });

    afterEach(function () {
        try {
            injector.clean();
            injector.remove();
        } catch (e) {}
    });

    describe('Magento_Customer/js/section-config', function () {
        describe('"getAffectedSections" method', function () {
            it('Does not throw before component is initialized.', function () {
                expect(function () {
                    obj.getAffectedSections('http://localhost.com/path');
                }).not.toThrow();
            });

            it('Returns proper sections when URL contains base URL.', function () {
                obj['Magento_Customer/js/section-config']({
                    sections: {
                        'path': [
                            'section'
                        ]
                    },
                    baseUrls: [
                        'http://localhost.com/',
                        'https://localhost.com/'
                    ]
                });

                expect(obj.getAffectedSections('https://localhost.com/path')).toEqual(['section']);
            });

            it('Returns proper sections when glob pattern is used at the end.', function () {
                obj['Magento_Customer/js/section-config']({
                    sections: {
                        'path/*': [
                            'section'
                        ]
                    },
                    baseUrls: [
                        'http://localhost.com/',
                        'https://localhost.com/'
                    ]
                });

                expect(obj.getAffectedSections('https://localhost.com/path/subpath')).toEqual(['section']);
            });

            it('Returns proper sections when glob pattern is used inside.', function () {
                obj['Magento_Customer/js/section-config']({
                    sections: {
                        '*/subpath': [
                            'section'
                        ]
                    },
                    baseUrls: [
                        'http://localhost.com/',
                        'https://localhost.com/'
                    ]
                });

                expect(obj.getAffectedSections('https://localhost.com/path/subpath')).toEqual(['section']);
            });

            it('Strips "index.php" suffix from provided URL.', function () {
                obj['Magento_Customer/js/section-config']({
                    sections: {
                        'path': [
                            'section'
                        ]
                    },
                    baseUrls: [
                        'http://localhost.com/'
                    ]
                });

                expect(obj.getAffectedSections('http://localhost.com/path/index.php')).toEqual(['section']);
            });

            it('Adds sections for all URLs "*" to found ones.', function () {
                obj['Magento_Customer/js/section-config']({
                    sections: {
                        'path': [
                            'section'
                        ],
                        '*': [
                            'all'
                        ]
                    },
                    baseUrls: [
                        'http://localhost.com/'
                    ]
                });

                expect(obj.getAffectedSections('http://localhost.com/path')).toEqual(['section', 'all']);
            });

            it('Returns "*" sections for all URLs.', function () {
                obj['Magento_Customer/js/section-config']({
                    sections: {
                        '*': [
                            'all'
                        ]
                    },
                    baseUrls: [
                        'http://localhost.com/'
                    ]
                });

                expect(obj.getAffectedSections('http://localhost.com/path')).toEqual(['all']);
            });
        });

        describe('"filterClientSideSections" method', function () {
            it('Does not throw before component is initialized.', function () {
                expect(function () {
                    obj.filterClientSideSections();
                }).not.toThrow();
            });

            it('Returns empty array when all sections are client side.', function () {
                var sections = ['test'];

                obj['Magento_Customer/js/section-config']({
                    clientSideSections: sections
                });
                expect(obj.filterClientSideSections(sections)).toEqual([]);
            });

            it('Filters out client side sections.', function () {
                var allSections = ['test', 'client'],
                    clientSections = ['client'];

                obj['Magento_Customer/js/section-config']({
                    clientSideSections: clientSections
                });
                expect(obj.filterClientSideSections(allSections)).toEqual(['test']);
            });
        });

        describe('"isClientSideSection" method', function () {
            it('Does not throw before component is initialized.', function () {
                expect(function () {
                    obj.isClientSideSection();
                }).not.toThrow();
            });

            it('Returns true if section is defined as client side.', function () {
                obj['Magento_Customer/js/section-config']({
                    clientSideSections: ['client']
                });
                expect(obj.isClientSideSection('client')).toBe(true);
            });

            it('Returns false if section is not defined as client side.', function () {
                obj['Magento_Customer/js/section-config']({
                    clientSideSections: ['client']
                });
                expect(obj.isClientSideSection('test')).toBe(false);
            });

            it('Returns false if section is not client side and sections are not defined.', function () {
                obj['Magento_Customer/js/section-config']({
                    clientSideSections: []
                });
                expect(obj.isClientSideSection('test')).toBe(false);
            });
        });

        describe('"getSectionNames" method', function () {
            it('Does not throw before component is initialized.', function () {
                expect(function () {
                    obj.getSectionNames();
                }).not.toThrow();
            });

            it('Returns defined section names.', function () {
                var sections = ['test'];

                obj['Magento_Customer/js/section-config']({
                    sectionNames: sections
                });
                expect(obj.getSectionNames()).toBe(sections);
            });
        });
    });
});
