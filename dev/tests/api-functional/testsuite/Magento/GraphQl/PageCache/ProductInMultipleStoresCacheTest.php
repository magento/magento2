<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\PageCache;

use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * This test considers FPC caching but it can't check the headers since we don't guarantee that test if in dev mode.
 *
 * @magentoAppIsolation enabled
 */
class ProductInMultipleStoresCacheTest extends GraphQlAbstract
{
    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        /** @var \Magento\Store\Model\Store $store */
        $store =  ObjectManager::getInstance()->get(\Magento\Store\Model\Store::class);
        $storeCodeFromFixture = 'fixture_second_store';

        /** @var \Magento\Config\Model\ResourceModel\Config $configResource */
        $configResource = ObjectManager::getInstance()->get(\Magento\Config\Model\ResourceModel\Config::class);
        /** @var \Magento\Config\App\Config\Type\System $config */
        $config = ObjectManager::getInstance()->get(\Magento\Config\App\Config\Type\System::class);

        $configResource->saveConfig(
            \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_DEFAULT,
            'EUR',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
            $store->load($storeCodeFromFixture)->getWebsiteId()
        );

        // allow USD & EUR currency
        $configResource->saveConfig(
            \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_ALLOW,
            'EUR,USD',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
            $store->load($storeCodeFromFixture)->getWebsiteId()
        );

        // allow USD & EUR currency
        $configResource->saveConfig(
            \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_ALLOW,
            'EUR,USD'
        );

        // configuration cache clean is required to reload currency setting
        $config->clean();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        /** @var \Magento\Config\App\Config\Type\System $config */
        $config = ObjectManager::getInstance()->get(\Magento\Config\App\Config\Type\System::class);
        /** @var \Magento\Config\Model\ResourceModel\Config $configResource */
        $configResource = ObjectManager::getInstance()->get(\Magento\Config\Model\ResourceModel\Config::class);

        // restore allow USD currency
        $configResource->saveConfig(
            \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_ALLOW,
            'USD'
        );

