<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order\Payment;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RepositoryTest extends \PHPUnit\Framework\TestCase
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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Repository
     */
    protected $repository;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->metaData = $this->createMock(\Magento\Sales\Model\ResourceModel\Metadata::class);
        $this->searchResultFactory = $this->createPartialMock(
            \Magento\Sales\Api\Data\OrderPaymentSearchResultInterfaceFactory::class,
            ['create']
        );
        $this->searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->collection = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Payment\Collection::class);
        $this->paymentResource = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Payment::class);
        $this->filterGroup = $this->createMock(\Magento\Framework\Api\Search\FilterGroup::class);
        $this->filter = $this->createMock(\Magento\Framework\Api\Filter::class);
        $this->collectionProcessor = $this->createMock(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
        );
        $this->repository = $objectManager->getObject(
            \Magento\Sales\Model\Order\Payment\Repository::class,
            [
                'searchResultFactory' => $this->searchResultFactory,
                'metaData' => $this->metaData,
                'collectionProcessor' => $this->collectionProcessor,
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
        $this->searchResultFactory->expects($this->atLeastOnce())->method('create')->willReturn($this->collection);
        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($this->searchCriteria, $this->collection);
        $this->assertSame($this->collection, $this->repository->getList($this->searchCriteria));
    }

    /**
     * @param bool $id
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockPayment($id = false)
    {
        $payment = $this->createMock(\Magento\Sales\Model\Order\Payment::class);

        if ($id !== false) {
            $payment->expects($this->once())->method('getId')->willReturn($id);
        }

        return $payment;
    }
}
