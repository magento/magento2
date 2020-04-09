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
    private $indexerMock;

    /**
     * @var Import|MockObject
     */
    private $importMock;

    /**
     * @var AdvancedPricing|MockObject
     */
    private $advancedPricingMock;

    /**
     * @var IndexerRegistry|MockObject
     */
    private $indexerRegistryMock;

    protected function setUp(): void
    {
        $this->indexerMock = $this->getMockForAbstractClass(IndexerInterface::class, [], '', false);
        $this->indexerRegistryMock = $this->createMock(IndexerRegistry::class);
        $this->importMock = new Import($this->indexerRegistryMock);
        $this->advancedPricingMock = $this->createMock(AdvancedPricing::class);
        $this->indexerRegistryMock->expects($this->any())
            ->method('get')
            ->with(Processor::INDEXER_ID)
            ->willReturn($this->indexerMock);
    }

    public function testAfterSaveReindexIsOnSave()
    {
        $this->indexerMock->expects($this->once())
            ->method('isScheduled')
            ->willReturn(false);
        $this->indexerMock->expects($this->once())
            ->method('invalidate');
        $this->importMock->afterSaveAdvancedPricing($this->advancedPricingMock);
    }

    public function testAfterSaveReindexIsOnSchedule()
    {
        $this->indexerMock->expects($this->once())
            ->method('isScheduled')
            ->willReturn(true);
        $this->indexerMock->expects($this->never())
            ->method('invalidate');
        $this->importMock->afterSaveAdvancedPricing($this->advancedPricingMock);
    }

    public function testAfterDeleteReindexIsOnSave()
    {
        $this->indexerMock->expects($this->once())
            ->method('isScheduled')
            ->willReturn(false);
        $this->indexerMock->expects($this->once())
            ->method('invalidate');
        $this->importMock->afterSaveAdvancedPricing($this->advancedPricingMock);
    }

    public function testAfterDeleteReindexIsOnSchedule()
    {
        $this->indexerMock->expects($this->once())
            ->method('isScheduled')
            ->willReturn(true);
        $this->indexerMock->expects($this->never())
            ->method('invalidate');
        $this->importMock->afterSaveAdvancedPricing($this->advancedPricingMock);
    }
}
