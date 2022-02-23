/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require(['loadIcons', 'domReady!', 'uiComponent'], function (loadIcons, Component) {
    alert('bootstrap1');
    console.log(Component);
    loadIcons('Magento_AdminAdobeIms/node_modules/@spectrum-css/icon/dist/spectrum-css-icons.svg');
    alert('bootstrap2');
});
