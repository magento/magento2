<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Test\Unit\Model;

use Magento\GiftMessage\Helper\Message as GiftMessageHelper;
use Magento\Store\Model\ScopeInterface as Scope;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\UrlInterface;

class GiftMessageConfigProviderTest extends \PHPUnit_Framework_TestCase
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
        $this->checkoutSessionMock = $this->getMock('Magento\Checkout\Model\Session', [], [], '', false);
        $this->httpContextMock = $this->getMock('Magento\Framework\App\Http\Context', [], [], '', false);
        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface', [], [], '', false);
        $this->localeFormatMock = $this->getMock('Magento\Framework\Locale\FormatInterface', [], [], '', false);
        $this->formKeyMock = $this->getMock('Magento\Framework\Data\Form\FormKey', [], [], '', false);
        $this->scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface', [], [], '', false);
        $contextMock = $this->getMock('Magento\Framework\App\Helper\Context', [], [], '', false);
        $this->cartRepositoryMock = $this->getMock(
            'Magento\GiftMessage\Api\CartRepositoryInterface',
            [],
            [],
            '',
            false
        );
        $this->itemRepositoryMock = $this->getMock(
            'Magento\GiftMessage\Api\ItemRepositoryInterface',
            [],
            [],
            '',
            false
        );
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
        $quoteItemMock = $this->getMock('Magento\Quote\Model\Quote\Item', [], [], '', false);
        $storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            ['isFrontUrlSecure', 'getBaseUrl', 'getCode'],
            [],
            '',
            false
        );
        $quoteMock = $this->getMock(
            'Magento\Quote\Model\Quote',
            ['getQuoteCurrencyCode', 'getStore', 'getIsVirtual', 'getAllVisibleItems'],
            [],
            '',
            false
        );
        $messageMock = $this->getMockForAbstractClass(
            'Magento\GiftMessage\Api\Data\MessageInterface',
            [],
            '',
            false,
            false,
            false,
            ['getData']
        );

        $this->scopeConfigMock->expects($this->atLeastOnce())->method('getValue')->willReturnMap(
            [
                [GiftMessageHelper::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ORDER, Scope::SCOPE_STORE, null, $orderLevel],
                [GiftMessageHelper::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS, Scope::SCOPE_STORE, null, $itemLevel]
            ]
        );

        $this->checkoutSessionMock->expects($this->atLeastOnce())->method('getQuoteId')->willReturn($quoteId);
        $this->cartRepositoryMock->expects($this->once())->method('get')->with($quoteId)->willReturn($messageMock);

        $this->checkoutSessionMock->expects($this->once())->method('loadCustomerQuote')->willReturnSelf();
        $this->checkoutSessionMock->expects($this->atLeastOnce())->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getIsVirtual')->willReturn(false);

        $messageMock->expects($this->atLeastOnce())->method('getData')->willReturn($messageDataMock);

        $quoteMock->expects($this->once())->method('getAllVisibleItems')->willReturn([$quoteItemMock]);
        $quoteItemMock->expects($this->once())->method('getId')->willReturn($itemId);
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
                'itemLevel' => [$itemId => $messageDataMock]
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
