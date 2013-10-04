<?php
/**
 * List of blocks to be skipped from instantiation test
 *
 * Format: array('Block_Class_Name', ...)
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
return array(
    // Blocks with abstract constructor arguments
    'Magento\Adminhtml\Block\System\Email\Template',
    'Magento\Adminhtml\Block\System\Email\Template\Edit',
    'Magento\Backend\Block\System\Config\Edit',
    'Magento\Backend\Block\System\Config\Form',
    'Magento\Backend\Block\System\Config\Tabs',
    'Magento\Review\Block\Form',
    // Fails because of bug in \Magento\Webapi\Model\Acl\Loader\Resource\ConfigReader constructor
    'Magento\Adminhtml\Block\Cms\Page',
    'Magento\Adminhtml\Block\Cms\Page\Edit',
    'Magento\Adminhtml\Block\Sales\Order',
    'Magento\Oauth\Block\Adminhtml\Oauth\Consumer',
    'Magento\Oauth\Block\Adminhtml\Oauth\Consumer\Grid',
    'Magento\Paypal\Block\Adminhtml\Settlement\Report',
    'Magento\Sales\Block\Adminhtml\Billing\Agreement\View',
    'Magento\User\Block\Role\Tab\Edit',
    'Magento\Webapi\Block\Adminhtml\Role\Edit\Tab\Resource',
    // Fails because of dependence on registry
    'Magento\Reminder\Block\Adminhtml\Reminder\Edit\Tab\Customers',
);
