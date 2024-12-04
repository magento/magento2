<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Bundle;

use Magento\Bundle\Test\Fixture\AddProductToCart as AddBundleProductToCart;
use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class BundleProductMainPriceTest extends GraphQlAbstract
{
    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedId;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    public function getQuery()
    {
        $productSku = 'fixed_bundle_product_with_special_price';
        return <<<QUERY
{
   products(filter:{ sku:{eq:"{$productSku}"}})
   {
    items {
      url_key
      sku
         price_range {
             minimum_price {
                    final_price {
                      value
                      currency
                    }
              discount {
                percent_off
                amount_off
              }
              regular_price {
                value
                currency
              } }
                  maximum_price {
                    final_price {
                      value
                      currency
                    }
                    regular_price {
                      value
                      currency
                    }
                    discount {
                      percent_off
                      amount_off
                    }
                  } }
   ... on BundleProduct {
          price_details{
              main_price
              main_final_price
              discount_percentage
          }
          dynamic_sku
          dynamic_price
          dynamic_weight
          price_view
          ship_bundle_items
          items {
            uid
            title
            required
            type
            position
            sku
            options {
              uid
              quantity
              position
              is_default
              price
              price_type
              can_change_quantity
              label
              product {
                uid
                name
                sku
                price_range {
                  minimum_price {
                    final_price {
                      value
                    }
                      regular_price {
                value
              } }
                  maximum_price {
                    final_price {
                      value
                      currency
                    }
                      regular_price {
                value
                        currency
              }
                  }

                }
                __typename
              }
            }
          }
  }
    }
  }
}
QUERY;
    }

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/fixed_bundle_product_with_special_price.php
     * @return void
     */
    public function testBundleProductPriceDetails(): void
    {
        $query = $this->getQuery();
        $response = $this->graphQlQuery($query);
        $product = $response['products']['items'][0];
        $this->assertArrayHasKey('price_details', $product);
        $priceDetails = $product['price_details'];
        $this->assertArrayHasKey('main_price', $priceDetails);
        $this->assertArrayHasKey('main_final_price', $priceDetails);
        $this->assertArrayHasKey('discount_percentage', $priceDetails);
        $this->assertEquals(50.0, $priceDetails['main_price']);
        $this->assertEquals(40.0, $priceDetails['main_final_price']);
        $this->assertEquals(20, $priceDetails['discount_percentage']);
    }

    #[
        DataFixture(ProductFixture::class, ['sku' => 'simple1', 'price' => 10], as:'p1'),
        DataFixture(ProductFixture::class, ['sku' => 'simple2', 'price' => 20], as:'p2'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$p1.sku$'], as:'link1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$p2.sku$'], as:'link2'),
        DataFixture(BundleOptionFixture::class, ['title' => 'Checkbox Options', 'type' => 'checkbox',
            'required' => 1,'product_links' => ['$link1$', '$link2$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['title' => 'Checkbox Options', 'type' => 'checkbox',
            'required' => 1,'product_links' => ['$link1$', '$link2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            ['sku' => 'bundle-product-multiselect-checkbox-options', '_options' => ['$opt1$', '$opt2$']],
            as:'bp1'
        ),
        DataFixture(GuestCartFixture::class, as: 'guestCart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$guestCart.id$',
                'product_id' => '$bp1.id$',
                'selections' => [['$p1.id$'], ['$p2.id$']],
                'qty' => 2
            ]
        )
    ]
    public function testCartBundleProductPriceDetails()
    {
        $guestCart = $this->fixtures->get('guestCart');
        $guestQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$guestCart->getId());

        $cartQuery = $this->getGuestCartQuery($guestQuoteMaskedId);
        $cartResponse = $this->graphQlMutation($cartQuery);
        $productPriceDetails = $cartResponse['cart']['itemsV2']['items'][0]['product']['price_details'];
        self::assertArrayHasKey('main_price', $productPriceDetails);
        self::assertArrayHasKey('main_final_price', $productPriceDetails);
        self::assertArrayHasKey('discount_percentage', $productPriceDetails);
        self::assertEquals(0, $productPriceDetails['main_price']);
        self::assertEquals(30, $productPriceDetails['main_final_price']);
        self::assertEquals(0, $productPriceDetails['discount_percentage']);
    }

    private function getGuestCartQuery(string $maskedId): string
    {
        return <<<QUERY
{
  cart(cart_id: "{$maskedId}") {
    itemsV2 {
      items {
        product {
          sku
          ... on BundleProduct {
            dynamic_price
            price_view
            price_details {
              main_price
              main_final_price
              discount_percentage
            }
          }
        }
      }
    }
  }
}
QUERY;
    }
}
