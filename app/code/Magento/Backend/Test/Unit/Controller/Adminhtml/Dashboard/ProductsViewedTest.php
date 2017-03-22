<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Controller\Adminhtml\Dashboard;

/**
 * Test for \Magento\Backend\Controller\Adminhtml\Dashboard\ProductViewed
 */
class ProductsViewedTest extends AbstractTestCase
{
    public function testExecute()
    {
        $this->assertExecute(
            \Magento\Backend\Controller\Adminhtml\Dashboard\ProductsViewed::class,
            \Magento\Backend\Block\Dashboard\Tab\Products\Viewed::class
        );
    }
}
