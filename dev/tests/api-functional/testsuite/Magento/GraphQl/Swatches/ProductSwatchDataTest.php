<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Swatches;

use Magento\Swatches\Helper\Media as SwatchesMedia;
use Magento\Swatches\Model\Swatch;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for configurable product option swatch data
 */
class ProductSwatchDataTest extends GraphQlAbstract
{
    /**
     * @var SwatchesMedia
     */
    private $swatchMediaHelper;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->swatchMediaHelper = $objectManager->get(SwatchesMedia::class);
    }

    /**
     * @magentoApiDataFixture Magento/Swatches/_files/text_swatch_attribute.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     */
    public function testTextSwatchDataValues()
    {
        $productSku = 'configurable';
        $query = <<<QUERY
{
  products(filter: {sku: {eq: "$productSku"}}) {
    items {
        ... on ConfigurableProduct{    
      configurable_options{
          values {
            swatch_data{
              value
            }
          } 
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

        $option = $product['configurable_options'][0];
        $this->assertArrayHasKey('values', $option);
        $length = count($option['values']);
        for ($i = 0; $i < $length; $i++) {
            $this->assertEquals('option ' . ($i + 1), $option['values'][$i]['swatch_data']['value']);
        }
    }

    /**
     * @magentoApiDataFixture Magento/Swatches/_files/visual_swatch_attribute_with_different_options_type.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     */
    public function testVisualSwatchDataValues()
    {
        $productSku = 'configurable';
        $imageName = '/visual_swatch_attribute_option_type_image.jpg';
        $color = '#000000';
        $query = <<<QUERY
{
  products(filter: {sku: {eq: "$productSku"}}) {
    items {
        ... on ConfigurableProduct{    
      configurable_options{
          values {
            swatch_data{
              value
              ... on ImageSwatchData {
                thumbnail
              }
            }
          } 
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

        $option = $product['configurable_options'][0];
        $this->assertArrayHasKey('values', $option);
        $this->assertEquals($color, $option['values'][0]['swatch_data']['value']);
        $this->assertContains(
            $option['values'][1]['swatch_data']['value'],
            $this->swatchMediaHelper->getSwatchAttributeImage(Swatch::SWATCH_IMAGE_NAME, $imageName)
        );
        $this->assertEquals(
            $option['values'][1]['swatch_data']['thumbnail'],
            $this->swatchMediaHelper->getSwatchAttributeImage(Swatch::SWATCH_THUMBNAIL_NAME, $imageName)
        );
    }
}
