/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
/* eslint-disable max-nested-callbacks */
/* jscs:disable jsDoc */

define([
    'jquery',
    'jquery/ui',
    'mage/collapsible'
], function ($) {
    'use strict';

    describe('Test for mage/collapsible jQuery plugin', function () {
        it('check if collapsible can be initialized and destroyed', function () {
            var group = $('<div id="1"></div>');

            group.collapsible();
            expect(group.is(':mage-collapsible')).toBeTruthy();

            group.collapsible('destroy');
            expect(group.is(':mage-collapsible')).toBeFalsy();
            group.remove();
        });

        describe('Test enable, disable, activate and deactivate methods', function () {
            var group = $('<div id="2"></div>'),
                content = $('<div data-role="content"></div>').appendTo(group),
                emptyGroup = $('<div></div>');

            $('<div data-role="title"></div>').prependTo(group);

            beforeEach(function () {
                group.appendTo('body');
            });

            afterEach(function () {
                group.remove();
            });

            it('check enable and disable methods', function () {
                group.collapsible();
                expect(group.is(':mage-collapsible')).toBeTruthy();

                group.collapsible('disable');
                expect(content.is(':hidden')).toBeTruthy();

                group.collapsible('enable');
                expect(content.is(':visible')).toBeTruthy();

                group.collapsible('destroy');
                expect(group.is(':mage-collapsible')).toBeFalsy();
            });

            it('check activate and deactivate methods', function () {
                group.collapsible();
                expect(group.is(':mage-collapsible')).toBeTruthy();

                group.collapsible('deactivate');
                expect(content.is(':hidden')).toBeTruthy();

                group.collapsible('activate');
                expect(content.is(':visible')).toBeTruthy();

                group.collapsible('destroy');
                expect(group.is(':mage-collapsible')).toBeFalsy();
            });

            it('check activate method on empty group', function () {
                emptyGroup.collapsible();
                expect(emptyGroup.is(':mage-collapsible')).toBeTruthy();

                expect(function () {
                    emptyGroup.collapsible('activate');
                }).not.toThrow();
            });
        });

        it('check if the widget gets expanded/collapsed when the title is clicked', function () {
            var group = $('<div id="3"></div>'),
                title = $('<div data-role="title"></div>').appendTo(group),
                content = $('<div data-role="content"></div>').appendTo(group);

            group.appendTo('body');

            group.collapsible();
            expect(group.is(':mage-collapsible')).toBeTruthy();

            group.collapsible('deactivate');
            expect(content.is(':hidden')).toBeTruthy();

            title.trigger('click');
            expect(content.is(':visible')).toBeTruthy();

            title.trigger('click');
            expect(content.is(':hidden')).toBeTruthy();

            group.collapsible('destroy');
            expect(group.is(':mage-collapsible')).toBeFalsy();
            group.remove();
        });

        it('check state classes', function () {
            var group = $('<div id="4"></div>'),
                title = $('<div data-role="title"></div>').appendTo(group);

            $('<div data-role="content"></div>').appendTo(group);

            group.appendTo('body');

            group.collapsible({
                openedState: 'opened',
                closedState: 'closed',
                disabledState: 'disabled'
            });
            expect(group.is(':mage-collapsible')).toBeTruthy();
            expect(group.hasClass('closed')).toBeTruthy();

            title.trigger('click');
            expect(group.hasClass('opened')).toBeTruthy();

            group.collapsible('disable');
            expect(group.hasClass('disabled')).toBeTruthy();

            group.collapsible('destroy');
            expect(group.is(':mage-collapsible')).toBeFalsy();
            group.remove();
        });

        it('check if icons are added to title when initialized and removed when destroyed', function () {
            var group = $('<div id="5"></div>'),
                title = $('<div data-role="title"></div>').appendTo(group);

            $('<div data-role="content"></div>').appendTo(group);

            group.appendTo('body');

            group.collapsible({
                icons: {
                    header: 'minus',
                    activeHeader: 'plus'
                }
            });
            expect(group.is(':mage-collapsible')).toBeTruthy();
            expect(title.children('[data-role=icons]').length).toBeTruthy();

            group.collapsible('destroy');
            expect(group.is(':mage-collapsible')).toBeFalsy();
            expect(title.children('[data-role=icons]').length).toBeFalsy();
            group.remove();
        });

        it('check if icon classes are changed when content gets expanded/collapsed', function () {
            var group = $('<div id="6"></div>'),
                title = $('<div data-role="title"></div>').appendTo(group),
                content = $('<div data-role="content"></div>').appendTo(group),
                icons;

            group.appendTo('body');

            group.collapsible({
                icons: {
                    header: 'minus',
                    activeHeader: 'plus'
                }
            });
            expect(group.is(':mage-collapsible')).toBeTruthy();

            icons = group.collapsible('option', 'icons');
            group.collapsible('deactivate');
            expect(content.is(':hidden')).toBeTruthy();
            expect(title.children('[data-role=icons]').hasClass(icons.header)).toBeTruthy();

            title.trigger('click');
            expect(title.children('[data-role=icons]').hasClass(icons.activeHeader)).toBeTruthy();

            group.collapsible('destroy');
            expect(group.is(':mage-collapsible')).toBeFalsy();
            group.remove();
        });

        it('check if content gets updated via Ajax when title is clicked', function () {
            var group = $('<div id="8"></div>'),
                title = $('<div data-role="title"></div>').appendTo(group),
                content = $('<div data-role="content"></div>').appendTo(group);

            $('<a data-ajax="true" href="test.html"></a>').appendTo(group);

            $.get = jasmine.createSpy().and.callFake(function () {
                var d = $.Deferred();

                d.promise().success = function () {
                };

                d.promise().complete = function () {
                };

                return d.promise();
            });

            group.appendTo('body');

            group.collapsible({
                ajaxContent: true
            });
            expect(group.is(':mage-collapsible')).toBeTruthy();

            group.collapsible('deactivate');
            expect(content.is(':hidden')).toBeTruthy();
            expect(content.children('p').length).toBeFalsy();

            title.trigger('click');
            expect(content.children('p')).toBeTruthy();

            group.collapsible('destroy');
            expect(group.is(':mage-collapsible')).toBeFalsy();
            group.remove();
        });

        describe('Test URL anchor handling', function () {
            var originalUrl = window.location.href,
                group,
                content;

            function setHash(hash) {
                window.history.replaceState(null, '', window.location.pathname + window.location.search + hash);
            }

            beforeEach(function () {
                jasmine.clock().install();
                group = $('<div id="anchor-group"></div>');
                $('<div data-role="title"></div>').appendTo(group);
                content = $('<div data-role="content" id="anchor-content" style="display: none;">' +
                    '<p id="anchor-nested">nested</p></div>').appendTo(group);
                group.appendTo('body');
                spyOn(content[0], 'scrollIntoView');
                spyOn(content.find('#anchor-nested')[0], 'scrollIntoView');
            });

            afterEach(function () {
                group.collapsible('destroy');
                group.remove();
                window.history.replaceState(null, '', originalUrl);
                jasmine.clock().uninstall();
            });

            it('shows and scrolls to the content targeted by the anchor when scrollToAnchor is enabled', function () {
                setHash('#anchor-content');
                group.collapsible({
                    scrollToAnchor: true
                });
                jasmine.clock().tick(1);

                expect(group.collapsible('option', 'active')).toBe(true);
                expect(content.is(':visible')).toBe(true);
                expect(content[0].scrollIntoView).toHaveBeenCalled();
            });

            it('scrolls to an element nested in the content targeted by the anchor', function () {
                var nested = content.find('#anchor-nested');

                setHash('#anchor-nested');
                group.collapsible({
                    scrollToAnchor: true
                });
                jasmine.clock().tick(1);

                expect(content.is(':visible')).toBe(true);
                expect(nested[0].scrollIntoView).toHaveBeenCalled();
                expect(content[0].scrollIntoView).not.toHaveBeenCalled();
            });

            it('shows the content targeted by the anchor without scrolling by default', function () {
                setHash('#anchor-content');
                group.collapsible();
                jasmine.clock().tick(1);

                expect(content.is(':visible')).toBe(true);
                expect(content[0].scrollIntoView).not.toHaveBeenCalled();
            });

            it('does nothing when the anchor is absent from the content', function () {
                setHash('#missing-anchor');
                group.collapsible({
                    scrollToAnchor: true
                });
                jasmine.clock().tick(1);

                expect(group.collapsible('option', 'active')).toBe(false);
                expect(content.is(':hidden')).toBe(true);
                expect(content[0].scrollIntoView).not.toHaveBeenCalled();
            });

            it('ignores an anchor that is not a valid selector', function () {
                setHash('#anchor-content[');

                expect(function () {
                    group.collapsible({
                        scrollToAnchor: true
                    });
                    jasmine.clock().tick(1);
                }).not.toThrow();
                expect(content.is(':hidden')).toBe(true);
                expect(content[0].scrollIntoView).not.toHaveBeenCalled();
            });

            it('does not open or scroll a disabled widget targeted by the anchor', function () {
                setHash('#anchor-content');
                group.collapsible({
                    disabled: true,
                    scrollToAnchor: true
                });
                jasmine.clock().tick(1);

                expect(content.is(':hidden')).toBe(true);
                expect(content[0].scrollIntoView).not.toHaveBeenCalled();
            });
        });
    });
});