        // configuration cache clean is required to reload currency setting
        $config->clean();
        parent::tearDown();
    }

    /**
     * Test a non existing or non existing currency
     *
     * @magentoApiDataFixture Magento/Store/_files/second_website_with_second_currency.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testProductFromSpecificAndDefaultStoreWithMultiCurrencyNonExisting()
    {
        $productSku = 'simple';

        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items {
            attribute_set_id
            created_at
            id
            name
            price {
                minimalPrice {
                    amount {
                        value
                        currency
                    }
                }
            }
            sku
            type_id
            updated_at
            ... on PhysicalProductInterface {
                weight
            }
        }
    }
}
QUERY;

        //test non existing currency
        $headerMap = ['Store' => 'default', 'Content-Currency' => 'someNonExistentCurrency'];
        $this->expectExceptionMessage('GraphQL response contains errors: Please correct the target currency');
        $this->graphQlQuery($query, [], '', $headerMap);
    }

    /**
     * Test a non existing or non allowed currency
     *
     * @magentoApiDataFixture Magento/Store/_files/second_website_with_second_currency.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testProductFromSpecificAndDefaultStoreWithMultiCurrencyNotAllowed()
    {
        $productSku = 'simple';

        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items {
            attribute_set_id
            created_at
            id
            name
            price {
                minimalPrice {
                    amount {
                        value
                        currency
                    }
                }
            }
            sku
            type_id
            updated_at
            ... on PhysicalProductInterface {
                weight
            }
        }
    }
}
QUERY;

        $storeCodeFromFixture = 'fixture_second_store';

        //test not allowed existing currency
        $headerMap = ['Store' => $storeCodeFromFixture, 'Content-Currency' => 'CAD'];
        $this->expectExceptionMessage(
            'GraphQL response contains errors: Please correct the target currency'
        );
        $this->graphQlQuery($query, [], '', $headerMap);
    }

    /**
     * Test a product from a custom and default store, with cache with repeating queries asserting different results.
     *
     * @magentoApiDataFixture Magento/Store/_files/second_website_with_second_currency.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testProductFromSpecificAndDefaultStoreWithMultiCurrency()
    {
        $productSku = 'simple';

        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items {
            attribute_set_id
            created_at
            id
            name
            price {
                minimalPrice {
                    amount {
                        value
                        currency
                    }
                }
            }
            sku
            type_id
            updated_at
            ... on PhysicalProductInterface {
                weight
            }
        }
    }
}
QUERY;

        /** @var \Magento\Store\Model\Store $store */
        $store =  ObjectManager::getInstance()->get(\Magento\Store\Model\Store::class);
        $storeCodeFromFixture = 'fixture_second_store';
        $storeId = $store->load($storeCodeFromFixture)->getStoreId();

        /** @var \Magento\Catalog\Model\Product $product */
        $product = ObjectManager::getInstance()->get(\Magento\Catalog\Model\Product::class);
        $product->load($product->getIdBySku($productSku));

        $website = ObjectManager::getInstance()->get(\Magento\Store\Model\Website::class);
        /** @var $website \Magento\Store\Model\Website */
        $website->load('test', 'code');
        $product->setWebsiteIds([1, $website->getId()]);

        // change product name for custom store
        $productNameInFixtureStore = 'Product\'s Name in Fixture Store';
        $product->setName($productNameInFixtureStore)->setStoreId($storeId)->save();

        // test store header only, query is cached at this point in EUR
        $headerMap = ['Store' => $storeCodeFromFixture];
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertEquals(
            $productNameInFixtureStore,
            $response['products']['items'][0]['name'],
            'Product name in fixture store is invalid.'
        );
        $this->assertEquals(
            'EUR',
            $response['products']['items'][0]['price']['minimalPrice']['amount']['currency'],
            'Currency code EUR in fixture ' . $storeCodeFromFixture . ' is unexpected'
        );

        // test cached store + currency header in Euros
        $headerMap = ['Store' => $storeCodeFromFixture, 'Content-Currency' => 'EUR'];
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertEquals(
            $productNameInFixtureStore,
            $response['products']['items'][0]['name'],
            'Product name in fixture ' . $storeCodeFromFixture . ' is invalid.'
        );
        $this->assertEquals(
            'EUR',
            $response['products']['items'][0]['price']['minimalPrice']['amount']['currency'],
            'Currency code EUR in fixture ' . $storeCodeFromFixture . ' is unexpected'
        );

        // test non cached store + currency header in USD
        $headerMap = ['Store' => $storeCodeFromFixture, 'Content-Currency' => 'USD'];
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertEquals(
            $productNameInFixtureStore,
            $response['products']['items'][0]['name'],
            'Product name in fixture store is invalid.'
        );
        $this->assertEquals(
            'USD',
            $response['products']['items'][0]['price']['minimalPrice']['amount']['currency'],
            'Currency code USD in fixture ' . $storeCodeFromFixture . ' is unexpected'
        );

        // test non cached store + currency header in USD not cached
        $headerMap = ['Store' => 'default', 'Content-Currency' => 'USD'];
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertEquals(
            'Simple Product',
            $response['products']['items'][0]['name'],
            'Product name in fixture store is invalid.'
        );
        $this->assertEquals(
            'USD',
            $response['products']['items'][0]['price']['minimalPrice']['amount']['currency'],
            'Currency code USD in fixture store default is unexpected'
        );

        // test non cached store + currency header in USD not cached
        $headerMap = ['Store' => 'default', 'Content-Currency' => 'EUR'];
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertEquals(
            'Simple Product',
            $response['products']['items'][0]['name'],
            'Product name in fixture store is invalid.'
        );
        $this->assertEquals(
            'EUR',
            $response['products']['items'][0]['price']['minimalPrice']['amount']['currency'],
            'Currency code EUR in fixture store default is unexpected'
        );

        // test non cached store + currency header in USD  cached
        $headerMap = ['Store' => 'default'];
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertEquals(
            'Simple Product',
            $response['products']['items'][0]['name'],
            'Product name in fixture store is invalid.'
        );
        $this->assertEquals(
            'USD',
            $response['products']['items'][0]['price']['minimalPrice']['amount']['currency'],
            'Currency code USD in fixture store default is unexpected'
        );

        // test cached response store + currency header with non existing currency, and no valid response, no cache
        $headerMap = ['Store' => $storeCodeFromFixture, 'Content-Currency' => 'SOMECURRENCY'];
        $this->expectExceptionMessage(
            'GraphQL response contains errors: Please correct the target currency'
        );
        $this->graphQlQuery($query, [], '', $headerMap);
    }
}
