/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
        'Magento_Ui/js/grid/dnd'
    ], function(dnd){
        'use strict';

        describe('Magento_Ui/js/grid/controls/grid/dnd', function(){
            var dragAndDrop,
                fakeElement;

            beforeEach(function(){
                spyOn(document, 'addEventListener');
                dragAndDrop = new dnd({a:'a'});
            });
            it('Dragging changes <body> state on init', function(){
                expect(document.addEventListener).toHaveBeenCalled();
                expect(dragAndDrop.$body).toBeDefined();
            });
            it('specifies column as dragable', function(){
                fakeElement = document.createElement('HTMLTableCellElement');
                dragAndDrop.addColumn(fakeElement);
                expect(dragAndDrop.columns.length).toBeGreaterThan(0);
            });
            it('has setTable method', function () {
                fakeElement = document.createElement('HTMLTableElement');
                dragAndDrop.setTable(fakeElement);
                expect(dragAndDrop.table).toBeDefined();
            });
            it('has setDragTable method', function () {
                fakeElement = document.createElement('HTMLTableElement');
                dragAndDrop.setDragTable(fakeElement);
                expect(dragAndDrop.dragTable).toBeDefined();
            });
        })
    });