/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(function () {
    'use strict';

    return {
        local: {
            path: 'text!tests/assets/text/local.html',
            result: '<!--\n/**\n * Copyright © 2013-2017 Magento, Inc. All rights reserved.\n * See COPYING.txt for license details.\n */\n-->\n<span>Local Template</span>'
        },
        external: {
            path: 'text!tests/assets/text/external.html',
            result: '<!--\n/**\n * Copyright © 2013-2017 Magento, Inc. All rights reserved.\n * See COPYING.txt for license details.\n */\n-->\n<span>External Template</span>'
        }
    };
});
