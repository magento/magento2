<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Reports\Controller\Adminhtml\Report\Statistics;

/**
 * @magentoAppArea adminhtml
 */
class IndexTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Test load page
     */
    public function testExecute()
    {
        $this->dispatch('backend/reports/report_statistics');
        $actual = $this->getResponse()->getBody();
        $this->assertStringContainsString('Never', $actual);
    }
}
