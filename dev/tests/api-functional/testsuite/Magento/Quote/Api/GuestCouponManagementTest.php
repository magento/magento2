<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Quote\Api;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\ApplyCoupon as ApplyCouponFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteIdMaskFixture;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Test\Fixture\Rule as SalesRuleFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GuestCouponManagementTest extends WebapiAbstract
{
    public const SERVICE_VERSION = 'V1';
    public const SERVICE_NAME = 'quoteGuestCouponManagementV1';
    public const RESOURCE_PATH = '/V1/guest-carts/';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    protected function tearDown(): void
    {
        $createdQuotes = ['test_order_1', 'test01'];
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        foreach ($createdQuotes as $quoteId) {
            $quote->load($quoteId, 'reserved_order_id');
            $quote->delete();
            /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
            $quoteIdMask = $this->objectManager->create(\Magento\Quote\Model\QuoteIdMask::class);
            $quoteIdMask->load($quoteId, 'quote_id');
            $quoteIdMask->delete();
        }
    }

    protected function getQuoteMaskedId($quoteId)
    {
        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->objectManager->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)->create();
        $quoteIdMask->load($quoteId, 'quote_id');
        return $quoteIdMask->getMaskedId();
    }

    /**
     * Test get coupon from guest cart
     */
    #[
        DataFixture(SalesRuleFixture::class, [
            'simple_action' => Rule::BY_PERCENT_ACTION,
            'discount_amount' => 10,
            'coupon_code' => 'test_coupon_10',
        ], 'rule'),
        DataFixture(ProductFixture::class, ['sku' => 'simple-coupon-get-%uniqid%', 'price' => 10], 'product'),
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test_order_1'], 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 2],
            'add_product_to_cart'
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(ApplyCouponFixture::class, ['cart_id' => '$cart.id$', 'coupon_codes' => ['test_coupon_10']]),
        DataFixture(QuoteIdMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testGet()
    {
        $cart = DataFixtureStorageManager::getStorage()->get('cart');
        $quoteIdMask = DataFixtureStorageManager::getStorage()->get('quoteIdMask');
        $cartId = $quoteIdMask->getMaskedId();
        /** @var \Magento\Quote\Api\CartRepositoryInterface $cartRepository */
        $cartRepository = $this->objectManager->get(\Magento\Quote\Api\CartRepositoryInterface::class);
        $quote = $cartRepository->get($cart->getId());
        $expectedCouponCode = $quote->getCouponCode();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/coupons/' ,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        $requestData = ["cartId" => $cartId];
        $this->assertEquals($expectedCouponCode, $this->_webApiCall($serviceInfo, $requestData));
    }

    /**
     * Test delete coupon from guest cart
     */
    #[
        DataFixture(SalesRuleFixture::class, [
            'simple_action' => Rule::BY_PERCENT_ACTION,
            'discount_amount' => 10,
            'coupon_code' => 'test_coupon_10',
        ], 'rule'),
        DataFixture(ProductFixture::class, ['sku' => 'simple-coupon-delete-%uniqid%', 'price' => 10], 'product'),
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test_order_1'], 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 2],
            'add_product_to_cart'
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(ApplyCouponFixture::class, ['cart_id' => '$cart.id$', 'coupon_codes' => ['test_coupon_10']]),
        DataFixture(QuoteIdMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testDelete()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_order_1', 'reserved_order_id');
        $quoteIdMask = DataFixtureStorageManager::getStorage()->get('quoteIdMask');
        $cartId = $quoteIdMask->getMaskedId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/coupons',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Remove',
            ],
        ];
        $requestData = ["cartId" => $cartId];
        $this->assertTrue($this->_webApiCall($serviceInfo, $requestData));
        $quote->load('test_order_1', 'reserved_order_id');
        $this->assertEquals('', $quote->getCouponCode());
    }

    /**
     * Test set coupon throws exception if coupon does not exist
     */
    #[
        DataFixture(ProductFixture::class, ['sku' => 'simple-coupon-invalid-%uniqid%', 'price' => 10], 'product'),
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test_order_1'], 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 2],
            'add_product_to_cart'
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(QuoteIdMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testSetCouponThrowsExceptionIfCouponDoesNotExist()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The coupon code isn\'t valid. Verify the code and try again.');

        $quoteIdMask = DataFixtureStorageManager::getStorage()->get('quoteIdMask');
        $cartId = $quoteIdMask->getMaskedId();

        $couponCode = 'invalid_coupon_code';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/coupons/' . $couponCode,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Set',
            ],
        ];

        $requestData = [
            "cartId" => $cartId,
            "couponCode" => $couponCode,
        ];

        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/quote.php
     * @magentoApiDataFixture Magento/Checkout/_files/discount_10percent.php
     */
    public function testSetCouponSuccess()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test01', 'reserved_order_id');
        $cartId = $this->getQuoteMaskedId($quote->getId());
        $salesRule = $this->objectManager->create(\Magento\SalesRule\Model\Rule::class);
        $salesRuleId = $this->objectManager->get(\Magento\Framework\Registry::class)
            ->registry('Magento/Checkout/_file/discount_10percent');
        $salesRule->load($salesRuleId);
        $couponCode = $salesRule->getPrimaryCoupon()->getCode();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/coupons/' . $couponCode,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Set',
            ],
        ];

        $requestData = [
            "cartId" => $cartId,
            "couponCode" => $couponCode,
        ];

        $this->assertTrue($this->_webApiCall($serviceInfo, $requestData));

        $quoteWithCoupon = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quoteWithCoupon->load('test01', 'reserved_order_id');

        $this->assertEquals($quoteWithCoupon->getCouponCode(), $couponCode);
    }
}
