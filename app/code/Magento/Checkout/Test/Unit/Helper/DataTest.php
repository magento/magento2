<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Checkout\Test\Unit\Helper;

use \Magento\Checkout\Helper\Data;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;

class DataTest extends \PHPUnit_Framework_TestCase
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
    protected  $_checkoutSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected  $_scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected  $_collectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManager;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Magento\Checkout\Helper\Data';
        $arguments = $objectManagerHelper->getConstructArguments($className);
        /** @var \Magento\Framework\App\Helper\Context $context */
        $context = $arguments['context'];
        $this->_translator = $arguments['inlineTranslation'];
        $this->_eventManager = $context->getEventManager();
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_scopeConfig->expects($this->any())
            ->method('getValue')
            ->will(
                $this->returnValueMap(
                    [
                        [
                            'checkout/payment_failed/template',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            8,
                            'fixture_email_template_payment_failed'
                        ],
                        [
                            'checkout/payment_failed/receiver',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            8,
                            'sysadmin'
                        ],
                        [
                            'trans_email/ident_sysadmin/email',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            8,
                            'sysadmin@example.com'
                        ],
                        [
                            'trans_email/ident_sysadmin/name',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            8,
                            'System Administrator'
                        ],
                        [
                            'checkout/payment_failed/identity',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            8,
                            'noreply@example.com'
                        ],
                        [
                            'carriers/ground/title',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            null,
                            'Ground Shipping'
                        ],
                        [
                            'payment/fixture-payment-method/title',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            null,
                            'Check Money Order'
                        ],
                        [
                            'checkout/options/onepage_checkout_enabled',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            null,
                            'One Page Checkout'
                        ]
                    ]
                )
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSendPaymentFailedEmail()
    {
        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();

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
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->_checkoutSession->expects($this->once())->method('getQuote')->will($this->returnValue($quoteMock));
        $this->assertEquals($quoteMock, $this->_helper->getQuote());
    }

    public function testFormatPrice()
    {
        $price = 5.5;
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            ['formatPrice', '__wakeup'],
            [],
            '',
            false
        );
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
            'Magento\Framework\App\Helper\Context'
        );
        $helper = $objectManagerHelper->getObject(
            'Magento\Checkout\Helper\Data',
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
        $itemMock = $this->getMock('Magento\Framework\DataObject', ['getPriceInclTax'], [], '', false);
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
        $storeManager = $this->getMock('\Magento\Store\Model\StoreManagerInterface', [], [], '', false);
        $objectManagerHelper = new ObjectManager($this);
        $helper = $objectManagerHelper->getObject(
            '\Magento\Checkout\Helper\Data',
            [
                'storeManager' => $storeManager,
                'priceCurrency' => $this->priceCurrency,
            ]
        );
        $itemMock = $this->getMock(
            'Magento\Framework\DataObject',
            ['getPriceInclTax', 'getQty', 'getTaxAmount', 'getDiscountTaxCompensation', 'getRowTotal'],
            [],
            '',
            false
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
        $itemMock = $this->getMock('Magento\Framework\DataObject', ['getRowTotalInclTax'], [], '', false);
        $itemMock->expects($this->exactly(2))->method('getRowTotalInclTax')->will($this->returnValue($rowTotalInclTax));
        $this->assertEquals($expected, $this->_helper->getSubtotalInclTax($itemMock));
    }

    public function testGetSubtotalInclTaxNegative()
    {
        $taxAmount = 1;
        $discountTaxCompensation = 1;
        $rowTotal = 15;
        $expected = 17;
        $itemMock = $this->getMock(
            'Magento\Framework\DataObject',
            ['getRowTotalInclTax', 'getTaxAmount', 'getDiscountTaxCompensation', 'getRowTotal'],
            [],
            '',
            false
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
        $storeManager = $this->getMock('\Magento\Store\Model\StoreManagerInterface', [], [], '', false);
        $objectManagerHelper = new ObjectManager($this);
        $helper = $objectManagerHelper->getObject(
            '\Magento\Checkout\Helper\Data',
            [
                'storeManager' => $storeManager,
                'priceCurrency' => $this->priceCurrency,
            ]
        );
        $itemMock = $this->getMock('Magento\Framework\DataObject', ['getQty'], [], '', false);
        $itemMock->expects($this->once())->method('getQty');
        $this->priceCurrency->expects($this->once())->method('round');
        $helper->getPriceInclTax($itemMock);
    }

    public function testGetBasePriceInclTax()
    {
        $storeManager = $this->getMock('\Magento\Store\Model\StoreManagerInterface', [], [], '', false);
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $helper = $objectManagerHelper->getObject(
            '\Magento\Checkout\Helper\Data',
            [
                'storeManager' => $storeManager,
                'priceCurrency' => $this->priceCurrency,
            ]
        );
        $itemMock = $this->getMock('Magento\Framework\DataObject', ['getQty', 'getQtyOrdered'], [], '', false);
        $itemMock->expects($this->once())->method('getQty')->will($this->returnValue(false));
        $itemMock->expects($this->exactly(2))->method('getQtyOrdered')->will($this->returnValue(5.5));
        $this->priceCurrency->expects($this->once())->method('round');
        $helper->getBasePriceInclTax($itemMock);
    }

    public function testGetBaseSubtotalInclTax()
    {
        $itemMock = $this->getMock(
            'Magento\Framework\DataObject',
            ['getBaseTaxAmount', 'getBaseDiscountTaxCompensation', 'getBaseRowTotal'],
            [],
            '',
            false
        );
        $itemMock->expects($this->once())->method('getBaseTaxAmount');
        $itemMock->expects($this->once())->method('getBaseDiscountTaxCompensation');
        $itemMock->expects($this->once())->method('getBaseRowTotal');
        $this->_helper->getBaseSubtotalInclTax($itemMock);
    }

    public function testIsAllowedGuestCheckoutWithoutStore()
    {
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $store = null;
        $quoteMock->expects($this->once())->method('getStoreId')->will($this->returnValue(1));
        $this->_scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->will($this->returnValue(true));
        $this->assertTrue($this->_helper->isAllowedGuestCheckout($quoteMock, $store));
    }
}
