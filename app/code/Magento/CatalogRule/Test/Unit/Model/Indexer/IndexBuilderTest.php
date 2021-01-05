<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\Indexer\ReindexRuleProduct;
use Magento\CatalogRule\Model\Indexer\ReindexRuleProductPrice;
use Magento\CatalogRule\Model\ResourceModel\Rule\Collection;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\CatalogRule\Model\Rule;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for \Magento\CatalogRule\Model\Indexer\IndexBuilder.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexBuilderTest extends TestCase
{
    /**
     * @var CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $ruleCollectionFactoryMock;

    /**
     * @var PriceCurrencyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $priceCurrencyMock;

    /**
     * @var ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceMock;

    /**
     * @var ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $connectionMock;

    /**
     * @var StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|LoggerInterface
     */
    private $loggerMock;

    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eavConfigMock;

    /**
     * @var DateTime|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dateFormatMock;

    /**
     * @var DateTime\DateTime|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dateTimeMock;

    /**
     * @var ProductFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $productFactoryMock;

    /**
     * @var int
     */
    private $batchCountMock;

    /**
     * @var ReindexRuleProduct|\PHPUnit\Framework\MockObject\MockObject
     */
    private $reindexRuleProductMock;

    /**
     * @var ReindexRuleProductPrice|\PHPUnit\Framework\MockObject\MockObject
     */
    private $reindexRuleProductPriceMock;

    /**
     * @var IndexBuilder
     */
    private $indexBuilder;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->ruleCollectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->priceCurrencyMock = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->getMockForAbstractClass();
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();
        $this->resourceMock->expects($this->once())->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();
        $this->eavConfigMock = $this->createMock(Config::class);
        $this->dateFormatMock = $this->createMock(DateTime::class);
        $this->dateTimeMock = $this->createMock(DateTime\DateTime::class);
        $this->productFactoryMock = $this->createMock(ProductFactory::class);
        $this->batchCountMock = 99;
        $this->reindexRuleProductMock = $this->createMock(ReindexRuleProduct::class);
        $this->reindexRuleProductPriceMock = $this->createMock(ReindexRuleProductPrice::class);

        $this->indexBuilder = $objectManager->getObject(
            IndexBuilder::class,
            [
                'ruleCollectionFactory' => $this->ruleCollectionFactoryMock,
                'priceCurrency' => $this->priceCurrencyMock,
                'resource' => $this->resourceMock,
                'storeManager' => $this->storeManagerMock,
                'logger' => $this->loggerMock,
                'eavConfig' => $this->eavConfigMock,
                'dateFormat' => $this->dateFormatMock,
                'dateTime' => $this->dateTimeMock,
                'productFactory' => $this->productFactoryMock,
                'batchCount' => $this->batchCountMock,
                'reindexRuleProduct' => $this->reindexRuleProductMock,
                'reindexRuleProductPrice' => $this->reindexRuleProductPriceMock,
            ]
        );
    }

    /**
     * Test for \Magento\CatalogRule\Model\Indexer\IndexBuilder::reindexByIds.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testReindexByIds()
    {
        $id1 = 1;
        $id2 = 1;
        $ids = [$id1, $id2];
        $collectionMock = $this->createMock(Collection::class);
        $collectionMock->expects($this->once())->method('addFieldToFilter')->with('is_active', 1)
            ->willReturn($collectionMock);
        $ruleMock = $this->createMock(Rule::class);
        $ruleMock->expects($this->once())->method('setProductsFilter')->with($ids);
        $collectionMock->expects($this->once())->method('getItems')->willReturn([$ruleMock]);
        $this->ruleCollectionFactoryMock->expects($this->once())->method('create')
            ->willReturn($collectionMock);
        $this->reindexRuleProductMock->expects($this->once())->method('execute')
            ->with($ruleMock, $this->batchCountMock);
        $this->reindexRuleProductPriceMock->expects($this->exactly(2))->method('execute')
            ->withConsecutive([$this->batchCountMock, $id1], [$this->batchCountMock, $id2]);

        $this->indexBuilder->reindexByIds($ids);
    }

    /**
     * Test for \Magento\CatalogRule\Model\Indexer\IndexBuilder::reindexById.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testReindexById()
    {
        $id = 1;
        $collectionMock = $this->createMock(Collection::class);
        $collectionMock->expects($this->once())->method('addFieldToFilter')->with('is_active', 1)
            ->willReturn($collectionMock);
        $ruleMock = $this->createMock(Rule::class);
        $ruleMock->expects($this->once())->method('setProductsFilter')->with([$id]);
        $collectionMock->expects($this->once())->method('getItems')->willReturn([$ruleMock]);
        $this->ruleCollectionFactoryMock->expects($this->once())->method('create')
            ->willReturn($collectionMock);
        $this->reindexRuleProductMock->expects($this->once())->method('execute')
            ->with($ruleMock, $this->batchCountMock);
        $this->reindexRuleProductPriceMock->expects($this->once())->method('execute')
            ->with($this->batchCountMock, $id);

        $this->indexBuilder->reindexById($id);
    }
}
