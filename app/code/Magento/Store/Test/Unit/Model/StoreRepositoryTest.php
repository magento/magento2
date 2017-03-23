<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\ResourceModel\Store\Collection;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\StoreRepository;
use Magento\Framework\App\Config;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StoreFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeFactory;

    /**
     * @var CollectionFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeCollectionFactory;

    /**
     * @var bool
     */
    protected $allLoaded = false;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var Config | \PHPUnit_Framework_MockObject_MockObject
     */
    private $appConfigMock;

    public function setUp()
    {
        $this->storeFactory = $this->getMockBuilder(StoreFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeRepository = new StoreRepository(
            $this->storeFactory,
            $this->storeCollectionFactory
        );
        $this->appConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->initDistroList();
    }

    private function initDistroList()
    {
        $repositoryReflection = new \ReflectionClass($this->storeRepository);
        $deploymentProperty = $repositoryReflection->getProperty('appConfig');
        $deploymentProperty->setAccessible(true);
        $deploymentProperty->setValue($this->storeRepository, $this->appConfigMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Requested store is not found
     */
    public function testGetWithException()
    {
        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeFactory->expects($this->once())
            ->method('create')
            ->willReturn($storeMock);

        $this->storeRepository->get('some_code');
    }

    public function testGetWithAvailableStoreFromScope()
    {
        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn(1);
        $this->storeFactory->expects($this->once())
            ->method('create')
            ->willReturn($storeMock);

        $this->assertEquals($storeMock, $this->storeRepository->get('some_code'));
    }

    public function testGetByIdWithAvailableStoreFromScope()
    {
        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $storeMock->expects($this->once())
            ->method('getCode')
            ->willReturn('some_code');
        $this->storeFactory->expects($this->once())
            ->method('create')
            ->willReturn($storeMock);
        $this->appConfigMock->expects($this->once())
            ->method('get')
            ->willReturn([]);

        $this->assertEquals($storeMock, $this->storeRepository->getById(1));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Requested store is not found
     */
    public function testGetByIdWithException()
    {
        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeFactory->expects($this->once())
            ->method('create')
            ->willReturn($storeMock);
        $this->appConfigMock->expects($this->once())
            ->method('get')
            ->willReturn([]);
        $this->storeRepository->getById(1);
    }

    public function testGetList()
    {
        $storeMock1 = $this->getMock(StoreInterface::class);
        $storeMock1->expects($this->once())
            ->method('getCode')
            ->willReturn('some_code');
        $storeMock1->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $storeMock2 = $this->getMock(StoreInterface::class);
        $storeMock2->expects($this->once())
            ->method('getCode')
            ->willReturn('some_code_2');
        $storeMock2->expects($this->once())
            ->method('getId')
            ->willReturn(2);
        $this->appConfigMock->expects($this->once())
            ->method('get')
            ->willReturn([
                [
                    'code' => 'some_code'
                ],
                [
                    'code' => 'some_code_2'
                ]
            ]);
        $this->storeFactory->expects($this->at(0))
            ->method('create')
            ->willReturn($storeMock1);
        $this->storeFactory->expects($this->at(1))
            ->method('create')
            ->willReturn($storeMock2);

        $this->assertEquals(
            ['some_code' => $storeMock1, 'some_code_2' => $storeMock2],
            $this->storeRepository->getList()
        );
    }
}
