<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Dashboard;

/**
 * Test for \Magento\Backend\Controller\Adminhtml\Dashboard\CustomersNewest
 */
class CustomersNewestTest extends AbstractTestCase
{
    public function testExecute()
    {
        $this->assertExecute(
            'Magento\Backend\Controller\Adminhtml\Dashboard\CustomersNewest',
            'Magento\Backend\Block\Dashboard\Tab\Customers\Newest'
        );
    }
}