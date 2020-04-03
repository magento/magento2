<?php
/**
 * List of blocks to be skipped from template files test
 *
 * Format: array('Block_Class_Name', ...)
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    // Fails because of dependence on registry
    \Magento\Reminder\Block\Adminhtml\Reminder\Edit\Tab\Customers::class,
    \Magento\LayeredNavigation\Block\Navigation::class,
    \Magento\LayeredNavigation\Block\Navigation\State::class,
    \Magento\Paypal\Block\Express\InContext\Minicart\Button::class,
];
