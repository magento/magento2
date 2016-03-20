<?php
/**
 * List of blocks to be skipped from template files test
 *
 * Format: array('Block_Class_Name', ...)
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    // Fails because of dependence on registry
    'Magento\Reminder\Block\Adminhtml\Reminder\Edit\Tab\Customers',
    'Magento\LayeredNavigation\Block\Navigation',
    'Magento\LayeredNavigation\Block\Navigation\State',
];
