/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
        'Magento_Ui/js/lib/component/manip'
    ], function (manip) {
        'use strict';

        describe( 'Magento_Ui/js/lib/component/manip', function(){
            var manipObj,
                returnedValue;

            beforeEach(function(){
                manipObj = manip;
            });
            it('has getRegion method', function(){
                returnedValue = manipObj.getRegion("region");
                expect(returnedValue).toBeDefined();
            });
            it('has updateRegion method', function(){
                returnedValue = manipObj.updateRegion([],"region");
                expect(typeof returnedValue).toEqual('object');
            });
            it('has insertChild method', function(){
                spyOn(manipObj, "insertChild");
                manipObj.insertChild();
                expect(manipObj.insertChild).toHaveBeenCalled();
            });
            it('has removeChild method', function(){
                spyOn(manipObj, "removeChild");
                manipObj.removeChild();
                expect(manipObj.removeChild).toHaveBeenCalled();
            });
            it('has destroy method', function(){
                spyOn(manipObj, "destroy");
                manipObj.destroy();
                expect(manipObj.destroy).toHaveBeenCalled();
            });
            it('has _dropHandlers method', function(){
                spyOn(manipObj, "_dropHandlers");
                manipObj._dropHandlers();
                expect(manipObj._dropHandlers).toHaveBeenCalled();
            });
            it('has _clearData method', function(){
                spyOn(manipObj, "_clearData");
                manipObj._clearData();
                expect(manipObj._clearData).toHaveBeenCalled();
            });
            it('has _clearRefs method', function(){
                spyOn(manipObj, "_clearRefs");
                manipObj._clearRefs();
                expect(manipObj._clearRefs).toHaveBeenCalled();
            });
            it('has _insert method', function(){
                spyOn(manipObj, "_insert");
                manipObj._insert();
                expect(manipObj._insert).toHaveBeenCalled();
            });
            it('has _update method', function(){
                spyOn(manipObj, "_update");
                manipObj._update();
                expect(manipObj._update).toHaveBeenCalled();
            });

        });
    });
