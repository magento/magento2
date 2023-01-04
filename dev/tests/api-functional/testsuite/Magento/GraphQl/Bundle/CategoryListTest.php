<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Bundle;

use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogInventory\Model\Configuration as CatalogInventoryConfiguration;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class CategoryListTest extends GraphQlAbstract
{
    #[
        ConfigFixture(CatalogInventoryConfiguration::XML_PATH_SHOW_OUT_OF_STOCK, '1', 'store', 'default'),
        DataFixture(CategoryFixture::class, ['url_path' => 'cat1'], 'cat1'),
        DataFixture(ProductFixture::class, ['sku' => 's1', 'stock_item' => ['is_in_stock' => false]], 's1'),
        DataFixture(ProductFixture::class, ['sku' => 's2'], 's2'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$s1.sku$', 'price' => 10, 'price_type' => 0], 'link1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$s2.sku$', 'price' => 20, 'price_type' => 0], 'link2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$link1$', '$link2$']], 'opt1'),
        DataFixture(
            BundleProductFixture::class,
            ['sku' => 'bundle1', 'category_ids' => ['$cat1.id$'], '_options' => ['$opt1$']],
            'bundle1'
        ),
    ]
    public function testOutOfStockBundleSelectionWithEnabledShowOutOfStock(): void
    {
        $query = $this->getQuery('cat1');
        $response = $this->graphQlQuery($query);
        self::assertNotEmpty($response['categoryList']);
        $categoryList = $response['categoryList'][0];
        self::assertNotEmpty($categoryList['products']['items']);
        $bundle = $categoryList['products']['items'][0];
        self::assertEquals('bundle1', $bundle['sku']);
        self::assertCount(2, $bundle['items'][0]['options']);
        self::assertEquals('s1', $bundle['items'][0]['options'][0]['product']['sku']);
        self::assertEquals('s2', $bundle['items'][0]['options'][1]['product']['sku']);
    }

    /**
     * @param string $urlPath
     * @return string
     */
    private function getQuery(string $urlPath): string
    {
        $query = <<<QUERY
{
    categoryList(filters: {url_path: {eq: "$urlPath"}}) {
        id
        name
        products {
            total_count
            items {
                sku
                ... on BundleProduct {
                    items {
                        options {
                            product {
                                sku
                            }
                        }
                    }
                }
            }
        }
    }
}
QUERY;

        return $query;
    }
}
