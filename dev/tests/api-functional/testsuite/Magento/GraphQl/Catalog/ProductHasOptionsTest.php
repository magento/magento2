<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
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
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_options.php
     *
     * @return void
     * @throws Exception
     */
    public function testHasOptionsAndRequiredOptionsAttribute(): void
    {
        $productSku = 'simple';
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

        $item = $response['products']['items'][0];
        $this->assertSame($productSku, $item['sku']);
        $this->assertArrayHasKey('has_options', $item);
        $this->assertArrayHasKey('required_options', $item);
        $this->assertTrue($item['has_options']);
        $this->assertTrue($item['required_options']);
    }
}
