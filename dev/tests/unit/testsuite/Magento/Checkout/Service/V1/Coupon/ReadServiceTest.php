<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Service\V1\Coupon;

use Magento\Checkout\Service\V1\Data\Cart\Coupon as Coupon;

class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReadService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $couponBuilderMock;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->quoteRepositoryMock = $this->getMock('\Magento\Sales\Model\QuoteRepository', [], [], '', false);
        $this->couponBuilderMock = $this->getMock(
            '\Magento\Checkout\Service\V1\Data\Cart\CouponBuilder', [], [], '', false
        );
        $this->service = $objectManager->getObject(
            'Magento\Checkout\Service\V1\Coupon\ReadService',
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'couponBuilder' => $this->couponBuilderMock,
            ]
        );
    }

    public function testGetCoupon()
    {
        $cartId = 11;
        $couponCode = 'test_coupon_code';

        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', ['getCouponCode', '__wakeup'], [], '', false);
        $quoteMock->expects($this->any())->method('getCouponCode')->will($this->returnValue($couponCode));

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->will($this->returnValue($quoteMock));

        $data = [Coupon::COUPON_CODE => $couponCode];

        $this->couponBuilderMock->expects($this->once())
            ->method('populateWithArray')
            ->with($data)
            ->will($this->returnSelf());
        $this->couponBuilderMock->expects($this->once())->method('create')->will($this->returnValue('couponCode'));

        $this->assertEquals('couponCode', $this->service->get($cartId));
    }
}
