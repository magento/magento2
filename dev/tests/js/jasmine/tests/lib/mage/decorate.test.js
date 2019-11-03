/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'mage/decorate',
    'jquery'
], function (decorate, $) {
    'use strict';

    describe('mage/decorate', function () {
        describe('"list" method', function () {
            var listId = 'testList';

            beforeEach(function () {
                var list = $('<ul id="' + listId + '"><li/><li/><li/></ul>');

                $('body').append(list);
            });

            afterEach(function () {
                $('#' + listId).remove();
            });

            it('Check correct class decoration', function () {
                var $list = $('#' + listId);

                $list.decorate('list');
                expect($list.find('li:first').hasClass('first')).toBe(false);
                expect($list.find('li:first').hasClass('odd')).toBe(true);
                expect($list.find('li:last').hasClass('last')).toBe(true);
                expect($list.find('li:odd').hasClass('even')).toBe(true);
                expect($list.find('li:even').hasClass('odd')).toBe(true);
            });
        });

        describe('"generic" method', function () {
            var listId = 'testList';

            beforeEach(function () {
                var list = $('<ul id="' + listId + '"><li/><li/><li/></ul>');

                $('body').append(list);
            });

            afterEach(function () {
                $('#' + listId).remove();
            });

            it('Check correct class decoration with default params', function () {
                var $list = $('#' + listId);

                $list.find('li').decorate('generic');
                expect($list.find('li:first').hasClass('first')).toBe(true);
                expect($list.find('li:first').hasClass('odd')).toBe(true);
                expect($list.find('li:last').hasClass('last')).toBe(true);
                expect($list.find('li:odd').hasClass('even')).toBe(true);
                expect($list.find('li:even').hasClass('odd')).toBe(true);
            });

            it('Check correct class decoration with custom params', function () {
                var $list = $('#' + listId);

                $list.find('li').decorate('generic', ['last', 'first']);
                expect($list.find('li:first').hasClass('first')).toBe(true);
                expect($list.find('li:first').hasClass('odd')).toBe(false);
                expect($list.find('li:last').hasClass('last')).toBe(true);
                expect($list.find('li:odd').hasClass('even')).toBe(false);
                expect($list.find('li:even').hasClass('odd')).toBe(false);
            });

            it('Check correct class decoration with empty items', function () {
                var $list = $('#' + listId);

                $list.find('span').decorate('generic', ['last', 'first']);
                expect($list.find('li:first').hasClass('first')).toBe(false);
                expect($list.find('li:first').hasClass('odd')).toBe(false);
                expect($list.find('li:last').hasClass('last')).toBe(false);
                expect($list.find('li:odd').hasClass('even')).toBe(false);
                expect($list.find('li:even').hasClass('odd')).toBe(false);
            });
        });

        describe('"table" method', function () {
            var tableId = 'testTable';

            beforeEach(function () {
                var table = $('<table id="' + tableId + '">' +
                    '<thead><tr><th/><th/></tr></thead>' +
                    '<tbody>' +
                    '<tr><td/><td/></tr>' +
                    '<tr><td/><td/></tr>' +
                    '<tr><td/><td/></tr>' +
                    '</tbody>>' +
                    '<tfoot><tr><th/><th/></tr></tfoot>' +
                    '</table>');

                $('body').append(table);
            });

            afterEach(function () {
                $('#' + tableId).remove();
            });

            it('Check correct class decoration with default params', function () {
                var $table = $('#' + tableId);

                $table.decorate('table');
                expect($table.find('tbody tr:first').hasClass('first')).toBe(true);
                expect($table.find('tbody tr:first').hasClass('odd')).toBe(true);
                expect($table.find('tbody tr:odd').hasClass('even')).toBe(true);
                expect($table.find('tbody tr:even').hasClass('odd')).toBe(true);
                expect($table.find('tbody tr:last').hasClass('last')).toBe(true);
                expect($table.find('thead tr:first').hasClass('first')).toBe(true);
                expect($table.find('thead tr:last').hasClass('last')).toBe(true);
                expect($table.find('tfoot tr:first').hasClass('first')).toBe(true);
                expect($table.find('tfoot tr:last').hasClass('last')).toBe(true);
                expect($table.find('tr td:last').hasClass('last')).toBe(true);
                expect($table.find('tr td:first').hasClass('first')).toBe(false);
            });

            it('Check correct class decoration with custom params', function () {
                var $table = $('#' + tableId);

                $table.decorate('table', {
                    'tbody': ['first'],
                    'tbody tr': ['first'],
                    'thead tr': ['first'],
                    'tfoot tr': ['last'],
                    'tr td': ['first']
                });
                expect($table.find('tbody:first').hasClass('first')).toBe(true);
                expect($table.find('tbody tr:first').hasClass('first')).toBe(true);
                expect($table.find('tbody tr:first').hasClass('odd')).toBe(false);
                expect($table.find('tbody tr:odd').hasClass('even')).toBe(false);
                expect($table.find('tbody tr:even').hasClass('odd')).toBe(false);
                expect($table.find('tbody tr:last').hasClass('last')).toBe(false);
                expect($table.find('thead tr:first').hasClass('first')).toBe(true);
                expect($table.find('thead tr:last').hasClass('last')).toBe(false);
                expect($table.find('tfoot tr:first').hasClass('first')).toBe(false);
                expect($table.find('tfoot tr:last').hasClass('last')).toBe(true);
                expect($table.find('tr td:last').hasClass('last')).toBe(false);
                expect($table.find('tr td:first').hasClass('first')).toBe(true);
            });
        });

        describe('"dataList" method', function () {
            var listId = 'testDataList';

            beforeEach(function () {
                var list = $('<dl id="' + listId + '"><dt/><dd/><dt/><dd/><dt/><dd/></dl>');

                $('body').append(list);
            });

            afterEach(function () {
                $('#' + listId).remove();
            });

            it('Check correct class decoration', function () {
                var $list = $('#' + listId);

                $list.decorate('dataList');
                expect($list.find('dt:first').hasClass('first')).toBe(false);
                expect($list.find('dt:first').hasClass('odd')).toBe(true);
                expect($list.find('dt:odd').hasClass('even')).toBe(true);
                expect($list.find('dt:even').hasClass('odd')).toBe(true);
                expect($list.find('dt:last').hasClass('last')).toBe(true);
                expect($list.find('dd:first').hasClass('first')).toBe(false);
                expect($list.find('dd:first').hasClass('odd')).toBe(true);
                expect($list.find('dd:odd').hasClass('even')).toBe(true);
                expect($list.find('dd:even').hasClass('odd')).toBe(true);
                expect($list.find('dd:last').hasClass('last')).toBe(true);
            });
        });

        describe('Call decorate with fake method', function () {
            var listId = 'testDataList';

            beforeEach(function () {
                var list = $('<dl id="' + listId + '"><dt/><dd/><dt/><dd/><dt/><dd/></dl>');

                $('body').append(list);
            });

            afterEach(function () {
                $('#' + listId).remove();
            });

            it('Check error message', function () {
                var $list = $('#' + listId);

                spyOn($, 'error');
                $list.decorate('customMethod');
                expect($.error).toHaveBeenCalledWith('Method customMethod does not exist on jQuery.decorate');
            });
        });
    });
});
