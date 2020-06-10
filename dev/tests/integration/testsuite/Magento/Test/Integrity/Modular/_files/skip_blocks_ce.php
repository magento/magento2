<?php
/**
 * List of blocks to be skipped from instantiation test
 *
 * Format: array('Block_Class_Name', ...)
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    // Blocks with abstract constructor arguments
    \Magento\Email\Block\Adminhtml\Template::class,
    \Magento\Email\Block\Adminhtml\Template\Edit::class,
    \Magento\Config\Block\System\Config\Edit::class,
    \Magento\Config\Block\System\Config\Form::class,
    \Magento\Config\Block\System\Config\Tabs::class,
    \Magento\Review\Block\Form::class,
    // Fails because of dependence on registry
    \Magento\Reminder\Block\Adminhtml\Reminder\Edit\Tab\Customers::class,
    \Magento\LayeredNavigation\Block\Navigation::class,
    \Magento\LayeredNavigation\Block\Navigation\State::class,
    \Magento\Paypal\Block\Express\InContext\Minicart\Button::class,
];
