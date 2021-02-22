<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $transportBuilder;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $translator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $checkoutSession;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfig;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $eventManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = \Magento\Checkout\Helper\Data::class;
        $arguments = $objectManagerHelper->getConstructArguments($className);
        /** @var \Magento\Framework\App\Helper\Context $context */
        $context = $arguments['context'];
        $this->translator = $arguments['inlineTranslation'];
        $this->eventManager = $context->getEventManager();
        $this->scopeConfig = $context->getScopeConfig();
        $this->scopeConfig->expects($this->any())
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

        $this->checkoutSession = $arguments['checkoutSession'];
        $arguments['localeDate']->expects($this->any())
            ->method('formatDateTime')
            ->willReturn('Oct 02, 2013');

        $this->transportBuilder = $arguments['transportBuilder'];

        $this->priceCurrency = $arguments['priceCurrency'];

        $this->helper = $objectManagerHelper->getObject($className, $arguments);
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

        $this->assertSame($this->helper, $this->helper->sendPaymentFailedEmail($quoteMock, 'test message'));
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    public function testGetCheckout()
    {
        $this->assertEquals($this->checkoutSession, $this->helper->getCheckout());
    }

    public function testGetQuote()
    {
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->checkoutSession->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $this->assertEquals($quoteMock, $this->helper->getQuote());
    }

    public function testFormatPrice()
    {
        $price = 5.5;
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $storeMock = $this->createPartialMock(\Magento\Store\Model\Store::class, ['formatPrice', '__wakeup']);
        $this->checkoutSession->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getStore')->willReturn($storeMock);
        $this->priceCurrency->expects($this->once())->method('format')->willReturn('5.5');
        $this->assertEquals('5.5', $this->helper->formatPrice($price));
    }

    public function testConvertPrice()
    {
        $price = 5.5;
        $this->priceCurrency->expects($this->once())->method('convertAndFormat')->willReturn($price);
        $this->assertEquals(5.5, $this->helper->convertPrice($price));
    }

    public function testCanOnepageCheckout()
    {
        $this->scopeConfig->expects($this->once())->method('isSetFlag')->with(
            'checkout/options/onepage_checkout_enabled',
            'store'
        )->willReturn(true);
        $this->assertTrue($this->helper->canOnepageCheckout());
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
        $context->getRequest()->expects($this->once())->method('getParam')->with('context')->willReturn(
            'checkout'
        );
        $this->assertTrue($helper->isContextCheckout());
    }

    public function testIsCustomerMustBeLogged()
    {
        $this->scopeConfig->expects($this->once())->method('isSetFlag')->with(
            'checkout/options/customer_must_be_logged',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )->willReturn(true);
        $this->assertTrue($this->helper->isCustomerMustBeLogged());
    }

    public function testGetPriceInclTax()
    {
        $itemMock = $this->createPartialMock(\Magento\Framework\DataObject::class, ['getPriceInclTax']);
        $itemMock->expects($this->exactly(2))->method('getPriceInclTax')->willReturn(5.5);
        $this->assertEquals(5.5, $this->helper->getPriceInclTax($itemMock));
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
        $itemMock->expects($this->once())->method('getPriceInclTax')->willReturn(false);
        $itemMock->expects($this->exactly(2))->method('getQty')->willReturn($qty);
        $itemMock->expects($this->never())->method('getQtyOrdered');
        $itemMock->expects($this->once())->method('getTaxAmount')->willReturn($taxAmount);
        $itemMock->expects($this->once())
            ->method('getDiscountTaxCompensation')->willReturn($discountTaxCompensation);
        $itemMock->expects($this->once())->method('getRowTotal')->willReturn($rowTotal);
        $this->priceCurrency->expects($this->once())->method('round')->with($roundPrice)->willReturn($roundPrice);
        $this->assertEquals($expected, $helper->getPriceInclTax($itemMock));
    }

    public function testGetSubtotalInclTax()
    {
        $rowTotalInclTax = 5.5;
        $expected = 5.5;
        $itemMock = $this->createPartialMock(\Magento\Framework\DataObject::class, ['getRowTotalInclTax']);
        $itemMock->expects($this->exactly(2))->method('getRowTotalInclTax')->willReturn($rowTotalInclTax);
        $this->assertEquals($expected, $this->helper->getSubtotalInclTax($itemMock));
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
        $itemMock->expects($this->once())->method('getRowTotalInclTax')->willReturn(false);
        $itemMock->expects($this->once())->method('getTaxAmount')->willReturn($taxAmount);
        $itemMock->expects($this->once())
            ->method('getDiscountTaxCompensation')->willReturn($discountTaxCompensation);
        $itemMock->expects($this->once())->method('getRowTotal')->willReturn($rowTotal);
        $this->assertEquals($expected, $this->helper->getSubtotalInclTax($itemMock));
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
        $itemMock->expects($this->once())->method('getQty')->willReturn(false);
        $itemMock->expects($this->exactly(2))->method('getQtyOrdered')->willReturn(5.5);
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
        $this->helper->getBaseSubtotalInclTax($itemMock);
    }

    public function testIsAllowedGuestCheckoutWithoutStore()
    {
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $store = null;
        $quoteMock->expects($this->once())->method('getStoreId')->willReturn(1);
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->willReturn(true);
        $this->assertTrue($this->helper->isAllowedGuestCheckout($quoteMock, $store));
    }
}
