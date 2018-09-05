<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\TestCase\GraphQlAbstract;

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
   		small_image_url
    }
  }    
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('small_image_url', $response['products']['items'][0]);
        self::assertContains('magento_image.jpg', $response['products']['items'][0]['small_image_url']);
        self::assertTrue($this->checkImageExists($response['products']['items'][0]['small_image_url']));
    }

    /**
     * small_image_url should contain a placeholder when there's no small image assigned
     * to the product
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testProductSmallImageUrlWithNoImage()
    {
        $productSku = 'simple';
        $query = <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}) {
    items {
   		small_image_url
    }
  }    
}
QUERY;
        $response = $this->graphQlQuery($query);
        self::assertArrayHasKey('small_image_url', $response['products']['items'][0]);
        self::assertContains('placeholder/small_image.jpg', $response['products']['items'][0]['small_image_url']);
        self::assertTrue($this->checkImageExists($response['products']['items'][0]['small_image_url']));
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

        return $responseStatus === 200 ? true : false;
    }
}
