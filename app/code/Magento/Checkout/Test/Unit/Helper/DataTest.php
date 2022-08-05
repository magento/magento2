<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->any())->method('getId')->willReturn(1);

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
        $storeMock = $this->getMockBuilder(Store::class)
            ->addMethods(['formatPrice'])
            ->disableOriginalConstructor()
            ->getMock();
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
        $itemMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getPriceInclTax'])
            ->disableOriginalConstructor()
            ->getMock();
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
        $storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $objectManagerHelper = new ObjectManager($this);
        $helper = $objectManagerHelper->getObject(
            Data::class,
            [
                'storeManager' => $storeManager,
                'priceCurrency' => $this->priceCurrency,
            ]
        );
        $itemMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(
                [
                    'getPriceInclTax',
                    'getQty',
                    'getTaxAmount',
                    'getDiscountTaxCompensation',
                    'getRowTotal',
                    'getQtyOrdered'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
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
        $itemMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getRowTotalInclTax'])
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->exactly(2))->method('getRowTotalInclTax')->willReturn($rowTotalInclTax);
        $this->assertEquals($expected, $this->helper->getSubtotalInclTax($itemMock));
    }

    public function testGetSubtotalInclTaxNegative()
    {
        $taxAmount = 1;
        $discountTaxCompensation = 1;
        $rowTotal = 15;
        $expected = 17;
        $itemMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getRowTotalInclTax', 'getTaxAmount', 'getDiscountTaxCompensation', 'getRowTotal'])
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->once())->method('getRowTotalInclTax')->willReturn(false);
        $itemMock->expects($this->once())->method('getTaxAmount')->willReturn($taxAmount);
        $itemMock->expects($this->once())
            ->method('getDiscountTaxCompensation')->willReturn($discountTaxCompensation);
        $itemMock->expects($this->once())->method('getRowTotal')->willReturn($rowTotal);
        $this->assertEquals($expected, $this->helper->getSubtotalInclTax($itemMock));
    }

    public function testGetBasePriceInclTaxWithoutQty()
    {
        $storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $objectManagerHelper = new ObjectManager($this);
        $helper = $objectManagerHelper->getObject(
            Data::class,
            [
                'storeManager' => $storeManager,
                'priceCurrency' => $this->priceCurrency,
            ]
        );
        $itemMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getQty'])
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->once())->method('getQty');
        $this->priceCurrency->expects($this->once())->method('round');
        $helper->getPriceInclTax($itemMock);
    }

    public function testGetBasePriceInclTax()
    {
        $storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $objectManagerHelper = new ObjectManager($this);
        $helper = $objectManagerHelper->getObject(
            Data::class,
            [
                'storeManager' => $storeManager,
                'priceCurrency' => $this->priceCurrency,
            ]
        );
        $itemMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getQty', 'getQtyOrdered'])
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->once())->method('getQty')->willReturn(false);
        $itemMock->expects($this->exactly(2))->method('getQtyOrdered')->willReturn(5.5);
        $this->priceCurrency->expects($this->once())->method('round');
        $helper->getBasePriceInclTax($itemMock);
    }

    public function testGetBaseSubtotalInclTax()
    {
        $itemMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getBaseTaxAmount', 'getBaseDiscountTaxCompensation', 'getBaseRowTotal'])
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->once())->method('getBaseTaxAmount');
        $itemMock->expects($this->once())->method('getBaseDiscountTaxCompensation');
        $itemMock->expects($this->once())->method('getBaseRowTotal');
        $this->helper->getBaseSubtotalInclTax($itemMock);
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
