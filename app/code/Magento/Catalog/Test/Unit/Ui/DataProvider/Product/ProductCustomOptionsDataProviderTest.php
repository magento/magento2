<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Catalog\Ui\DataProvider\Product\ProductCustomOptionsDataProvider;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\DB\Select as DbSelect;

class ProductCustomOptionsDataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var ProductCustomOptionsDataProvider
     */
    protected $dataProvider;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var AbstractCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionMock;

    /**
     * @var DbSelect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dbSelectMock;

    protected function setUp()
    {
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->collectionMock = $this->getMockBuilder(AbstractCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getSelect', 'getTable', 'getIterator', 'isLoaded', 'toArray', 'getSize'])
            ->getMockForAbstractClass();
        $this->dbSelectMock = $this->getMockBuilder(DbSelect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->dataProvider = $this->objectManagerHelper->getObject(
            ProductCustomOptionsDataProvider::class,
            [
                'collectionFactory' => $this->collectionFactoryMock,
                'request' => $this->requestMock
            ]
        );
    }

    /**
     * @param int $amount
     * @param array $collectionArray
     * @param array $result
     * @dataProvider getDataDataProvider
     */
    public function testGetDataCollectionIsLoaded($amount, array $collectionArray, array $result)
    {
        $this->collectionMock->expects($this->never())
            ->method('load');

        $this->setCommonExpectations(true, $amount, $collectionArray);

        $this->assertSame($result, $this->dataProvider->getData());
    }

    /**
     * @param int $amount
     * @param array $collectionArray
     * @param array $result
     * @dataProvider getDataDataProvider
     */
    public function testGetData($amount, array $collectionArray, array $result)
    {
        $tableName = 'catalog_product_option_table';

        $this->collectionMock->expects($this->once())
            ->method('isLoaded')
            ->willReturn(false);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('current_product_id', null)
            ->willReturn(0);
        $this->collectionMock->expects($this->any())
            ->method('getSelect')
            ->willReturn($this->dbSelectMock);
        $this->dbSelectMock->expects($this->any())
            ->method('distinct')
            ->willReturnSelf();
        $this->collectionMock->expects($this->any())
            ->method('getTable')
            ->with('catalog_product_option')
            ->willReturn($tableName);
        $this->dbSelectMock->expects($this->once())
            ->method('join')
            ->with(['opt' => $tableName], 'opt.product_id = e.entity_id', null)
            ->willReturnSelf();
        $this->collectionMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $this->collectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([]));

        $this->setCommonExpectations(false, $amount, $collectionArray);

        $this->assertSame($result, $this->dataProvider->getData());
    }

    /**
     * @return array
     */
    public function getDataDataProvider()
    {
        return [
            0 => [
                'amount' => 2,
                'collectionArray' => [
                    '12' => ['id' => '12', 'value' => 'test1'],
                    '25' => ['id' => '25', 'value' => 'test2']
                ],
                'result' => [
                    'totalRecords' => 2,
                    'items' => [
                        ['id' => '12', 'value' => 'test1'],
                        ['id' => '25', 'value' => 'test2']
                    ]
                ]
            ]
        ];
    }

    /**
     * Set common expectations
     *
     * @param bool $isLoaded
     * @param int $amount
     * @param array $collectionArray
     * @return void
     */
    protected function setCommonExpectations($isLoaded, $amount, array $collectionArray)
    {
        $this->collectionMock->expects($this->once())
            ->method('isLoaded')
            ->willReturn($isLoaded);
        $this->collectionMock->expects($this->once())
            ->method('toArray')
            ->willReturn($collectionArray);
        $this->collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($amount);
    }
}
