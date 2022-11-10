<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import\ProductTest;

use Magento\CatalogImportExport\Model\Import\ProductTestBase;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Source\Csv;
use Magento\TestFramework\Helper\Bootstrap as BootstrapHelper;

/**
 * Integration test for \Magento\CatalogImportExport\Model\Import\Product class.
 *
 * @magentoAppArea adminhtml
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_catalog_product_reindex_schedule.php
 */
class ProductImagesTest extends ProductTestBase
{
    /**
     * Test that product import with images works properly
     *
     * @magentoDataFixture mediaImportImageFixture
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSaveMediaImage()
    {
        $this->importDataForMediaTest('import_media.csv');

        $product = $this->getProductBySku('simple_new');

        $this->assertEquals('/m/a/magento_image.jpg', $product->getData('image'));
        $this->assertEquals('/m/a/magento_small_image.jpg', $product->getData('small_image'));
        $this->assertEquals('/m/a/magento_thumbnail.jpg', $product->getData('thumbnail'));
        $this->assertEquals('/m/a/magento_image.jpg', $product->getData('swatch_image'));

        $gallery = $product->getMediaGalleryImages();
        $this->assertInstanceOf(\Magento\Framework\Data\Collection::class, $gallery);

        $items = $gallery->getItems();
        $this->assertCount(5, $items);

        $imageItem = array_shift($items);
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $imageItem);
        $this->assertEquals('/m/a/magento_image.jpg', $imageItem->getFile());
        $this->assertEquals('Image Label', $imageItem->getLabel());

        $smallImageItem = array_shift($items);
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $smallImageItem);
        $this->assertEquals('/m/a/magento_small_image.jpg', $smallImageItem->getFile());
        $this->assertEquals('Small Image Label', $smallImageItem->getLabel());

        $thumbnailItem = array_shift($items);
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $thumbnailItem);
        $this->assertEquals('/m/a/magento_thumbnail.jpg', $thumbnailItem->getFile());
        $this->assertEquals('Thumbnail Label', $thumbnailItem->getLabel());

        $additionalImageOneItem = array_shift($items);
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $additionalImageOneItem);
        $this->assertEquals('/m/a/magento_additional_image_one.jpg', $additionalImageOneItem->getFile());
        $this->assertEquals('Additional Image Label One', $additionalImageOneItem->getLabel());

        $additionalImageTwoItem = array_shift($items);
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $additionalImageTwoItem);
        $this->assertEquals('/m/a/magento_additional_image_two.jpg', $additionalImageTwoItem->getFile());
        $this->assertEquals('Additional Image Label Two', $additionalImageTwoItem->getLabel());
    }

    /**
     * Tests that "hide_from_product_page" attribute is hidden after importing product images.
     *
     * @magentoDataFixture mediaImportImageFixture
     */
    public function testSaveHiddenImages()
    {
        $this->importDataForMediaTest('import_media_hidden_images.csv');
        $product = $this->getProductBySku('simple_new');
        $images = $product->getMediaGalleryEntries();

        $hiddenImages = array_filter(
            $images,
            static function (DataObject $image) {
                return (int)$image->getDisabled() === 1;
            }
        );

        $this->assertCount(3, $hiddenImages);

        $imageItem = array_shift($hiddenImages);
        $this->assertEquals('/m/a/magento_image.jpg', $imageItem->getFile());

        $imageItem = array_shift($hiddenImages);
        $this->assertEquals('/m/a/magento_thumbnail.jpg', $imageItem->getFile());

        $imageItem = array_shift($hiddenImages);
        $this->assertEquals('/m/a/magento_additional_image_two.jpg', $imageItem->getFile());
    }

    /**
     * Tests importing product images with "no_selection" attribute.
     *
     * @magentoDataFixture mediaImportImageFixture
     */
    public function testSaveImagesNoSelection()
    {
        $this->importDataForMediaTest('import_media_with_no_selection.csv');
        $product = $this->getProductBySku('simple_new');

        $this->assertEquals('/m/a/magento_image.jpg', $product->getData('image'));
        $this->assertNull($product->getData('small_image'));
        $this->assertNull($product->getData('thumbnail'));
        $this->assertNull($product->getData('swatch_image'));
    }

