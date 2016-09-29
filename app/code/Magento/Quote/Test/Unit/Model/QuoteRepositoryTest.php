<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model;

use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteRepository\LoadHandler;
use Magento\Quote\Model\QuoteRepository\SaveHandler;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $model;

    /**
     * @var \Magento\Quote\Model\QuoteFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteFactoryMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Quote\Api\Data\CartSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsDataFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteCollectionMock;

    /**
     * @var JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesJoinProcessorMock;

    /**
     * @var LoadHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loadHandlerMock;

    /**
     * @var LoadHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $saveHandlerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    private $objectManagerMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        \Magento\Framework\App\ObjectManager::setInstance($this->objectManagerMock);

        $this->quoteFactoryMock = $this->getMock(\Magento\Quote\Model\QuoteFactory::class, ['create'], [], '', false);
        $this->storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->quoteMock = $this->getMock(
            \Magento\Quote\Model\Quote::class,
            [
                'load',
                'loadByCustomer',
                'getIsActive',
                'getId',
                '__wakeup',
                'setSharedStoreIds',
                'save',
                'delete',
                'getCustomerId',
                'getStoreId',
                'getData'
            ],
            [],
            '',
            false
        );
        $this->storeMock = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $this->searchResultsDataFactory = $this->getMock(
            \Magento\Quote\Api\Data\CartSearchResultsInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->quoteCollectionMock =
            $this->getMock(\Magento\Quote\Model\ResourceModel\Quote\Collection::class, [], [], '', false);

        $this->extensionAttributesJoinProcessorMock = $this->getMock(
            \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface::class,
            [],
            [],
            '',
            false
        );
        $this->collectionProcessor = $this->getMock(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class,
            [],
            [],
            '',
            false
        );
        $this->model = $objectManager->getObject(
            \Magento\Quote\Model\QuoteRepository::class,
            [
                'quoteFactory' => $this->quoteFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'searchResultsDataFactory' => $this->searchResultsDataFactory,
                'quoteCollection' => $this->quoteCollectionMock,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock,
                'collectionProcessor' => $this->collectionProcessor
            ]
        );

        $this->loadHandlerMock = $this->getMock(LoadHandler::class, [], [], '', false);
        $this->saveHandlerMock = $this->getMock(SaveHandler::class, [], [], '', false);

        $reflection = new \ReflectionClass(get_class($this->model));
        $reflectionProperty = $reflection->getProperty('loadHandler');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->model, $this->loadHandlerMock);

        $reflectionProperty = $reflection->getProperty('saveHandler');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->model, $this->saveHandlerMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with cartId = 14
     */
    public function testGetWithExceptionById()
    {
        $cartId = 14;

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn(1);
        $this->quoteMock->expects($this->never())->method('setSharedStoreIds');
        $this->quoteMock->expects($this->once())
            ->method('load')
            ->with($cartId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn(false);

        $this->model->get($cartId);
    }

    public function testGet()
    {
        $cartId = 15;

        $this->quoteFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->quoteMock);
        $this->storeManagerMock->expects(static::once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects(static::once())
            ->method('getId')
            ->willReturn(1);
        $this->quoteMock->expects(static::never())
            ->method('setSharedStoreIds');
        $this->quoteMock->expects(static::once())
            ->method('load')
            ->with($cartId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects(static::once())
            ->method('getId')
            ->willReturn($cartId);
        $this->quoteMock->expects(static::never())
            ->method('getCustomerId');
        $this->loadHandlerMock->expects(static::once())
            ->method('load')
            ->with($this->quoteMock);

        static::assertEquals($this->quoteMock, $this->model->get($cartId));
        static::assertEquals($this->quoteMock, $this->model->get($cartId));
    }

    public function testGetForCustomerAfterGet()
    {
        $cartId = 15;
        $customerId = 23;

        $this->quoteFactoryMock->expects(static::exactly(2))
            ->method('create')
            ->willReturn($this->quoteMock);
        $this->storeManagerMock->expects(static::exactly(2))
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects(static::exactly(2))
            ->method('getId')
            ->willReturn(1);
        $this->quoteMock->expects(static::never())
            ->method('setSharedStoreIds');
        $this->quoteMock->expects(static::once())
            ->method('load')
            ->with($cartId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects(static::once())
            ->method('loadByCustomer')
            ->with($customerId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects(static::exactly(3))
            ->method('getId')
            ->willReturn($cartId);
        $this->quoteMock->expects(static::any())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->loadHandlerMock->expects(static::exactly(2))
            ->method('load')
            ->with($this->quoteMock);

        static::assertEquals($this->quoteMock, $this->model->get($cartId));
        static::assertEquals($this->quoteMock, $this->model->getForCustomer($customerId));
    }

    public function testGetWithSharedStoreIds()
    {
        $cartId = 16;
        $sharedStoreIds = [1, 2];

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn(1);
        $this->quoteMock->expects($this->once())
            ->method('setSharedStoreIds')
            ->with($sharedStoreIds)
            ->willReturnSelf();
        $this->quoteMock->expects($this->once())
            ->method('load')
            ->with($cartId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn($cartId);

        $this->loadHandlerMock->expects($this->once())
            ->method('load')
            ->with($this->quoteMock)
            ->willReturn($this->quoteMock);

        $this->assertEquals($this->quoteMock, $this->model->get($cartId, $sharedStoreIds));
    }

    public function testGetForCustomer()
    {
        $cartId = 17;
        $customerId = 23;

        $this->quoteFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->quoteMock);
        $this->storeManagerMock->expects(static::once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects(static::once())
            ->method('getId')
            ->willReturn(1);
        $this->quoteMock->expects(static::never())
            ->method('setSharedStoreIds');
        $this->quoteMock->expects(static::once())
            ->method('loadByCustomer')
            ->with($customerId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects(static::exactly(2))
            ->method('getId')
            ->willReturn($cartId);

        $this->loadHandlerMock->expects(static::once())
            ->method('load')
            ->with($this->quoteMock);

        static::assertEquals($this->quoteMock, $this->model->getForCustomer($customerId));
        static::assertEquals($this->quoteMock, $this->model->getForCustomer($customerId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with cartId = 14
     */
    public function testGetActiveWithExceptionById()
    {
        $cartId = 14;

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn(1);
        $this->quoteMock->expects($this->never())->method('setSharedStoreIds');
        $this->quoteMock->expects($this->once())
            ->method('load')
            ->with($cartId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn(false);
        $this->quoteMock->expects($this->never())->method('getIsActive');

        $this->model->getActive($cartId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with cartId = 15
     */
    public function testGetActiveWithExceptionByIsActive()
    {
        $cartId = 15;

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn(1);
        $this->quoteMock->expects($this->never())->method('setSharedStoreIds');
        $this->quoteMock->expects($this->once())
            ->method('load')
            ->with($cartId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn($cartId);
        $this->quoteMock->expects($this->once())->method('getIsActive')->willReturn(0);

        $this->loadHandlerMock->expects($this->once())
            ->method('load')
            ->with($this->quoteMock)
            ->willReturn($this->quoteMock);

        $this->model->getActive($cartId);
    }

    public function testGetActive()
    {
        $cartId = 15;

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn(1);
        $this->quoteMock->expects($this->never())->method('setSharedStoreIds');
        $this->quoteMock->expects($this->once())
            ->method('load')
            ->with($cartId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn($cartId);
        $this->quoteMock->expects($this->once())->method('getIsActive')->willReturn(1);

        $this->loadHandlerMock->expects($this->once())
            ->method('load')
            ->with($this->quoteMock)
            ->willReturn($this->quoteMock);

        $this->assertEquals($this->quoteMock, $this->model->getActive($cartId));
    }

    public function testGetActiveWithSharedStoreIds()
    {
        $cartId = 16;
        $sharedStoreIds = [1, 2];

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn(1);
        $this->quoteMock->expects($this->once())
            ->method('setSharedStoreIds')
            ->with($sharedStoreIds)
            ->willReturnSelf();
        $this->quoteMock->expects($this->once())
            ->method('load')
            ->with($cartId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn($cartId);
        $this->quoteMock->expects($this->once())->method('getIsActive')->willReturn(1);

        $this->loadHandlerMock->expects($this->once())
            ->method('load')
            ->with($this->quoteMock)
            ->willReturn($this->quoteMock);

        $this->assertEquals($this->quoteMock, $this->model->getActive($cartId, $sharedStoreIds));
    }

    public function testGetActiveForCustomer()
    {
        $cartId = 17;
        $customerId = 23;

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn(1);
        $this->quoteMock->expects($this->never())->method('setSharedStoreIds');
        $this->quoteMock->expects($this->once())
            ->method('loadByCustomer')
            ->with($customerId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects($this->exactly(2))->method('getId')->willReturn($cartId);
        $this->quoteMock->expects($this->exactly(2))->method('getIsActive')->willReturn(1);

        $this->loadHandlerMock->expects($this->once())
            ->method('load')
            ->with($this->quoteMock)
            ->willReturn($this->quoteMock);

        $this->assertEquals($this->quoteMock, $this->model->getActiveForCustomer($customerId));
        $this->assertEquals($this->quoteMock, $this->model->getActiveForCustomer($customerId));
    }

    public function testSave()
    {
        $cartId = 100;
        $quoteMock = $this->getMock(
            \Magento\Quote\Model\Quote::class,
            ['getId', 'getCustomerId', 'getStoreId', 'hasData', 'setData'],
            [],
            '',
            false
        );
        $quoteMock->expects($this->exactly(3))->method('getId')->willReturn($cartId);
        $quoteMock->expects($this->once())->method('getCustomerId')->willReturn(2);
        $quoteMock->expects($this->once())->method('getStoreId')->willReturn(5);

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn(1);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn($cartId);
        $this->quoteMock->expects($this->once())->method('setSharedStoreIds');
        $this->quoteMock->expects($this->once())
            ->method('load')
            ->with($cartId)
            ->willReturn($this->storeMock);
        $this->loadHandlerMock->expects($this->once())
            ->method('load')
            ->with($this->quoteMock)
            ->willReturn($this->quoteMock);

        $this->quoteMock->expects($this->once())->method('getData')->willReturn(['key' => 'value']);

        $quoteMock->expects($this->once())->method('hasData')->with('key')->willReturn(false);
        $quoteMock->expects($this->once())->method('setData')->with('key', 'value')->willReturnSelf();

        $this->saveHandlerMock->expects($this->once())->method('save')->with($quoteMock)->willReturn($quoteMock);
        $this->model->save($quoteMock);
    }

    public function testDelete()
    {
        $this->quoteMock->expects($this->once())
            ->method('delete');
        $this->quoteMock->expects($this->exactly(1))->method('getId')->willReturn(1);
        $this->quoteMock->expects($this->exactly(1))->method('getCustomerId')->willReturn(2);

        $this->model->delete($this->quoteMock);
    }

    public function testGetList()
    {
        $pageSize = 10;
        $collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->quoteCollectionMock);
        $cartMock = $this->getMock(CartInterface::class, [], [], '', false);
        $this->loadHandlerMock->expects($this->once())
            ->method('load')
            ->with($cartMock);
        $this->objectManagerMock->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnOnConsecutiveCalls($collectionFactoryMock);

        $searchResult = $this->getMock(\Magento\Quote\Api\Data\CartSearchResultsInterface::class, [], [], '', false);
        $searchCriteriaMock = $this->getMock(\Magento\Framework\Api\SearchCriteria::class, [], [], '', false);
        $this->searchResultsDataFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($searchResult);

        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $this->quoteCollectionMock);

        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with(
                $this->isInstanceOf(\Magento\Quote\Model\ResourceModel\Quote\Collection::class)
            );
        $this->quoteCollectionMock->expects($this->atLeastOnce())->method('getItems')->willReturn([$cartMock]);
        $searchResult->expects($this->once())->method('setTotalCount')->with($pageSize);
        $this->quoteCollectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($pageSize);
        $searchResult->expects($this->once())
            ->method('setItems')
            ->with([$cartMock]);
        $this->assertEquals($searchResult, $this->model->getList($searchCriteriaMock));
    }

    /**
     * @deprecated
     * @return array
     */
    public function getListSuccessDataProvider()
    {
        return [
            'asc' => [SortOrder::SORT_ASC, 'ASC'],
            'desc' => [SortOrder::SORT_DESC, 'DESC']
        ];
    }
}
