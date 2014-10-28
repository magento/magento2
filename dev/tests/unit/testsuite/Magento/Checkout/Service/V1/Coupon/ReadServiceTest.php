<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            '\Magento\Checkout\Service\V1\Coupon\ReadService',
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
            ->method('get')
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
