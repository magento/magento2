<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Bundle;

use Magento\Bundle\Model\Product\OptionList;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Test\Fixture\Group as GroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;

/**
 * Test querying Bundle products
 */
class BundleProductViewTest extends GraphQlAbstract
{
    private const KEY_PRICE_TYPE_FIXED = 'FIXED';
    private const KEY_PRICE_TYPE_DYNAMIC = 'DYNAMIC';

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/product_1.php
     */
    public function testAllFieldsBundleProducts()
    {
        $productSku = 'bundle-product';
        $query
            = <<<QUERY
{
   products(filter: {sku: {eq: "{$productSku}"}})
   {
       items{
           sku
           type_id
           id
           name
           ... on PhysicalProductInterface {
             weight
           }
           ... on BundleProduct {
           dynamic_sku
            dynamic_price
            dynamic_weight
            price_view
            ship_bundle_items
            items {
              option_id
              title
              required
              type
              position
              sku
              price_range{
                  maximum_price {
                    final_price {
                      currency
                      value
                    }
                  }
                  minimum_price {
                    final_price {
                      currency
                      value
                    }
                  }
              }
              options {
                id
                quantity
                position
                is_default
                price
                price_type
                can_change_quantity
                label
                product {
                  id
                  name
                  sku
                  type_id
                   }
                }
            }
           }
       }
   }
}
QUERY;

        $response = $this->graphQlQuery($query);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $bundleProduct = $productRepository->get($productSku, false, null, true);
        if ((bool)$bundleProduct->getShipmentType()) {
            $this->assertEquals('SEPARATELY', $response['products']['items'][0]['ship_bundle_items']);
        } else {
            $this->assertEquals('TOGETHER', $response['products']['items'][0]['ship_bundle_items']);
        }
        if ((bool)$bundleProduct->getPriceView()) {
            $this->assertEquals('AS_LOW_AS', $response['products']['items'][0]['price_view']);
        } else {
            $this->assertEquals('PRICE_RANGE', $response['products']['items'][0]['price_view']);
        }
        $this->assertBundleBaseFields($bundleProduct, $response['products']['items'][0]);
        $product = $response['products']['items'][0]['items'][0];
        $this->assertEquals(10, $product['price_range']['maximum_price']['final_price']['value']);
        $this->assertEquals(10, $product['price_range']['minimum_price']['final_price']['value']);

        $this->assertBundleProductOptions($bundleProduct, $response['products']['items'][0]);
        $this->assertNotEmpty(
            $response['products']['items'][0]['items'],
            "Precondition failed: 'items' must not be empty"
        );
    }

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/bundle_product_with_not_visible_children.php
     */
    public function testBundleProductWithNotVisibleChildren()
    {
        $productSku = 'bundle-product-1';
        $query
            = <<<QUERY
{
   products(filter: {sku: {eq: "{$productSku}"}})
   {
       items{
           sku
           type_id
           id
           name
           ... on PhysicalProductInterface {
             weight
           }
           ... on BundleProduct {
           dynamic_sku
            dynamic_price
            dynamic_weight
            price_view
            ship_bundle_items
            items {
              option_id
              title
              required
              type
              position
              sku
              options {
                id
                quantity
                position
                is_default
                price
                price_type
                can_change_quantity
                label
                product {
                  id
                  name
                  sku
                  type_id
                   }
                }
            }
           }
       }
   }
}
QUERY;

        /** @var \Magento\Config\Model\ResourceModel\Config $config */
        $config = ObjectManager::getInstance()->get(\Magento\Config\Model\ResourceModel\Config::class);
        $config->saveConfig(
            \Magento\CatalogInventory\Model\Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
            0,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
        ObjectManager::getInstance()->get(\Magento\Framework\App\Cache::class)
            ->clean(\Magento\Framework\App\Config::CACHE_TAG);
        $response = $this->graphQlQuery($query);
        $this->assertNotEmpty(
            $response['products']['items'],
            "Precondition failed: 'items' must not be empty"
        );

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $bundleProduct = $productRepository->get($productSku, false, null, true);
        if ((bool)$bundleProduct->getShipmentType()) {
            $this->assertEquals('SEPARATELY', $response['products']['items'][0]['ship_bundle_items']);
        } else {
            $this->assertEquals('TOGETHER', $response['products']['items'][0]['ship_bundle_items']);
        }
        if ((bool)$bundleProduct->getPriceView()) {
            $this->assertEquals('AS_LOW_AS', $response['products']['items'][0]['price_view']);
        } else {
            $this->assertEquals('PRICE_RANGE', $response['products']['items'][0]['price_view']);
        }
        $this->assertBundleBaseFields($bundleProduct, $response['products']['items'][0]);

        $this->assertBundleProductOptions($bundleProduct, $response['products']['items'][0]);
        $this->assertNotEmpty(
            $response['products']['items'][0]['items'],
            "Precondition failed: 'items' must not be empty"
        );
    }

    /**
     * @param ProductInterface $product
     * @param array $actualResponse
     */
    private function assertBundleBaseFields($product, $actualResponse)
    {
        $assertionMap = [
            ['response_field' => 'sku', 'expected_value' => $product->getSku()],
            ['response_field' => 'type_id', 'expected_value' => $product->getTypeId()],
            ['response_field' => 'id', 'expected_value' => $product->getId()],
            ['response_field' => 'name', 'expected_value' => $product->getName()],
            ['response_field' => 'weight', 'expected_value' => $product->getWeight()],
            ['response_field' => 'dynamic_price', 'expected_value' => !(bool)$product->getPriceType()],
            ['response_field' => 'dynamic_weight', 'expected_value' => !(bool)$product->getWeightType()],
            ['response_field' => 'dynamic_sku', 'expected_value' => !(bool)$product->getSkuType()]
        ];

        $this->assertResponseFields($actualResponse, $assertionMap);
    }

    /**
     * @param ProductInterface $product
     * @param  array $actualResponse
     */
    private function assertBundleProductOptions($product, $actualResponse)
    {
        $this->assertNotEmpty(
            $actualResponse['items'],
            "Precondition failed: 'bundle product items' must not be empty"
        );
        /** @var OptionList $optionList */
        $optionList = ObjectManager::getInstance()->get(\Magento\Bundle\Model\Product\OptionList::class);
        $options = $optionList->getItems($product);
        $option = $options[0];
        /** @var \Magento\Bundle\Api\Data\LinkInterface $bundleProductLinks */
        $bundleProductLinks = $option->getProductLinks();
        $bundleProductLink = $bundleProductLinks[0];
        $childProductSku = $bundleProductLink->getSku();
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $childProduct = $productRepository->get($childProductSku);
        $this->assertCount(1, $options);
        $this->assertResponseFields(
            $actualResponse['items'][0],
            [
                'option_id' => $option->getOptionId(),
                'title' => $option->getTitle(),
                'required' =>(bool)$option->getRequired(),
                'type' => $option->getType(),
                'position' => $option->getPosition(),
                'sku' => $option->getSku()
            ]
        );
        $this->assertResponseFields(
            $actualResponse['items'][0]['options'][0],
            [
                'id' => $bundleProductLink->getId(),
                'quantity' => (int)$bundleProductLink->getQty(),
                'position' => $bundleProductLink->getPosition(),
                'is_default' => (bool)$bundleProductLink->getIsDefault(),
                'price_type' => self::KEY_PRICE_TYPE_FIXED,
                'can_change_quantity' => $bundleProductLink->getCanChangeQuantity()
            ]
        );
        $this->assertEquals(
            $childProduct->getName(),
            $actualResponse['items'][0]['options'][0]['label']
        );
        $this->assertResponseFields(
            $actualResponse['items'][0]['options'][0]['product'],
            [
                'id' => $childProduct->getId(),
                'name' => $childProduct->getName(),
                'type_id' => $childProduct->getTypeId(),
                'sku' => $childProduct->getSku()
            ]
        );
    }

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/product_with_multiple_options_1.php
     */
    public function testAndMaxMinPriceBundleProduct()
    {
        $productSku = 'bundle-product';
        $query
            = <<<QUERY
{
   products(filter: {sku: {eq: "{$productSku}"}})
   {
       items{
           id
           type_id
           ... on PhysicalProductInterface {
             weight
           }
           price {
             minimalPrice {
               amount {
                 value
                 currency
               }
               adjustments {
                 amount {
                   value
                   currency
                 }
                 code
                 description
               }
             }
             maximalPrice {
               amount {
                 value
                 currency
               }
               adjustments {
                 amount {
                   value
                   currency
                 }
                 code
                 description
               }
             }
             regularPrice {
               amount {
                 value
                 currency
               }
               adjustments {
                 amount {
                   value
                   currency
                 }
                 code
                 description
               }
             }
           }
           ... on BundleProduct {
           dynamic_sku
            dynamic_price
            dynamic_weight
            price_view
            ship_bundle_items
            items {
                options {
                    label
                    product {
                        id
                        name
                        sku
                    }
                }
            }
           }
       }
   }
}
QUERY;
        $response = $this->graphQlQuery($query);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $bundleProduct = $productRepository->get($productSku, false, null, true);
        /** @var \Magento\Framework\Pricing\PriceInfo\Base $priceInfo */
        $priceInfo = $bundleProduct->getPriceInfo();
        $priceCode = \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE;
        $minimalPrice = $priceInfo->getPrice($priceCode)->getMinimalPrice()->getValue();
        $maximalPrice = $priceInfo->getPrice($priceCode)->getMaximalPrice()->getValue();
        $this->assertEquals(
            $minimalPrice,
            $response['products']['items'][0]['price']['minimalPrice']['amount']['value']
        );
        $this->assertEquals(
            $maximalPrice,
            $response['products']['items'][0]['price']['maximalPrice']['amount']['value']
        );
    }

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/product_1.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testNonExistentFieldQtyExceptionOnBundleProduct()
    {
        $productSku = 'bundle-product';
        $query
            = <<<QUERY
{
   products(filter: {sku: {eq: "{$productSku}"}})
   {
       items{
           id
           type_id
           qty
           ... on PhysicalProductInterface {
             weight
           }

           ... on BundleProduct {
           dynamic_sku
            dynamic_price
            dynamic_weight
            price_view
            ship_bundle_items
             bundle_product_links {
               id
               name
               sku
             }
           }
       }
   }
}
QUERY;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'GraphQL response contains errors: Cannot query field "qty" on type "ProductInterface".'
        );
        $this->graphQlQuery($query);
    }

