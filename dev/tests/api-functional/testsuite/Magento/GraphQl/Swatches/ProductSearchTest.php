<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Swatches;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test layered navigation filter.
 *
 * @package Magento\GraphQl\Swatches
 */
class ProductSearchTest extends GraphQlAbstract
{
    /**
     * Verify that layered navigation filters are returned for product query
     *
     * @magentoApiDataFixture Magento/Swatches/_files/products_with_layered_navigation_swatch.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFilterLn()
    {
        $query = <<<QUERY
{
    products (
        filter:{
            sku:{
                like:"%simple%"
            }
        }
        pageSize: 4
        currentPage: 1
        sort: {
            name: DESC
        }
    )
    {
        items {
            sku
        }
        filters {
            name
            filter_items_count
            request_var
            filter_items {
                label
                value_string
                items_count
                ... on SwatchLayerFilterItemInterface {
                    swatch_data {
                        type
                        value
                    }
                }
            }
        }
    }
}
QUERY;
        /**
         * @var ProductRepositoryInterface $productRepository
         */
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(
            'filters',
            $response['products'],
            'Filters are missing in product query result.'
        );
        $this->assertFilters(
            $response,
            $this->getExpectedFiltersDataSet(),
            'Returned filters data set does not match the expected value'
        );
    }

    /**
     * Get array with expected data for layered navigation filters
     *
     * @return array
     */
    private function getExpectedFiltersDataSet()
    {
        /** @var \Magento\Eav\Model\Config $eavConfig */
        $eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
        $attribute = $eavConfig->getAttribute('catalog_product', 'color_swatch');
        /** @var \Magento\Eav\Api\Data\AttributeOptionInterface[] $options */
        $options = $attribute->getOptions();
        // Fetching option ID is required for continuous debug as of autoincrement IDs.
        return [
            [
                'name' => 'Category',
                'filter_items_count' => 1,
                'request_var' => 'cat',
                'filter_items' => [
                    [
                        'label' => 'Category 1',
                        'value_string' => '333',
                        'items_count' => 3,
                    ],
                ],
            ],
            [
                'name' => 'Test Swatch',
                'filter_items_count' => 1,
                'request_var' => 'color_swatch',
                'filter_items' => [
                    [
                        'label' => 'option 1',
                        'value_string' => $options[1]->getValue(),
                        'items_count' => 2,
                        'swatch_data' => [
                            'type' => '1',
                            'value' => '#555555',
                        ],
                    ],
                ],
             ],
            [
                'name' => 'Price',
                'filter_items_count' => 3,
                'request_var' => 'price',
                'filter_items' => [
                    [
                        'label' => '<span class="price">$0.00</span> - <span class="price">$9.99</span>',
                        'value_string' => '-10',
                        'items_count' => 1,
                    ],
                    [
                        'label' => '<span class="price">$10.00</span> - <span class="price">$19.99</span>',
                        'value_string' => '10-20',
                        'items_count' => 1,
                    ],
                    [
                        'label' => '<span class="price">$20.00</span> and above',
                        'value_string' => '20-',
                        'items_count' => 1,
                    ],
                ],
            ],
        ];
    }

    /**
     * Assert filters data.
     *
     * @param array $response
     * @param array $expectedFilters
     * @param string $message
     */
    private function assertFilters($response, $expectedFilters, $message = '')
    {
        $this->assertArrayHasKey('filters', $response['products'], 'Product has filters');
        $this->assertTrue(is_array(($response['products']['filters'])), 'Product filters is array');
        $this->assertTrue(count($response['products']['filters']) > 0, 'Product filters is not empty');
        foreach ($expectedFilters as $expectedFilter) {
            $found = false;
            foreach ($response['products']['filters'] as $responseFilter) {
                if ($responseFilter['name'] == $expectedFilter['name']
                    && $responseFilter['request_var'] == $expectedFilter['request_var']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $this->fail($message);
            }
        }
    }
}
