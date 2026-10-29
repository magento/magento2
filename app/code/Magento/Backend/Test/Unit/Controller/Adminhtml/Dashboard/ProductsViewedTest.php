<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Controller\Adminhtml\Dashboard;

use Magento\Backend\Block\Dashboard\Tab\Products\Viewed;
use Magento\Backend\Controller\Adminhtml\Dashboard\ProductsViewed;

/**
 * Test for \Magento\Backend\Controller\Adminhtml\Dashboard\ProductViewed
 */
class ProductsViewedTest extends AbstractTestCase
{
    public function testExecute()
    {
        $this->assertExecute(
            ProductsViewed::class,
            Viewed::class
        );
    }
}
