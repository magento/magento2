<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Exception;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for checking has_options & required_options attributes in Products query.
 */
class ProductHasOptionsTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_custom_options.php
     *
     * @return void
     * @throws Exception
     */
    public function testHasOptionsAndRequiredOptionsAttribute(): void
    {
        $productSku = 'simple_with_custom_options';
        $query = <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}) {
    items {
      sku
      has_options
      required_options
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('has_options', $response['products']['items'][0]);
        $this->assertArrayHasKey('required_options', $response['products']['items'][0]);
    }
}
