<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Boolean;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\UrlInterface;
use Magento\GiftMessage\Api\CartRepositoryInterface;
use Magento\GiftMessage\Api\ItemRepositoryInterface;
use Magento\GiftMessage\Helper\Message as GiftMessageHelper;
use Magento\GiftMessage\Model\GiftMessageConfigProvider;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\ScopeInterface as Scope;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GiftMessageConfigProviderTest extends TestCase
{
    /**
     * @var GiftMessageConfigProvider
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $cartRepositoryMock;

    /**
     * @var MockObject
     */
    protected $itemRepositoryMock;

    /**
     * @var MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var MockObject
     */
    protected $httpContextMock;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MockObject
     */
    protected $localeFormatMock;

    /**
     * @var MockObject
     */
    protected $formKeyMock;

    /**
     * @var MockObject
     */
    protected $scopeConfigMock;

    protected function setUp(): void
    {
        $this->checkoutSessionMock = $this->createMock(Session::class);
        $this->httpContextMock = $this->createMock(\Magento\Framework\App\Http\Context::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->localeFormatMock = $this->getMockForAbstractClass(FormatInterface::class);
        $this->formKeyMock = $this->createMock(FormKey::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $contextMock = $this->createMock(\Magento\Framework\App\Helper\Context::class);
        $this->cartRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);
        $this->itemRepositoryMock = $this->getMockForAbstractClass(ItemRepositoryInterface::class);
        $contextMock->expects($this->atLeastOnce())->method('getScopeConfig')->willReturn($this->scopeConfigMock);

        $this->model = new GiftMessageConfigProvider(
            $contextMock,
            $this->cartRepositoryMock,
            $this->itemRepositoryMock,
            $this->checkoutSessionMock,
            $this->httpContextMock,
            $this->storeManagerMock,
            $this->localeFormatMock,
            $this->formKeyMock
        );
    }

    public function testGetConfig()
    {
        $orderLevel = true;
        $itemLevel = true;
        $isCustomerLoggedIn = true;
        $quoteId = 42;
        $itemId = 9000;
        $currencyCode = 'EUR';
        $priceFormat = [$currencyCode];
        $storeCode = 4;
        $messageDataMock = ['from' => 'John Doe', 'to' => 'Jane Doe'];
        $formKey = 'ABCDEFGHIJKLMNOP';
        $baseUrl = 'https://magento.com/';
        $quoteItemMock = $this->createMock(Item::class);
        $productMock = $this->createMock(Product::class);
        $storeMock = $this->createPartialMock(
            Store::class,
            ['getBaseUrl', 'getCode']
        );
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getQuoteCurrencyCode'])
            ->onlyMethods(['getStore', 'getIsVirtual', 'getAllVisibleItems', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $messageMock = $this->createMock(\Magento\GiftMessage\Model\Message::class);

        $this->scopeConfigMock->expects($this->atLeastOnce())->method('isSetFlag')->willReturnMap(
            [
                [GiftMessageHelper::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ORDER, Scope::SCOPE_STORE, null, $orderLevel],
                [GiftMessageHelper::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS, Scope::SCOPE_STORE, null, $itemLevel]
            ]
        );

        $this->checkoutSessionMock->expects($this->atLeastOnce())->method('getQuoteId')->willReturn($quoteId);
        $this->cartRepositoryMock->expects($this->once())->method('get')->with($quoteId)->willReturn($messageMock);
        $this->checkoutSessionMock->expects($this->atLeastOnce())->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->any())->method('getId')->willReturn($quoteId);
        $quoteMock->expects($this->once())->method('getIsVirtual')->willReturn(false);
        $messageMock->expects($this->atLeastOnce())->method('getData')->willReturn($messageDataMock);
        $quoteMock->expects($this->once())->method('getAllVisibleItems')->willReturn([$quoteItemMock]);
        $quoteItemMock->expects($this->once())->method('getId')->willReturn($itemId);
        $quoteItemMock->expects($this->any())->method('getProduct')->willReturn($productMock);
        $productMock->expects($this->any())->method('getGiftMessageAvailable')->willReturn(Boolean::VALUE_USE_CONFIG);
        $this->itemRepositoryMock->expects($this->once())->method('get')->with($quoteId, $itemId)
            ->willReturn($messageMock);
        $quoteMock->expects($this->once())->method('getQuoteCurrencyCode')->willReturn($currencyCode);
        $this->localeFormatMock->expects($this->once())->method('getPriceFormat')->with(null, $currencyCode)
            ->willReturn($priceFormat);

        $quoteMock->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getCode')->willReturn($storeCode);

        $this->httpContextMock->expects($this->once())->method('getValue')->with(CustomerContext::CONTEXT_AUTH)
            ->willReturn($isCustomerLoggedIn);
        $this->formKeyMock->expects($this->once())->method('getFormKey')->willReturn($formKey);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getBaseUrl')->with(UrlInterface::URL_TYPE_LINK)
            ->willReturn($baseUrl);

        $expectedResult = [
            'giftMessage' => [
                'orderLevel' => $messageDataMock,
                'itemLevel' => [
                    $itemId => [
                        'message' => $messageDataMock,
                    ],
                ]
            ],
            'isOrderLevelGiftOptionsEnabled' => $orderLevel,
            'isItemLevelGiftOptionsEnabled' => $itemLevel,
            'priceFormat' => [0 => $currencyCode],
            'storeCode' => $storeCode,
            'isCustomerLoggedIn' => $isCustomerLoggedIn,
            'formKey' => $formKey,
            'baseUrl' => $baseUrl
        ];
        $this->assertSame($expectedResult, $this->model->getConfig());
    }
}
