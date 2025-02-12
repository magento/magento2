<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Cart;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\CartTotalManagementInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\Cart\CartTotalManagement;
use Magento\Quote\Model\ShippingMethodManagement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CartTotalManagementTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var MockObject
     */
    protected $shippingMock;

    /**
     * @var MockObject
     */
    protected $paymentMock;

    /**
     * @var MockObject
     */
    protected $cartTotalMock;

    /**
     * @var CartTotalManagementInterface
     */
    protected $model;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->shippingMock = $this->createMock(ShippingMethodManagement::class);
        $this->paymentMock = $this->getMockForAbstractClass(PaymentMethodManagementInterface::class);
        $this->cartTotalMock = $this->getMockForAbstractClass(CartTotalRepositoryInterface::class);
        $this->model = $this->objectManager->getObject(
            CartTotalManagement::class,
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
        $paymentDataMock = $this->getMockForAbstractClass(PaymentInterface::class);

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
        $paymentDataMock = $this->getMockForAbstractClass(PaymentInterface::class);

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

    /**
     * @return array
     */
    public static function collectTotalsShippingData()
    {
        return [
            ['careful_carrier', null],
            [null, 'drone_delivery'],
            [null, null],
        ];
    }
}
