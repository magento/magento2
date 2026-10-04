<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Helper;

use Magento\Checkout\Helper\Data;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends TestCase
{
    /**
     * @var MockObject|PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var MockObject
     */
    private $transportBuilder;

    /**
     * @var MockObject
     */
    private $translator;

    /**
     * @var MockObject
     */
    private $checkoutSession;

    /**
     * @var MockObject
     */
    private $scopeConfig;

    /**
     * @var MockObject
     */
    private $eventManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $className = Data::class;
        $arguments = $objectManagerHelper->getConstructArguments($className);
        /** @var Context $context */
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
        $arguments['localeDate']->method('formatDateTime')->willReturn('Oct 02, 2013');

        $this->transportBuilder = $arguments['transportBuilder'];

        $this->priceCurrency = $arguments['priceCurrency'];

        $this->helper = $objectManagerHelper->getObject($className, $arguments);
    }

    /**
     * @return void
     */
    public function testSendPaymentFailedEmail()
    {
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->method('getId')->willReturn(1);

        $this->assertSame($this->helper, $this->helper->sendPaymentFailedEmail($quoteMock, 'test message'));
    }

    public function testGetCheckout()
    {
        $this->assertEquals($this->checkoutSession, $this->helper->getCheckout());
    }

    public function testGetQuote()
    {
        $quoteMock = $this->createMock(Quote::class);
        $this->checkoutSession->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $this->assertEquals($quoteMock, $this->helper->getQuote());
    }

    public function testFormatPrice()
    {
        $price = 5.5;
        $quoteMock = $this->createMock(Quote::class);
        $storeMock = $this->createMock(Store::class);
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
        $objectManagerHelper = new ObjectManager($this);
        $context = $objectManagerHelper->getObject(
            Context::class
        );
        $helper = $objectManagerHelper->getObject(
            Data::class,
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
            ScopeInterface::SCOPE_STORE
        )->willReturn(true);
        $this->assertTrue($this->helper->isCustomerMustBeLogged());
    }

    public function testGetPriceInclTax()
    {
        $item = new DataObject(['price_incl_tax' => 5.5]);
        $this->assertEquals(5.5, $this->helper->getPriceInclTax($item));
    }

    public function testGetPriceInclTaxWithoutTax()
    {
        $qty = 1;
        $taxAmount = 1;
        $discountTaxCompensation = 1;
        $rowTotal = 15;
        $roundPrice = 17;
        $expected = 17;
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $objectManagerHelper = new ObjectManager($this);
        $helper = $objectManagerHelper->getObject(
            Data::class,
            [
                'storeManager' => $storeManager,
                'priceCurrency' => $this->priceCurrency,
            ]
        );
        $itemMock = new DataObject([
            'qty' => $qty,
            'tax_amount' => $taxAmount,
            'discount_tax_compensation' => $discountTaxCompensation,
            'row_total' => $rowTotal,
        ]);
        $this->priceCurrency->expects($this->once())->method('round')->with($roundPrice)->willReturn($roundPrice);
        $this->assertEquals($expected, $helper->getPriceInclTax($itemMock));
    }

    public function testGetSubtotalInclTax()
    {
        $rowTotalInclTax = 5.5;
        $expected = 5.5;
        $itemMock = new DataObject(['row_total_incl_tax' => $rowTotalInclTax]);
        $this->assertEquals($expected, $this->helper->getSubtotalInclTax($itemMock));
    }

    public function testGetSubtotalInclTaxNegative()
    {
        $taxAmount = 1;
        $discountTaxCompensation = 1;
        $rowTotal = 15;
        $expected = 17;
        $itemMock = new DataObject([
            'tax_amount' => $taxAmount,
            'discount_tax_compensation' => $discountTaxCompensation,
            'row_total' => $rowTotal,
        ]);
        $this->assertEquals($expected, $this->helper->getSubtotalInclTax($itemMock));
    }

    public function testGetBasePriceInclTaxWithoutQty()
    {
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $objectManagerHelper = new ObjectManager($this);
        $helper = $objectManagerHelper->getObject(
            Data::class,
            [
                'storeManager' => $storeManager,
                'priceCurrency' => $this->priceCurrency,
            ]
        );
        $itemMock = new DataObject([
            'qty' => 1,
            'tax_amount' => 0,
            'discount_tax_compensation' => 0,
            'row_total' => 0,
        ]);
        $this->priceCurrency->expects($this->once())->method('round');
        $helper->getPriceInclTax($itemMock);
    }

    public function testGetBasePriceInclTax()
    {
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $objectManagerHelper = new ObjectManager($this);
        $helper = $objectManagerHelper->getObject(
            Data::class,
            [
                'storeManager' => $storeManager,
                'priceCurrency' => $this->priceCurrency,
            ]
        );
        $itemMock = new DataObject([
            'qty' => false,
            'qty_ordered' => 1,
            'base_tax_amount' => 0,
            'base_discount_tax_compensation' => 0,
            'base_row_total' => 0,
        ]);
        $this->priceCurrency->expects($this->once())->method('round');
        $helper->getBasePriceInclTax($itemMock);
    }

    public function testGetBaseSubtotalInclTax()
    {
        $item = new DataObject([
            'base_tax_amount' => 0,
            'base_discount_tax_compensation' => 0,
            'base_row_total' => 0,
        ]);
        $this->helper->getBaseSubtotalInclTax($item);
    }

    public function testIsAllowedGuestCheckoutWithoutStore()
    {
        $quoteMock = $this->createMock(Quote::class);
        $store = null;
        $quoteMock->expects($this->once())->method('getStoreId')->willReturn(1);
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->willReturn(true);
        $this->assertTrue($this->helper->isAllowedGuestCheckout($quoteMock, $store));
    }
}
