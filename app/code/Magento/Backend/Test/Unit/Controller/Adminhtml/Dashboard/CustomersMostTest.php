<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Backend\Test\Unit\Controller\Adminhtml\Dashboard;

/**
 * Test for \Magento\Backend\Controller\Adminhtml\Dashboard\CustomersMost
 */
class CustomersMostTest extends AbstractTestCase
{
    public function testExecute()
    {
        $this->assertExecute(
            \Magento\Backend\Controller\Adminhtml\Dashboard\CustomersMost::class,
            \Magento\Backend\Block\Dashboard\Tab\Customers\Most::class
        );
    }
}
