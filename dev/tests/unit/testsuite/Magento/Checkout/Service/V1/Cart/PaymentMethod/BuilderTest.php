<?php
/**
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

namespace Magento\Checkout\Service\V1\Cart\PaymentMethod;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Service\V1\Data\Cart\PaymentMethod\Builder
     */
    protected $builder;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->builder = $this->objectManager->getObject(
            '\Magento\Checkout\Service\V1\Data\Cart\PaymentMethod\Builder'
        );
    }

    public function testBuildPaymentObject()
    {
        $paymentData = [
            'method' => 'checkmo',
            'payment_details' => 'paymentDetailsTest'
        ];

        $paymentMethodMock = $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\PaymentMethod', [], [], '', false);
        $paymentMethodMock->expects($this->once())->method('__toArray')->will($this->returnValue($paymentData));
        $paymentMethodMock->expects($this->once())
            ->method('getPaymentDetails')
            ->will($this->returnValue(serialize(['paymentDetailsTest'])));

        $paymentMock = $this->getMock('\Magento\Sales\Model\Quote\Payment', [], [], '', false);
        $paymentMock->expects($this->once())
            ->method('importData')
            ->with($this->contains('checkmo'))
            ->will($this->returnSelf());

        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $quoteMock->expects($this->once())->method('getPayment')->will($this->returnValue($paymentMock));

        $this->assertEquals($paymentMock, $this->builder->build($paymentMethodMock, $quoteMock));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The requested Payment Method is not available.
     */
    public function testBuildPaymentObjectThrowsExceptionIfPaymentMethodNotAvailable()
    {
        $paymentData = [
            'method' => 'notAvailableMethod',
            'payment_details' => 'paymentDetailsTest'
        ];

        $paymentMethodMock = $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\PaymentMethod', [], [], '', false);
        $paymentMethodMock->expects($this->once())->method('__toArray')->will($this->returnValue($paymentData));
        $paymentMethodMock->expects($this->once())
            ->method('getPaymentDetails')
            ->will($this->returnValue(['paymentDetailsTest']));

        $paymentMock = $this->getMock('\Magento\Sales\Model\Quote\Payment', [], [], '', false);
        $paymentMock->expects($this->once())
            ->method('importData')
            ->with($this->contains('notAvailableMethod'))
            ->will($this->throwException(
                new \Magento\Framework\Exception\LocalizedException('The requested Payment Method is not available.'))
            );

        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $quoteMock->expects($this->once())->method('getPayment')->will($this->returnValue($paymentMock));

        $this->assertEquals($paymentMock, $this->builder->build($paymentMethodMock, $quoteMock));
    }
}
