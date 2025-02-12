<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\ConfigurableProduct;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Checks disabled child product is not in configurable variants.
 */
class ConfigurableProductChildStatusDisabledTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_disable_first_child.php
     * @magentoConfigFixture default_store cataloginventory/options/show_out_of_stock 1
     */
    public function testConfigurableProductChildStatusDisabled()
    {
        $parentSku = 'configurable';
        $enabledChildSku = 'simple_20';
        $disabledChildSku = 'simple_10';
        $query = $this->getQuery($parentSku);
        $response = $this->graphQlQuery($query);
        $this->assertContainsEquals(
            ['product' => ['sku' => $enabledChildSku, 'stock_status' => 'IN_STOCK']],
            $response['products']['items'][0]['variants']
        );
        $this->assertNotContainsEquals(
            ['product' => ['sku' => $disabledChildSku, 'stock_status' => 'OUT_OF_STOCK']],
            $response['products']['items'][0]['variants']
        );
    }

    /**
     * @param string $sku
     * @return string
     */
    private function getQuery(string $sku)
    {
        return <<<QUERY
        {
            products(filter: {sku: {eq: "{$sku}"}})
            {
                items {
                    sku
                    ... on ConfigurableProduct {
                        variants {
                            product {
                                sku
                                stock_status
                            }
                        }
                    }
                }
            }
        }
QUERY;
    }
}
