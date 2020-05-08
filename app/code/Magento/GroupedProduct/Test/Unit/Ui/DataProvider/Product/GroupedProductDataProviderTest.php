<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Ui\DataProvider\Product;

use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GroupedProduct\Ui\DataProvider\Product\GroupedProductDataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupedProductDataProviderTest extends TestCase
{
    const ALLOWED_TYPE = 'simple';

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var Collection|MockObject
     */
    protected $collectionMock;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $configMock;

    /**
     * @return void
     */
    protected function setUp(): void
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
