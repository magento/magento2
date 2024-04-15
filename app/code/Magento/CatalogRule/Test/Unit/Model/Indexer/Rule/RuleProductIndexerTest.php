<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\CatalogRule\Test\Unit\Model\Indexer\Rule;

use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;
use Magento\CatalogRule\Model\Indexer\Rule\GetAffectedProductIds;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductIndexer;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\CacheContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuleProductIndexerTest extends TestCase
{
    /**
     * @var IndexBuilder|MockObject
     */
    private $indexBuilder;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManager;

    /**
     * @var ProductRuleProcessor|MockObject
     */
    private $productRuleProcessor;

    /**
     * @var GetAffectedProductIdsTest|MockObject
     */
    private $getAffectedProductIds;

    /**
     * @var RuleProductIndexer
     */
    private $indexer;

    /**
     * @var CacheContext|MockObject
     */
    private $cacheContextMock;

    protected function setUp(): void
    {
        $this->indexBuilder = $this->createMock(IndexBuilder::class);
        $this->eventManager = $this->createMock(ManagerInterface::class);
        $this->productRuleProcessor = $this->createMock(ProductRuleProcessor::class);
        $this->getAffectedProductIds = $this->createMock(GetAffectedProductIds::class);

        $this->indexer = new RuleProductIndexer(
            $this->indexBuilder,
            $this->eventManager,
            $this->productRuleProcessor,
            $this->getAffectedProductIds
        );

        $this->cacheContextMock = $this->createMock(CacheContext::class);

        $cacheContextProperty = new \ReflectionProperty(
            RuleProductIndexer::class,
            'cacheContext'
        );
        $cacheContextProperty->setAccessible(true);
        $cacheContextProperty->setValue($this->indexer, $this->cacheContextMock);
    }

    public function testDoExecuteList()
    {
        $ruleIds = [1, 2, 5];
        $productIds = [3, 6, 7, 9];
        $this->getAffectedProductIds->expects($this->once())
            ->method('execute')
            ->with($ruleIds)
            ->willReturn($productIds);

        $this->productRuleProcessor
            ->expects($this->once())
            ->method('reindexList')
            ->with($productIds, true);

        $this->indexer->executeList($ruleIds);
    }

    public function testDoExecuteRow()
    {
        $ruleIds = [1];
        $productIds = [3, 6, 7, 9];
        $this->getAffectedProductIds->expects($this->once())
            ->method('execute')
            ->with($ruleIds)
            ->willReturn($productIds);

        $this->productRuleProcessor
            ->expects($this->once())
            ->method('reindexList')
            ->with($productIds, true);

        $this->indexer->executeRow($ruleIds[0]);
    }
}
