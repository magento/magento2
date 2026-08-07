<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
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
     * @magentoDataFixture mediaImportImageFixture
     * @return void
     */
    public function testReplaceImageImportModeRemovesUnlistedAdditionalImages(): void
    {
        $this->importDataForMediaTest('import_media_replace_setup.csv');
        $product = $this->getProductBySku('simple_new');
        $this->assertCount(5, $product->getMediaGalleryImages()->getItems());

        $this->importDataForMediaTest(
            'import_media_replace_additional_images.csv',
            0,
            \Magento\ImportExport\Model\Import::PRODUCT_IMAGE_IMPORT_MODE_REPLACE
        );

        $product = $this->getProductBySku('simple_new');
        $files = array_map(
            static function (\Magento\Framework\DataObject $item) {
                return $item->getFile();
            },
            array_values($product->getMediaGalleryImages()->getItems())
        );

        $this->assertContains('/m/a/magento_image.jpg', $files);
        $this->assertContains('/m/a/magento_small_image.jpg', $files);
        $this->assertContains('/m/a/magento_thumbnail.jpg', $files);
        $this->assertContains('/r/e/repro_replace_additional_a.jpg', $files);
        $this->assertNotContains('/r/e/repro_replace_additional_b.jpg', $files);
        $this->assertCount(4, $files);

        $this->assertEquals('/m/a/magento_image.jpg', $product->getData('image'));
        $this->assertEquals('/m/a/magento_small_image.jpg', $product->getData('small_image'));
        $this->assertEquals('/m/a/magento_thumbnail.jpg', $product->getData('thumbnail'));

        $this->importDataForMediaTest('import_media_replace_append.csv');
        $product = $this->getProductBySku('simple_new');
        $filesAfterAppend = array_map(
            static function (\Magento\Framework\DataObject $item) {
                return $item->getFile();
            },
            array_values($product->getMediaGalleryImages()->getItems())
        );
        $this->assertContains('/r/e/repro_replace_additional_a.jpg', $filesAfterAppend);
        $this->assertContains('/r/e/repro_replace_additional_c.jpg', $filesAfterAppend);
        $this->assertNotContains('/r/e/repro_replace_additional_b.jpg', $filesAfterAppend);
        $this->assertGreaterThanOrEqual(5, count($filesAfterAppend));
        $this->assertEquals('/m/a/magento_image.jpg', $product->getData('image'));
        $this->assertEquals('/m/a/magento_small_image.jpg', $product->getData('small_image'));
        $this->assertEquals('/m/a/magento_thumbnail.jpg', $product->getData('thumbnail'));
    }

    /**
     * @magentoDataFixture mediaImportImageFixture
     * @return void
     */
    public function testReplaceImageImportModeWithEmptyAdditionalImagesKeepsRoles(): void
    {
        $this->importDataForMediaTest('import_media_replace_setup.csv');
        $this->assertCount(5, $this->getProductBySku('simple_new')->getMediaGalleryImages()->getItems());

        $this->importDataForMediaTest(
            'import_media_replace_empty_additional.csv',
            0,
            \Magento\ImportExport\Model\Import::PRODUCT_IMAGE_IMPORT_MODE_REPLACE
        );

        $product = $this->getProductBySku('simple_new');
        $files = array_map(
            static function (\Magento\Framework\DataObject $item) {
                return $item->getFile();
            },
            array_values($product->getMediaGalleryImages()->getItems())
        );

        $this->assertContains('/m/a/magento_image.jpg', $files);
        $this->assertContains('/m/a/magento_small_image.jpg', $files);
        $this->assertContains('/m/a/magento_thumbnail.jpg', $files);
        $this->assertNotContains('/r/e/repro_replace_additional_a.jpg', $files);
        $this->assertNotContains('/r/e/repro_replace_additional_b.jpg', $files);
        $this->assertCount(3, $files);
        $this->assertEquals('/m/a/magento_image.jpg', $product->getData('image'));
        $this->assertEquals('/m/a/magento_small_image.jpg', $product->getData('small_image'));
        $this->assertEquals('/m/a/magento_thumbnail.jpg', $product->getData('thumbnail'));
        $this->assertEquals('/m/a/magento_image.jpg', $product->getData('swatch_image'));
    }

    /**
     * @magentoDataFixture mediaImportImageFixture
     * @return void
     */
    public function testReplaceSkipsUnlinkWhenImageUploadFails(): void
    {
        $this->importDataForMediaTest('import_media_replace_setup.csv');
        $filesBefore = $this->getGalleryFiles('simple_new');
        $this->assertContains('/r/e/repro_replace_additional_a.jpg', $filesBefore);
        $this->assertContains('/r/e/repro_replace_additional_b.jpg', $filesBefore);

        $this->importDataForMediaTest(
            'import_media_replace_with_missing_image.csv',
            2,
            Import::PRODUCT_IMAGE_IMPORT_MODE_REPLACE
        );

        $files = $this->getGalleryFiles('simple_new');
        $this->assertContains('/r/e/repro_replace_additional_a.jpg', $files);
        $this->assertContains('/r/e/repro_replace_additional_b.jpg', $files);
        $this->assertContains('/m/a/magento_image.jpg', $files);
        $this->assertContains('/m/a/magento_small_image.jpg', $files);
        $this->assertContains('/m/a/magento_thumbnail.jpg', $files);
    }

    /**
     * @magentoDataFixture mediaImportImageFixture
     * @return void
     */
    public function testReplacePromotesAdditionalToThumbnailAndDropsOldThumbnail(): void
    {
        $this->importDataForMediaTest('import_media_replace_setup.csv');
        // Point thumbnail at former additional A; B remains a pure additional image.
        $this->importDataForMediaTest('import_media_replace_thumbnail_to_additional_a.csv');

        $product = $this->getProductBySku('simple_new');
        $this->assertEquals('/r/e/repro_replace_additional_a.jpg', $product->getData('thumbnail'));
        $filesBefore = $this->getGalleryFiles('simple_new');
        $this->assertContains('/r/e/repro_replace_additional_a.jpg', $filesBefore);
        $this->assertContains('/r/e/repro_replace_additional_b.jpg', $filesBefore);

        $this->importDataForMediaTest(
            'import_media_replace_thumbnail_from_additional_empty.csv',
            0,
            Import::PRODUCT_IMAGE_IMPORT_MODE_REPLACE
        );

        $product = $this->getProductBySku('simple_new');
        $files = $this->getGalleryFiles('simple_new');

        $this->assertEquals('/r/e/repro_replace_additional_b.jpg', $product->getData('thumbnail'));
        $this->assertEquals('/m/a/magento_image.jpg', $product->getData('image'));
        $this->assertEquals('/m/a/magento_small_image.jpg', $product->getData('small_image'));
        $this->assertEquals('/m/a/magento_image.jpg', $product->getData('swatch_image'));

        $this->assertContains('/r/e/repro_replace_additional_b.jpg', $files);
        $this->assertContains('/m/a/magento_image.jpg', $files);
        $this->assertContains('/m/a/magento_small_image.jpg', $files);
        // Old thumbnail A is no longer a role and not in additional_images → removed.
        $this->assertNotContains('/r/e/repro_replace_additional_a.jpg', $files);
        // B was only an additional image before; now it is the thumbnail (kept).
        // Pure extras not re-listed are removed; former B is kept via thumbnail keepPath.
    }

    /**
     * @magentoDataFixture mediaImportImageFixture
     * @return void
     */
    public function testReplaceWithoutAdditionalImagesColumnDoesNotDropGallery(): void
    {
        $this->importDataForMediaTest('import_media_replace_setup.csv');
        $this->assertCount(5, $this->getProductBySku('simple_new')->getMediaGalleryImages()->getItems());

        $this->importDataForMediaTest(
            'import_media_replace_roles_only.csv',
            0,
            \Magento\ImportExport\Model\Import::PRODUCT_IMAGE_IMPORT_MODE_REPLACE
        );

        $product = $this->getProductBySku('simple_new');
        $files = array_map(
            static function (\Magento\Framework\DataObject $item) {
                return $item->getFile();
            },
            array_values($product->getMediaGalleryImages()->getItems())
        );

        $this->assertContains('/r/e/repro_replace_additional_a.jpg', $files);
        $this->assertContains('/r/e/repro_replace_additional_b.jpg', $files);
        $this->assertCount(5, $files);
    }

    /**

     * @magentoDataFixture mediaImportImageFixture
     * @return void
     */
    public function testReplaceKeepsUnionOfAdditionalImagesFromMultipleRows(): void
    {
        $this->importDataForMediaTest('import_media_replace_setup.csv');
        $this->assertCount(5, $this->getGalleryFiles('simple_new'));

        $this->importDataForMediaTest(
            'import_media_replace_union_rows.csv',
            0,
            Import::PRODUCT_IMAGE_IMPORT_MODE_REPLACE
        );

        $files = $this->getGalleryFiles('simple_new');
        $this->assertContains('/r/e/repro_replace_additional_a.jpg', $files);
        $this->assertContains('/r/e/repro_replace_additional_c.jpg', $files);
        $this->assertNotContains('/r/e/repro_replace_additional_b.jpg', $files);
        $this->assertContains('/m/a/magento_image.jpg', $files);
        $this->assertContains('/m/a/magento_small_image.jpg', $files);
        $this->assertContains('/m/a/magento_thumbnail.jpg', $files);
    }

    /**

     * @magentoDataFixture mediaImportImageFixture
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @return void
     */
    public function testReplaceAcrossBunchesWithStoreRoleReassignment(): void
    {
        $this->importDataForMediaTest('import_media_replace_setup.csv');
        // Make store-scoped base use additional B so replace must not drop it until reassigned.
        $this->importDataForMediaTest('import_media_assign_store_base.csv');
        $this->assertEquals(
            '/r/e/repro_replace_additional_b.jpg',
            $this->getProductBySku('simple_new', 'fixturestore')->getData('image')
        );

        // bunch_size=1 forces default and store rows into different bunches.
        $this->importDataForMediaTest(
            'import_media_replace_cross_bunch.csv',
            0,
            Import::PRODUCT_IMAGE_IMPORT_MODE_REPLACE,
            1
        );

        $files = $this->getGalleryFiles('simple_new');
        $this->assertContains('/r/e/repro_replace_additional_a.jpg', $files);
        $this->assertNotContains('/r/e/repro_replace_additional_b.jpg', $files);
        $this->assertContains('/m/a/magento_image.jpg', $files);
        $this->assertContains('/m/a/magento_small_image.jpg', $files);
        $this->assertContains('/m/a/magento_thumbnail.jpg', $files);

        $this->assertEquals(
            '/m/a/magento_image.jpg',
            $this->getProductBySku('simple_new', 'fixturestore')->getData('image')
        );
        $this->assertEquals('/m/a/magento_image.jpg', $this->getProductBySku('simple_new')->getData('image'));
    }

    /**

     * @magentoDataFixture mediaImportImageFixture
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @return void
     */
    public function testStoreAdditionalImagesImportedWithoutDefaultReplaceRow(): void
    {
        $this->importDataForMediaTest('import_media_replace_setup.csv');
        $this->assertCount(5, $this->getGalleryFiles('simple_new'));

        $this->importDataForMediaTest(
            'import_media_replace_store_additional_only.csv',
            0,
            Import::PRODUCT_IMAGE_IMPORT_MODE_REPLACE
        );

        $files = $this->getGalleryFiles('simple_new');
        $this->assertContains('/r/e/repro_replace_additional_a.jpg', $files);
        $this->assertContains('/r/e/repro_replace_additional_b.jpg', $files);
        $this->assertContains('/r/e/repro_replace_additional_c.jpg', $files);
        $this->assertCount(6, $files);
    }

    /**
     * @magentoDataFixture mediaImportImageFixture
     * @return void
     */
    public function testReplaceDoesNotRemoveExternalVideoEntries(): void
    {
        $this->importDataForMediaTest('import_media_replace_setup.csv');
        $this->insertExternalVideoGalleryRow('simple_new');

        $this->assertSame(1, $this->countExternalVideoEntries('simple_new'));

        $this->importDataForMediaTest(
            'import_media_replace_additional_images.csv',
            0,
            Import::PRODUCT_IMAGE_IMPORT_MODE_REPLACE
        );

        $files = $this->getGalleryFiles('simple_new');
        $this->assertContains('/r/e/repro_replace_additional_a.jpg', $files);
        $this->assertNotContains('/r/e/repro_replace_additional_b.jpg', $files);
        $this->assertSame(1, $this->countExternalVideoEntries('simple_new'));
    }

    /**
     * @magentoDataFixture mediaImportImageFixture
     * @return void
     */
    public function testReplaceWithoutDeleteUnusedKeepsPhysicalFiles(): void
    {
        $this->importDataForMediaTest('import_media_replace_setup.csv');
        $removedPath = '/r/e/repro_replace_additional_b.jpg';
        $this->assertTrue($this->mediaFileExists($removedPath));
        $this->assertGreaterThan(0, $this->countMediaGalleryRowsForPath($removedPath));

        $this->importDataForMediaTest(
            'import_media_replace_additional_images.csv',
            0,
            Import::PRODUCT_IMAGE_IMPORT_MODE_REPLACE
        );

        $this->assertNotContains($removedPath, $this->getGalleryFiles('simple_new'));
        $this->assertSame(0, $this->countMediaGalleryRowsForPath($removedPath));
        $this->assertTrue($this->mediaFileExists($removedPath));
    }

    /**
     * @magentoDataFixture mediaImportImageFixture
     * @return void
     */
    public function testReplaceWithDeleteUnusedRemovesPhysicalFiles(): void
    {
        $this->importDataForMediaTest('import_media_replace_setup.csv');
        $removedPath = '/r/e/repro_replace_additional_b.jpg';
        $keptPath = '/r/e/repro_replace_additional_a.jpg';
        $this->assertTrue($this->mediaFileExists($removedPath));
        $this->assertTrue($this->mediaFileExists($keptPath));

        $this->importDataForMediaTest(
            'import_media_replace_additional_images.csv',
            0,
            Import::PRODUCT_IMAGE_IMPORT_MODE_REPLACE,
            null,
            true
        );

        $files = $this->getGalleryFiles('simple_new');
        $this->assertContains($keptPath, $files);
        $this->assertNotContains($removedPath, $files);
        $this->assertFalse($this->mediaFileExists($removedPath));
        $this->assertTrue($this->mediaFileExists($keptPath));
    }

    /**
     * @param string $galleryFile
     * @return bool
     */
    private function mediaFileExists(string $galleryFile): bool
    {
        $filesystem = $this->objectManager->get(Filesystem::class);
        $mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $relative = 'catalog/product/' . ltrim($galleryFile, '/');

        return $mediaDirectory->isFile($relative);
    }

    /**
     * @param string $galleryFile
     * @return int
     */
    private function countMediaGalleryRowsForPath(string $galleryFile): int
    {
        $connection = $this->objectManager->get(\Magento\Framework\App\ResourceConnection::class)
            ->getConnection();
        $table = $connection->getTableName('catalog_product_entity_media_gallery');
        $variants = array_unique([
            $galleryFile,
            '/' . ltrim($galleryFile, '/'),
            ltrim($galleryFile, '/'),
        ]);

        return (int)$connection->fetchOne(
            $connection->select()
                ->from($table, ['cnt' => new \Zend_Db_Expr('COUNT(value_id)')])
                ->where('value IN (?)', $variants)
        );
    }

    /**
     * @param string $sku
     * @return string[]
     */
    private function getGalleryFiles(string $sku): array
    {
        return array_map(
            static function (\Magento\Framework\DataObject $item) {
                return $item->getFile();
            },
            array_values($this->getProductBySku($sku)->getMediaGalleryImages()->getItems())
        );
    }

    /**
     * @param string $sku
     * @return int
     */
    private function countExternalVideoEntries(string $sku): int
    {
        $count = 0;
        foreach ($this->getProductBySku($sku)->getMediaGalleryEntries() as $entry) {
            if ($entry->getMediaType() === 'external-video') {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Insert a gallery value with media_type=external-video without GalleryManagement save quirks.
     *
     * @param string $sku
     * @return void
     */
    private function insertExternalVideoGalleryRow(string $sku): void
    {
        $connection = $this->objectManager->get(\Magento\Framework\App\ResourceConnection::class)
            ->getConnection();
        $productResource = $this->objectManager->get(\Magento\Catalog\Model\ResourceModel\Product::class);
        $productId = (int)$productResource->getIdBySku($sku);
        $linkField = $this->objectManager->get(\Magento\Framework\EntityManager\MetadataPool::class)
            ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->getLinkField();
        $linkValue = (int)$connection->fetchOne(
            $connection->select()
                ->from($productResource->getEntityTable(), $linkField)
                ->where('entity_id = ?', $productId)
        );

        $attributeId = (int)$productResource->getAttribute('media_gallery')->getAttributeId();
        $galleryTable = $productResource->getTable('catalog_product_entity_media_gallery');
        $valueToEntity = $productResource->getTable('catalog_product_entity_media_gallery_value_to_entity');
        $valueTable = $productResource->getTable('catalog_product_entity_media_gallery_value');

        $connection->insert($galleryTable, [
            'attribute_id' => $attributeId,
            'value' => '/r/e/replace_test_video.jpg',
            'media_type' => 'external-video',
            'disabled' => 0,
        ]);
        $valueId = (int)$connection->lastInsertId($galleryTable);
        $connection->insert($valueToEntity, [
            'value_id' => $valueId,
            $linkField => $linkValue,
        ]);
        $connection->insert($valueTable, [
            'value_id' => $valueId,
            'store_id' => 0,
            $linkField => $linkValue,
            'label' => 'Replace Test Video',
            'position' => 99,
            'disabled' => 0,
        ]);
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
