<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Ui\DataProvider\Product;

use Magento\Bundle\Helper\Data;
use Magento\Bundle\Ui\DataProvider\Product\BundleDataProvider;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BundleDataProviderTest extends TestCase
{
    private const ALLOWED_TYPE = 'simple';

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
     * @var Data|MockObject
     */
    protected $dataHelperMock;

    /**
     * @var PoolInterface|MockObject
     */
    private $modifierPool;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->modifierPool = $this->getMockBuilder(PoolInterface::class)
            ->getMockForAbstractClass();

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
            'request' => $this->requestMock,
            'dataHelper' =>  $this->dataHelperMock,
            'addFieldStrategies' => [],
            'addFilterStrategies' => [],
            'meta' => [],
            'data' => [],
            'modifiersPool' => $this->modifierPool,
        ]);
    }

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
            ->with(Store::DEFAULT_STORE_ID);
        $this->collectionMock->expects($this->once())
            ->method('toArray')
            ->willReturn($items);
        $this->collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn(count($items));
        $this->modifierPool->expects($this->once())
            ->method('getModifiersInstances')
            ->willReturn([]);

        $this->assertEquals($expectedData, $this->getModel()->getData());
    }

    public function testGetCollection()
    {
        $this->assertInstanceOf(Collection::class, $this->getModel()->getCollection());
    }
}
