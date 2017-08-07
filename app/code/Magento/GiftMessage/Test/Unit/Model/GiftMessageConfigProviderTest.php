<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Test\Unit\Model;

use Magento\Catalog\Model\Product\Attribute\Source\Boolean;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\UrlInterface;
use Magento\GiftMessage\Helper\Message as GiftMessageHelper;
use Magento\Store\Model\ScopeInterface as Scope;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GiftMessageConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GiftMessage\Model\GiftMessageConfigProvider
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpContextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeFormatMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $formKeyMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    protected function setUp()
    {
        $this->checkoutSessionMock = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->httpContextMock = $this->createMock(\Magento\Framework\App\Http\Context::class);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->localeFormatMock = $this->createMock(\Magento\Framework\Locale\FormatInterface::class);
        $this->formKeyMock = $this->createMock(\Magento\Framework\Data\Form\FormKey::class);
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $contextMock = $this->createMock(\Magento\Framework\App\Helper\Context::class);
        $this->cartRepositoryMock = $this->createMock(\Magento\GiftMessage\Api\CartRepositoryInterface::class);
        $this->itemRepositoryMock = $this->createMock(\Magento\GiftMessage\Api\ItemRepositoryInterface::class);
        $contextMock->expects($this->atLeastOnce())->method('getScopeConfig')->willReturn($this->scopeConfigMock);

        $this->model = new \Magento\GiftMessage\Model\GiftMessageConfigProvider(
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
        $isFrontUrlSecure = true;
        $baseUrl = 'https://magento.com/';
        $quoteItemMock = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $storeMock = $this->createPartialMock(
            \Magento\Store\Model\Store::class,
            ['isFrontUrlSecure', 'getBaseUrl', 'getCode']
        );
        $quoteMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            ['getQuoteCurrencyCode', 'getStore', 'getIsVirtual', 'getAllVisibleItems', 'getId']
        );
        $messageMock = $this->createMock(\Magento\GiftMessage\Model\Message::class);

        $this->scopeConfigMock->expects($this->atLeastOnce())->method('getValue')->willReturnMap(
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
        $storeMock->expects($this->once())->method('isFrontUrlSecure')->willReturn($isFrontUrlSecure);
        $storeMock->expects($this->once())->method('getBaseUrl')->with(UrlInterface::URL_TYPE_LINK, $isFrontUrlSecure)
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
