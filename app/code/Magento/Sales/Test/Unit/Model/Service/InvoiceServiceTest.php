<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Service;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Api\InvoiceCommentRepositoryInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\AbstractModel;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\InvoiceNotifier;
use Magento\Sales\Model\Service\InvoiceService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Sales\Model\Order\Invoice as InvoiceModel;
use Magento\Framework\Data\Collection;
use Magento\Sales\Api\Data\InvoiceInterface;

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InvoiceServiceTest extends TestCase
{

    /**
     * Repository
     *
     * @var InvoiceRepositoryInterface|MockObject
     */
    protected $repositoryMock;

    /**
     * Repository
     *
     * @var InvoiceCommentRepositoryInterface|MockObject
     */
    protected $commentRepositoryMock;

    /**
     * Search Criteria Builder
     *
     * @var SearchCriteriaBuilder|MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * Filter Builder
     *
     * @var FilterBuilder|MockObject
     */
    protected $filterBuilderMock;

    /**
     * Invoice Notifier
     *
     * @var InvoiceNotifier|MockObject
     */
    protected $invoiceNotifierMock;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * SetUp
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->repositoryMock = $this->createMock(InvoiceRepositoryInterface::class);
        $this->commentRepositoryMock = $this->createMock(InvoiceCommentRepositoryInterface::class);
        $this->searchCriteriaBuilderMock = $this->createPartialMock(
            SearchCriteriaBuilder::class,
            ['create', 'addFilters']
        );
        $this->filterBuilderMock = $this->createPartialMock(
            FilterBuilder::class,
            ['setField', 'setValue', 'setConditionType', 'create']
        );
        $this->invoiceNotifierMock = $this->createPartialMock(
            InvoiceNotifier::class,
            ['notify']
        );

        $this->invoiceService = $objectManager->getObject(
            InvoiceService::class,
            [
                'repository' => $this->repositoryMock,
                'commentRepository' => $this->commentRepositoryMock,
                'criteriaBuilder' => $this->searchCriteriaBuilderMock,
                'filterBuilder' => $this->filterBuilderMock,
                'notifier' => $this->invoiceNotifierMock
            ]
        );
    }

    /**
     * Run test setCapture method
     */
    public function testSetCapture()
    {
        $id = 145;
        $returnValue = true;

        $invoiceMock = $this->createPartialMock(Invoice::class, ['capture']);

        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($invoiceMock);
        $invoiceMock->expects($this->once())
            ->method('capture')
            ->willReturn($returnValue);

        $this->assertTrue($this->invoiceService->setCapture($id));
    }

    /**
     * Run test getCommentsList method
     */
    public function testGetCommentsList()
    {
        $id = 25;
        $returnValue = 'return-value';

        $filterMock = $this->createMock(Filter::class);
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);

        $this->filterBuilderMock->expects($this->once())
            ->method('setField')
            ->with('parent_id')->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('setValue')
            ->with($id)->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('setConditionType')
            ->with('eq')->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($filterMock);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilters')
            ->with([$filterMock]);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);
        $this->commentRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($returnValue);

        $this->assertEquals($returnValue, $this->invoiceService->getCommentsList($id));
    }

    /**
     * Run test notify method
     */
    public function testNotify()
    {
        $id = 123;
        $returnValue = 'return-value';

        $modelMock = $this->createMock(AbstractModel::class);

        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($modelMock);
        $this->invoiceNotifierMock->expects($this->once())
            ->method('notify')
            ->with($modelMock)
            ->willReturn($returnValue);

        $this->assertEquals($returnValue, $this->invoiceService->notify($id));
    }

    /**
     * Run test setVoid method
     */
    public function testSetVoid()
    {
        $id = 145;
        $returnValue = true;

        $invoiceMock = $this->createPartialMock(Invoice::class, ['void']);

        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($invoiceMock);
        $invoiceMock->expects($this->once())
            ->method('void')
            ->willReturn($returnValue);

        $this->assertTrue($this->invoiceService->setVoid($id));
    }

    public function testPrepareInvoiceSetsHistoryEntityNameWhenOriginalEntityTypePresent(): void
    {
        $orderRepository   = $this->createMock(OrderRepositoryInterface::class);
        $orderConverter    = $this->createMock(ConvertOrder::class);
        $serializer   = $this->createMock(Json::class);

        $service = new InvoiceService(
            $this->repositoryMock,
            $this->commentRepositoryMock,
            $this->searchCriteriaBuilderMock,
            $this->filterBuilderMock,
            $this->invoiceNotifierMock,
            $orderRepository,
            $orderConverter,
            $serializer
        );

        $order = $this->getMockBuilder(SalesOrder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllItems', 'getEntityType', 'setHistoryEntityName', 'getInvoiceCollection'])
            ->getMock();

        $invoice = $this->getMockBuilder(InvoiceModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setTotalQty', 'collectTotals'])
            ->getMock();

        $invoiceCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addItem'])
            ->getMock();

        $order->method('getAllItems')->willReturn([]);
        $order->method('getEntityType')->willReturn('order');
        $order->method('getInvoiceCollection')->willReturn($invoiceCollection);

        $order->expects($this->once())
            ->method('setHistoryEntityName')
            ->with('order');

        $orderConverter->expects($this->once())
            ->method('toInvoice')
            ->with($order)
            ->willReturn($invoice);

        $invoice->expects($this->once())->method('setTotalQty')->with(0);
        $invoice->expects($this->once())->method('collectTotals');

        $invoiceCollection->expects($this->once())->method('addItem')->with($invoice);

        $result = $service->prepareInvoice($order, []);
        $this->assertInstanceOf(InvoiceInterface::class, $result);
    }
}
