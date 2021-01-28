<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Service;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ShipmentServiceTest
 */
class ShipmentServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Repository
     *
     * @var \Magento\Sales\Api\ShipmentCommentRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
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
     * Repository
     *
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $repositoryMock;

    /**
     * Shipment Notifier
     *
     * @var \Magento\Shipping\Model\ShipmentNotifier|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $notifierMock;

    /**
     * @var \Magento\Sales\Model\Service\ShipmentService
     */
    protected $shipmentService;

    /**
     * SetUp
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->commentRepositoryMock = $this->getMockForAbstractClass(
            \Magento\Sales\Api\ShipmentCommentRepositoryInterface::class,
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
        $this->repositoryMock = $this->getMockForAbstractClass(
            \Magento\Sales\Api\ShipmentRepositoryInterface::class,
            ['get'],
            '',
            false
        );
        $this->notifierMock = $this->createPartialMock(\Magento\Shipping\Model\ShipmentNotifier::class, ['notify']);

        $this->shipmentService = $objectManager->getObject(
            \Magento\Sales\Model\Service\ShipmentService::class,
            [
                'commentRepository' => $this->commentRepositoryMock,
                'criteriaBuilder' => $this->searchCriteriaBuilderMock,
                'filterBuilder' => $this->filterBuilderMock,
                'repository' => $this->repositoryMock,
                'notifier' => $this->notifierMock,
            ]
        );
    }

    /**
     * Run test getLabel method
     */
    public function testGetLabel()
    {
        $id = 145;
        $returnValue = 'return-value';

        $shipmentMock = $this->createPartialMock(\Magento\Sales\Model\Order\Shipment::class, ['getShippingLabel']);

        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($shipmentMock);
        $shipmentMock->expects($this->once())
            ->method('getShippingLabel')
            ->willReturn($returnValue);

        $this->assertEquals($returnValue, $this->shipmentService->getLabel($id));
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

        $this->assertEquals($returnValue, $this->shipmentService->getCommentsList($id));
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
        $this->notifierMock->expects($this->once())
            ->method('notify')
            ->with($modelMock)
            ->willReturn($returnValue);

        $this->assertEquals($returnValue, $this->shipmentService->notify($id));
    }
}
