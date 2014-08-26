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

namespace Magento\Checkout\Service\V1\PaymentMethod;

class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReadService
     */
    protected $service;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteLoaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodConverterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMethodConverterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $methodListMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->quoteLoaderMock = $this->getMock('\Magento\Checkout\Service\V1\QuoteLoader', [], [], '', false);
        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->quoteMethodConverterMock = $this->getMock(
            '\Magento\Checkout\Service\V1\Data\Cart\PaymentMethod\Converter', [], [], '', false
        );
        $this->paymentMethodConverterMock = $this->getMock(
            '\Magento\Checkout\Service\V1\Data\PaymentMethod\Converter', [], [], '', false
        );
        $this->methodListMock = $this->getMock('\Magento\Payment\Model\MethodList', [], [], '', false);

        $this->service = $this->objectManager->getObject(
            '\Magento\Checkout\Service\V1\PaymentMethod\ReadService',
            [
                'quoteLoader' => $this->quoteLoaderMock,
                'storeManager' => $this->storeManagerMock,
                'quoteMethodConverter' => $this->quoteMethodConverterMock,
                'paymentMethodConverter' => $this->paymentMethodConverterMock,
                'methodList' => $this->methodListMock,
            ]
        );
    }

    public function testGetPaymentIfPaymentMethodNotSet()
    {
        $cartId = 11;
        $storeId = 12;
        $storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($storeMock));

        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $paymentMock = $this->getMock('\Magento\Sales\Model\Quote\Payment', [], [], '', false);
        $quoteMock->expects($this->once())->method('getPayment')->will($this->returnValue($paymentMock));
        $paymentMock->expects($this->once())->method('getId')->will($this->returnValue(null));

        $this->quoteLoaderMock->expects($this->once())
            ->method('load')
            ->with($cartId, $storeId)
            ->will($this->returnValue($quoteMock));

        $this->assertNull($this->service->getPayment($cartId));
    }

    public function testGetPaymentSuccess()
    {
        $cartId = 11;
        $storeId = 12;
        $storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($storeMock));

        $paymentMock = $this->getMock('\Magento\Sales\Model\Quote\Payment', [], [], '', false);
        $paymentMock->expects($this->once())->method('getId')->will($this->returnValue(1));

        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $quoteMock->expects($this->once())->method('getPayment')->will($this->returnValue($paymentMock));

        $this->quoteLoaderMock->expects($this->once())
            ->method('load')
            ->with($cartId, $storeId)
            ->will($this->returnValue($quoteMock));

        $paymentMethodMock = $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\PaymentMethod', [], [], '', false);

        $this->quoteMethodConverterMock->expects($this->once())
            ->method('toDataObject')
            ->with($paymentMock)
            ->will($this->returnValue($paymentMethodMock));

        $this->assertEquals($paymentMethodMock, $this->service->getPayment($cartId));
    }

    public function testGetList()
    {
        $cartId = 10;
        $storeId = 12;
        $storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $storeMock->expects($this->once())->method('getId')->will($this->returnValue($storeId));
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($storeMock));

        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);

        $this->quoteLoaderMock->expects($this->once())
            ->method('load')
            ->with($cartId, $storeId)
            ->will($this->returnValue($quoteMock));

        $methodList = [
            $this->getMock('\Magento\Payment\Model\MethodInterface'),
            $this->getMock('\Magento\Payment\Model\MethodInterface')
        ];

        $this->methodListMock->expects($this->once())
            ->method('getAvailableMethods')
            ->with($quoteMock)
            ->will($this->returnValue($methodList));

        $paymentMethodMock = $this->getMock('\Magento\Checkout\Service\V1\Data\PaymentMethod', [], [], '', false);

        $this->paymentMethodConverterMock->expects($this->atLeastOnce())
            ->method('toDataObject')
            ->will($this->returnValue($paymentMethodMock));

        $expectedResult = [
            $this->getMock('\Magento\Checkout\Service\V1\Data\PaymentMethod', [], [], '', false),
            $this->getMock('\Magento\Checkout\Service\V1\Data\PaymentMethod', [], [], '', false)
        ];

        $this->assertEquals($expectedResult, $this->service->getList($cartId));
    }
}
 