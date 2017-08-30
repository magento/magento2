<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Cart;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CartTotalManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartTotalMock;

    /**
     * @var \Magento\Quote\Api\CartTotalManagementInterface
     */
    protected $model;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->shippingMock = $this->createMock(\Magento\Quote\Model\ShippingMethodManagement::class);
        $this->paymentMock = $this->createMock(\Magento\Quote\Api\PaymentMethodManagementInterface::class);
        $this->cartTotalMock = $this->createMock(\Magento\Quote\Api\CartTotalRepositoryInterface::class);
        $this->model = $this->objectManager->getObject(
            \Magento\Quote\Model\Cart\CartTotalManagement::class,
            [
                'shippingMethodManagement' => $this->shippingMock,
                'paymentMethodManagement' => $this->paymentMock,
                'cartTotalsRepository' => $this->cartTotalMock,
            ]
        );
    }

    public function testCollectTotals()
    {
        $cartId = 123;
        $shippingCarrierCode = 'careful_carrier';
        $shippingMethodCode = 'drone_delivery';
        $total = 3322.31;
        $paymentDataMock = $this->createMock(\Magento\Quote\Api\Data\PaymentInterface::class);

        $this->shippingMock->expects($this->once())
            ->method('set')
            ->with($cartId, $shippingCarrierCode, $shippingMethodCode);
        $this->paymentMock->expects($this->once())->method('set')->with($cartId, $paymentDataMock);
        $this->cartTotalMock->expects($this->once())->method('get')->with($cartId)->willReturn($total);
        $this->assertEquals(
            $total,
            $this->model->collectTotals($cartId, $paymentDataMock, $shippingCarrierCode, $shippingMethodCode)
        );
    }

    /**
     * @dataProvider collectTotalsShippingData
     * @param $shippingCarrierCode
     * @param $shippingMethodCode
     */
    public function testCollectTotalsNoShipping($shippingCarrierCode, $shippingMethodCode)
    {
        $cartId = 123;
        $total = 3322.31;
        $paymentDataMock = $this->createMock(\Magento\Quote\Api\Data\PaymentInterface::class);

        $this->shippingMock->expects($this->never())
            ->method('set')
            ->with($cartId, $shippingCarrierCode, $shippingMethodCode);
        $this->paymentMock->expects($this->once())->method('set')->with($cartId, $paymentDataMock);
        $this->cartTotalMock->expects($this->once())->method('get')->with($cartId)->willReturn($total);
        $this->assertEquals(
            $total,
            $this->model->collectTotals($cartId, $paymentDataMock, $shippingCarrierCode, $shippingMethodCode)
        );
    }

    public function collectTotalsShippingData()
    {
        return [
            ['careful_carrier', null],
            [null, 'drone_delivery'],
            [null, null],
        ];
    }
}