    #[
        DbIsolation(false),
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(GroupFixture::class, ['website_id' => '$website2.id$'], 'group2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$group2.id$', 'code' => 'store2'], 'store2'),
        DataFixture(ProductFixture::class, ['sku' => 'p1', 'website_ids' => [1, '$website2.id$']], 'p1'),
        DataFixture(ProductFixture::class, ['sku' => 'p2', 'website_ids' => [1, '$website2.id$']], 'p2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$p1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$p2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            ['sku' => 'bundle-product', '_options' => ['$opt1$', '$opt2$'], 'website_ids' => [1, '$website2.id$']]
        ),
    ]
    public function testBundleProductWithDisabledProductOption()
    {
        /** @var StoreManagerInterface $storeManager */
        $storeManager = ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $storeIdDefault = $storeManager->getDefaultStoreView()->getId();
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $simpleProduct = $productRepository->get('p1', true, $storeIdDefault, true);
        $simpleProduct->setStatus(ProductStatus::STATUS_DISABLED);
        $simpleProduct->setStoreIds([$storeIdDefault]);
        $productRepository->save($simpleProduct);

        $productSku = 'bundle-product';
        $query
            = <<<QUERY
{
   products(filter: {sku: {eq: "{$productSku}"}})
   {
       items{
           sku
           type_id
           id
           name
           ... on PhysicalProductInterface {
             weight
           }
           ... on BundleProduct {
           dynamic_sku
            dynamic_price
            dynamic_weight
            price_view
            ship_bundle_items
            items {
              option_id
              title
              required
              type
              position
              sku
              options {
                id
                quantity
                position
                is_default
                price
                price_type
                can_change_quantity
                label
                product {
                  id
                  name
                  sku
                  type_id
                   }
                }
            }
           }
       }
   }
}
QUERY;

        $response = $this->graphQlQuery($query, [], '', ['Store' => 'default']);
        $this->assertEmpty($response['products']['items']);
        $response = $this->graphQlQuery($query, [], '', ['Store' => 'store2']);
        $this->assertNotEmpty($response['products']['items']);
        $this->assertEquals($productSku, $response['products']['items'][0]['sku']);
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 10], 'p1'),
        DataFixture(ProductFixture::class, ['price' => 20], 'p2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$p1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$p2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            ['id' => 3, 'sku' => '4bundle-product','_options' => ['$opt1$', '$opt2$']],
            '4bundle-product'
        ),
        DataFixture(
            BundleProductFixture::class,
            ['id' => 4, 'sku' => '5bundle-product','_options' => ['$opt1$', '$opt2$']],
            '5bundle-product'
        ),
    ]
    public function testBundleProductHavingSKUAsNextBundleProductId()
    {

        $productSku = '4bundle-product';
        $query
            = <<<QUERY
{
   products(filter: {sku: {eq: "{$productSku}"}})
   {
       items{
            id
           type_id
           name
           sku
           ... on BundleProduct {
            items {
              option_id
              title
              required
              type
              position
              sku
              options {
                id
                quantity
                position
                is_default
                price
                price_type
                can_change_quantity
                label
                product {
                  id
                  name
                  sku
                  type_id
                   }
                }
            }
           }
       }
   }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertNotEmpty($response['products']['items']);
    }
}