    /**
     * Test that new images should be added after the existing ones.
     *
     * @magentoDataFixture mediaImportImageFixture
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testNewImagesShouldBeAddedAfterExistingOnes()
    {
        $this->importDataForMediaTest('import_media.csv');

        $product = $this->getProductBySku('simple_new');

        $items = array_values($product->getMediaGalleryImages()->getItems());

        $images = [
            ['file' => '/m/a/magento_image.jpg', 'label' => 'Image Label'],
            ['file' => '/m/a/magento_small_image.jpg', 'label' => 'Small Image Label'],
            ['file' => '/m/a/magento_thumbnail.jpg', 'label' => 'Thumbnail Label'],
            ['file' => '/m/a/magento_additional_image_one.jpg', 'label' => 'Additional Image Label One'],
            ['file' => '/m/a/magento_additional_image_two.jpg', 'label' => 'Additional Image Label Two'],
        ];

        $this->assertCount(5, $items);
        $this->assertEquals(
            $images,
            array_map(
                function (\Magento\Framework\DataObject $item) {
                    return $item->toArray(['file', 'label']);
                },
                $items
            )
        );

        $this->importDataForMediaTest('import_media_additional_long_name_image.csv');
        $product->cleanModelCache();
        $product = $this->getProductBySku('simple_new');
        $items = array_values($product->getMediaGalleryImages()->getItems());
        $images[] = ['file' => '/m/a/' . self::LONG_FILE_NAME_IMAGE, 'label' => ''];
        $this->assertCount(6, $items);
        $this->assertEquals(
            $images,
            array_map(
                function (\Magento\Framework\DataObject $item) {
                    return $item->toArray(['file', 'label']);
                },
                $items
            )
        );
    }

    /**
     * Test import twice and check that image will not be duplicate
     *
     * @magentoDataFixture mediaImportImageFixture
     * @return void
     */
    public function testSaveMediaImageDuplicateImages(): void
    {
        $this->importDataForMediaTest('import_media.csv');
        $imagesCount = count($this->getProductBySku('simple_new')->getMediaGalleryImages()->getItems());

        // import the same file again
        $this->importDataForMediaTest('import_media.csv');

        $this->assertCount($imagesCount, $this->getProductBySku('simple_new')->getMediaGalleryImages()->getItems());
    }

    /**
     * Test that errors occurred during importing images are logged.
     *
     * @magentoDataFixture mediaImportImageFixture
     * @magentoDataFixture mediaImportImageFixtureError
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSaveMediaImageError()
    {
        $this->logger->expects(self::once())->method('critical');
        $this->importDataForMediaTest('import_media.csv', 1);
    }

    /**
     * Tests that images are hidden only for a store view in "store_view_code".
     *
     * @magentoDataFixture mediaImportImageFixture
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testHideImageForStoreView()
    {
        $expectedImageFile = '/m/a/magento_image.jpg';
        $secondStoreCode = 'fixturestore';
        $productSku = 'simple';
        $this->importDataForMediaTest('import_hide_image_for_storeview.csv');
        $product = $this->getProductBySku($productSku);
        $imageItems = $product->getMediaGalleryImages()->getItems();
        $this->assertCount(1, $imageItems);
        $imageItem = array_shift($imageItems);
        $this->assertEquals($expectedImageFile, $imageItem->getFile());
        $product = $this->getProductBySku($productSku, $secondStoreCode);
        $imageItems = $product->getMediaGalleryImages()->getItems();
        $this->assertCount(0, $imageItems);
    }

    /**
     * Test that images labels are updated only for a store view in "store_view_code".
     *
     * @magentoDataFixture mediaImportImageFixture
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testChangeImageLabelForStoreView()
    {
        $expectedImageFile = '/m/a/magento_image.jpg';
        $expectedLabelForDefaultStoreView = 'Image Alt Text';
        $expectedLabelForSecondStoreView = 'Magento Logo';
        $secondStoreCode = 'fixturestore';
        $productSku = 'simple';
        $this->importDataForMediaTest('import_change_image_label_for_storeview.csv');
        $product = $this->getProductBySku($productSku);
        $imageItems = $product->getMediaGalleryImages()->getItems();
        $this->assertCount(1, $imageItems);
        $imageItem = array_shift($imageItems);
        $this->assertEquals($expectedImageFile, $imageItem->getFile());
        $this->assertEquals($expectedLabelForDefaultStoreView, $imageItem->getLabel());
        $product = $this->getProductBySku($productSku, $secondStoreCode);
        $imageItems = $product->getMediaGalleryImages()->getItems();
        $this->assertCount(1, $imageItems);
        $imageItem = array_shift($imageItems);
        $this->assertEquals($expectedImageFile, $imageItem->getFile());
        $this->assertEquals($expectedLabelForSecondStoreView, $imageItem->getLabel());
    }

    /**
     * Tests that image name does not have to be prefixed by slash
     *
     * @magentoDataFixture mediaImportImageFixture
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testUpdateImageByNameNotPrefixedWithSlash()
    {
        $expectedLabelForDefaultStoreView = 'image label updated';
        $expectedImageFile = '/m/a/magento_image.jpg';
        $secondStoreCode = 'fixturestore';
        $productSku = 'simple';
        $this->importDataForMediaTest('import_image_name_without_slash.csv');
        $product = $this->getProductBySku($productSku);
        $imageItems = $product->getMediaGalleryImages()->getItems();
        $this->assertCount(1, $imageItems);
        $imageItem = array_shift($imageItems);
        $this->assertEquals($expectedImageFile, $imageItem->getFile());
        $this->assertEquals($expectedLabelForDefaultStoreView, $imageItem->getLabel());
        $product = $this->getProductBySku($productSku, $secondStoreCode);
        $imageItems = $product->getMediaGalleryImages()->getItems();
        $this->assertCount(0, $imageItems);
    }
}
