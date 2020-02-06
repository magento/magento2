<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\ConfigurableProduct;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Class ConfigurableProductFrontendLabelAttributeTest
 *
 * @package Magento\GraphQl\ConfigurableProduct
 */
class ConfigurableProductFrontendLabelAttributeTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_with_frontend_label_attribute.php
     */
    public function testGetFrontendLabelAttribute()
    {
        $expectLabelValue = 'Default Store View label';
        $productSku = 'configurable';

        $query = <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}) {
    items {
      name
        ... on ConfigurableProduct{
        configurable_options{
          id
          label
        }
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('products', $response);
        $this->assertArrayHasKey('items', $response['products']);
        $this->assertArrayHasKey(0, $response['products']['items']);
        
        $product = $response['products']['items'][0];
        $this->assertArrayHasKey('configurable_options', $product);
        $this->assertArrayHasKey(0, $product['configurable_options']);
        $this->assertArrayHasKey('label', $product['configurable_options'][0]);

        $option = $product['configurable_options'][0];
        $this->assertEquals($expectLabelValue, $option['label']);
    }
}
