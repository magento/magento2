<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
namespace Magento\Quote\Api;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Quote\Model\QuoteIdMask;

/**
 * Class GuestCollectRatesTest checks that totals will be recollected properly with new shipping method
 */
class GuestCollectRatesTest extends WebapiAbstract
{
    public const SERVICE_VERSION = 'V1';
    public const SERVICE_NAME = 'quoteGuestCartTotalManagementV1';
    public const RESOURCE_PATH = '/V1/guest-carts/';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Quote
     */
    protected $quote;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->quote = $this->objectManager->create(Quote::class);
    }

    /**
     * Checks that totals are properly recollected after changing shipping method
     */
    #[
        DataFixture(ProductFixture::class, ['sku' => 'simple-collect-rates-%uniqid%', 'price' => 10], 'product'),
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'guest_quote_collect_rates'], 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 2],
            'add_product_to_cart'
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
    ]
    public function testCollectRatesWithChangedShippingMethod()
    {
        $this->quote->load('guest_quote_collect_rates', 'reserved_order_id');
        $cartId = $this->quote->getId();

        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->objectManager
            ->create(QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');

        $cartId = $quoteIdMask->getMaskedId();
        $requestData = [
            "shippingMethodCode" => "freeshipping",
            "shippingCarrierCode" => "freeshipping",
            "paymentMethod" => [
                "method" => "checkmo",
            ],
        ];

        if (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
            $requestData['cartId'] = $cartId;
        }

        $totals = $this->_webApiCall($this->getListServiceInfo($cartId), $requestData);
        $this->assertEquals(20, $totals['grand_total']);
        $this->assertEquals(0, $totals['shipping_amount']);

        $requestData['shippingMethodCode'] = 'flatrate';
        $requestData['shippingCarrierCode'] = 'flatrate';

        $totals = $this->_webApiCall($this->getListServiceInfo($cartId), $requestData);
        $this->assertEquals(30, $totals['grand_total']);
        $this->assertEquals(10, $totals['shipping_amount']);
    }

    /**
     * Service info
     *
     * @param int $cartId
     * @return array
     */
    protected function getListServiceInfo($cartId)
    {
        return [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/collect-totals',
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'quoteGuestCartTotalManagementV1CollectTotals',
            ],
        ];
    }
}
