<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Swatches;

use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Swatches\Helper\Media as SwatchesMedia;
use Magento\Swatches\Model\Swatch;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Class ProductSwatchDataTest
 */
class ProductSwatchDataTest extends GraphQlAbstract
{
    /**
     * @var SwatchesMedia
     */
    private $swatchMediaHelper;

    /**
     * @var UrlBuilder
     */
    private $imageUrlBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->swatchMediaHelper = $objectManager->get(SwatchesMedia::class);
        $this->imageUrlBuilder = $objectManager->get(UrlBuilder::class);
    }

    /**
     * @param string $productSku
     *
     * @return mixed
     * @throws \PHPUnit\Framework\Exception
     */
    private function getSwatchDataValues($productSku = 'configurable')
    {
        $query = <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}) {
    items {
        ... on ConfigurableProduct{    
      configurable_options{
          values {
            swatch_data{
              type
              value
              thumbnail
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

        return $option['values'];
    }

    /**
     * @magentoApiDataFixture Magento/Swatches/_files/visual_swatch_attribute_with_enabled_product_image_for_swatch.php
     */
    public function testGetSwatchDataForVisualOptionsWithProductImage()
    {
        $productSku = 'configurable_12345';
        $productImage = '/m/a/magento_image.jpg';
        $swatchImageName = '/visual_swatch_attribute_option_type_image.jpg';
        $expectedValues = [
            0 => [
                'swatch_data' => [
                    'type' => 'IMAGE',
                    'value' => $this->imageUrlBuilder->getUrl($productImage, Swatch::SWATCH_IMAGE_NAME),
                    'thumbnail' => $this->imageUrlBuilder->getUrl($productImage, Swatch::SWATCH_THUMBNAIL_NAME),
                ],
            ],
            1 => [
                'swatch_data' => [
                    'type' => 'IMAGE',
                    'value' => $this->swatchMediaHelper->getSwatchAttributeImage(Swatch::SWATCH_IMAGE_NAME, $swatchImageName),
                    'thumbnail' => $this->swatchMediaHelper->getSwatchAttributeImage(Swatch::SWATCH_THUMBNAIL_NAME, $swatchImageName),
                ],
            ],
            2 => [
                'swatch_data' => NULL,
            ],
        ];

        $values = $this->getSwatchDataValues($productSku);
        $this->assertEquals($values, $expectedValues);
    }

    /**
     * @magentoApiDataFixture Magento/Swatches/_files/textual_swatch_attribute.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     */
    public function testGetSwatchDataForTextualOptions()
    {
        $expectType = "TEXTUAL";
        $expectValue = "option 1";
        $expectThumbnail = null;

        $values = $this->getSwatchDataValues();
        $this->assertArrayHasKey(0, $values);

        $value = $values[0];
        $this->assertArrayHasKey('swatch_data', $value);
        $this->assertEquals($expectType, $value['swatch_data']['type']);
        $this->assertEquals($expectValue, $value['swatch_data']['value']);
        $this->assertEquals($expectThumbnail, $value['swatch_data']['thumbnail']);
    }

    /**
     * @magentoApiDataFixture Magento/Swatches/_files/visual_swatch_attribute_with_different_options_type.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     */
    public function testGetSwatchDataForVisualOptions()
    {
        $imageName = '/visual_swatch_attribute_option_type_image.jpg';
        $expectedValues = [
            0 => [
                'swatch_data' => [
                    'type' => 'COLOR',
                    'value' => '#000000',
                    'thumbnail' => NULL,
                    ],
                ],
            1 => [
                'swatch_data' => [
                    'type' => 'IMAGE',
                    'value' => $this->swatchMediaHelper->getSwatchAttributeImage(Swatch::SWATCH_IMAGE_NAME, $imageName),
                    'thumbnail' => $this->swatchMediaHelper->getSwatchAttributeImage(Swatch::SWATCH_THUMBNAIL_NAME, $imageName),
                    ],
                ],
            2 => [
                    'swatch_data' => NULL,
                ],
        ];

        $values = $this->getSwatchDataValues();
        $this->assertEquals($values, $expectedValues);
    }
}
