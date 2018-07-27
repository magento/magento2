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
class InvoiceServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Repository
     *
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * Repository
     *
     * @var \Magento\Sales\Api\InvoiceCommentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commentRepositoryMock;

    /**
     * Search Criteria Builder
     *
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * Filter Builder
     *
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilderMock;

    /**
     * Invoice Notifier
     *
     * @var \Magento\Sales\Model\Order\InvoiceNotifier|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceNotifierMock;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * SetUp
     */
    protected function setUp()
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->repositoryMock = $this->getMockForAbstractClass(
            'Magento\Sales\Api\InvoiceRepositoryInterface',
            ['get'],
            '',
            false
        );
        $this->commentRepositoryMock = $this->getMockForAbstractClass(
            'Magento\Sales\Api\InvoiceCommentRepositoryInterface',
            ['getList'],
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
        $this->invoiceNotifierMock = $this->getMock(
            'Magento\Sales\Model\Order\InvoiceNotifier',
            ['notify'],
            [],
            '',
            false
        );

        $this->invoiceService = $objectManager->getObject(
            'Magento\Sales\Model\Service\InvoiceService',
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

        $invoiceMock = $this->getMock(
            'Magento\Sales\Model\Order\Invoice',
            ['capture'],
            [],
            '',
            false
        );

        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($id)
            ->will($this->returnValue($invoiceMock));
        $invoiceMock->expects($this->once())
            ->method('capture')
            ->will($this->returnValue($returnValue));

        $this->assertTrue($this->invoiceService->setCapture($id));
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
        $this->commentRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->will($this->returnValue($returnValue));

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
            'Magento\Sales\Model\AbstractModel',
            [],
            '',
            false
        );

        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($id)
            ->will($this->returnValue($modelMock));
        $this->invoiceNotifierMock->expects($this->once())
            ->method('notify')
            ->with($modelMock)
            ->will($this->returnValue($returnValue));

        $this->assertEquals($returnValue, $this->invoiceService->notify($id));
    }

    /**
     * Run test setVoid method
     */
    public function testSetVoid()
    {
        $id = 145;
        $returnValue = true;

        $invoiceMock = $this->getMock(
            'Magento\Sales\Model\Order\Invoice',
            ['void'],
            [],
            '',
            false
        );

        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($id)
            ->will($this->returnValue($invoiceMock));
        $invoiceMock->expects($this->once())
            ->method('void')
            ->will($this->returnValue($returnValue));

        $this->assertTrue($this->invoiceService->setVoid($id));
    }
}
