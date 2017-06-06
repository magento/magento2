/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

try {
    module.exports = require('../../../../app/etc/themes');
} catch(err){
    var e=new Error("Please configure your themes in app/etc/themes.js - start with copying themes.template.js");
    throw e;
}
