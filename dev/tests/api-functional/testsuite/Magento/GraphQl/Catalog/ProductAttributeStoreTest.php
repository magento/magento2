<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class ProductAttributeStoreTest extends GraphQlAbstract
{
    /**
     * Test that custom attribute labels are returned respecting store
     *
     * @magentoApiDataFixture Magento/Store/_files/store.php
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_attribute_store_options.php
     * @throws LocalizedException
     */
    public function testAttributeStoreLabels(): void
    {
        $this->attributeLabelTest('Test Configurable Default Store');
        $this->attributeLabelTest('Test Configurable Test Store', ['Store' => 'test']);
    }

    /**
     * @param $expectedLabel
     * @param array $headers
     * @throws LocalizedException
     * @throws Exception
     */
    private function attributeLabelTest($expectedLabel, array $headers = []): void
    {
        $query = <<<QUERY
{
    products(search:"Simple",
         pageSize: 3
         currentPage: 1
       )
  {
    aggregations
    {
        attribute_code
        label
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query, [], '', $headers);
        $this->assertNotEmpty($response['products']['aggregations']);
        $attributes = $response['products']['aggregations'];
        foreach ($attributes as $attribute) {
            if ($attribute['attribute_code'] === 'test_configurable') {
                $this->assertEquals($expectedLabel, $attribute['label']);
            }
        }
    }
}
