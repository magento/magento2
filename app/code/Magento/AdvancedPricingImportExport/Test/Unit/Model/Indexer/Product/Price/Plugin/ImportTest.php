<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Indexer\Product\Price\Plugin;

use Magento\AdvancedPricingImportExport\Model\Indexer\Product\Price\Plugin\Import as Import;

class ImportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Indexer\IndexerInterface |\PHPUnit\Framework\MockObject\MockObject
     */
    private $indexer;

    /**
     * @var Import |\PHPUnit\Framework\MockObject\MockObject
     */
    private $import;

    /**
     * @var \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing|\PHPUnit\Framework\MockObject\MockObject
     */
    private $advancedPricing;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    private $indexerRegistry;

    protected function setUp(): void
    {
        $this->indexer = $this->getMockForAbstractClass(
            \Magento\Framework\Indexer\IndexerInterface::class,
            [],
            '',
            false
        );
        $this->indexerRegistry = $this->createMock(
            \Magento\Framework\Indexer\IndexerRegistry::class
        );
        $this->import = new \Magento\AdvancedPricingImportExport\Model\Indexer\Product\Price\Plugin\Import(
            $this->indexerRegistry
        );
        $this->advancedPricing = $this->createMock(
            \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::class
        );
        $this->indexerRegistry->expects($this->any())
            ->method('get')
            ->with(\Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID)
            ->willReturn($this->indexer);
    }

    public function testAfterSaveReindexIsOnSave()
    {
        $this->indexer->expects($this->once())
            ->method('isScheduled')
            ->willReturn(false);
        $this->indexer->expects($this->once())
            ->method('invalidate');
        $this->import->afterSaveAdvancedPricing($this->advancedPricing);
    }

    public function testAfterSaveReindexIsOnSchedule()
    {
        $this->indexer->expects($this->once())
            ->method('isScheduled')
            ->willReturn(true);
        $this->indexer->expects($this->never())
            ->method('invalidate');
        $this->import->afterSaveAdvancedPricing($this->advancedPricing);
    }

    public function testAfterDeleteReindexIsOnSave()
    {
        $this->indexer->expects($this->once())
            ->method('isScheduled')
            ->willReturn(false);
        $this->indexer->expects($this->once())
            ->method('invalidate');
        $this->import->afterSaveAdvancedPricing($this->advancedPricing);
    }

    public function testAfterDeleteReindexIsOnSchedule()
    {
        $this->indexer->expects($this->once())
            ->method('isScheduled')
            ->willReturn(true);
        $this->indexer->expects($this->never())
            ->method('invalidate');
        $this->import->afterSaveAdvancedPricing($this->advancedPricing);
    }
}
