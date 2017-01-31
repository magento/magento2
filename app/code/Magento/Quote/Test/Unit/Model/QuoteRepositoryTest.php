<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model;

use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Quote\Model\QuoteRepository\LoadHandler;

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

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->quoteFactoryMock = $this->getMock('\Magento\Quote\Model\QuoteFactory', ['create'], [], '', false);
        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->quoteMock = $this->getMock(
            '\Magento\Quote\Model\Quote',
            [
                'load',
                'loadByCustomer',
                'getIsActive',
                'getId',
                '__wakeup',
                'setSharedStoreIds',
                'save',
                'delete',
                'getCustomerId'
            ],
            [],
            '',
            false
        );
        $this->storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $this->searchResultsDataFactory = $this->getMock(
            '\Magento\Quote\Api\Data\CartSearchResultsInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->quoteCollectionMock =
            $this->getMock('Magento\Quote\Model\ResourceModel\Quote\Collection', [], [], '', false);

        $this->extensionAttributesJoinProcessorMock = $this->getMock(
            'Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface',
            [],
            [],
            '',
            false
        );

        $this->model = $objectManager->getObject(
            'Magento\Quote\Model\QuoteRepository',
            [
                'quoteFactory' => $this->quoteFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'searchResultsDataFactory' => $this->searchResultsDataFactory,
                'quoteCollection' => $this->quoteCollectionMock,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock
            ]
        );

        $this->loadHandlerMock = $this->getMock(LoadHandler::class, [], [], '', false);
        $reflection = new \ReflectionClass(get_class($this->model));
        $reflectionProperty = $reflection->getProperty('loadHandler');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->model, $this->loadHandlerMock);
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
        $this->markTestSkipped('MAGETWO-48531');
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

        $this->assertEquals($this->quoteMock, $this->model->get($cartId, $sharedStoreIds));
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
        $this->markTestSkipped('MAGETWO-48531');
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

        $this->model->getActive($cartId);
    }

    public function testGetActive()
    {
        $this->markTestSkipped('MAGETWO-48531');
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
        $this->quoteMock->expects($this->exactly(2))->method('getIsActive')->willReturn(1);

        $this->assertEquals($this->quoteMock, $this->model->getActive($cartId));
        $this->assertEquals($this->quoteMock, $this->model->getActive($cartId));
    }

    public function testGetActiveWithSharedStoreIds()
    {
        $this->markTestSkipped('MAGETWO-48531');
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
        $this->quoteMock->expects($this->exactly(2))->method('getIsActive')->willReturn(1);

        $this->assertEquals($this->quoteMock, $this->model->getActive($cartId, $sharedStoreIds));
        $this->assertEquals($this->quoteMock, $this->model->getActive($cartId, $sharedStoreIds));
    }

    public function testGetActiveForCustomer()
    {
        $this->markTestSkipped('MAGETWO-48531');
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

        $this->assertEquals($this->quoteMock, $this->model->getActiveForCustomer($customerId));
        $this->assertEquals($this->quoteMock, $this->model->getActiveForCustomer($customerId));
    }

    public function testSave()
    {
        $this->markTestSkipped('MAGETWO-48531');
        $this->quoteMock->expects($this->once())
            ->method('save');
        $this->quoteMock->expects($this->exactly(1))->method('getId')->willReturn(1);
        $this->quoteMock->expects($this->exactly(1))->method('getCustomerId')->willReturn(2);

        $this->model->save($this->quoteMock);
    }

    public function testDelete()
    {
        $this->quoteMock->expects($this->once())
            ->method('delete');
        $this->quoteMock->expects($this->exactly(1))->method('getId')->willReturn(1);
        $this->quoteMock->expects($this->exactly(1))->method('getCustomerId')->willReturn(2);

        $this->model->delete($this->quoteMock);
    }

    /**
     * @param int $direction
     * @param string $expectedDirection
     * @dataProvider getListSuccessDataProvider
     */
    public function testGetListSuccess($direction, $expectedDirection)
    {
        $this->markTestSkipped('MAGETWO-48531');
        $searchResult = $this->getMock('\Magento\Quote\Api\Data\CartSearchResultsInterface', [], [], '', false);
        $searchCriteriaMock = $this->getMock('\Magento\Framework\Api\SearchCriteria', [], [], '', false);
        $cartMock = $this->getMock('Magento\Payment\Model\Cart', [], [], '', false);
        $filterMock = $this->getMock('\Magento\Framework\Api\Filter', [], [], '', false);
        $pageSize = 10;

        $this->searchResultsDataFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($searchResult));

        $searchResult
            ->expects($this->once())
            ->method('setSearchCriteria');

        $filterGroupMock = $this->getMock('\Magento\Framework\Api\Search\FilterGroup', [], [], '', false);
        $searchCriteriaMock
            ->expects($this->any())
            ->method('getFilterGroups')
            ->will($this->returnValue([$filterGroupMock]));

        //addFilterGroupToCollection() checks
        $filterGroupMock->expects($this->any())->method('getFilters')->will($this->returnValue([$filterMock]));
        $filterMock->expects($this->once())->method('getField')->will($this->returnValue('store_id'));
        $filterMock->expects($this->any())->method('getConditionType')->will($this->returnValue('eq'));
        $filterMock->expects($this->once())->method('getValue')->will($this->returnValue('filter_value'));

        //back in getList()
        $this->quoteCollectionMock->expects($this->once())->method('getSize')->willReturn($pageSize);
        $searchResult->expects($this->once())->method('setTotalCount')->with($pageSize);
        $sortOrderMock = $this->getMockBuilder('Magento\Framework\Api\SortOrder')
            ->setMethods(['getField', 'getDirection'])
            ->disableOriginalConstructor()
            ->getMock();

        //foreach cycle
        $searchCriteriaMock
            ->expects($this->once())
            ->method('getSortOrders')
            ->will($this->returnValue([$sortOrderMock]));
        $sortOrderMock->expects($this->once())->method('getField')->will($this->returnValue('id'));
        $sortOrderMock->expects($this->once())->method('getDirection')->will($this->returnValue($direction));
        $this->quoteCollectionMock->expects($this->once())->method('addOrder')->with('id', $expectedDirection);

        $searchCriteriaMock->expects($this->once())->method('getCurrentPage')->will($this->returnValue(1));
        $searchCriteriaMock->expects($this->once())->method('getPageSize')->will($this->returnValue(10));
        $this->quoteCollectionMock->expects($this->once())->method('setCurPage')->with(1);
        $this->quoteCollectionMock->expects($this->once())->method('setPageSize')->with(10);

        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with(
                $this->isInstanceOf('\Magento\Quote\Model\ResourceModel\Quote\Collection')
            );

        $this->quoteCollectionMock->expects($this->once())->method('getItems')->willReturn([$cartMock]);
        $searchResult->expects($this->once())->method('setItems')->with([$cartMock]);

        $this->model = $this->getMock(
            'Magento\Quote\Model\QuoteRepository',
            ['getQuoteCollection'],
            [
                'quoteFactory' => $this->quoteFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'quoteCollection' => $this->quoteCollectionMock,
                'searchResultsDataFactory' => $this->searchResultsDataFactory,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock
            ]
        );
        $this->model->expects($this->once())->method('getQuoteCollection')->willReturn($this->quoteCollectionMock);
        $this->assertEquals($searchResult, $this->model->getList($searchCriteriaMock));
    }

    /**
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
