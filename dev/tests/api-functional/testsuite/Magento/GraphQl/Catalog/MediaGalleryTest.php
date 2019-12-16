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
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_multiple_images.php
     */
    public function testMediaGallery()
    {
        $productSku = 'simple';
        $query = <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}) {
    items {    
      media_gallery {
      	label
        url
      }
    }
  }    
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertNotEmpty($response['products']['items'][0]['media_gallery']);
        $mediaGallery = $response['products']['items'][0]['media_gallery'];
        $this->assertCount(2, $mediaGallery);
        $this->assertEquals('Image Alt Text', $mediaGallery[0]['label']);
        self::assertTrue($this->checkImageExists($mediaGallery[0]['url']));
        $this->assertEquals('Thumbnail Image', $mediaGallery[1]['label']);
        self::assertTrue($this->checkImageExists($mediaGallery[1]['url']));
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_media_gallery_entries.php
     */
    public function testMediaGalleryForProductVideos()
    {
        $productSku = 'simple';
        $query = <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}) {
    items {    
      media_gallery {
      	label
        url
        ... on ProductVideo {
              video_content {
                  media_type
                  video_provider
                  video_url
                  video_title
                  video_description
                  video_metadata
              }
          }
      }
    }
  }    
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertNotEmpty($response['products']['items'][0]['media_gallery']);
        $mediaGallery = $response['products']['items'][0]['media_gallery'];
        $this->assertCount(1, $mediaGallery);
        $this->assertEquals('Video Label', $mediaGallery[0]['label']);
        self::assertTrue($this->checkImageExists($mediaGallery[0]['url']));
        $this->assertNotEmpty($mediaGallery[0]['video_content']);
        $video_content = $mediaGallery[0]['video_content'];
        $this->assertEquals('external-video', $video_content['media_type']);
        $this->assertEquals('youtube', $video_content['video_provider']);
        $this->assertEquals('http://www.youtube.com/v/tH_2PFNmWoga', $video_content['video_url']);
        $this->assertEquals('Video title', $video_content['video_title']);
        $this->assertEquals('Video description', $video_content['video_description']);
        $this->assertEquals('Video Metadata', $video_content['video_metadata']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testProductMediaGalleryEntries()
    {
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
        self::assertContains(
            'magento_image.jpg',
            $response['products']['items'][0]['media_gallery_entries'][0]['file']
        );
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
        return $responseStatus === 200;
    }
}
