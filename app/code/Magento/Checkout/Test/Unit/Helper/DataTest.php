<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Checkout\Test\Unit\Helper;

use Magento\Checkout\Helper\Data;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var Data
     */
    private $_helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_transportBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_translator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private  $_checkoutSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private  $_scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_eventManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = \Magento\Checkout\Helper\Data::class;
        $arguments = $objectManagerHelper->getConstructArguments($className);
        /** @var \Magento\Framework\App\Helper\Context $context */
        $context = $arguments['context'];
        $this->_translator = $arguments['inlineTranslation'];
        $this->_eventManager = $context->getEventManager();
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        'checkout/payment_failed/template',
                        ScopeInterface::SCOPE_STORE,
                        8,
                        'fixture_email_template_payment_failed',
                    ],
                    [
                        'checkout/payment_failed/receiver',
                        ScopeInterface::SCOPE_STORE,
                        8,
                        'sysadmin',
                    ],
                    [
                        'trans_email/ident_sysadmin/email',
                        ScopeInterface::SCOPE_STORE,
                        8,
                        'sysadmin@example.com',
                    ],
                    [
                        'trans_email/ident_sysadmin/name',
                        ScopeInterface::SCOPE_STORE,
                        8,
                        'System Administrator',
                    ],
                    [
                        'checkout/payment_failed/identity',
                        ScopeInterface::SCOPE_STORE,
                        8,
                        'noreply@example.com',
                    ],
                    [
                        'carriers/ground/title',
                        ScopeInterface::SCOPE_STORE,
                        null,
                        'Ground Shipping',
                    ],
                    [
                        'payment/fixture-payment-method/title',
                        ScopeInterface::SCOPE_STORE,
                        null,
                        'Check Money Order',
                    ],
                    [
                        'checkout/options/onepage_checkout_enabled',
                        ScopeInterface::SCOPE_STORE,
                        null,
                        'One Page Checkout',
                    ],
                ]
            );

        $this->_checkoutSession = $arguments['checkoutSession'];
        $arguments['localeDate']->expects($this->any())
            ->method('formatDateTime')
            ->willReturn('Oct 02, 2013');

        $this->_transportBuilder = $arguments['transportBuilder'];

        $this->priceCurrency = $arguments['priceCurrency'];

        $this->_helper = $objectManagerHelper->getObject($className, $arguments);
    }

    /**
     * @return void
     */
    public function testSendPaymentFailedEmail()
    {
        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->any())->method('getId')->willReturn(1);

        $this->assertSame($this->_helper, $this->_helper->sendPaymentFailedEmail($quoteMock, 'test message'));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function testGetCheckout()
    {
        $this->assertEquals($this->_checkoutSession, $this->_helper->getCheckout());
    }

    public function testGetQuote()
    {
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->_checkoutSession->expects($this->once())->method('getQuote')->will($this->returnValue($quoteMock));
        $this->assertEquals($quoteMock, $this->_helper->getQuote());
    }

    public function testFormatPrice()
    {
        $price = 5.5;
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $storeMock = $this->createPartialMock(\Magento\Store\Model\Store::class, ['formatPrice', '__wakeup']);
        $this->_checkoutSession->expects($this->once())->method('getQuote')->will($this->returnValue($quoteMock));
        $quoteMock->expects($this->once())->method('getStore')->will($this->returnValue($storeMock));
        $this->priceCurrency->expects($this->once())->method('format')->will($this->returnValue('5.5'));
        $this->assertEquals('5.5', $this->_helper->formatPrice($price));
    }

    public function testConvertPrice()
    {
        $price = 5.5;
        $this->priceCurrency->expects($this->once())->method('convertAndFormat')->willReturn($price);
        $this->assertEquals(5.5, $this->_helper->convertPrice($price));
    }

    public function testCanOnepageCheckout()
    {
        $this->_scopeConfig->expects($this->once())->method('getValue')->with(
            'checkout/options/onepage_checkout_enabled',
            'store'
        )->will($this->returnValue(true));
        $this->assertTrue($this->_helper->canOnepageCheckout());
    }

    public function testIsContextCheckout()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $context = $objectManagerHelper->getObject(
            \Magento\Framework\App\Helper\Context::class
        );
        $helper = $objectManagerHelper->getObject(
            \Magento\Checkout\Helper\Data::class,
            ['context' => $context]
        );
        $context->getRequest()->expects($this->once())->method('getParam')->with('context')->will(
            $this->returnValue('checkout')
        );
        $this->assertTrue($helper->isContextCheckout());
    }

    public function testIsCustomerMustBeLogged()
    {
        $this->_scopeConfig->expects($this->once())->method('isSetFlag')->with(
            'checkout/options/customer_must_be_logged',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )->will($this->returnValue(true));
        $this->assertTrue($this->_helper->isCustomerMustBeLogged());
    }

    public function testGetPriceInclTax()
    {
        $itemMock = $this->createPartialMock(\Magento\Framework\DataObject::class, ['getPriceInclTax']);
        $itemMock->expects($this->exactly(2))->method('getPriceInclTax')->will($this->returnValue(5.5));
        $this->assertEquals(5.5, $this->_helper->getPriceInclTax($itemMock));
    }

    public function testGetPriceInclTaxWithoutTax()
    {
        $qty = 1;
        $taxAmount = 1;
        $discountTaxCompensation = 1;
        $rowTotal = 15;
        $roundPrice = 17;
        $expected = 17;
        $storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $objectManagerHelper = new ObjectManager($this);
        $helper = $objectManagerHelper->getObject(
            \Magento\Checkout\Helper\Data::class,
            [
                'storeManager' => $storeManager,
                'priceCurrency' => $this->priceCurrency,
            ]
        );
        $itemMock = $this->createPartialMock(
            \Magento\Framework\DataObject::class,
            ['getPriceInclTax', 'getQty', 'getTaxAmount', 'getDiscountTaxCompensation', 'getRowTotal', 'getQtyOrdered']
        );
        $itemMock->expects($this->once())->method('getPriceInclTax')->will($this->returnValue(false));
        $itemMock->expects($this->exactly(2))->method('getQty')->will($this->returnValue($qty));
        $itemMock->expects($this->never())->method('getQtyOrdered');
        $itemMock->expects($this->once())->method('getTaxAmount')->will($this->returnValue($taxAmount));
        $itemMock->expects($this->once())
            ->method('getDiscountTaxCompensation')->will($this->returnValue($discountTaxCompensation));
        $itemMock->expects($this->once())->method('getRowTotal')->will($this->returnValue($rowTotal));
        $this->priceCurrency->expects($this->once())->method('round')->with($roundPrice)->willReturn($roundPrice);
        $this->assertEquals($expected, $helper->getPriceInclTax($itemMock));
    }

    public function testGetSubtotalInclTax()
    {
        $rowTotalInclTax = 5.5;
        $expected = 5.5;
        $itemMock = $this->createPartialMock(\Magento\Framework\DataObject::class, ['getRowTotalInclTax']);
        $itemMock->expects($this->exactly(2))->method('getRowTotalInclTax')->will($this->returnValue($rowTotalInclTax));
        $this->assertEquals($expected, $this->_helper->getSubtotalInclTax($itemMock));
    }

    public function testGetSubtotalInclTaxNegative()
    {
        $taxAmount = 1;
        $discountTaxCompensation = 1;
        $rowTotal = 15;
        $expected = 17;
        $itemMock = $this->createPartialMock(
            \Magento\Framework\DataObject::class,
            ['getRowTotalInclTax', 'getTaxAmount', 'getDiscountTaxCompensation', 'getRowTotal']
        );
        $itemMock->expects($this->once())->method('getRowTotalInclTax')->will($this->returnValue(false));
        $itemMock->expects($this->once())->method('getTaxAmount')->will($this->returnValue($taxAmount));
        $itemMock->expects($this->once())
            ->method('getDiscountTaxCompensation')->will($this->returnValue($discountTaxCompensation));
        $itemMock->expects($this->once())->method('getRowTotal')->will($this->returnValue($rowTotal));
        $this->assertEquals($expected, $this->_helper->getSubtotalInclTax($itemMock));
    }

    public function testGetBasePriceInclTaxWithoutQty()
    {
        $storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $objectManagerHelper = new ObjectManager($this);
        $helper = $objectManagerHelper->getObject(
            \Magento\Checkout\Helper\Data::class,
            [
                'storeManager' => $storeManager,
                'priceCurrency' => $this->priceCurrency,
            ]
        );
        $itemMock = $this->createPartialMock(\Magento\Framework\DataObject::class, ['getQty']);
        $itemMock->expects($this->once())->method('getQty');
        $this->priceCurrency->expects($this->once())->method('round');
        $helper->getPriceInclTax($itemMock);
    }

    public function testGetBasePriceInclTax()
    {
        $storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $helper = $objectManagerHelper->getObject(
            \Magento\Checkout\Helper\Data::class,
            [
                'storeManager' => $storeManager,
                'priceCurrency' => $this->priceCurrency,
            ]
        );
        $itemMock = $this->createPartialMock(\Magento\Framework\DataObject::class, ['getQty', 'getQtyOrdered']);
        $itemMock->expects($this->once())->method('getQty')->will($this->returnValue(false));
        $itemMock->expects($this->exactly(2))->method('getQtyOrdered')->will($this->returnValue(5.5));
        $this->priceCurrency->expects($this->once())->method('round');
        $helper->getBasePriceInclTax($itemMock);
    }

    public function testGetBaseSubtotalInclTax()
    {
        $itemMock = $this->createPartialMock(
            \Magento\Framework\DataObject::class,
            ['getBaseTaxAmount', 'getBaseDiscountTaxCompensation', 'getBaseRowTotal']
        );
        $itemMock->expects($this->once())->method('getBaseTaxAmount');
        $itemMock->expects($this->once())->method('getBaseDiscountTaxCompensation');
        $itemMock->expects($this->once())->method('getBaseRowTotal');
        $this->_helper->getBaseSubtotalInclTax($itemMock);
    }

    public function testIsAllowedGuestCheckoutWithoutStore()
    {
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $store = null;
        $quoteMock->expects($this->once())->method('getStoreId')->will($this->returnValue(1));
        $this->_scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->will($this->returnValue(true));
        $this->assertTrue($this->_helper->isAllowedGuestCheckout($quoteMock, $store));
    }
}
