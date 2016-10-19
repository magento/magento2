/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/grid/columns/actions'
], function (_, Actions) {
    'use strict';

    describe('ui/js/grid/columns/actions', function () {
        var model,
            action;

        beforeEach(function () {
            model = new Actions({
                index: 'actions',
                name: 'listing_action',
                indexField: 'id',
                dataScope: '',
                rows: [{
                    identifier: 'row'
                }]
            });
            action = {
                index: 'delete',
                hidden: true,
                rowIndex: 0,
                callback: function() {
                    return true;
                }
            };
        });

        it('Check addAction function', function () {
            expect(model.addAction('delete', action)).toBe(model);
        });

        it('Check getAction function', function () {
            var someAction = _.clone(action);

            someAction.index = 'edit';
            model.addAction('edit', someAction);
            expect(model.getAction(0, 'edit')).toEqual(someAction);
        });

        it('Check getVisibleActions function', function () {
            var someAction = _.clone(action);

            someAction.hidden = false;
            someAction.index= 'view';
            model.addAction('delete', action);
            model.addAction('view', someAction);
            expect(model.getVisibleActions('0')).toEqual([someAction]);
        });

        it('Check updateActions function', function () {
            expect(model.updateActions()).toEqual(model);
        });

        it('Check applyAction function', function () {
            model.addAction('delete', action);
            expect(model.applyAction('delete', 0)).toEqual(model);
        });

        it('Check isSingle and isMultiple function', function () {
            var someAction = _.clone(action);

            action.hidden = false;
            model.addAction('delete', action);
            expect(model.isSingle(0)).toBeTruthy();
            someAction.hidden = false;
            someAction.index = 'edit';
            model.addAction('edit', someAction);
            expect(model.isSingle(0)).toBeFalsy();
            expect(model.isMultiple(0)).toBeTruthy();
        });

        it('Check isActionVisible function', function () {
            expect(model.isActionVisible(action)).toBeFalsy();
            action.hidden = false;
            expect(model.isActionVisible(action)).toBeTruthy();
        });
    });
});