<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\App;

use Magento\GraphQl\App\State\GraphQlStateDiff;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;

/**
 * Tests the dispatch method in the GraphQl Controller class using a simple product query
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @magentoAppArea graphql
 */
class GraphQlStateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var GraphQlStateDiff|null
     */
    private ?GraphQlStateDiff $graphQlStateDiff;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        if (!class_exists(GraphQlStateDiff::class)) {
            $this->markTestSkipped('GraphQlStateDiff class is not available on this version of Magento.');
        }

        $this->graphQlStateDiff = new GraphQlStateDiff();
        parent::setUp();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->graphQlStateDiff->tearDown();
        $this->graphQlStateDiff = null;
        parent::tearDown();
    }

    /**
     * Runs various GraphQL queries and checks if state of shared objects in Object Manager have changed
     * @dataProvider queryDataProvider
     * @param string $query
     * @param array $variables
     * @param array $variables2  This is the second set of variables to be used in the second request
     * @param array $authInfo
     * @param string $operationName
     * @param string $expected
     * @magentoDataFixture Magento/Store/_files/store.php
     * @magentoDataFixture Magento/Catalog/_files/multiple_mixed_products.php
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @return void
     * @throws \Exception
     */
    public function testState(
        string $query,
        array $variables,
        array $variables2,
        array $authInfo,
        string $operationName,
        string $expected,
    ): void {
        $this->graphQlStateDiff
            ->testState($query, $variables, $variables2, $authInfo, $operationName, $expected, $this);
    }

    /**
     * @magentoConfigFixture default_store catalog/seo/product_url_suffix test_product_suffix
     * @magentoConfigFixture default_store catalog/seo/category_url_suffix test_category_suffix
     * @magentoConfigFixture default_store catalog/seo/title_separator ___
     * @magentoConfigFixture default_store catalog/frontend/list_mode 2
     * @magentoConfigFixture default_store catalog/frontend/grid_per_page_values 16
     * @magentoConfigFixture default_store catalog/frontend/list_per_page_values 8
     * @magentoConfigFixture default_store catalog/frontend/grid_per_page 16
     * @magentoConfigFixture default_store catalog/frontend/list_per_page 8
     * @magentoConfigFixture default_store catalog/frontend/default_sort_by asc
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @dataProvider cartQueryProvider
     * @param string $query
     * @param array $variables
     * @param array $variables2
     * @param array $authInfo
     * @param string $operationName
     * @param string $expected
     * @return void
     */
    public function testCartState(
        string $query,
        array $variables,
        array $variables2,
        array $authInfo,
        string $operationName,
        string $expected
    ): void {
        if ($operationName == 'getCart') {
            $this->getMaskedQuoteIdByReservedOrderId =
                $this->graphQlStateDiff->getTestObjectManager()->get(GetMaskedQuoteIdByReservedOrderId::class);
            $variables['id'] = $this->getMaskedQuoteIdByReservedOrderId->execute($variables['id']);
        }
        $this->graphQlStateDiff
            ->testState($query, $variables, $variables2, $authInfo, $operationName, $expected, $this);
    }

    /**
     * Queries, variables, operation names, and expected responses for test
     *
     * @return array[]
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private static function queryDataProvider(): array
    {
        return [
            'Get Navigation Menu by category_id' => [
                <<<'QUERY'
                query navigationMenu($id: Int!) {
                    category(id: $id) {
                        id
                        name
                        product_count
                        path
                        children {
                            id
                            name
                            position
                            level
                            url_key
                            url_path
                            product_count
                            children_count
                            path
                            productImagePreview: products(pageSize: 1) {
                                items {
                                    small_image {
                                        label
                                        url
                                    }
                                }
                            }
                        }
                    }
                }
                QUERY,
                ['id' => 4],
                [],
                [],
                'navigationMenu',
                '"id":4,"name":"Category 1.1","product_count":2,'
            ],
            'Get Product Search by product_name' => [
                <<<'QUERY'
                query productDetailByName($name: String, $onServer: Boolean!) {
                    products(filter: { name: { match: $name } }) {
                        items {
                            id
                            sku
                            name
                            ... on ConfigurableProduct {
                                configurable_options {
                                    attribute_code
                                    attribute_id
                                    id
                                    label
                                    values {
                                        default_label
                                        label
                                        store_label
                                        use_default_value
                                        value_index
                                    }
                                }
                                variants {
                                    product {
                                        #fashion_color
                                        #fashion_size
                                        id
                                        media_gallery_entries {
                                            disabled
                                            file
                                            label
                                            position
                                        }
                                        sku
                                        stock_status
                                    }
                                }
                            }
                            meta_title @include(if: $onServer)
                            meta_keyword @include(if: $onServer)
                            meta_description @include(if: $onServer)
                        }
                    }
                }
                QUERY,
                ['name' => 'Configurable%20Product', 'onServer' => false],
                [],
                [],
                'productDetailByName',
                '"sku":"configurable","name":"Configurable Product"'
            ],
            'Get List of Products by category_id' => [
                <<<'QUERY'
                query category($id: Int!, $currentPage: Int, $pageSize: Int) {
                    category(id: $id) {
                        product_count
                        description
                        url_key
                        name
                        id
                        breadcrumbs {
                            category_name
                            category_url_key
                            __typename
                        }
                        products(pageSize: $pageSize, currentPage: $currentPage) {
                            total_count
                            items {
                                id
                                name
                                # small_image
                                # short_description
                                url_key
                                special_price
                                special_from_date
                                special_to_date
                                price {
                                    regularPrice {
                                        amount {
                                            value
                                            currency
                                            __typename
                                        }
                                        __typename
                                    }
                                    __typename
                                }
                                __typename
                            }
                            __typename
                        }
                    __typename
                    }
                }
                QUERY,
                ['id' => 4, 'currentPage' => 1, 'pageSize' => 12],
                [],
                [],
                'category',
                '"url_key":"category-1-1","name":"Category 1.1"'
            ],
            'Get Simple Product Details by name' => [
                <<<'QUERY'
                query productDetail($name: String, $onServer: Boolean!) {
                    productDetail: products(filter: { name: { match: $name } }) {
                        items {
                            sku
                            name
                            price {
                                regularPrice {
                                    amount {
                                        currency
                                        value
                                    }
                                }
                            }
                            description {html}
                            media_gallery_entries {
                                label
                                position
                                disabled
                                file
                            }
                            ... on ConfigurableProduct {
                                configurable_options {
                                    attribute_code
                                    attribute_id
                                    id
                                    label
                                    values {
                                        default_label
                                        label
                                        store_label
                                        use_default_value
                                        value_index
                                    }
                                }
                                variants {
                                    product {
                                        id
                                        media_gallery_entries {
                                            disabled
                                            file
                                            label
                                            position
                                        }
                                        sku
                                        stock_status
                                    }
                                }
                            }
                            meta_title @include(if: $onServer)
                            # Yes, Products have `meta_keyword` and
                            # everything else has `meta_keywords`.
                            meta_keyword @include(if: $onServer)
                            meta_description @include(if: $onServer)
                        }
                    }
                }
                QUERY,
                ['name' => 'Simple Product1', 'onServer' => false],
                [],
                [],
                'productDetail',
                '"sku":"simple1","name":"Simple Product1"'
            ],
            'Get Url Info by url_key' => [
                <<<'QUERY'
                query resolveUrl($urlKey: String!) {
                    urlResolver(url: $urlKey) {
                        type
                        id
                    }
                }
                QUERY,
                ['urlKey' => 'no-route'],
                [],
                [],
                'resolveUrl',
                '"type":"CMS_PAGE","id":1'
            ],
            'Get available Stores' => [
                <<<'QUERY'
                query availableStores($currentGroup: Boolean!) {
                    availableStores(useCurrentGroup:$currentGroup) {
                        id,
                        code,
                        store_code,
                        store_name,
                        store_sort_order,
                        is_default_store,
                        store_group_code,
                        store_group_name,
                        is_default_store_group,
                        website_id,
                        website_code,
                        website_name,
                        locale,
                        base_currency_code,
                        default_display_currency_code,
                        timezone,
                        weight_unit,
                        base_url,
                        base_link_url,
                        base_media_url,
                        secure_base_url,
                        secure_base_link_url,
                        secure_base_static_url,
                        secure_base_media_url,
                        store_name
                        use_store_in_url
                    }
                }
                QUERY,
                ['currentGroup' => true],
                [],
                [],
                'availableStores',
                '"store_code":"default"'
            ],
            'Get store config' => [
                <<<'QUERY'
                query {
                    storeConfig {
                        product_url_suffix,
                        category_url_suffix,
                        title_separator,
                        list_mode,
                        grid_per_page_values,
                        list_per_page_values,
                        grid_per_page,
                        list_per_page,
                        catalog_default_sort_by,
                        root_category_id
                        root_category_uid
                    }
                }
                QUERY,
                [],
                [],
                [],
                'storeConfig',
                '"storeConfig":{"product_url_suffix":".html"'
            ],
            'Get Categories by name' => [
                <<<'QUERY'
                query categories($name: String!) {
                    categories(filters: {name: {match: $name}}
                            pageSize: 3
                            currentPage: 3
                          ) {
                            total_count
                            page_info {
                              current_page
                              page_size
                              total_pages

                          }
                        items {
                          name
                        }
                    }
                }
                QUERY,
                ['name' => 'Category'],
                [],
                [],
                'categories',
                '"data":{"categories"'
            ],
            'Get Products by name' => [
                <<<'QUERY'
                query products($name: String!) {
                    products(
                        search: $name
                        filter: {}
                        pageSize: 1000
                        currentPage: 1
                        sort: {name: ASC}
                      ) {

                    items {
                            name
                            image
                            {
                                url
                            }
                      attribute_set_id
                      canonical_url
                      color
                      country_of_manufacture
                      created_at
                      gift_message_available
                      id
                      manufacturer
                      meta_description
                      meta_keyword
                      meta_title
                      new_from_date
                      new_to_date
                      only_x_left_in_stock
                      options_container
                      rating_summary
                      review_count
                      sku
                      special_price
                      special_to_date
                      stock_status
                      swatch_image
                      uid
                      updated_at
                      url_key
                      url_path
                      url_suffix

                    }
                    page_info {
                      current_page
                      page_size
                      total_pages
                    }
                    sort_fields {
                      default
                    }
                    suggestions {
                      search
                    }
                    total_count
                  }
                }
                QUERY,
                ['name' => 'Simple Product1'],
                [],
                [],
                'products',
                '"data":{"products":{"items":[{'
            ],
        ];
    }

    public static function cartQueryProvider(): array
    {
        return [
            'Get Cart' => [
                <<<'QUERY'
                query cart($id: String!) {
                    cart(cart_id: $id) {
                        applied_coupons {
                          code
                        }

                        available_payment_methods {
                          code
                          is_deferred
                          title
                        }
                        billing_address {
                          city
                          company
                          firstname
                          lastname
                          postcode
                          street
                          telephone
                          uid
                          vat_id
                        }
                        email
                        id
                        is_virtual
                        items {
                          id
                          quantity
                          uid
                        }
                        selected_payment_method {
                          code
                          purchase_order_number
                          title
                        }
                        total_quantity
                    }
                }
                QUERY,
                ['id' => 'test_quote'],
                [],
                [],
                'getCart',
                '"cart":{"applied_coupons":null,"available_payment_methods":[{"code":"checkmo"'
            ],
        ];
    }
}
