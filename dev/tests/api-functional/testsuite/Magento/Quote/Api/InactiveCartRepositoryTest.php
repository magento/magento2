<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Api;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\MakeCartInactive as MakeCartInactiveFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class InactiveCartRepositoryTest extends WebapiAbstract
{
    protected function setUp(): void
    {
        $this->_markTestAsRestOnly();
        parent::setUp();
    }

    #[
        DataFixture(ProductFixture::class, ['sku' => 'inactive-cart-product-1'], 'product1'),
        DataFixture(ProductFixture::class, ['sku' => 'inactive-cart-product-2'], 'product2'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product1.id$']),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product2.id$', 'qty' => 2]
        ),
        DataFixture(MakeCartInactiveFixture::class, ['cart_id' => '$cart.id$']),
    ]
    public function testGetInactiveCartReturnsItems(): void
    {
        $cartId = (int)DataFixtureStorageManager::getStorage()->get('cart')->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];

        $cartData = $this->_webApiCall($serviceInfo, ['cartId' => $cartId]);

        $this->assertFalse((bool)$cartData['is_active']);
        $this->assertArrayHasKey('items', $cartData);
        $qtyBySku = array_column($cartData['items'], 'qty', 'sku');
        ksort($qtyBySku);
        $this->assertEquals(['inactive-cart-product-1' => 1, 'inactive-cart-product-2' => 2], $qtyBySku);
    }

    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(MakeCartInactiveFixture::class, ['cart_id' => '$cart.id$']),
    ]
    public function testGuestGetInactiveCartDoesNotReturnItems(): void
    {
        $cartId = (int)DataFixtureStorageManager::getStorage()->get('cart')->getId();
        $maskedId = Bootstrap::getObjectManager()->get(QuoteIdToMaskedQuoteIdInterface::class)->execute($cartId);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/guest-carts/' . $maskedId,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];

        $cartData = $this->_webApiCall($serviceInfo);

        $this->assertFalse((bool)$cartData['is_active']);
        $this->assertArrayNotHasKey('items', $cartData);
    }
}
