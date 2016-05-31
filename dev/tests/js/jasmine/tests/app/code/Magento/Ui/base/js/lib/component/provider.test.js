/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
        'Magento_Ui/js/lib/component/provider'
    ], function (provider) {
        'use strict';

        describe( 'Magento_Ui/js/lib/component/provider', function(){
            var providerObj,
                returnedValue;

            beforeEach(function(){
                providerObj = provider;
            });
            it('has observe method', function(){
                returnedValue = providerObj.observe("elems");
                expect(typeof  returnedValue).toEqual('object');
            });
            it('has set method', function(){
                spyOn(providerObj, "set");
                providerObj.set();
                expect(providerObj.set).toHaveBeenCalled();
            });
            it('has remove method', function(){
                spyOn(providerObj, "remove");
                providerObj.remove();
                expect(providerObj.remove).toHaveBeenCalled();
            });
            it('has restore method', function(){
                spyOn(providerObj, "restore");
                providerObj.restore();
                expect(providerObj.restore).toHaveBeenCalled();
            });
            it('has removeStored method', function(){
                spyOn(providerObj, "removeStored");
                providerObj.removeStored();
                expect(providerObj.removeStored).toHaveBeenCalled();
            });
        });
    });
