<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\App;

use Magento\Framework\App\Http as HttpApp;
use Magento\Framework\App\Request\HttpFactory as RequestFactory;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\ObjectManagerInterface;
use Magento\GraphQl\App\State\Comparator;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests the dispatch method in the GraphQl Controller class using a simple product query
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @magentoAppArea graphql
 * @magentoDataFixture Magento/Catalog/_files/multiple_mixed_products.php
 * @magentoDataFixture Magento/Catalog/_files/categories.php
 *
 */
class GraphQlStateTest extends \PHPUnit\Framework\TestCase
{
    private const CONTENT_TYPE = 'application/json';

    /** @var ObjectManagerInterface */
    private ObjectManagerInterface $objectManager;

    /** @var Comparator */
    private Comparator $comparator;

    /** @var RequestFactory */
    private RequestFactory $requestFactory;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->comparator = $this->objectManager->create(Comparator::class);
        $this->requestFactory = $this->objectManager->get(RequestFactory::class);
        parent::setUp();
    }

    /**
     * Runs various GraphQL queries and checks if state of shared objects in Object Manager have changed
     *
     * @dataProvider queryDataProvider
     * @param string $query
     * @param array $variables
     * @param string $operationName
     * @param string $expected
     * @return void
     * @throws \Exception
     */
    public function testState(string $query, array $variables, string $operationName, string $expected): void
    {
        $jsonEncodedRequest = json_encode([
            'query' => $query,
            'variables' => $variables,
            'operationName' => $operationName
        ]);
        $output1 = $this->request($jsonEncodedRequest, $operationName, true);
        $this->assertStringContainsString($expected, $output1);
        $output2 = $this->request($jsonEncodedRequest, $operationName);
        $this->assertStringContainsString($expected, $output2);
        $this->assertEquals($output1, $output2);
    }

    /**
     * @param string $query
     * @param string $operationName
     * @param bool $firstRequest
     * @return string
     * @throws \Exception
     */
    private function request(string $query, string $operationName, bool $firstRequest = false): string
    {
        $this->comparator->rememberObjectsStateBefore($firstRequest);
        $response = $this->doRequest($query);
        $this->comparator->rememberObjectsStateAfter($firstRequest);
        $result = $this->comparator->compare($operationName);
        $this->assertEmpty(
            $result,
            sprintf(
                '%d objects changed state during request. Details: %s',
                count($result),
                var_export($result, true)
            )
        );
        return $response;
    }

    /**
     * Process the GraphQL request
     *
     * @param string $query
     * @return string
     */
    private function doRequest(string $query)
    {
        $request = $this->requestFactory->create();
        $request->setContent($query);
        $request->setMethod('POST');
        $request->setPathInfo('/graphql');
        $request->getHeaders()->addHeaders(['content_type' => self::CONTENT_TYPE]);
        $unusedResponse = $this->objectManager->create(HttpResponse::class);
        $httpApp = $this->objectManager->create(
            HttpApp::class,
            ['request' => $request, 'response' => $unusedResponse]
        );
        $actualResponse = $httpApp->launch();
        return $actualResponse->getContent();
    }

    /**
     * Queries, variables, operation names, and expected responses for test
     *
     * @return array[]
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function queryDataProvider(): array
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
                'resolveUrl',
                '"type":"CMS_PAGE","id":1'
            ],
        ];
    }
}
