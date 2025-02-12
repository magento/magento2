/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* eslint-disable max-nested-callbacks */

define([
    'jquery',
    'mage/dropdown'
], function ($) {
    'use strict';

    describe('Test for mage/dropdown jQuery plugin', function () {
        it('check if dialog opens when the triggerEvent is triggered', function () {
            var opener = $('<div/>'),
                dialog = $('<div/>');

            dialog.dropdownDialog({
                'triggerEvent': 'click',
                'triggerTarget': opener
            });

            opener.trigger('click');
            expect(dialog.dropdownDialog('isOpen')).toBeTruthy();

            dialog.dropdownDialog('destroy');

            dialog.dropdownDialog({
                'triggerEvent': null,
                'triggerTarget': opener
            });

            opener.trigger('click');
            expect(dialog.dropdownDialog('isOpen')).toBeFalsy();
            dialog.dropdownDialog('destroy');
        });

        it('check if a specified class is added to the trigger', function () {
            var opener = $('<div/>'),
                dialog = $('<div/>');

            dialog.dropdownDialog({
                'triggerClass': 'active',
                'triggerTarget': opener
            });

            dialog.dropdownDialog('open');
            expect(opener.hasClass('active')).toBeTruthy();

            dialog.dropdownDialog('close');
            dialog.dropdownDialog('destroy');

            dialog.dropdownDialog({
                'triggerClass': null,
                'triggerTarget': opener
            });

            dialog.dropdownDialog('open');
            expect(opener.hasClass('active')).toBeFalsy();

            dialog.dropdownDialog('close');
            dialog.dropdownDialog('destroy');
        });

        it('check if a specified class is added to the element which the dialog appends to', function () {
            var parent = $('<div/>'),
                dialog = $('<div/>');

            dialog.dropdownDialog({
                'parentClass': 'active',
                'appendTo': parent
            });

            dialog.dropdownDialog('open');
            expect(parent.hasClass('active')).toBeTruthy();

            dialog.dropdownDialog('close');
            dialog.dropdownDialog('destroy');

            dialog.dropdownDialog({
                'parentClass': null,
                'appendTo': parent
            });

            dialog.dropdownDialog('open');
            expect(parent.hasClass('active')).toBeFalsy();

            dialog.dropdownDialog('close');
            dialog.dropdownDialog('destroy');
        });

        it('check if a specified class is added to the element that becomes dialog', function () {
            var dialog = $('<div/>'),
                content;

            dialog.dropdownDialog({
                'dialogContentClass': 'active'
            });

            content = $('.ui-dialog-content');
            dialog.dropdownDialog('open');
            expect(content.hasClass('active')).toBeTruthy();

            dialog.dropdownDialog('close');
            dialog.dropdownDialog('destroy');

            dialog.dropdownDialog({
                'dialogContentClass': null
            });

            dialog.dropdownDialog('open');
            expect(content.hasClass('active')).toBeFalsy();

            dialog.dropdownDialog('close');
            dialog.dropdownDialog('destroy');
        });

        it('check if a specified class is added to dialog', function () {
            var dialog = $('<div/>'),
                uiClass = '.ui-dialog',
                ui;

            dialog.dropdownDialog({
                'defaultDialogClass': 'custom'
            });

            ui = $(uiClass);
            expect(ui.hasClass('custom')).toBeTruthy();
            expect(ui.hasClass('mage-dropdown-dialog')).toBeFalsy();

            dialog.dropdownDialog('destroy');

            dialog.dropdownDialog({});
            ui = $(uiClass);
            expect(ui.hasClass('mage-dropdown-dialog')).toBeTruthy();

            dialog.dropdownDialog('destroy');
        });

        it('check if the specified trigger actually opens the dialog', function () {
            var opener = $('<div/>'),
                dialog = $('<div/>');

            dialog.dropdownDialog({
                'triggerTarget': opener
            });

            opener.trigger('click');
            expect(dialog.dropdownDialog('isOpen')).toBeTruthy();

            dialog.dropdownDialog('close');
            dialog.dropdownDialog('destroy');

            dialog.dropdownDialog({
                'triggerTarget': null
            });

            opener.trigger('click');
            expect(dialog.dropdownDialog('isOpen')).toBeFalsy();

            dialog.dropdownDialog('destroy');
        });

        it('check if the dialog gets closed when clicking outside of it', function () {
            var container = $('<div/>'),
                outside = $('<div/>').attr('id', 'outside').appendTo(container),
                dialog = $('<div/>').attr('id', 'dialog').appendTo(container);

            container.appendTo('body');

            dialog.dropdownDialog({
                'closeOnClickOutside': true
            });

            dialog.dropdownDialog('open');
            outside.trigger('click');
            expect(dialog.dropdownDialog('isOpen')).toBeFalsy();

            dialog.dropdownDialog('destroy');

            dialog.dropdownDialog({
                'closeOnClickOutside': false
            });

            dialog.dropdownDialog('open');
            outside.trigger('click');
            expect(dialog.dropdownDialog('isOpen')).toBeTruthy();

            dialog.dropdownDialog('destroy');
        });

        it('check if the dialog gets closed when mouse leaves the dialog area', function () {
            var container = $('<div/>'),
                dialog = $('<div/>').attr('id', 'dialog').appendTo(container);

            $('<div/>').attr('id', 'outside').appendTo(container);
            $('<div/>').attr('id', 'opener').appendTo(container);

            container.appendTo('body');

            jasmine.clock().install();

            dialog.dropdownDialog({
                'closeOnMouseLeave': true
            });

            dialog.dropdownDialog('open');
            dialog.trigger('mouseenter');
            expect(dialog.dropdownDialog('isOpen')).toBeTruthy();

            dialog.trigger('mouseleave');

            jasmine.clock().tick(10);

            expect(dialog.dropdownDialog('isOpen')).toBeFalsy();
            dialog.dropdownDialog('destroy');

            jasmine.clock().uninstall();
        });

        it('check if the dialog does not close when mouse leaves the dialog area', function () {
            var container = $('<div/>'),
                dialog = $('<div/>').attr('id', 'dialog').appendTo(container);

            $('<div/>').attr('id', 'outside').appendTo(container);
            $('<div/>').attr('id', 'opener').appendTo(container);

            container.appendTo('body');

            jasmine.clock().install();

            dialog.dropdownDialog({
                'closeOnMouseLeave': false
            });

            dialog.dropdownDialog('open');
            dialog.trigger('mouseenter');
            dialog.trigger('mouseleave');
            jasmine.clock().tick(10);
            expect(dialog.dropdownDialog('isOpen')).toBeTruthy();
            dialog.dropdownDialog('destroy');

            jasmine.clock().uninstall();
        });

        it('check if the dialog gets closed with the specified delay', function (done) {
            var container = $('<div/>'),
                dialog = $('<div/>').attr('id', 'dialog').appendTo(container);

            $('<div/>').attr('id', 'outside').appendTo(container);
            $('<div/>').attr('id', 'opener').appendTo(container);

            container.appendTo('body');

            dialog.dropdownDialog({
                'timeout': 5
            });

            dialog.dropdownDialog('open');
            dialog.trigger('mouseenter');
            dialog.trigger('mouseleave');
            expect(dialog.dropdownDialog('isOpen')).toBeTruthy();

            setTimeout(function () {
                expect(dialog.dropdownDialog('isOpen')).toBeFalsy();
                dialog.dropdownDialog('destroy');
                done();
            }, 6);
        });

        it('check if the title bar is prevented from being created', function () {
            var dialog = $('<div/>'),
                uiClass = '.ui-dialog',
                ui;

            dialog.dropdownDialog({
                'createTitleBar': true
            });

            ui = $(uiClass);
            expect(ui.find('.ui-dialog-titlebar').length > 0).toBeTruthy();

            dialog.dropdownDialog('destroy');

            dialog.dropdownDialog({
                'createTitleBar': false
            });

            ui = $(uiClass);
            expect(ui.find('.ui-dialog-titlebar').length <= 0).toBeTruthy();

            dialog.dropdownDialog('destroy');
        });

        it('check if the position function gets disabled', function () {
            var dialog = $('<div/>'),
                uiClass = '.ui-dialog',
                ui;

            dialog.dropdownDialog({
                'autoPosition': false
            });

            ui = $(uiClass);
            dialog.dropdownDialog('open');
            expect(ui.css('top') === 'auto').toBeTruthy();

            dialog.dropdownDialog('destroy');

            dialog.dropdownDialog({
                'autoPosition': true
            });

            ui = $(uiClass);
            dialog.dropdownDialog('open');
            expect(ui.css('top') !== '0px').toBeTruthy();

            dialog.dropdownDialog('destroy');
        });

        it('check if the size function gets disabled', function () {
            var dialog = $('<div/>'),
                uiClass = '.ui-dialog',
                ui;

            dialog.dropdownDialog({
                'autoSize': true,
                'width': '300'
            });

            ui = $(uiClass);
            dialog.dropdownDialog('open');
            expect(ui.css('width') === '300px').toBeTruthy();

            dialog.dropdownDialog('destroy');

            dialog.dropdownDialog({
                'autoSize': false,
                'width': '300'
            });

            ui = $(uiClass);
            dialog.dropdownDialog('open');
            expect(ui.css('width') === '300px').toBeFalsy();

            dialog.dropdownDialog('destroy');
        });
    });
});
