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
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_children_on_different_websites.php
     * @dataProvider childrenAssignedToDifferentWebsitesDataProvider
     * @param string $store
     * @param string $childSku
     * @param string $attributeLabel
     */
    public function testConfigurableProductWithChildrenAssignedToDifferentWebsites(
        string $store,
        string $childSku,
        string $attributeLabel
    ) {
        $headers = ['Store' => $store];
        $query = $this->getQuery('configurable');
        $response = $this->graphQlQuery($query, [], '', $headers);
        self::assertCount(1, $response['products']['items']);
        $product = $response['products']['items'][0];
        self::assertCount(1, $product['variants']);
        $variant = $response['products']['items'][0]['variants'][0];
        self::assertEquals($childSku, $variant['product']['sku']);
        self::assertCount(1, $variant['attributes']);
        $attribute = $variant['attributes'][0];
        self::assertEquals($attributeLabel, $attribute['label']);
    }

    /**
     * @return array
     */
    public static function childrenAssignedToDifferentWebsitesDataProvider(): array
    {
        return [
            ['default', 'simple_option_2', 'Option 2'],
            ['fixture_second_store', 'simple_option_1', 'Option 1'],
        ];
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
