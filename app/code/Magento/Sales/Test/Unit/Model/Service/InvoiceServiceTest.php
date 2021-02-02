<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Service;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class InvoiceServiceTest
 */
class InvoiceServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Repository
     *
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $repositoryMock;

    /**
     * Repository
     *
     * @var \Magento\Sales\Api\InvoiceCommentRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $commentRepositoryMock;

    /**
     * Search Criteria Builder
     *
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * Filter Builder
     *
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $filterBuilderMock;

    /**
     * Invoice Notifier
     *
     * @var \Magento\Sales\Model\Order\InvoiceNotifier|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $invoiceNotifierMock;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * SetUp
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->repositoryMock = $this->getMockForAbstractClass(
            \Magento\Sales\Api\InvoiceRepositoryInterface::class,
            ['get'],
            '',
            false
        );
        $this->commentRepositoryMock = $this->getMockForAbstractClass(
            \Magento\Sales\Api\InvoiceCommentRepositoryInterface::class,
            ['getList'],
            '',
            false
        );
        $this->searchCriteriaBuilderMock = $this->createPartialMock(
            \Magento\Framework\Api\SearchCriteriaBuilder::class,
            ['create', 'addFilters']
        );
        $this->filterBuilderMock = $this->createPartialMock(
            \Magento\Framework\Api\FilterBuilder::class,
            ['setField', 'setValue', 'setConditionType', 'create']
        );
        $this->invoiceNotifierMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\InvoiceNotifier::class,
            ['notify']
        );

        $this->invoiceService = $objectManager->getObject(
            \Magento\Sales\Model\Service\InvoiceService::class,
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

        $invoiceMock = $this->createPartialMock(\Magento\Sales\Model\Order\Invoice::class, ['capture']);

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

        $filterMock = $this->createMock(\Magento\Framework\Api\Filter::class);
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);

        $this->filterBuilderMock->expects($this->once())
            ->method('setField')
            ->with('parent_id')
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('setValue')
            ->with($id)
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('setConditionType')
            ->with('eq')
            ->willReturnSelf();
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

        $modelMock = $this->getMockForAbstractClass(
            \Magento\Sales\Model\AbstractModel::class,
            [],
            '',
            false
        );

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

        $invoiceMock = $this->createPartialMock(\Magento\Sales\Model\Order\Invoice::class, ['void']);

        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($invoiceMock);
        $invoiceMock->expects($this->once())
            ->method('void')
            ->willReturn($returnValue);

        $this->assertTrue($this->invoiceService->setVoid($id));
    }
}
