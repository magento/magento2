<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order\Payment;


class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteria;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metaData;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterGroup;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentResource;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Repository
     */
    protected $repository;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->metaData = $this->getMock(
            'Magento\Sales\Model\ResourceModel\Metadata',
            [],
            [],
            '',
            false
        );
        $this->searchResultFactory = $this->getMock(
            'Magento\Sales\Api\Data\OrderPaymentSearchResultInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->searchCriteria = $this->getMock(
            'Magento\Framework\Api\SearchCriteria',
            [],
            [],
            '',
            false
        );
        $this->collection = $this->getMock(
            'Magento\Sales\Model\ResourceModel\Order\Payment\Collection',
            [],
            [],
            '',
            false
        );
        $this->paymentResource = $this->getMock(
            'Magento\Sales\Model\ResourceModel\Order\Payment',
            [],
            [],
            '',
            false
        );
        $this->filterGroup = $this->getMock(
            'Magento\Framework\Api\Search\FilterGroup',
            [],
            [],
            '',
            false
        );
        $this->filter = $this->getMock(
            'Magento\Framework\Api\Filter',
            [],
            [],
            '',
            false
        );
        $this->repository = $objectManager->getObject(
            'Magento\Sales\Model\Order\Payment\Repository',
            [
                'searchResultFactory' => $this->searchResultFactory,
                'metaData' => $this->metaData,
            ]
        );
    }

    public function testCreate()
    {
        $expected = "expect";
        $this->metaData->expects($this->once())->method('getNewInstance')->willReturn($expected);
        $this->assertEquals($expected, $this->repository->create());
    }

    public function testSave()
    {
        $payment = $this->mockPayment();
        $this->metaData->expects($this->once())->method('getMapper')->willReturn($this->paymentResource);
        $this->paymentResource->expects($this->once())->method('save')
            ->with($payment)
            ->willReturn($payment);
        $this->assertSame($payment, $this->repository->save($payment));
    }

    public function testDelete()
    {
        $payment = $this->mockPayment();
        $this->metaData->expects($this->once())->method('getMapper')->willReturn($this->paymentResource);
        $this->paymentResource->expects($this->once())->method('delete')->with($payment);
        $this->assertTrue($this->repository->delete($payment));
    }

    public function testGet()
    {
        $paymentId = 1;
        $payment = $this->mockPayment($paymentId);
        $payment->expects($this->any())->method('load')->with($paymentId)->willReturn($payment);
        $this->metaData->expects($this->once())->method('getNewInstance')->willReturn($payment);
        $this->assertSame($payment, $this->repository->get($paymentId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetException()
    {
        $this->repository->get(null);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetNoSuchEntity()
    {
        $paymentId = 1;
        $payment = $this->mockPayment(null);
        $payment->expects($this->any())->method('load')->with($paymentId)->willReturn($payment);
        $this->metaData->expects($this->once())->method('getNewInstance')->willReturn($payment);
        $this->assertSame($payment, $this->repository->get($paymentId));
    }

    public function testGetList()
    {
        $field = 'order_id';
        $value = 45;
        $this->getListMock($field, $value);
        $this->assertSame($this->collection, $this->repository->getList($this->searchCriteria));
    }

    /**
     * @param bool $id
     * @return mixed
     */
    protected function mockPayment($id = false)
    {
        $payment = $this->getMock(
            'Magento\Sales\Model\Order\Payment',
            [],
            [],
            '',
            false
        );

        if ($id !== false) {
            $payment->expects($this->once())->method('getId')->willReturn($id);
        }

        return $payment;
    }

    /**
     * @param $field
     * @param $value
     */
    protected function getListMock($field, $value)
    {
        $currentPage = 1;
        $pageSize = 10;
        $this->searchResultFactory->expects($this->atLeastOnce())->method('create')->willReturn($this->collection);
        $this->searchCriteria->expects($this->once())->method('getFilterGroups')->willReturn([$this->filterGroup]);
        $this->filterGroup->expects($this->once())->method('getFilters')->willReturn([$this->filter]);
        $this->filter->expects($this->once())->method('getConditionType')->willReturn(null);
        $this->filter->expects($this->once())->method('getField')->willReturn($field);
        $this->filter->expects($this->once())->method('getValue')->willReturn($value);
        $this->collection->expects($this->once())->method('addFieldToFilter')->with($field, ['eq' => $value]);
        $this->searchCriteria->expects($this->once())->method('getCurrentPage')->willReturn($currentPage);
        $this->searchCriteria->expects($this->once())->method('getPageSize')->willReturn($pageSize);
        $this->collection->expects($this->once())->method('setCurPage')->with();
        $this->collection->expects($this->once())->method('setPageSize')->with();
    }
}
