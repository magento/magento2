<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\GraphQlResolverCache\Model\Resolver\Result\Type as GraphQlResolverCacheType;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\Cache as CacheAlias;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class GetCartItemsV2Test extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteId;

    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->quoteIdToMaskedQuoteId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
    }

    #[
        CacheAlias(GraphQlResolverCacheType::TYPE_IDENTIFIER, false),
        DataFixture('Magento/Catalog/_files/product_with_image.php'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => 1, 'qty' => 1]),
    ]
    public function testGetCartItemsV2(): void
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteId->execute((int) $cart->getId());
        $query = $this->getQuery($maskedQuoteId);

        $response = $this->graphQlQuery($query);
        self::assertArrayHasKey('cart', $response);
        self::assertEquals($maskedQuoteId, $response['cart']['id']);
        self::assertArrayHasKey('itemsV2', $response['cart']);
        self::assertArrayHasKey('items', $response['cart']['itemsV2']);
        self::assertCount(1, $response['cart']['itemsV2']['items']);
        self::assertArrayHasKey('product', $response['cart']['itemsV2']['items'][0]);

        $product = $response['cart']['itemsV2']['items'][0]['product'];
        self::assertEquals('simple', $product['sku']);
        self::assertArrayHasKey('media_gallery', $product);
        self::assertCount(1, $product['media_gallery']);
        self::assertArrayHasKey('url', $product['media_gallery'][0]);
    }

    /**
     * @param string $maskedQuoteId
     * @return string
     */
    private function getQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
{
  cart(cart_id: "{$maskedQuoteId}") {
    itemsV2 {
      items {
        product {
          media_gallery {
            url
          }
          sku
        }
      }
    }
    id
  }
}
QUERY;
    }
}
