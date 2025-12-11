<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Integration\Controller\Adminhtml\Report\Product;

use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @magentoAppArea adminhtml
 */
class ExportSoldExcelTest extends AbstractBackendController
{
    public function testExecute() : void
    {
        $this->dispatch('backend/reports/report_product/exportSoldExcel');
        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
    }
}
