<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Unit\Helper\Shortcut;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class CheckoutValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Paypal\Helper\Shortcut\CheckoutValidator */
    protected $checkoutValidator;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $sessionMock;

    /** @var \Magento\Paypal\Helper\Shortcut\Validator|\PHPUnit_Framework_MockObject_MockObject */
    protected $paypalShortcutHelperMock;

    /** @var \Magento\Payment\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $paymentHelperMock;

    protected function setUp()
    {
        $this->sessionMock = $this->getMock(\Magento\Checkout\Model\Session::class, [], [], '', false);
        $this->paypalShortcutHelperMock = $this->getMock(
            \Magento\Paypal\Helper\Shortcut\Validator::class,
            [],
            [],
            '',
            false
        );
        $this->paymentHelperMock = $this->getMock(\Magento\Payment\Helper\Data::class, [], [], '', false);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->checkoutValidator = $this->objectManagerHelper->getObject(
            \Magento\Paypal\Helper\Shortcut\CheckoutValidator::class,
            [
                'checkoutSession' => $this->sessionMock,
                'shortcutValidator' => $this->paypalShortcutHelperMock,
                'paymentData' => $this->paymentHelperMock
            ]
        );
    }

    public function testValidate()
    {
        $code = 'code';
        $isInCatalog = true;
        $methodInstanceMock = $this->getMockBuilder(\Magento\Payment\Model\Method\AbstractMethod::class)
            ->disableOriginalConstructor()->setMethods([])->getMock();

        $this->paypalShortcutHelperMock->expects($this->once())->method('isContextAvailable')
            ->with($code, $isInCatalog)->will($this->returnValue(true));
        $this->paypalShortcutHelperMock->expects($this->once())->method('isPriceOrSetAvailable')
            ->with($isInCatalog)->will($this->returnValue(true));
        $this->paymentHelperMock->expects($this->once())->method('getMethodInstance')->with($code)
            ->will($this->returnValue($methodInstanceMock));
        $methodInstanceMock->expects($this->once())->method('isAvailable')->with(null)
            ->will($this->returnValue(true));

        $this->assertTrue($this->checkoutValidator->validate($code, $isInCatalog));
    }

    public function testIsMethodQuoteAvailableNoQuoteMethodNotAvailableFalse()
    {
        $quote = null;
        $isInCatalog = true;
        $paymentCode = 'code';
        $methodInstanceMock = $this->getMockBuilder(\Magento\Payment\Model\Method\AbstractMethod::class)
            ->disableOriginalConstructor()->setMethods([])->getMock();

        $this->paymentHelperMock->expects($this->once())->method('getMethodInstance')->with($paymentCode)
            ->will($this->returnValue($methodInstanceMock));
        $methodInstanceMock->expects($this->once())->method('isAvailable')->with($quote)
            ->will($this->returnValue(false));

        $this->assertFalse($this->checkoutValidator->isMethodQuoteAvailable($paymentCode, $isInCatalog));
    }

    /**
     * @dataProvider methodAvailabilityDataProvider
     * @param bool $availability
     */
    public function testIsMethodQuoteAvailableWithQuoteMethodNotAvailable($availability)
    {
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->disableOriginalConstructor()->setMethods([])
            ->getMock();
        $isInCatalog = false;
        $paymentCode = 'code';
        $methodInstanceMock = $this->getMockBuilder(\Magento\Payment\Model\Method\AbstractMethod::class)
            ->disableOriginalConstructor()->setMethods([])->getMock();

        $this->sessionMock->expects($this->once())->method('getQuote')->will($this->returnValue($quote));
        $this->paymentHelperMock->expects($this->once())->method('getMethodInstance')->with($paymentCode)
            ->will($this->returnValue($methodInstanceMock));
        $methodInstanceMock->expects($this->once())->method('isAvailable')->with($quote)
            ->will($this->returnValue($availability));

        $this->assertEquals(
            $availability,
            $this->checkoutValidator->isMethodQuoteAvailable($paymentCode, $isInCatalog)
        );
    }

    /**
     * @return array
     */
    public function methodAvailabilityDataProvider()
    {
        return [[true], [false]];
    }

    public function testIsQuoteSummaryValidNoQuote()
    {
        $isInCatalog = true;
        $this->assertTrue($this->checkoutValidator->isQuoteSummaryValid($isInCatalog));
    }

    public function testIsQuoteSummaryValidMinimumAmountFalse()
    {
        $isInCatalog = false;
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->disableOriginalConstructor()->setMethods([])
            ->getMock();

        $this->sessionMock->expects($this->once())->method('getQuote')->will($this->returnValue($quote));
        $quote->expects($this->once())->method('validateMinimumAmount')->will($this->returnValue(false));

        $this->assertFalse($this->checkoutValidator->isQuoteSummaryValid($isInCatalog));
    }

    public function testIsQuoteSummaryValidGrandTotalFalse()
    {
        $isInCatalog = false;
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->disableOriginalConstructor()
            ->setMethods(['getGrandTotal', 'validateMinimumAmount', '__wakeup'])
            ->getMock();

        $this->sessionMock->expects($this->once())->method('getQuote')->will($this->returnValue($quote));
        $quote->expects($this->once())->method('validateMinimumAmount')->will($this->returnValue(true));
        $quote->expects($this->once())->method('getGrandTotal')->will($this->returnValue(0));

        $this->assertFalse($this->checkoutValidator->isQuoteSummaryValid($isInCatalog));
    }

    public function testIsQuoteSummaryValidTrue()
    {
        $isInCatalog = false;
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->disableOriginalConstructor()
            ->setMethods(['getGrandTotal', 'validateMinimumAmount', '__wakeup'])
            ->getMock();

        $this->sessionMock->expects($this->once())->method('getQuote')->will($this->returnValue($quote));
        $quote->expects($this->once())->method('validateMinimumAmount')->will($this->returnValue(true));
        $quote->expects($this->once())->method('getGrandTotal')->will($this->returnValue(1));

        $this->assertTrue($this->checkoutValidator->isQuoteSummaryValid($isInCatalog));
    }
}
