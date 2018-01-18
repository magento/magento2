<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Ui\DataProvider\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Bundle\Ui\DataProvider\Product\BundleDataProvider;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Bundle\Helper\Data;
use Magento\CatalogInventory\Api\StockStateInterface;

class BundleDataProviderTest extends \PHPUnit\Framework\TestCase
{
    const ALLOWED_TYPE = 'simple';

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionMock;

    /**
     * @var Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataHelperMock;

    /**
     * @var StockStateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockStateMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->stockStateMock = $this->getMockBuilder(StockStateInterface::class)
            ->getMockForAbstractClass();

        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'toArray',
                    'isLoaded',
                    'addAttributeToFilter',
                    'load',
                    'getSize',
                    'addFilterByRequiredOptions',
                    'addStoreFilter'
                ]
            )->getMock();
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->collectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->collectionMock);
        $this->dataHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllowedSelectionTypes'])
            ->getMock();
    }

    /**
     * @return object
     */
    protected function getModel()
    {
        return $this->objectManager->getObject(BundleDataProvider::class, [
            'name' => 'testName',
            'primaryFieldName' => 'testPrimaryFieldName',
            'requestFieldName' => 'testRequestFieldName',
            'collectionFactory' => $this->collectionFactoryMock,
            'dataHelper' =>  $this->dataHelperMock,
            'stockState' => $this->stockStateMock,
            'addFieldStrategies' => [],
            'addFilterStrategies' => [],
            'meta' => [],
            'data' => [],
        ]);
    }

    /**
     * Testing getData() method
     *
     * @return void
     */
    public function testGetData()
    {
        $items = ['testProduct1', 'testProduct2'];
        $expectedData = [
            'totalRecords' => count($items),
            'items' => $items,
        ];

        $this->dataHelperMock->expects($this->once())
            ->method('getAllowedSelectionTypes')
            ->willReturn([self::ALLOWED_TYPE]);
        $this->collectionMock->expects($this->once())
            ->method('isLoaded')
            ->willReturn(false);
        $this->collectionMock->expects($this->once())
            ->method('addAttributeToFilter')
            ->with('type_id', [self::ALLOWED_TYPE]);
        $this->collectionMock->expects($this->once())
            ->method('addFilterByRequiredOptions');
        $this->collectionMock->expects($this->once())
            ->method('addStoreFilter')
            ->with(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $this->collectionMock->expects($this->once())
            ->method('toArray')
            ->willReturn($items);
        $this->collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn(count($items));

        $this->assertEquals($expectedData, $this->getModel()->getData());
    }

    /**
     * Testing getCollection() method
     *
     * @return void
     */
    public function testGetCollection()
    {
        $this->assertInstanceOf(Collection::class, $this->getModel()->getCollection());
    }
}
