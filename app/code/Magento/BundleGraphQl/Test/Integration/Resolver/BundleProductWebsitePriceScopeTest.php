<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Test\Integration\Resolver;

use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Model\Product\Price as BundlePrice;
use Magento\Bundle\Test\Fixture\Link as BundleLinkFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\BundleGraphQl\Test\Fixture\BundleSelectionWebsitePrice;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Service\GraphQlRequest;
use Magento\Store\Test\Fixture\Group as GroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that GraphQL bundle option prices honor website-scoped fixed prices.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BundleProductWebsitePriceScopeTest extends TestCase
{
    private const SECOND_STORE_CODE = 'fixture_second_store';
    private const GLOBAL_OPTION_PRICE = 5000.0;
    private const WEBSITE_SCOPED_OPTION_PRICE = 55.0;

    /**
     * @var GraphQlRequest
     */
    private $graphQlRequest;

    /**
     * @var SerializerInterface
     */
    private $json;

    protected function setUp(): void
    {
        $this->graphQlRequest = Bootstrap::getObjectManager()->create(GraphQlRequest::class);
        $this->json = Bootstrap::getObjectManager()->get(SerializerInterface::class);
    }

    #[
        AppArea('graphql'),
        AppIsolation(true),
        DbIsolation(false),
        DataFixture(WebsiteFixture::class, as: 'custom_website'),
        DataFixture(GroupFixture::class, ['website_id' => '$custom_website.id$'], as: 'custom_store_group'),
        DataFixture(
            StoreFixture::class,
            ['code' => self::SECOND_STORE_CODE, 'store_group_id' => '$custom_store_group.id$'],
            as: 'custom_store'
        ),
        // Catalog\Helper\Data::isPriceGlobal() reads this path at SCOPE_STORE. Magento\TestFramework\App\Config
        // resolves the "current store" placeholder to a scope CODE at the moment setValue() runs and stores the
        // override under that literal code; it does not re-resolve "current" dynamically on read. Using "current"
        // here (before the store fixture even exists) would snapshot the *default* store's code, which never
        // matches what's actually current during GraphQL resolution (fixture_second_store, after the Store
        // header switches it) - so the override would silently miss and the real, unset value (Global) wins.
        // Pinning scopeValue explicitly to the store this test actually queries keeps the write and read keyed
        // the same, and this fixture must run after the Store fixture above so the store exists to resolve.
        Config('catalog/price/scope', 1, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, self::SECOND_STORE_CODE),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'bundle_ws_scope_simple_1', 'price' => 10, 'website_ids' => [1, '$custom_website.id$']],
            as: 'simple1'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'bundle_ws_scope_simple_2', 'price' => 10, 'website_ids' => [1, '$custom_website.id$']],
            as: 'simple2'
        ),
        DataFixture(
            BundleLinkFixture::class,
            [
                'sku' => '$simple1.sku$',
                'qty' => 1,
                'price' => self::GLOBAL_OPTION_PRICE,
                'price_type' => LinkInterface::PRICE_TYPE_FIXED,
            ],
            as: 'link1'
        ),
        DataFixture(
            BundleLinkFixture::class,
            [
                'sku' => '$simple2.sku$',
                'qty' => 1,
                'price' => self::GLOBAL_OPTION_PRICE,
                'price_type' => LinkInterface::PRICE_TYPE_FIXED,
            ],
            as: 'link2'
        ),
        DataFixture(
            BundleOptionFixture::class,
            [
                'title' => 'Bundle Option',
                'type' => 'checkbox',
                'required' => true,
                'product_links' => ['$link1$', '$link2$'],
            ],
            as: 'option1'
        ),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle_ws_scope_product',
                'website_ids' => [1, '$custom_website.id$'],
                'price_type' => BundlePrice::PRICE_TYPE_FIXED,
                'price' => 100,
                '_options' => ['$option1$'],
            ],
            as: 'bundle1'
        ),
        DataFixture(
            BundleSelectionWebsitePrice::class,
            [
                'sku' => '$bundle1.sku$',
                'website_id' => '$custom_website.id$',
                'price' => self::WEBSITE_SCOPED_OPTION_PRICE,
                'price_type' => LinkInterface::PRICE_TYPE_FIXED,
            ]
        ),
    ]
    public function testBundleOptionPriceHonorsWebsiteScopeFromStoreHeader(): void
    {
        $query = $this->getBundleOptionsQuery('bundle_ws_scope_product');

        $defaultScopeResponse = $this->json->unserialize(
            $this->graphQlRequest->send($query)->getContent()
        );
        $this->assertBundleOptionPrices($defaultScopeResponse, self::GLOBAL_OPTION_PRICE);

        $secondStoreResponse = $this->json->unserialize(
            $this->graphQlRequest->send($query, [], '', ['Store' => self::SECOND_STORE_CODE])->getContent()
        );
        $this->assertBundleOptionPrices($secondStoreResponse, self::WEBSITE_SCOPED_OPTION_PRICE);
    }

    /**
     * Build the bundle options GraphQL query for the given bundle product SKU.
     *
     * @param string $sku
     * @return string
     */
    private function getBundleOptionsQuery(string $sku): string
    {
        return <<<QUERY
        {
          products(filter: {sku: {eq: "$sku"}}) {
            items {
              sku
              __typename
              ... on BundleProduct {
                items {
                  sku
                  options {
                    price
                    label
                    product { sku }
                  }
                }
              }
            }
          }
        }
        QUERY;
    }

    /**
     * Assert that every bundle option price in the response matches the expected value.
     *
     * @param array $response
     * @param float $expectedPrice
     * @return void
     */
    private function assertBundleOptionPrices(array $response, float $expectedPrice): void
    {
        $this->assertArrayNotHasKey(
            'errors',
            $response,
            'GraphQL response contains errors: ' . json_encode($response['errors'] ?? [])
        );

        $items = $response['data']['products']['items'] ?? [];
        $this->assertNotEmpty($items, 'Products array has items');

        $bundleLinks = $items[0]['items'] ?? [];
        $this->assertNotEmpty($bundleLinks, 'Bundle items should not be empty');

        foreach ($bundleLinks as $bundleLink) {
            $this->assertNotEmpty($bundleLink['options'], 'Bundle option links should not be empty');
            foreach ($bundleLink['options'] as $option) {
                $this->assertEquals(
                    $expectedPrice,
                    $option['price'],
                    'Bundle option price does not match the expected website-scoped price'
                );
            }
        }
    }
}
