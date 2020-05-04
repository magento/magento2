/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/grid/filters/range',
    'mage/translate'
], function (Range, $t) {
    'use strict';

    return Range.extend({
        defaults: {
            elementTmpl: 'ui/collection',
            templates: {
                base: {
                    template: 'Magento_ImportExport/export/filter-grid/cells/filter/range'
                },
                ranges: {
                    from: {
                        label: $t('From')
                    },
                    to: {
                        label: $t('To')
                    }
                }
            }
        }
    });
});
