<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Ui\DataProvider\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GroupedProduct\Ui\DataProvider\Product\GroupedProductDataProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;

class GroupedProductDataProviderTest extends \PHPUnit\Framework\TestCase
{
    const ALLOWED_TYPE = 'simple';

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionMock;

    /**
     * @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
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
        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->setMethods(['getComposableTypes'])
            ->getMockForAbstractClass();
    }

    /**
     * @return object
     */
    protected function getModel()
    {
        return $this->objectManager->getObject(GroupedProductDataProvider::class, [
            'name' => 'testName',
            'primaryFieldName' => 'testPrimaryFieldName',
            'requestFieldName' => 'testRequestFieldName',
            'collectionFactory' => $this->collectionFactoryMock,
            'request' => $this->requestMock,
            'config' => $this->configMock,
            'addFieldStrategies' => [],
            'addFilterStrategies' => [],
            'meta' => [],
            'data' => [],
        ]);
    }

    public function testGetData()
    {
        $items = ['testProduct1', 'testProduct2'];
        $expectedData = [
            'totalRecords' => count($items),
            'items' => $items,
        ];

        $this->configMock->expects($this->once())
            ->method('getComposableTypes')
            ->willReturn([self::ALLOWED_TYPE]);
        $this->collectionMock->expects($this->once())
            ->method('isLoaded')
            ->willReturn(false);
        $this->collectionMock->expects($this->once())
            ->method('addAttributeToFilter')
            ->with('type_id', [self::ALLOWED_TYPE]);
        $this->collectionMock->expects($this->once())
            ->method('toArray')
            ->willReturn($items);
        $this->collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn(count($items));

        $this->assertEquals($expectedData, $this->getModel()->getData());
    }

    public function testGetCollection()
    {
        $this->assertInstanceOf(Collection::class, $this->getModel()->getCollection());
    }
}
