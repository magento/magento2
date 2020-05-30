<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class ProductImageTest extends GraphQlAbstract
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testProductWithBaseImage()
    {
        $productSku = 'simple';
        $query = <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}) {
    items {
        image {
            url
            label
        }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertStringContainsString('magento_image.jpg', $response['products']['items'][0]['image']['url']);
        self::assertTrue($this->checkImageExists($response['products']['items'][0]['image']['url']));
        self::assertEquals('Image Alt Text', $response['products']['items'][0]['image']['label']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testProductWithoutBaseImage()
    {
        $productSku = 'simple';
        $query = <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}) {
    items {
        image {
            url
            label
        }
        small_image {
            url
            label
        }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        self::assertEquals('Simple Product', $response['products']['items'][0]['image']['label']);
        self::assertStringEndsWith(
            'images/product/placeholder/image.jpg',
            $response['products']['items'][0]['image']['url']
        );
        self::assertEquals('Simple Product', $response['products']['items'][0]['small_image']['label']);
        self::assertStringEndsWith(
            'images/product/placeholder/small_image.jpg',
            $response['products']['items'][0]['small_image']['url']
        );
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testProductWithSmallImage()
    {
        $productSku = 'simple';
        $query = <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}) {
    items {
        small_image {
            url
            label
        }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertStringContainsString('magento_image.jpg', $response['products']['items'][0]['small_image']['url']);
        self::assertTrue($this->checkImageExists($response['products']['items'][0]['small_image']['url']));
        self::assertEquals('Image Alt Text', $response['products']['items'][0]['small_image']['label']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testProductWithThumbnail()
    {
        $productSku = 'simple';
        $query = <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}) {
    items {
        thumbnail {
            url
            label
        }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertStringContainsString('magento_image.jpg', $response['products']['items'][0]['thumbnail']['url']);
        self::assertTrue($this->checkImageExists($response['products']['items'][0]['thumbnail']['url']));
        self::assertEquals('Image Alt Text', $response['products']['items'][0]['thumbnail']['label']);
    }

    /**
     * @param string $url
     * @return bool
     */
    private function checkImageExists(string $url): bool
    {
        $connection = curl_init($url);
        curl_setopt($connection, CURLOPT_HEADER, true);
        curl_setopt($connection, CURLOPT_NOBODY, true);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($connection);
        $responseStatus = curl_getinfo($connection, CURLINFO_HTTP_CODE);

        return $responseStatus === 200;
    }
}
