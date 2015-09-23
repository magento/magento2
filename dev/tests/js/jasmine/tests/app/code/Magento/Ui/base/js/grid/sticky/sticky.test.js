/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/grid/sticky/sticky'
], function (_, Sticky) {
    'use strict';

    describe('ui/js/grid/sticky/sticky', function () {
        var stickyObj,
            data,
            stub;

        Sticky.prototype.initialize = function () {
        };

        stickyObj = new Sticky({});

        describe('has initialized', function () {
            it('has been defined', function () {
                expect(stickyObj).toBeDefined();
            });
            it('has initialized observable', function () {
                data = stickyObj.initObservable();
                expect(data).toBeDefined();
            });
            it('has initListingNode method', function () {
                spyOn(stickyObj, 'initListingNode');
                stickyObj.initListingNode();
                expect(stickyObj.initListingNode).toHaveBeenCalled();
            });
            it('has initStickyToolbarNode method', function () {
                stickyObj.initStickyToolbarNode({});
                expect(stickyObj.stickyToolbarNode).toBeDefined();
            });
            it('has initContainerNode method', function () {
                spyOn(stickyObj, 'initContainerNode');
                stickyObj.initContainerNode();
                expect(stickyObj.initContainerNode).toHaveBeenCalled();
            });
            it('has initListeners method', function () {
                spyOn(stickyObj, 'initListeners');
                stickyObj.initListeners();
                expect(stickyObj.initListeners).toHaveBeenCalled();
            });
            it('has initOnScroll method', function () {
                stickyObj.initOnScroll();
                expect(stickyObj.lastHorizontalScrollPos).toBeDefined();
            });
            it('has initOnListingScroll method', function () {
                spyOn(stickyObj, 'initOnListingScroll');
                stickyObj.initOnListingScroll();
                expect(stickyObj.initOnListingScroll).toHaveBeenCalled();
            });
            it('has initOnResize method', function () {
                spyOn(stickyObj, 'initOnResize');
                stickyObj.initOnResize();
                expect(stickyObj.initOnResize).toHaveBeenCalled();
            });
        });
        describe('has handlers', function () {
            it('has onWindowScroll event', function () {
                stickyObj.adjustOffset = function (){
                    return this;
                };

                stickyObj.lastHorizontalScrollPos = 100500;
                spyOn(stickyObj, 'adjustDataGridCapPositions');
                stickyObj.onWindowScroll();
                expect(stickyObj.adjustDataGridCapPositions).toHaveBeenCalled();
            });
            it('has onListingScroll method', function () {
                spyOn(stickyObj, 'adjustOffset');
                stickyObj.onListingScroll();
                expect(stickyObj.adjustOffset).toHaveBeenCalled();
            });
            it('has onResize method', function () {
                spyOn(stickyObj, 'onResize');
                stickyObj.onResize();
                expect(stickyObj.onResize).toHaveBeenCalled();
            });
        });
        describe('has getters', function () {
            it('has getListingWidth', function () {
                stickyObj.listingNode = {
                    width: function () {
                        return 100500;
                    }
                };
                data = stickyObj.getListingWidth();
                expect(data).toBeDefined();
            });
            it('has getTableWidth method', function () {
                spyOn(stickyObj, 'getTableWidth');
                stickyObj.getTableWidth();
                expect(stickyObj.getTableWidth).toHaveBeenCalled();
            });
            it('has getTopElement', function () {
                stickyObj.toolbarNode = {};
                data = stickyObj.getTopElement();
                expect(data).toBeDefined();
            });
            it('has getOtherStickyElementsSize', function () {
                stickyObj.otherStickyElsSize = null;
                data = stickyObj.getOtherStickyElementsSize();
                expect(data).toEqual(stickyObj.otherStickyElsSize);
            });
            it('has getListingTopYCoord method', function () {
                spyOn(stickyObj, 'getListingTopYCoord');
                stickyObj.getListingTopYCoord();
                expect(stickyObj.getListingTopYCoord).toHaveBeenCalled();
            });
            it('has getMustBeSticky method', function () {
                spyOn(stickyObj, 'getMustBeSticky');
                stickyObj.getMustBeSticky();
                expect(stickyObj.getMustBeSticky).toHaveBeenCalled();
            });
        });
        describe('has dom manipulators', function () {
            it('has resizeContainer event', function () {
                spyOn(stickyObj, 'resizeContainer');
                stickyObj.resizeContainer();
                expect(stickyObj.resizeContainer).toHaveBeenCalled();
            });
            it('has resizeCols event', function () {
                spyOn(stickyObj, 'resizeCols');
                stickyObj.resizeCols();
                expect(stickyObj.resizeCols).toHaveBeenCalled();
            });
            it('has resetToTop event', function () {
                spyOn(stickyObj, 'resetToTop');
                stickyObj.resetToTop();
                expect(stickyObj.resetToTop).toHaveBeenCalled();
            });
            it('has toggleContainerVisibility event', function () {
                spyOn(stickyObj, 'visible');
                stickyObj.toggleContainerVisibility();
                expect(stickyObj.visible).toHaveBeenCalled();
            });
            it('has adjustContainerElemsWidth event', function () {
                stickyObj.resizeContainer = function(){
                    return this;
                };
                stickyObj.resizeCols = function(){
                    return this;
                };
                spyOn(stickyObj, 'resizeBulk');
                stickyObj.adjustContainerElemsWidth();
                expect(stickyObj.resizeBulk).toHaveBeenCalled();
            });
            it('has adjustOffset event', function () {
                spyOn(stickyObj, 'adjustOffset');
                stickyObj.adjustOffset();
                expect(stickyObj.adjustOffset).toHaveBeenCalled();
            });
            it('has checkPos event', function () {
                stickyObj.visible = function(){
                    return false;
                };
                stickyObj.getMustBeSticky = function(){
                    return false;
                };

                data = stickyObj.checkPos();
                expect(data).toBeDefined();
            })
        });
    })
});