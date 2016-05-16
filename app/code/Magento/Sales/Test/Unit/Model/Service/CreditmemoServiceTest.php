<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Service;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Item;

/**
 * Class CreditmemoServiceTest
 */
class CreditmemoServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoRepositoryMock;

    /**
     * @var \Magento\Sales\Api\CreditmemoCommentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoCommentRepositoryMock;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var \Magento\Sales\Model\Order\CreditmemoNotifier|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoNotifierMock;

    /**
     * @var PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrencyMock;

    /**
     * @var \Magento\Sales\Model\Service\CreditmemoService
     */
    protected $creditmemoService;

    /**
     * SetUp
     */
    protected function setUp()
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->creditmemoRepositoryMock = $this->getMockForAbstractClass(
            'Magento\Sales\Api\CreditmemoRepositoryInterface',
            ['get'],
            '',
            false
        );
        $this->creditmemoCommentRepositoryMock = $this->getMockForAbstractClass(
            'Magento\Sales\Api\CreditmemoCommentRepositoryInterface',
            [],
            '',
            false
        );
        $this->searchCriteriaBuilderMock = $this->getMock(
            'Magento\Framework\Api\SearchCriteriaBuilder',
            ['create', 'addFilters'],
            [],
            '',
            false
        );
        $this->filterBuilderMock = $this->getMock(
            'Magento\Framework\Api\FilterBuilder',
            ['setField', 'setValue', 'setConditionType', 'create'],
            [],
            '',
            false
        );
        $this->creditmemoNotifierMock = $this->getMock(
            'Magento\Sales\Model\Order\CreditmemoNotifier',
            [],
            [],
            '',
            false
        );
        $this->priceCurrencyMock = $this->getMockBuilder(PriceCurrencyInterface::class)->getMockForAbstractClass();

        $this->creditmemoService = $objectManager->getObject(
            'Magento\Sales\Model\Service\CreditmemoService',
            [
                'creditmemoRepository' => $this->creditmemoRepositoryMock,
                'creditmemoCommentRepository' => $this->creditmemoCommentRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'filterBuilder' => $this->filterBuilderMock,
                'creditmemoNotifier' => $this->creditmemoNotifierMock,
                'priceCurrency' => $this->priceCurrencyMock
            ]
        );
    }

    /**
     * Run test cancel method
     * @expectedExceptionMessage You can not cancel Credit Memo
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testCancel()
    {
        $this->assertTrue($this->creditmemoService->cancel(1));
    }

    /**
     * Run test getCommentsList method
     */
    public function testGetCommentsList()
    {
        $id = 25;
        $returnValue = 'return-value';

        $filterMock = $this->getMock(
            'Magento\Framework\Api\Filter',
            [],
            [],
            '',
            false
        );
        $searchCriteriaMock = $this->getMock(
            'Magento\Framework\Api\SearchCriteria',
            [],
            [],
            '',
            false
        );

        $this->filterBuilderMock->expects($this->once())
            ->method('setField')
            ->with('parent_id')
            ->will($this->returnSelf());
        $this->filterBuilderMock->expects($this->once())
            ->method('setValue')
            ->with($id)
            ->will($this->returnSelf());
        $this->filterBuilderMock->expects($this->once())
            ->method('setConditionType')
            ->with('eq')
            ->will($this->returnSelf());
        $this->filterBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($filterMock));
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilters')
            ->with([$filterMock]);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($searchCriteriaMock));
        $this->creditmemoCommentRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->will($this->returnValue($returnValue));

        $this->assertEquals($returnValue, $this->creditmemoService->getCommentsList($id));
    }

    /**
     * Run test notify method
     */
    public function testNotify()
    {
        $id = 123;
        $returnValue = 'return-value';

        $modelMock = $this->getMockForAbstractClass(
            'Magento\Sales\Model\AbstractModel',
            [],
            '',
            false
        );

        $this->creditmemoRepositoryMock->expects($this->once())
            ->method('get')
            ->with($id)
            ->will($this->returnValue($modelMock));
        $this->creditmemoNotifierMock->expects($this->once())
            ->method('notify')
            ->with($modelMock)
        ->will($this->returnValue($returnValue));

        $this->assertEquals($returnValue, $this->creditmemoService->notify($id));
    }

    public function testRefund()
    {
        $creditMemoMock = $this->getMockBuilder(Creditmemo::class)
            ->setMethods(['getId', 'getOrder', 'getBaseGrandTotal', 'getAllItems', 'setDoTransaction'])
            ->disableOriginalConstructor()
            ->getMock();
        $creditMemoMock->expects($this->once())->method('getId')->willReturn(null);
        $orderMock = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $creditMemoMock->expects($this->atLeastOnce())->method('getOrder')->willReturn($orderMock);
        $itemMock = $this->getMockBuilder(
            Item::class
        )->disableOriginalConstructor()->getMock();
        $creditMemoMock->expects($this->once())->method('getAllItems')->willReturn([$itemMock]);
        $itemMock->expects($this->once()) -> method('setCreditMemo')->with($creditMemoMock);
        $itemMock->expects($this->once()) -> method('getQty')->willReturn(1);
        $itemMock->expects($this->once()) -> method('register');
        $creditMemoMock->expects($this->once())->method('setDoTransaction')->with(false);
        $this->assertSame($creditMemoMock, $this->creditmemoService->refund($creditMemoMock, true));
    }

    /**
     * @expectedExceptionMessage The most money available to refund is 1.
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testRefundExpectsMoneyAvailableToReturn()
    {
        $baseGrandTotal = 10;
        $baseTotalRefunded = 9;
        $baseTotalPaid = 10;
        $creditMemoMock = $this->getMockBuilder(CreditmemoInterface::class)
            ->setMethods(['getId', 'getOrder', 'getBaseGrandTotal', 'formatBasePrice'])
            ->getMockForAbstractClass();
        $creditMemoMock->expects($this->once())->method('getId')->willReturn(null);
        $orderMock = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $creditMemoMock->expects($this->atLeastOnce())->method('getOrder')->willReturn($orderMock);
        $creditMemoMock->expects($this->once())->method('getBaseGrandTotal')->willReturn($baseGrandTotal);
        $orderMock->expects($this->atLeastOnce())->method('getBaseTotalRefunded')->willReturn($baseTotalRefunded);
        $this->priceCurrencyMock->expects($this->exactly(2))->method('round')->withConsecutive(
            [$baseTotalRefunded + $baseGrandTotal],
            [$baseTotalPaid]
        )->willReturnOnConsecutiveCalls(
            $baseTotalRefunded + $baseGrandTotal,
            $baseTotalPaid
        );
        $orderMock->expects($this->atLeastOnce())->method('getBaseTotalPaid')->willReturn($baseTotalPaid);
        $baseAvailableRefund = $baseTotalPaid - $baseTotalRefunded;
        $orderMock->expects($this->once())->method('formatBasePrice')->with(
            $baseAvailableRefund
        )->willReturn($baseAvailableRefund);
        $this->creditmemoService->refund($creditMemoMock, true);
    }

    /**
     * @expectedExceptionMessage We cannot register an existing credit memo.
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testRefundDoNotExpectsId()
    {
        $creditMemoMock = $this->getMockBuilder(CreditmemoInterface::class)
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $creditMemoMock->expects($this->once())->method('getId')->willReturn(444);
        $this->creditmemoService->refund($creditMemoMock, true);
    }
}
