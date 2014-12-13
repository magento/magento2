/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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