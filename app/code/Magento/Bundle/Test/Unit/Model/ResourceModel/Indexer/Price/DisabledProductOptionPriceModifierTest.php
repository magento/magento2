<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\ResourceModel\Indexer\Price;

use Magento\Bundle\Model\ResourceModel\Indexer\Price\DisabledProductOptionPriceModifier;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\Config;
use Magento\Bundle\Model\ResourceModel\Selection as BundleSelection;
use Magento\Bundle\Model\Product\SelectionProductsDisabledRequired;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class to test Remove bundle product from price index
 */
class DisabledProductOptionPriceModifierTest extends TestCase
{
    /**
     * @var string
     */
    private $connectionName = 'indexer';

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var DisabledProductOptionPriceModifier
     */
    private $priceModifier;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $resourceMock = $this->createMock(ResourceConnection::class);
        $resourceMock->method('getConnection')
            ->with($this->connectionName)
            ->willReturn($this->connectionMock);
        $resourceMock->method('getTableName')->willReturnArgument(0);
        $metadataPool = $this->createMock(MetadataPool::class);
        $configMock = $this->createMock(Config::class);
        $bundleSelectionMock = $this->createMock(BundleSelection::class);
        $selectionProductsMock = $this->createMock(SelectionProductsDisabledRequired::class);
        $this->priceModifier = new DisabledProductOptionPriceModifier(
            $resourceMock,
            $configMock,
            $metadataPool,
            $bundleSelectionMock,
            $selectionProductsMock
        );
    }

    /**
     * Test Get all website ids of product
     */
    public function testGetWebsiteIdsOfProduct(): void
    {
        $selectMock = $this->createMock(Select::class);
        $statementMock = $this->createMock(\Zend_Db_Statement_Pdo::class);
        $statementMock->method('fetchColumn')->willReturn(1);
        $selectMock->method('from')->willReturnSelf();
        $selectMock->method('where')->willReturnSelf();
        $selectMock->method('query')->willReturn($statementMock);

        $this->connectionMock->method('select')
            ->willReturn($selectMock);

        $this->connectionMock->expects($this->once())
            ->method('fetchCol')
            ->with($selectMock)
            ->willReturn([]);

        /** @var IndexTableStructure|MockObject $indexTableStructure */
        $indexTableStructure = $this->createMock(IndexTableStructure::class);
        $this->assertEmpty($this->priceModifier->modifyPrice($indexTableStructure, [1]));
    }
}
