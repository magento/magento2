<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Model\ResourceModel\Catalog;

/**
 * Test class for \Magento\Sitemap\Model\ResourceModel\Catalog\Product.
 * - test products collection generation for sitemap
 *
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 * @magentoDataFixture Magento/Sitemap/_files/sitemap_products.php
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Base product image path
     */
    const BASE_IMAGE_PATH = 'http://localhost/pub/media/catalog/product/cache/c9e0b0ef589f3508e5ba515cde53c5ff';
    
    /**
     * Test getCollection None images
     * 1) Check that image attributes were not loaded
     * 2) Check no images were loaded
     *
     * @magentoConfigFixture default_store sitemap/product/image_include none
     */
    public function testGetCollectionNone()
    {
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Sitemap\Model\ResourceModel\Catalog\Product::class
        );
        $products = $model->getCollection(\Magento\Store\Model\Store::DISTRO_STORE_ID);

        $this->_checkProductCollection($products, 3, [1, 4, 5]);

        // Check that no image attributes were loaded
        foreach ($products as $product) {
            $this->assertEmpty($product->getName(), 'Attribute name is not empty');
            $this->assertEmpty($product->getImage(), 'Attribute image is not empty');
            $this->assertEmpty($product->getThumbnail(), 'Attribute thumbnail is not empty');
        }

        $this->assertEmpty($products[4]->getImages(), 'Images were loaded');
    }

    /**
     * Test getCollection All images
     * 1) Check thumbnails
     * 2) Check images loading
     * 3) Check thumbnails when no thumbnail selected
     *
     * @magentoConfigFixture default_store sitemap/product/image_include all
     */
    public function testGetCollectionAll()
    {
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Sitemap\Model\ResourceModel\Catalog\Product::class
        );
        $products = $model->getCollection(\Magento\Store\Model\Store::DISTRO_STORE_ID);

        $this->_checkProductCollection($products, 3, [1, 4, 5]);

        // Check name attribute was loaded
        foreach ($products as $product) {
            $this->assertNotEmpty($product->getName(), 'name attribute was not loaded');
        }

        // Check thumbnail attribute
        $this->assertEmpty($products[1]->getThumbnail(), 'Thumbnail attribute was loaded');
        $this->assertEmpty($products[4]->getImage(), 'Image attribute was loaded');
        $this->assertEquals('/m/a/magento_image_sitemap.png', $products[4]->getThumbnail(), 'Incorrect thumbnail');

        // Check images loading
        $this->assertEmpty($products[1]->getImages(), 'Images were loaded');
        $this->assertNotEmpty($products[4]->getImages(), 'Images were not loaded');
        $this->assertEquals('Simple Images', $products[4]->getImages()->getTitle(), 'Incorrect title');
        $this->assertEquals(
            self::BASE_IMAGE_PATH.'/m/a/magento_image_sitemap.png',
            $products[4]->getImages()->getThumbnail(),
            'Incorrect thumbnail'
        );
        $this->assertCount(2, $products[4]->getImages()->getCollection(), 'Not all images were loaded');

        $imagesCollection = $products[4]->getImages()->getCollection();
        $this->assertEquals(
            self::BASE_IMAGE_PATH.'/m/a/magento_image_sitemap.png',
            $imagesCollection[0]->getUrl(),
            'Incorrect image url'
        );
        $this->assertEquals(
            self::BASE_IMAGE_PATH.'/s/e/second_image.png',
            $imagesCollection[1]->getUrl(),
            'Incorrect image url'
        );
        $this->assertEmpty($imagesCollection[0]->getCaption(), 'Caption not empty');

        // Check no selection
        $this->assertEmpty($products[5]->getImage(), 'image is not empty');
        $this->assertEquals('no_selection', $products[5]->getThumbnail(), 'thumbnail is incorrect');
        $imagesCollection = $products[5]->getImages()->getCollection();
        $this->assertCount(1, $imagesCollection);
        $this->assertEquals(
            self::BASE_IMAGE_PATH.'/s/e/second_image_1.png',
            $imagesCollection[0]->getUrl(),
            'Image url is incorrect'
        );
        $this->assertEquals(
            self::BASE_IMAGE_PATH.'/s/e/second_image_1.png',
            $products[5]->getImages()->getThumbnail(),
            'Product thumbnail is incorrect'
        );
    }

    /**
     * Test getCollection None images
     * 1) Check that image attributes were not loaded
     * 2) Check no images were loaded
     * 3) Check thumbnails when no thumbnail selected
     *
     * @magentoConfigFixture default_store sitemap/product/image_include base
     */
    public function testGetCollectionBase()
    {
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Sitemap\Model\ResourceModel\Catalog\Product::class
        );
        $products = $model->getCollection(\Magento\Store\Model\Store::DISTRO_STORE_ID);

        $this->_checkProductCollection($products, 3, [1, 4, 5]);

        // Check name attribute was loaded
        foreach ($products as $product) {
            $this->assertNotEmpty($product->getName(), 'name attribute was not loaded');
        }

        // Check thumbnail attribute
        $this->assertEmpty($products[1]->getImage(), 'image attribute was loaded');
        $this->assertEmpty($products[4]->getThumbnail(), 'thumbnail attribute was loaded');
        $this->assertEquals('/s/e/second_image.png', $products[4]->getImage(), 'Incorrect image attribute');

        // Check images loading
        $this->assertEmpty($products[1]->getImages(), 'Images were loaded');
        $this->assertNotEmpty($products[4]->getImages(), 'Images were not loaded');
        $this->assertEquals('Simple Images', $products[4]->getImages()->getTitle(), 'Incorrect title');
        $this->assertEquals(
            self::BASE_IMAGE_PATH.'/s/e/second_image.png',
            $products[4]->getImages()->getThumbnail(),
            'Incorrect thumbnail'
        );
        $this->assertCount(1, $products[4]->getImages()->getCollection(), 'Number of loaded images is incorrect');

        $imagesCollection = $products[4]->getImages()->getCollection();
        $this->assertEquals(
            self::BASE_IMAGE_PATH.'/s/e/second_image.png',
            $imagesCollection[0]->getUrl(),
            'Incorrect image url'
        );
        $this->assertEmpty($imagesCollection[0]->getCaption(), 'Caption not empty');

        // Check no selection
        $this->assertEmpty($products[5]->getThumbnail(), 'thumbnail is not empty');
        $this->assertEquals('no_selection', $products[5]->getImage(), 'image is incorrect');
        $this->assertEmpty($products[5]->getImages(), 'Product images were loaded');
    }

    /**
     * Check product collection
     * 1) Check that all products are loaded
     * 2) Check that products are loaded correctly and all required attributes present
     *
     * @param array $products
     * @param int $expectedCount
     * @param array $expectedKeys
     */
    protected function _checkProductCollection(array $products, $expectedCount, array $expectedKeys)
    {
        // Check all expected products were added into collection
        $this->assertCount($expectedCount, $products, 'Number of loaded products is incorrect');
        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $products);
        }

        // Check all expected attributes are present
        foreach ($products as $product) {
            $this->assertNotEmpty($product->getUpdatedAt());
            $this->assertNotEmpty($product->getId());
            $this->assertNotEmpty($product->getUrl());
        }
    }
}
