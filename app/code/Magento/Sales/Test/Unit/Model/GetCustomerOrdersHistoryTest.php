<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Api\Data\PaymentAdditionalInfoInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\GetCustomerOrdersHistory;
use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Tax\Api\OrderTaxManagementInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetCustomerOrdersHistoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    private $orderRepository;

    /**
     * @var Metadata|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadata;

    /**
     * @var SearchResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultFactory;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    /**
     * @var OrderTaxManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderTaxManagementMock;

    /**
     * @var PaymentAdditionalInfoInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentAdditionalInfoFactory;

    /**
     * Setup the test
     *
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $className = \Magento\Sales\Model\ResourceModel\Metadata::class;
        $this->metadata = $this->createMock($className);

        $className = \Magento\Sales\Api\Data\OrderSearchResultInterfaceFactory::class;
        $this->searchResultFactory = $this->createPartialMock($className, ['create']);
        $this->collectionProcessor = $this->createMock(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
        );
        $orderExtensionFactoryMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderExtensionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderTaxManagementMock = $this->getMockBuilder(OrderTaxManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->paymentAdditionalInfoFactory = $this->getMockBuilder(PaymentAdditionalInfoInterfaceFactory::class)
            ->disableOriginalConstructor()->setMethods(['create'])->getMockForAbstractClass();
        $this->orderRepository = $this->objectManager->getObject(
            \Magento\Sales\Model\OrderRepository::class,
            [
                'metadata' => $this->metadata,
                'searchResultFactory' => $this->searchResultFactory,
                'collectionProcessor' => $this->collectionProcessor,
                'orderExtensionFactory' => $orderExtensionFactoryMock,
                'orderTaxManagement' => $this->orderTaxManagementMock,
                'paymentAdditionalInfoFactory' => $this->paymentAdditionalInfoFactory
            ]
        );
    }
    /**
     * Test for method getMine
     *
     * @return void
     */
    public function testExectute()
    {
        $customerId = 1;
        $field = 'field';
        $conditionType = 'eq';

        $filterBuilder = $this->getMockBuilder(\Magento\Framework\Api\FilterBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $filterGroupBuilder = $this->objectManager
            ->getObject(\Magento\Framework\Api\Search\FilterGroupBuilder::class);

        $getCustomerOrdersHistory = new GetCustomerOrdersHistory(
            $this->orderRepository,
            $filterBuilder,
            $filterGroupBuilder
        );

        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);

        $filterMock = $this->createMock(\Magento\Framework\Api\Filter::class);
        $filterMock->expects($this->once())->method('getField')->willReturn($field);
        $filterMock->expects($this->once())->method('getConditionType')->willReturn($conditionType);
        $filterMock->expects($this->once())->method('getValue')->willReturn($customerId);

        $filterGroupMock = $this->createMock(\Magento\Framework\Api\Search\FilterGroup::class);
        $filterGroupMock->expects($this->atLeastOnce())->method('getFilters')->willReturn([$filterMock]);

        $searchCriteriaMock->expects($this->atLeastOnce())->method('getFilterGroups')->willReturn(
            [$filterGroupMock]
        );

        $collectionMock = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Collection::class);
        $itemsMock = $this->getMockBuilder(OrderInterface::class)->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $orderTaxDetailsMock = $this->getMockBuilder(\Magento\Tax\Api\Data\OrderTaxDetailsInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAppliedTaxes', 'getItems'])->getMockForAbstractClass();
        $paymentMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderPaymentInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $paymentAdditionalInfo = $this->getMockBuilder(\Magento\Payment\Api\Data\PaymentAdditionalInfoInterface::class)
            ->disableOriginalConstructor()->setMethods(['setKey', 'setValue'])->getMockForAbstractClass();

        $extensionAttributes = $this->createPartialMock(
            \Magento\Sales\Api\Data\OrderExtension::class,
            [
                'getShippingAssignments', 'setShippingAssignments', 'setConvertingFromQuote',
                'setAppliedTaxes', 'setItemAppliedTaxes', 'setPaymentAdditionalInfo'
            ]
        );
        $shippingAssignmentBuilder = $this->createMock(
            \Magento\Sales\Model\Order\ShippingAssignmentBuilder::class
        );
        $itemsMock->expects($this->atLeastOnce())->method('getEntityId')->willReturn(1);
        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);
        $itemsMock->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $itemsMock->expects($this->atleastOnce())->method('getPayment')->willReturn($paymentMock);
        $paymentMock->expects($this->atLeastOnce())->method('getAdditionalInformation')
            ->willReturn(['method' => 'checkmo']);
        $this->paymentAdditionalInfoFactory->expects($this->atLeastOnce())->method('create')
            ->willReturn($paymentAdditionalInfo);
        $paymentAdditionalInfo->expects($this->atLeastOnce())->method('setKey')->willReturnSelf();
        $paymentAdditionalInfo->expects($this->atLeastOnce())->method('setValue')->willReturnSelf();
        $this->orderTaxManagementMock->expects($this->atLeastOnce())->method('getOrderTaxDetails')
            ->willReturn($orderTaxDetailsMock);
        $extensionAttributes->expects($this->any())
            ->method('getShippingAssignments')
            ->willReturn($shippingAssignmentBuilder);

        $this->searchResultFactory->expects($this->once())->method('create')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('getItems')->willReturn([$itemsMock]);

        $collectionMock->expects($this->once())->method('getSearchCriteria')->willReturn($searchCriteriaMock);

        $result = $getCustomerOrdersHistory->execute($customerId, $searchCriteriaMock);

        $searchCriteria = $result->getSearchCriteria();
        $this->assertEquals($searchCriteriaMock, $searchCriteria);

        $filterGroup = $searchCriteria->getFilterGroups()[0];
        $filter = $filterGroup->getFilters()[0];
        $this->assertEquals($filterMock, $filter);

        $this->assertEquals($customerId, $filter->getValue());
        $this->assertEquals($conditionType, $filter->getConditionType());
        $this->assertEquals($field, $filter->getField());
    }
}
