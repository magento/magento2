<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\ConfigurableProduct;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test configurable product queries work correctly with multiple websites
 */
class ConfigurableProductMultipleStoreViewTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_in_multiple_websites.php
     */
    public function testConfigurableProductAssignedToOneWebsite()
    {
        $headerMapFirstStore['Store'] = 'default';
        $headerMapSecondStore['Store'] = 'fixture_second_store';
        $parentSku = 'configurable';
        $query = $this->getQuery($parentSku);
        $responseForFirstWebsite = $this->graphQlQuery($query, [], '', $headerMapFirstStore);
        $responseForSecondWebsite = $this->graphQlQuery($query, [], '', $headerMapSecondStore);

        $secondWebsiteVariants = $responseForSecondWebsite['products']['items'][0]['variants'];
        self::assertEmpty($responseForFirstWebsite['products']['items']);
        self::assertEquals(2, count($secondWebsiteVariants));
        self::assertContains('simple_10', $secondWebsiteVariants[0]['product']);
        self::assertContains('Option 1', $secondWebsiteVariants[0]['attributes'][0]);
        self::assertContains('simple_20', $secondWebsiteVariants[1]['product']);
        self::assertContains('Option 2', $secondWebsiteVariants[1]['attributes'][0]);
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
                            attributes {
                                code
                                label
                                value_index
                            }
                        }
                    }
                }
            }
        }
QUERY;
    }
}
