<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test class for product with multiselect attributes
 */
class ProductWithMultiselectAttributeTest extends GraphQlAbstract
{
    /**
     * Check correct displaying for multiselect attributes
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_multiselect_attribute.php
     */
    public function testQueryProductWithMultiselectAttribute()
    {
        $query = <<<QUERY
{
  products(filter: {sku: {eq: "simple_ms_2" }}) {
    items {
      name
      multiselect_attribute
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertStringContainsString(
            'Option 2, Option 3, Option 4 "!@#$%^&*',
            $response['products']['items'][0]['multiselect_attribute']
        );
    }
}
