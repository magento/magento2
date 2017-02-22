<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Block\PayPal;

use Magento\Braintree\Block\PayPal\Shortcut;

/**
 * Class ShortcutTest
 */
class ShortcutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Math\Random|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mathRandomMock;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeResolverMock;

    /**
     * @var \Magento\Braintree\Model\Config\PayPal|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paypalConfigMock;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Checkout\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutDataMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $container;

    /**
     * @var Shortcut
     */
    protected $block;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    public function setUp()
    {
        $this->mathRandomMock = $this->getMockBuilder('\Magento\Framework\Math\Random')
            ->disableOriginalConstructor()
            ->getMock();

        $this->localeResolverMock = $this->getMock('\Magento\Framework\Locale\ResolverInterface');

        $this->paypalConfigMock = $this->getMockBuilder('\Magento\Braintree\Model\Config\PayPal')
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSessionMock = $this->getMockBuilder('\Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSessionMock = $this->getMockBuilder('\Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutDataMock = $this->getMockBuilder('\Magento\Checkout\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlBuilderMock = $this->getMock('\Magento\Framework\UrlInterface');

        $contextMock = $this->getMockBuilder('\Magento\Framework\View\Element\Template\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);

        $this->container = new \Magento\Framework\DataObject(
            [
                'module_name' => 'Magento_Catalog',
            ]
        );

        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $this->objectManagerHelper->getObject(
            'Magento\Braintree\Block\PayPal\Shortcut',
            [
                'context' => $contextMock,
                'mathRandom' => $this->mathRandomMock,
                'localeResolver' => $this->localeResolverMock,
                'paypalConfig' => $this->paypalConfigMock,
                'checkoutSession' => $this->checkoutSessionMock,
                'customerSession' => $this->customerSessionMock,
                'checkoutData' => $this->checkoutDataMock,
                'data' => [
                    'container' => $this->container,
                ]
            ]
        );
    }

    public function testGetClientToken()
    {
        $clientToken = 'clientToken';
        $this->paypalConfigMock->expects($this->once())
            ->method('getClientToken')
            ->willReturn($clientToken);

        $this->assertEquals($clientToken, $this->block->getClientToken());
    }

    public function testGetAmount()
    {
        $amount = 10.5;
        $quote = new \Magento\Framework\DataObject(
            [
                'base_grand_total' => $amount,
            ]
        );

        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);

        $this->assertEquals($amount, $this->block->getAmount());
    }

    public function testGetReviewPageUrl()
    {
        $url = 'http://localhost/braintree/paypal/review';

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('braintree/paypal/review')
            ->willReturn($url);

        $this->assertEquals($url, $this->block->getReviewPageUrl());
    }

    public function testGetCurrency()
    {
        $currency = 'USD';

        $quote = new \Magento\Framework\DataObject(
            [
                'currency' => new \Magento\Framework\DataObject(
                    [
                        'base_currency_code' => $currency,
                    ]
                ),
            ]
        );
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);

        $this->assertEquals($currency, $this->block->getCurrency());
    }

    public function testGetLocale()
    {
        $locale = 'en_US';

        $this->localeResolverMock->expects($this->once())
            ->method('getLocale')
            ->willReturn($locale);

        $this->assertEquals($locale, $this->block->getLocale());
    }

    public function testMerchantName()
    {
        $merchantName = 'Magento';

        $this->paypalConfigMock->expects($this->once())
            ->method('getMerchantNameOverride')
            ->willReturn($merchantName);

        $this->assertEquals($merchantName, $this->block->getMerchantName());
    }

    public function testEnableBillingAddress()
    {
        $flag = true;

        $this->paypalConfigMock->expects($this->once())
            ->method('isBillingAddressEnabled')
            ->willReturn($flag);

        $this->assertEquals($flag, $this->block->enableBillingAddress());
    }

    /**
     * @param bool $isLoggedIn
     * @param bool $isAllowedGuestCheckout
     * @dataProvider skipShortcutForGuestDataProvider
     */
    public function testSkipShortcutForGuest($isLoggedIn, $isAllowedGuestCheckout, $expected)
    {
        $this->customerSessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->willReturn($isLoggedIn);

        $quoteMock = $this->getMockBuilder('\Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutSessionMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $this->checkoutDataMock->expects($this->any())
            ->method('isAllowedGuestCheckout')
            ->willReturn($isAllowedGuestCheckout);

        $this->assertEquals($expected, $this->block->skipShortcutForGuest());
    }

    public function skipShortcutForGuestDataProvider()
    {
        return [
            'logged_in' => [
                'is_logged_in' => true,
                'is_guest_checkout_allowed' => true,
                'expected' => false,
            ],
            'not_logged_in_guest_allowed' => [
                'is_logged_in' => false,
                'is_guest_checkout_allowed' => true,
                'expected' => false,
            ],
            'not_logged_in_guest_not_allowed' => [
                'is_logged_in' => false,
                'is_guest_checkout_allowed' => false,
                'expected' => true,
            ],
        ];
    }

    public function testGetHtmlElementIdsMiniCart()
    {
        $block = $this->objectManagerHelper->getObject(
            'Magento\Braintree\Block\PayPal\Shortcut',
            [
                'data' => [
                    Shortcut::MINI_CART_FLAG_KEY => 1
                ]
            ]
        );

        $this->assertEquals('braintree_paypal_container_minicart', $block->getContainerId());
        $this->assertEquals('braintree_paypal_submit_form_minicart', $block->getSubmitFormId());
        $this->assertEquals('braintree_paypal_payment_method_nonce_minicart', $block->getPaymentMethodNonceId());
        $this->assertEquals('braintree_paypal_payment_details_minicart', $block->getPaymentDetailsId());
    }

    public function testGetHtmlElementIdsShoppingCart()
    {
        $random = '_shopping_cart';
        $this->mathRandomMock->expects($this->any())
            ->method('getRandomString')
            ->willReturn($random);

        $block = $this->objectManagerHelper->getObject(
            'Magento\Braintree\Block\PayPal\Shortcut',
            [
                'mathRandom' => $this->mathRandomMock,
                'data' => [
                    Shortcut::MINI_CART_FLAG_KEY => 0
                ]
            ]
        );

        $this->assertEquals('braintree_paypal_container_shopping_cart', $block->getContainerId());
        $this->assertEquals('braintree_paypal_submit_form_shopping_cart', $block->getSubmitFormId());
        $this->assertEquals('braintree_paypal_payment_method_nonce_shopping_cart', $block->getPaymentMethodNonceId());
        $this->assertEquals('braintree_paypal_payment_details_shopping_cart', $block->getPaymentDetailsId());
    }
}
