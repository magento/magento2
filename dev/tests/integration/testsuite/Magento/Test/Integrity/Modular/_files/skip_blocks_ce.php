<?php
/**
 * List of blocks to be skipped from instantiation test
 *
 * Format: array('Block_Class_Name', ...)
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    // Blocks with abstract constructor arguments
    'Magento\Email\Block\Adminhtml\Template',
    'Magento\Email\Block\Adminhtml\Template\Edit',
    'Magento\Config\Block\System\Config\Edit',
    'Magento\Config\Block\System\Config\Form',
    'Magento\Config\Block\System\Config\Tabs',
    'Magento\Review\Block\Form',
    'Magento\Braintree\Block\Form',
    'Magento\Braintree\Block\Payment',
    // Fails because of dependence on registry
    'Magento\Reminder\Block\Adminhtml\Reminder\Edit\Tab\Customers',
    'Magento\LayeredNavigation\Block\Navigation',
    'Magento\LayeredNavigation\Block\Navigation\State'
];
