<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\ConfigurableProduct;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Checks the price range of configurable products with disabled child products.
 */
class ConfigurableProductPriceRangeWithDisabledChildProductsTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_disable_first_child.php
     */
    public function testConfigurableProductPriceRangeWithDisabledChildProducts(): void
    {
        $parentSku = 'configurable';
        $enabledChildSku = 'simple_20';
        $disabledChildSku = 'simple_10';
        $enabledChildPrice = 20;
        $response = $this->graphQlQuery($this->getQuery($parentSku));
        $this->assertEquals(
            $enabledChildPrice,
            $response['products']['items'][0]['price_range']['minimum_price']['final_price']['value']
        );
        $this->assertEquals(
            $enabledChildPrice,
            $response['products']['items'][0]['price_range']['minimum_price']['regular_price']['value']
        );
        $this->assertContainsEquals(
            ['product' => ['sku' => $enabledChildSku]],
            $response['products']['items'][0]['variants']
        );
        $this->assertNotContainsEquals(
            ['product' => ['sku' => $disabledChildSku]],
            $response['products']['items'][0]['variants']
        );
    }

    /**
     * @param string $sku
     * @return string
     */
    private function getQuery(string $sku): string
    {
        return <<<QUERY
        {
            products(filter: {sku: {eq: "{$sku}"}})
            {
                items {
                    sku
                    price_range {
                        minimum_price {
                            discount {
                                amount_off
                                percent_off
                            }
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
                    ...on ConfigurableProduct {
                        variants {
                            product {
                                sku
                            }
                        }
                    }
                }
            }
        }
        QUERY;
    }
}
