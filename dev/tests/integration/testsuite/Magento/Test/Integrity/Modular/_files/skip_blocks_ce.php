<?php
/**
 * List of blocks to be skipped from instantiation test
 *
 * Format: array('Block_Class_Name', ...)
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
return [
    // Blocks with abstract constructor arguments
    'Magento\Email\Block\Adminhtml\Template',
    'Magento\Email\Block\Adminhtml\Template\Edit',
    'Magento\Backend\Block\System\Config\Edit',
    'Magento\Backend\Block\System\Config\Form',
    'Magento\Backend\Block\System\Config\Tabs',
    'Magento\Review\Block\Form',
    // Fails because of dependence on registry
    'Magento\Reminder\Block\Adminhtml\Reminder\Edit\Tab\Customers',
    'Magento\LayeredNavigation\Block\Navigation',
    'Magento\LayeredNavigation\Block\Navigation\State',
];
