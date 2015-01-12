<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Stock;

/**
 * Class StockItemRepositoryTest
 */
class StockItemRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StockItemRepository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockConfigurationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockStateProviderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $queryBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDateMock;

    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexProcessorMock;

    protected function setUp()
    {
        $this->stockConfigurationMock = $this->getMockBuilder(
            'Magento\CatalogInventory\Api\StockConfigurationInterface'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockStateProviderMock = $this->getMockBuilder(
            'Magento\CatalogInventory\Model\Spi\StockStateProviderInterface'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockItemResourceMock = $this->getMockBuilder('Magento\CatalogInventory\Model\Resource\Stock\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockItemMock = $this->getMockBuilder('Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockItemCollectionMock = $this->getMockBuilder(
            'Magento\CatalogInventory\Api\Data\StockItemCollectionInterfaceFactory'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->productFactoryMock = $this->getMockBuilder('Magento\Catalog\Model\ProductFactory')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'create'])
            ->getMock();
        $this->productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $this->productFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->productMock);

        $this->queryBuilderMock = $this->getMockBuilder('Magento\Framework\DB\QueryBuilderFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mapperMock = $this->getMockBuilder('Magento\Framework\DB\MapperFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeDateMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->indexProcessorMock = $this->getMock(
            'Magento\CatalogInventory\Model\Indexer\Stock\Processor',
            ['reindexRow'],
            [],
            '',
            false
        );

        $this->model = new StockItemRepository(
            $this->stockConfigurationMock,
            $this->stockStateProviderMock,
            $this->stockItemResourceMock,
            $this->stockItemMock,
            $this->stockItemCollectionMock,
            $this->productFactoryMock,
            $this->queryBuilderMock,
            $this->mapperMock,
            $this->localeDateMock,
            $this->indexProcessorMock
        );
    }

    public function testSave()
    {
        $params = [];

        $stockItemMock = $this->getMockBuilder('Magento\CatalogInventory\Api\Data\StockItemInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->indexProcessorMock->expects($this->any())
            ->method('reindexRow')
            ->withAnyParameters();
        $this->assertInstanceOf(
            'Magento\CatalogInventory\Api\Data\StockItemInterface',
            $this->model->save($stockItemMock, $params)
        );
    }
}
