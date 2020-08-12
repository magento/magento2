<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreManagerTest extends TestCase
{
    /**
     * @var StoreManager
     */
    protected $model;

    /**
     * @var StoreRepositoryInterface|MockObject
     */
    protected $storeRepositoryMock;

    /**
     * @var StoreResolverInterface|MockObject
     */
    protected $storeResolverMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->storeRepositoryMock = $this->getMockBuilder(StoreRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $this->storeResolverMock = $this->getMockBuilder(StoreResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $this->model = $objectManager->getObject(
            StoreManager::class,
            [
                'storeRepository' => $this->storeRepositoryMock,
                'storeResolver' => $this->storeResolverMock
            ]
        );
    }

    public function testGetStoreEmptyParameter()
    {
        $storeId = 1;
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $this->storeResolverMock->expects($this->any())->method('getCurrentStoreId')->willReturn($storeId);
        $this->storeRepositoryMock->expects($this->atLeastOnce())
            ->method('getById')
            ->with($storeId)
            ->willReturn($storeMock);
        $this->assertInstanceOf(StoreInterface::class, $this->model->getStore());
        $this->assertEquals($storeMock, $this->model->getStore());
    }

    public function testGetStoreStringParameter()
    {
        $storeId = 'store_code';
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $this->storeRepositoryMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($storeId)
            ->willReturn($storeMock);
        $actualStore = $this->model->getStore($storeId);
        $this->assertInstanceOf(StoreInterface::class, $actualStore);
        $this->assertEquals($storeMock, $actualStore);
    }

    public function testGetStoreObjectStoreParameter()
    {
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $actualStore = $this->model->getStore($storeMock);
        $this->assertInstanceOf(StoreInterface::class, $actualStore);
        $this->assertEquals($storeMock, $actualStore);
    }

    /**
     * @dataProvider getStoresDataProvider
     */
    public function testGetStores($storesList, $withDefault, $codeKey, $expectedStores)
    {
        $this->storeRepositoryMock->expects($this->any())->method('getList')->willReturn($storesList);
        $this->assertEquals($expectedStores, $this->model->getStores($withDefault, $codeKey));
    }

    /**
     * @return array
     */
    public function getStoresDataProvider()
    {
        $defaultStoreMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $defaultStoreMock->expects($this->any())->method('getId')->willReturn(0);
        $defaultStoreMock->expects($this->any())->method('getCode')->willReturn('default');
        $storeMock->expects($this->any())->method('getId')->willReturn(1);
        $storeMock->expects($this->any())->method('getCode')->willReturn('first_store');

        return [
            'withoutDefaultAndId' => [
                'storesList' => [$defaultStoreMock, $storeMock],
                'withDefault' => false,
                'codeKey' => false,
                'expectedStores' =>  [1 => $storeMock]
            ],
            'withoutDefaultAndCodeKey' => [
                'storesList' => [$defaultStoreMock,$storeMock],
                'withDefault' => false,
                'codeKey' => true,
                'expectedStores' =>  ['first_store' => $storeMock]
            ],
            'withDefaultAndId' => [
                'storesList' => [$defaultStoreMock,$storeMock],
                'withDefault' => true,
                'codeKey' => false,
                'expectedStores' =>  [0 => $defaultStoreMock, 1 => $storeMock]
            ],
            'withDefaultAndCodeKey' => [
                'storesList' => [$defaultStoreMock,$storeMock],
                'withDefault' => true,
                'codeKey' => true,
                'expectedStores' =>  ['default' => $defaultStoreMock, 'first_store' => $storeMock]
            ],
        ];
    }
}
