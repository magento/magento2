<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Model;

use Magento\Framework\App\DeploymentConfig;

class StoreManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $model;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeRepositoryMock;

    /**
     * @var \Magento\Store\Api\StoreResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeResolverMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->storeRepositoryMock = $this->getMockBuilder('Magento\Store\Api\StoreRepositoryInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->storeResolverMock = $this->getMockBuilder('Magento\Store\Api\StoreResolverInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->model = $objectManager->getObject(
            '\Magento\Store\Model\StoreManager',
            [
                'storeRepository' => $this->storeRepositoryMock,
                'storeResolver' => $this->storeResolverMock
            ]
        );
    }

    public function testGetStoreEmptyParameter()
    {
        $storeId = 1;
        $storeMock = $this->getMockBuilder('Magento\Store\Api\Data\StoreInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->storeResolverMock->expects($this->any())->method('getCurrentStoreId')->willReturn($storeId);
        $this->storeRepositoryMock->expects($this->atLeastOnce())
            ->method('getById')
            ->with($storeId)
            ->willReturn($storeMock);
        $this->assertInstanceOf('Magento\Store\Api\Data\StoreInterface', $this->model->getStore());
        $this->assertEquals($storeMock, $this->model->getStore());
    }

    public function testGetStoreStringParameter()
    {
        $storeId = 'store_code';
        $storeMock = $this->getMockBuilder('Magento\Store\Api\Data\StoreInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->storeRepositoryMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($storeId)
            ->willReturn($storeMock);
        $actualStore = $this->model->getStore($storeId);
        $this->assertInstanceOf('Magento\Store\Api\Data\StoreInterface', $actualStore);
        $this->assertEquals($storeMock, $actualStore);
    }

    public function testGetStoreObjectStoreParameter()
    {
        $storeMock = $this->getMockBuilder('Magento\Store\Api\Data\StoreInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $actualStore = $this->model->getStore($storeMock);
        $this->assertInstanceOf('Magento\Store\Api\Data\StoreInterface', $actualStore);
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
        $defaultStoreMock = $this->getMockBuilder('Magento\Store\Api\Data\StoreInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $storeMock = $this->getMockBuilder('Magento\Store\Api\Data\StoreInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
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
