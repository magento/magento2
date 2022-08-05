<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Service;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Api\ShipmentCommentRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\AbstractModel;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Service\ShipmentService;
use Magento\Shipping\Model\ShipmentNotifier;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShipmentServiceTest extends TestCase
{
    /**
     * Repository
     *
     * @var ShipmentCommentRepositoryInterface|MockObject
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
     * Repository
     *
     * @var ShipmentRepositoryInterface|MockObject
     */
    protected $repositoryMock;

    /**
     * Shipment Notifier
     *
     * @var ShipmentNotifier|MockObject
     */
    protected $notifierMock;

    /**
     * @var ShipmentService
     */
    protected $shipmentService;

    /**
     * SetUp
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->commentRepositoryMock = $this->getMockForAbstractClass(
            ShipmentCommentRepositoryInterface::class,
            ['getList'],
            '',
            false
        );
        $this->searchCriteriaBuilderMock = $this->createPartialMock(
            SearchCriteriaBuilder::class,
            ['create', 'addFilters']
        );
        $this->filterBuilderMock = $this->createPartialMock(
            FilterBuilder::class,
            ['setField', 'setValue', 'setConditionType', 'create']
        );
        $this->repositoryMock = $this->getMockForAbstractClass(
            ShipmentRepositoryInterface::class,
            ['get'],
            '',
            false
        );
        $this->notifierMock = $this->createPartialMock(ShipmentNotifier::class, ['notify']);

        $this->shipmentService = $objectManager->getObject(
            ShipmentService::class,
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

        $shipmentMock = $this->createPartialMock(Shipment::class, ['getShippingLabel']);

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
            AbstractModel::class,
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
