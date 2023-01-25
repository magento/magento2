<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Payment;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderPaymentSearchResultInterfaceFactory;
use Magento\Sales\Model\Order\Payment\Repository;
use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Sales\Model\ResourceModel\Order\Payment;
use Magento\Sales\Model\ResourceModel\Order\Payment\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RepositoryTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $searchResultFactory;

    /**
     * @var MockObject
     */
    protected $searchCriteria;

    /**
     * @var MockObject
     */
    protected $collection;

    /**
     * @var MockObject
     */
    protected $metaData;

    /**
     * @var MockObject
     */
    protected $filterGroup;

    /**
     * @var MockObject
     */
    protected $filter;

    /**
     * @var MockObject
     */
    protected $paymentResource;

    /**
     * @var MockObject
     */
    private $collectionProcessor;

    /**
     * @var Repository
     */
    protected $repository;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->metaData = $this->createMock(Metadata::class);
        $this->searchResultFactory = $this->createPartialMock(
            OrderPaymentSearchResultInterfaceFactory::class,
            ['create']
        );
        $this->searchCriteria = $this->createMock(SearchCriteria::class);
        $this->collection = $this->createMock(Collection::class);
        $this->paymentResource = $this->createMock(Payment::class);
        $this->filterGroup = $this->createMock(FilterGroup::class);
        $this->filter = $this->createMock(Filter::class);
        $this->collectionProcessor = $this->createMock(
            CollectionProcessorInterface::class
        );
        $this->repository = $objectManager->getObject(
            Repository::class,
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
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function testGetException()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->repository->get(null);
    }

    /**
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function testGetNoSuchEntity()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
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
     * @return MockObject
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
