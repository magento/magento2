/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/lib/core/element/links'
], function (links) {
    'use strict';

    describe('Magento_Ui/js/lib/core/element/links', function () {
        var linksObj,
            returnedValue;

        beforeEach(function () {
            linksObj = links;
            linksObj.maps = {
                exports: {},
                imports: {}
            };

        });
        it('has setLinks method', function () {
            returnedValue = linksObj.setLinks(undefined, 'imports');
            expect(typeof returnedValue).toEqual('object');
            spyOn(linksObj, 'setLinks');
            linksObj.setLinks(undefined, 'imports');
            expect(linksObj.setLinks).toHaveBeenCalled();
        });
        it('has setListeners method', function () {
            spyOn(linksObj, 'setListeners');
            linksObj.setListeners();
            expect(linksObj.setListeners).toHaveBeenCalled();
        });
    });
});
