/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
        'Magento_Ui/js/lib/component/traversal'
    ], function (traversal) {
        'use strict';

        describe( 'Magento_Ui/js/lib/component/traversal', function(){
            var traversalObj;

            beforeEach(function(){
                traversalObj = traversal;
            });
            it('has delegate method', function(){
                spyOn(traversalObj, "delegate");
                traversalObj.delegate();
                expect(traversalObj.delegate).toHaveBeenCalled();
            });
        });
    });
