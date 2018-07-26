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
    }
}
