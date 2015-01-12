/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/** Assembles available filter controls and returns it's mapping. */
define([
    './input',
    './select',
    './range'
], function (InputControl, SelectControl, RangeControl) {
    'use strict';

    return {
        input:      InputControl,
        select:     SelectControl,
        date:       RangeControl,
        range:      RangeControl,
        store:      SelectControl
    }
});