/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

function showSection(section) {
    document.querySelectorAll('section').forEach(function (element) {
        element.style.display = element.getAttribute('data-section') === section ? null : 'none';
    })
}
