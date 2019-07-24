<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test cases for product media gallery data retrieval.
 */
class MediaGalleryTest extends GraphQlAbstract
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testProductSmallImageUrlWithExistingImage()
    {
        $productSku = 'simple';
        $query = <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}) {
    items {
        small_image {
            url
        }
    }
  }    
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('url', $response['products']['items'][0]['small_image']);
        self::assertContains('magento_image.jpg', $response['products']['items'][0]['small_image']['url']);
        self::assertTrue($this->checkImageExists($response['products']['items'][0]['small_image']['url']));
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_multiple_images.php
     */
    public function testMediaGalleryTypesAreCorrect()
    {
        $productSku = 'simple';
        $query = <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}) {
    items {    
      media_gallery_entries {
      	label
        media_type
        file
        types
      }
    }
  }    
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertNotEmpty($response['products']['items'][0]['media_gallery_entries']);
        $mediaGallery = $response['products']['items'][0]['media_gallery_entries'];
        $this->assertCount(2, $mediaGallery);
        $this->assertEquals('Image Alt Text', $mediaGallery[0]['label']);
        $this->assertEquals('image', $mediaGallery[0]['media_type']);
        $this->assertContains('magento_image', $mediaGallery[0]['file']);
        $this->assertEquals(['image', 'small_image'], $mediaGallery[0]['types']);
        $this->assertEquals('Thumbnail Image', $mediaGallery[1]['label']);
        $this->assertEquals('image', $mediaGallery[1]['media_type']);
        $this->assertContains('magento_thumbnail', $mediaGallery[1]['file']);
        $this->assertEquals(['thumbnail', 'swatch_image'], $mediaGallery[1]['types']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testProductMediaGalleryEntries()
    {
        $this->markTestSkipped('https://github.com/magento/graphql-ce/issues/738');
        $productSku = 'simple';
        $query = <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}) {
    items {
      name
      sku
      media_gallery_entries {
        id
        file
        types
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('file', $response['products']['items'][0]['media_gallery_entries'][0]);
        self::assertContains('magento_image.jpg', $response['products']['items'][0]['media_gallery_entries'][0]['url']);
    }

    /**
     * @param string $url
     * @return bool
     */
    private function checkImageExists(string $url): bool
    {
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        $connection = curl_init($url);
        curl_setopt($connection, CURLOPT_HEADER, true);
        curl_setopt($connection, CURLOPT_NOBODY, true);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($connection);
        $responseStatus = curl_getinfo($connection, CURLINFO_HTTP_CODE);
        // phpcs:enable Magento2.Functions.DiscouragedFunction
        return $responseStatus === 200 ? true : false;
    }
}
