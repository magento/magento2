<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Model\Report;

use Magento\Braintree\Model\Adapter\BraintreeSearchAdapter;
use Magento\Braintree\Model\Report\ConditionAppliers\ApplierInterface;
use Magento\Braintree\Model\Report\ConditionAppliers\AppliersPool;
use Magento\Braintree\Model\Report\FilterMapper;

/**
 * Class FilterMapperTest
 *
 * Test for class \Magento\Braintree\Model\Report\FilterMapper
 */
class FilterMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BraintreeSearchAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $braintreeSearchAdapterMock;

    /**
     * @var AppliersPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appliersPoolMock;

    /**
     * @var ApplierInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $applierMock;

    /**
     * Setup
     */
    protected function setUp()
    {
        $methods = [
            'id',
            'merchantAccountId',
            'orderId',
            'paypalPaymentId',
            'createdUsing',
            'type',
            'createdAt',
            'amount',
            'status',
            'settlementBatchId',
            'paymentInstrumentType',
        ];
        $this->braintreeSearchAdapterMock = $this->getMockBuilder(BraintreeSearchAdapter::class)
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMock();
        foreach ($methods as $method) {
            $this->braintreeSearchAdapterMock->expects($this->once())->method($method)
                ->willReturn(new BraintreeSearchNodeStub());
        }

        $this->appliersPoolMock = $this->getMockBuilder(AppliersPool::class)
            ->setMethods(['getApplier'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->applierMock = $this->getMockBuilder(ApplierInterface::class)
            ->setMethods(['apply'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Positive test
     */
    public function testGetFilterPositiveApply()
    {
        $this->applierMock->expects($this->exactly(3))
            ->method('apply')
            ->willReturn(true);

        $this->appliersPoolMock->expects($this->exactly(3))
            ->method('getApplier')
            ->willReturn($this->applierMock);

        $mapper = new FilterMapper($this->appliersPoolMock, $this->braintreeSearchAdapterMock);

        $result = $mapper->getFilter('id', ['eq' => 'value']);
        $this->assertInstanceOf(BraintreeSearchNodeStub::class, $result);

        $result = $mapper->getFilter('orderId', ['eq' => 'value']);
        $this->assertInstanceOf(BraintreeSearchNodeStub::class, $result);

        $result = $mapper->getFilter('amount', ['eq' => 'value']);
        $this->assertInstanceOf(BraintreeSearchNodeStub::class, $result);
    }

    /**
     * Negative test
     */
    public function testGetFilterNegativeApply()
    {
        $this->applierMock->expects($this->never())
            ->method('apply')
            ->willReturn(true);

        $this->appliersPoolMock->expects($this->once())
            ->method('getApplier')
            ->willReturn($this->applierMock);

        $mapper = new FilterMapper($this->appliersPoolMock, $this->braintreeSearchAdapterMock);
        $result = $mapper->getFilter('orderId', []);
        $this->assertEquals(null, $result);
    }
}
