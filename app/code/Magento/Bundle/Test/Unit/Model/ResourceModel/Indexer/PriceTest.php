<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\ResourceModel\Indexer;

use Magento\Bundle\Model\ResourceModel\Indexer\Price;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\BasePriceModifier;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructureFactory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\JoinAttributeProcessor;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Module\Manager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class to test Bundle products Price indexer resource model
 */
class PriceTest extends TestCase
{
    /**
     * @var string
     */
    private $connectionName = 'test_connection';

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Price
     */
    private $priceModel;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->resourceMock->method('getConnection')
            ->with($this->connectionName)
            ->willReturn($this->connectionMock);
        $this->resourceMock->method('getTableName')->willReturnArgument(0);

        /** @var IndexTableStructureFactory|MockObject $indexTableStructureFactory */
        $indexTableStructureFactory = $this->createMock(IndexTableStructureFactory::class);
        /** @var TableMaintainer|MockObject $tableMaintainer */
        $tableMaintainer = $this->createMock(TableMaintainer::class);
        /** @var MetadataPool|MockObject $metadataPool */
        $metadataPool = $this->createMock(MetadataPool::class);
        /** @var BasePriceModifier|MockObject $basePriceModifier */
        $basePriceModifier = $this->createMock(BasePriceModifier::class);
        /** @var JoinAttributeProcessor|MockObject $joinAttributeProcessor */
        $joinAttributeProcessor = $this->createMock(JoinAttributeProcessor::class);
        /** @var ManagerInterface|MockObject $eventManager */
        $eventManager = $this->createMock(ManagerInterface::class);
        /** @var Manager|MockObject $moduleManager */
        $moduleManager = $this->createMock(Manager::class);
        $fullReindexAction = false;

        $this->priceModel = new Price(
            $indexTableStructureFactory,
            $tableMaintainer,
            $metadataPool,
            $this->resourceMock,
            $basePriceModifier,
            $joinAttributeProcessor,
            $eventManager,
            $moduleManager,
            $fullReindexAction,
            $this->connectionName
        );
    }

    /**
     * Tests create Bundle Price temporary table
     */
    public function testGetBundlePriceTable(): void
    {
        $expectedTmpTableName = 'catalog_product_index_price_bundle_temp';
        $expectedTableName = 'catalog_product_index_price_bundle_tmp';

        $this->connectionMock->expects($this->once())
            ->method('createTemporaryTableLike')
            ->with($expectedTmpTableName, $expectedTableName, true);

        $this->assertEquals(
            $expectedTmpTableName,
            $this->invokeMethodViaReflection('getBundlePriceTable')
        );
    }

    /**
     * Tests create Bundle Selection Prices Index temporary table
     */
    public function testGetBundleSelectionTable(): void
    {
        $expectedTmpTableName = 'catalog_product_index_price_bundle_sel_temp';
        $expectedTableName = 'catalog_product_index_price_bundle_sel_tmp';

        $this->connectionMock->expects($this->once())
            ->method('createTemporaryTableLike')
            ->with($expectedTmpTableName, $expectedTableName, true);

        $this->assertEquals(
            $expectedTmpTableName,
            $this->invokeMethodViaReflection('getBundleSelectionTable')
        );
    }

    /**
     * Tests create Bundle Option Prices Index temporary table
     */
    public function testGetBundleOptionTable(): void
    {
        $expectedTmpTableName = 'catalog_product_index_price_bundle_opt_temp';
        $expectedTableName = 'catalog_product_index_price_bundle_opt_tmp';

        $this->connectionMock->expects($this->once())
            ->method('createTemporaryTableLike')
            ->with($expectedTmpTableName, $expectedTableName, true);

        $this->assertEquals(
            $expectedTmpTableName,
            $this->invokeMethodViaReflection('getBundleOptionTable')
        );
    }

    /**
     * Invoke private method via reflection
     *
     * @param string $methodName
     * @return string
     */
    private function invokeMethodViaReflection(string $methodName): string
    {
        $method = new \ReflectionMethod(
            Price::class,
            $methodName
        );
        $method->setAccessible(true);

        return (string)$method->invoke($this->priceModel);
    }
}
