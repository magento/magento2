<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Integration\Controller\Adminhtml\Report\Product;

use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @magentoAppArea adminhtml
 */
class ExportDownloadsExcelTest extends AbstractBackendController
{
    public function testExecute()
    {
        $this->dispatch('backend/reports/report_product/exportDownloadsExcel');
        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
    }
}
