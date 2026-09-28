<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Reports\Controller\Adminhtml\Report\Product;

/**
 * @magentoAppArea adminhtml
 */
class SoldTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function testExecute()
    {
        $this->dispatch('backend/reports/report_product/sold');
        $actual = $this->getResponse()->getBody();
        $this->assertStringContainsString('Ordered Products Report', $actual);
        //verify if SKU column is presented on grid
        $this->assertStringContainsString('SKU', $actual);
    }
}
