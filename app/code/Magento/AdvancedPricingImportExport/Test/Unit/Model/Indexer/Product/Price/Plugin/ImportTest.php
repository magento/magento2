<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Indexer\Product\Price\Plugin;

use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing;
use Magento\AdvancedPricingImportExport\Model\Indexer\Product\Price\Plugin\Import as Import;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ImportTest extends TestCase
{
    /**
     * @var IndexerInterface|MockObject
     */
    private $indexer;

    /**
     * @var Import|MockObject
     */
    private $import;

    /**
     * @var AdvancedPricing|MockObject
     */
    private $advancedPricing;

    /**
     * @var IndexerRegistry|MockObject
     */
    private $indexerRegistry;

    protected function setUp(): void
    {
        $this->indexer = $this->getMockForAbstractClass(
            IndexerInterface::class,
            [],
            '',
            false
        );
        $this->indexerRegistry = $this->createMock(
            IndexerRegistry::class
        );
        $this->import = new \Magento\AdvancedPricingImportExport\Model\Indexer\Product\Price\Plugin\Import(
            $this->indexerRegistry
        );
        $this->advancedPricing = $this->createMock(
            AdvancedPricing::class
        );
        $this->indexerRegistry
            ->method('get')
            ->with(Processor::INDEXER_ID)
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
