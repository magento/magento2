/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


define([
    'Magento_Ui/js/lib/registry/events'
], function (EventBus) {
    'use strict';

    describe('Magento_Ui/js/lib/registry/events', function () {
        var storage = {
                has : function(){
                    return false;
                },
                get : function(){
                    return [];
                }
            },
            eventsClass = new EventBus(storage);

        describe('"resolve" method', function () {
            it('Check for defined ', function () {
                expect(eventsClass.resolve()).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof(eventsClass.resolve());

                expect(type).toEqual('object');
            });
        });
        describe('"wait" method', function () {
            it('Check for defined ', function () {
                expect(eventsClass.wait([],{})).toBeDefined();
            });
            it('Check return object property "requests" defined', function () {
                var thisObject = eventsClass.wait([],{}).requests;

                expect(thisObject).toBeDefined();
            });
            it('Check return object property "requests" type', function () {
                var thisObject = typeof(eventsClass.wait([],{}).requests);

                expect(thisObject).toEqual('object');
            });
        });
        describe('"_resolve" method', function () {
            it('Check completion method', function () {
                eventsClass.request = [{
                    callback: function(){return true;},
                    deps: {}
                }];
                expect(eventsClass._resolve(0)).toEqual(false);
            });
        });
    });
});
