<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Create;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Framework\Currency;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Payment;
use Magento\Sales\Block\Adminhtml\Order\Create\Form;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTest extends TestCase
{
    /**
     * @var QuoteSession|MockObject
     */
    private $quoteSession;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepository;

    /**
     * @var CurrencyInterface|MockObject
     */
    private $localeCurrency;

    /**
     * @var Form
     */
    private $block;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        /** @var Context|MockObject $context */
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $context->method('getStoreManager')
            ->willReturn($this->storeManager);

        $this->quoteSession = $this->getMockBuilder(QuoteSession::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId', 'getQuoteId', 'getStoreId', 'getStore', 'getQuote'])
            ->getMock();
        /** @var Create|MockObject $create */
        $create = $this->getMockBuilder(Create::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var PriceCurrencyInterface|MockObject $priceCurrency */
        $priceCurrency = $this->getMockForAbstractClass(PriceCurrencyInterface::class);
        /** @var EncoderInterface|MockObject $encoder */
        $encoder = $this->getMockForAbstractClass(EncoderInterface::class);
        $encoder->method('encode')
            ->willReturnCallback(function ($param) {
                return json_encode($param);
            });
        /** @var FormFactory|MockObject $formFactory */
        $formFactory = $this->getMockBuilder(FormFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepository = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);

        $this->localeCurrency = $this->getMockForAbstractClass(CurrencyInterface::class);
        /** @var Mapper|MockObject $addressMapper */
        $addressMapper = $this->getMockBuilder(Mapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->block = new Form(
            $context,
            $this->quoteSession,
            $create,
            $priceCurrency,
            $encoder,
            $formFactory,
            $this->customerRepository,
            $this->localeCurrency,
            $addressMapper
        );
    }

    /**
     * Checks if order contains all needed data.
     */
    public function testGetOrderDataJson()
    {
        $customerId = 1;
        $storeId = 1;
        $quoteId = 2;
        $expected = [
            'customer_id' => $customerId,
            'addresses' => [],
            'store_id' => $storeId,
            'currency_symbol' => '$',
            'shipping_method_reseted' => false,
            'payment_method' => 'free',
            'quote_id' => $quoteId
        ];

        $this->storeManager->method('setCurrentStore')
            ->with($storeId);
        $this->quoteSession->method('getCustomerId')
            ->willReturn($customerId);
        $this->quoteSession->method('getStoreId')
            ->willReturn($storeId);
        $this->quoteSession->method('getQuoteId')
            ->willReturn($quoteId);

        $customer = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer->method('getAddresses')
            ->willReturn([]);
        $this->customerRepository->method('getById')
            ->with($customerId)
            ->willReturn($customer);

        $this->withCurrencySymbol('$');

        $this->withQuote();

        self::assertEquals($expected, json_decode($this->block->getOrderDataJson(), true));
    }

    /**
     * Configures mock object for currency.
     *
     * @param string $symbol
     */
    private function withCurrencySymbol(string $symbol)
    {
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->method('getCurrentCurrencyCode')
            ->willReturn('USD');
        $this->quoteSession->method('getStore')
            ->willReturn($store);

        $currency = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $currency->method('getSymbol')
            ->willReturn($symbol);
        $this->localeCurrency->method('getCurrency')
            ->with('USD')
            ->willReturn($currency);
    }

    /**
     * Configures shipping and payment mock objects.
     */
    private function withQuote()
    {
        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteSession->method('getQuote')
            ->willReturn($quote);

        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $address->method('getShippingMethod')
            ->willReturn('free');
        $quote->method('getShippingAddress')
            ->willReturn($address);

        $payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $payment->method('getMethod')
            ->willReturn('free');
        $quote->method('getPayment')
            ->willReturn($payment);
    }
}
