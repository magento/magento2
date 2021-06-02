<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class ProductHasOptionsTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_custom_options.php
     */
    public function testHasOptionsAndRequiredOptionsAttribute()
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

        self::assertArrayHasKey('has_options', $response['products']['items'][0]);
        self::assertArrayHasKey('required_options', $response['products']['items'][0]);
    }
}
