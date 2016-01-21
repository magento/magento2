<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Model\Report;

use Magento\BraintreeTwo\Gateway\Config\Config;
use Magento\BraintreeTwo\Model\Adapter\BraintreeSearchAdapter;
use Magento\BraintreeTwo\Model\Report\ConditionAppliers\ApplierInterface;
use Magento\BraintreeTwo\Model\Report\ConditionAppliers\AppliersPool;
use Magento\BraintreeTwo\Model\Report\FilterMapper;
use Magento\BraintreeTwo\Model\Ui\ConfigProvider;

/**
 * Class FilterMapperTest
 *
 * Test for class \Magento\BraintreeTwo\Model\Report\FilterMapper
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

    protected function setUp()
    {
        $this->braintreeSearchAdapterMock = $this->getMockBuilder(BraintreeSearchAdapter::class)
            ->setMethods([
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
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->braintreeSearchAdapterMock->expects($this->once())->method('id')
            ->willReturn(new BraintreeSearchNodeStub());
        $this->braintreeSearchAdapterMock->expects($this->once())->method('merchantAccountId')
            ->willReturn(new BraintreeSearchNodeStub());
        $this->braintreeSearchAdapterMock->expects($this->once())->method('orderId')
            ->willReturn(new BraintreeSearchNodeStub());
        $this->braintreeSearchAdapterMock->expects($this->once())->method('paypalPaymentId')
            ->willReturn(new BraintreeSearchNodeStub());
        $this->braintreeSearchAdapterMock->expects($this->once())->method('createdUsing')
            ->willReturn(new BraintreeSearchNodeStub());
        $this->braintreeSearchAdapterMock->expects($this->once())->method('type')
            ->willReturn(new BraintreeSearchNodeStub());
        $this->braintreeSearchAdapterMock->expects($this->once())->method('createdAt')
            ->willReturn(new BraintreeSearchNodeStub());
        $this->braintreeSearchAdapterMock->expects($this->once())->method('amount')
            ->willReturn(new BraintreeSearchNodeStub());
        $this->braintreeSearchAdapterMock->expects($this->once())->method('status')
            ->willReturn(new BraintreeSearchNodeStub());
        $this->braintreeSearchAdapterMock->expects($this->once())->method('settlementBatchId')
            ->willReturn(new BraintreeSearchNodeStub());
        $this->braintreeSearchAdapterMock->expects($this->once())->method('paymentInstrumentType')
            ->willReturn(new BraintreeSearchNodeStub());

        $this->appliersPoolMock = $this->getMockBuilder(AppliersPool::class)
            ->setMethods(['getApplier'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->applierMock = $this->getMockBuilder(ApplierInterface::class)
            ->setMethods(['apply'])
            ->disableOriginalConstructor()
            ->getMock();
    }

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

class BraintreeSearchNodeStub
{
}

