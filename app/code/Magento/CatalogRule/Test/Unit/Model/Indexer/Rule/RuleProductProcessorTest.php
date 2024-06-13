<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Indexer\Rule;

use Magento\Catalog\Model\Indexer\Product\Price\Processor as ProductPriceProcessor;
use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;
use Magento\CatalogRule\Model\Indexer\Rule\GetAffectedProductIds;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RuleProductProcessorTest extends TestCase
{

    /**
     * @var IndexerRegistry|MockObject
     */
    private $indexerRegistry;

    /**
     * @var RuleProductProcessor
     */
    private $ruleProductProcessor;

    /**
     * @var ProductRuleProcessor|MockObject
     */
    private $productRuleProcessor;

    /**
     * @var ProductPriceProcessor|MockObject
     */
    private $productPriceProcessor;

    /**
     * @var GetAffectedProductIds|MockObject
     */
    private $getAffectedProductIds;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->indexerRegistry = $this->createMock(IndexerRegistry::class);
        $this->productRuleProcessor = $this->createMock(ProductRuleProcessor::class);
        $this->productPriceProcessor = $this->createMock(ProductPriceProcessor::class);
        $this->getAffectedProductIds = $this->createMock(GetAffectedProductIds::class);

        $this->ruleProductProcessor = new RuleProductProcessor(
            $this->indexerRegistry,
            $this->productRuleProcessor,
            $this->productPriceProcessor,
            $this->getAffectedProductIds
        );
    }

    /**
     * @param array $ids
     * @param bool $forceReindex
     * @param bool $isScheduled
     * @param bool $isGetAffectedProductIdsInvoked
     * @param bool $isProductRuleProcessorInvoked
     * @param array $affectedProductIds
     * @dataProvider reindexListDataProvider
     */
    public function testReindexList(
        array $ids,
        bool $forceReindex,
        bool $isScheduled,
        bool $isGetAffectedProductIdsInvoked,
        bool $isProductRuleProcessorInvoked,
        array $affectedProductIds,
    ): void {
        $indexer = $this->createMock(IndexerInterface::class);
        $indexer->expects($this->any())
            ->method('isScheduled')
            ->willReturn($isScheduled);
        $this->indexerRegistry->expects($this->any())
            ->method('get')
            ->with(RuleProductProcessor::INDEXER_ID)
            ->willReturn($indexer);

        if ($isGetAffectedProductIdsInvoked) {
            $this->getAffectedProductIds->expects($this->once())
                ->method('execute')
                ->with($ids)
                ->willReturn($affectedProductIds);
        } else {
            $this->getAffectedProductIds->expects($this->never())
                ->method('execute');
        }

        if ($isProductRuleProcessorInvoked) {
            $this->productRuleProcessor->expects($this->once())
                ->method('reindexList')
                ->with($affectedProductIds, false);
            $this->productPriceProcessor->expects($this->once())
                ->method('reindexList')
                ->with($affectedProductIds, false);
        } else {
            $this->productRuleProcessor->expects($this->never())
                ->method('reindexList');
            $this->productPriceProcessor->expects($this->never())
                ->method('reindexList');
        }

        $this->ruleProductProcessor->reindexList($ids, $forceReindex);
    }

    /**
     * @param int $id
     * @param bool $forceReindex
     * @param bool $isScheduled
     * @param bool $isGetAffectedProductIdsInvoked
     * @param bool $isProductRuleProcessorInvoked
     * @param array $affectedProductIds
     * @dataProvider reindexRowDataProvider
     */
    public function testReindexRow(
        int $id,
        bool $forceReindex,
        bool $isScheduled,
        bool $isGetAffectedProductIdsInvoked,
        bool $isProductRuleProcessorInvoked,
        array $affectedProductIds,
    ): void {
        if ($id === false) {
            $this->markTestSkipped('Not applicable');
        }
        $indexer = $this->createMock(IndexerInterface::class);
        $indexer->expects($this->any())
            ->method('isScheduled')
            ->willReturn($isScheduled);
        $this->indexerRegistry->expects($this->any())
            ->method('get')
            ->with(RuleProductProcessor::INDEXER_ID)
            ->willReturn($indexer);

        if ($isGetAffectedProductIdsInvoked) {
            $this->getAffectedProductIds->expects($this->once())
                ->method('execute')
                ->with([$id])
                ->willReturn($affectedProductIds);
        } else {
            $this->getAffectedProductIds->expects($this->never())
                ->method('execute');
        }

        if ($isProductRuleProcessorInvoked) {
            $this->productRuleProcessor->expects($this->once())
                ->method('reindexList')
                ->with($affectedProductIds, false);
            $this->productPriceProcessor->expects($this->once())
                ->method('reindexList')
                ->with($affectedProductIds, false);
        } else {
            $this->productRuleProcessor->expects($this->never())
                ->method('reindexList');
            $this->productPriceProcessor->expects($this->never())
                ->method('reindexList');
        }

        $this->ruleProductProcessor->reindexRow($id, $forceReindex);
    }

    /**
     * @return array
     */
    public static function reindexListDataProvider(): array
    {
        return [
            [[1, 2, 3], false, true, false, false, []],
            [[], false, false, false, false, []],
            [[1, 2, 3], false, false, true, false, []],
            [[1, 2, 3], false, false, true, true, [4, 5]],
            [[1, 2, 3], true, false, true, true, [4, 5]],
        ];
    }

    /**
     * @return array
     */
    public static function reindexRowDataProvider(): array
    {
        return [
            [1, false, true, false, false, []],
            [1, false, false, true, false, []],
            [1, false, false, true, true, [4, 5]],
            [1, true, false, true, true, [4, 5]],
        ];
    }
}
