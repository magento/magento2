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
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_catalog_product_reindex_schedule.php
 */
class ProductImagesTest extends ProductTestBase
{
    /**
     * Tests that images are imported correctly
     *
     * @magentoDataFixture mediaImportImageFixture
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     * @dataProvider importImagesDataProvider
     * @magentoAppIsolation enabled
     * @param string $importFile
     * @param string $productSku
     * @param string $storeCode
     * @param array $expectedImages
     * @param array $select
     */
    public function testImportImages(
        string $importFile,
        string $productSku,
        string $storeCode,
        array $expectedImages,
        array $select = ['file', 'label', 'position']
    ): void {
        $this->importDataForMediaTest($importFile);
        $product = $this->getProductBySku($productSku, $storeCode);
        $actualImages = array_map(
            function (\Magento\Framework\DataObject $item) use ($select) {
                return $item->toArray($select);
            },
            $product->getMediaGalleryImages()->getItems()
        );
        $this->assertEquals($expectedImages, array_values($actualImages));
    }

    /**
     * @return array[]
     */
    public function importImagesDataProvider(): array
    {
        return [
            [
                'import_media_additional_images_storeview.csv',
                'simple',
                'default',
                [
                    [
                        'file' => '/m/a/magento_image.jpg',
                        'label' => 'Image Alt Text',
                        'position' => 1
                    ],
                    [
                        'file' => '/m/a/magento_additional_image_one.jpg',
                        'label' => null,
                        'position' => 2
                    ],
                    [
                        'file' => '/m/a/magento_additional_image_two.jpg',
                        'label' => null,
                        'position' => 3
                    ],
                ]
            ],
            [
                'import_media_additional_images_storeview.csv',
                'simple',
                'fixturestore',
                [
                    [
                        'file' => '/m/a/magento_image.jpg',
                        'label' => 'Image Alt Text',
                        'position' => 1
                    ],
                    [
                        'file' => '/m/a/magento_additional_image_one.jpg',
                        'label' => 'Additional Image Label One',
                        'position' => 2
                    ],
                    [
                        'file' => '/m/a/magento_additional_image_two.jpg',
                        'label' => 'Additional Image Label Two',
                        'position' => 3
                    ],
                ]
            ]
        ];
    }

    /**
     * Test that product import with images works properly
     *
     * @magentoDataFixture mediaImportImageFixture
     * @magentoAppIsolation enabled
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
     * @magentoAppIsolation enabled
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
     * @magentoAppIsolation enabled
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
     * @magentoAppIsolation enabled
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
     * @magentoAppIsolation enabled
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
     * Make sure the non existing image in the csv file won't erase the qty key of the existing products.
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testImportWithNonExistingImage()
    {
        $products = [
            'simple_new' => 100,
        ];

        $this->importFile('products_to_import_with_non_existing_image.csv');

        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        foreach ($products as $productSku => $productQty) {
            $product = $productRepository->get($productSku);
            $stockItem = $product->getExtensionAttributes()->getStockItem();
            $this->assertEquals($productQty, $stockItem->getQty());
        }
    }

    /**
     * Tests situation when images for importing products are already present in filesystem.
     *
     * @magentoDataFixture Magento/CatalogImportExport/Model/Import/_files/import_with_filesystem_images.php
     * @magentoAppIsolation enabled
     */
    public function testImportWithFilesystemImages()
    {
        /** @var Filesystem $filesystem */
        $filesystem = ObjectManager::getInstance()->get(Filesystem::class);
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $writeAdapter */
        $writeAdapter = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);

        if (!$writeAdapter->isWritable()) {
            $this->markTestSkipped('Due to unwritable media directory');
        }

        $this->importDataForMediaTest('import_media_existing_images.csv');
    }

    /**
     * Test that product import with images for non-default store works properly.
     *
     * @magentoDataFixture mediaImportImageFixture
     * @magentoAppIsolation enabled
     */
    public function testImportImageForNonDefaultStore()
    {
        $this->importDataForMediaTest('import_media_two_stores.csv');
        $product = $this->getProductBySku('simple_with_images');
        $mediaGallery = $product->getData('media_gallery');
        foreach ($mediaGallery['images'] as $image) {
            $image['file'] === '/m/a/magento_image.jpg'
                ? self::assertSame('1', $image['disabled'])
                : self::assertSame('0', $image['disabled']);
        }
    }

    /**
     * Hide product images via hide_from_product_page attribute during import CSV.
     *
     * @magentoDataFixture mediaImportImageFixture
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     *
     * @return void
     */
    public function testImagesAreHiddenAfterImport(): void
    {
        $expectedActiveImages = [
            [
                'file' => '/m/a/magento_additional_image_one.jpg',
                'label' => 'Additional Image Label One',
                'disabled' => '0',
            ],
            [
                'file' => '/m/a/magento_additional_image_two.jpg',
                'label' => 'Additional Image Label Two',
                'disabled' => '0',
            ],
        ];

        $expectedHiddenImage = [
            'file' => '/m/a/magento_image.jpg',
            'label' => 'Image Alt Text',
            'disabled' => '1',
        ];
        $expectedAllProductImages = array_merge(
            [$expectedHiddenImage],
            $expectedActiveImages
        );

        $this->importDataForMediaTest('hide_from_product_page_images.csv');
        $actualAllProductImages = [];
        $product = $this->getProductBySku('simple');

        // Check that new images were imported and existing image is disabled after import
        $productMediaData = $product->getData('media_gallery');

        $this->assertNotEmpty($productMediaData['images']);
        $allProductImages = $productMediaData['images'];
        $this->assertCount(3, $allProductImages, 'Images were imported incorrectly');

        foreach ($allProductImages as $image) {
            $actualAllProductImages[] = [
                'file' => $image['file'],
                'label' => $image['label'],
                'disabled' => $image['disabled'],
            ];
        }

        $this->assertEquals(
            $expectedAllProductImages,
            $actualAllProductImages,
            'Images are incorrect after import'
        );

        // Check that on storefront only enabled images are shown
        $actualActiveImages = $product->getMediaGalleryImages();
        $this->assertSame(
            $expectedActiveImages,
            $actualActiveImages->toArray(['file', 'label', 'disabled'])['items'],
            'Hidden image is present on frontend after import'
        );
    }

    /**
     * Checking product images after Add/Update import failure
     *
     * @magentoDataFixture mediaImportImageFixture
     * @magentoDataFixture Magento/CatalogImportExport/Model/Import/_files/import_with_filesystem_images.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testProductBaseImageAfterImport()
    {
        $this->importDataForMediaTest('import_media.csv');

        $this->testImportWithNonExistingImage();

        /** @var $productAfterImport \Magento\Catalog\Model\Product */
        $productAfterImport = $this->getProductBySku('simple_new');
        $this->assertNotEquals('/no/exists/image/magento_image.jpg', $productAfterImport->getData('image'));
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
     * Test that configurable product images are imported correctly.
     *
     * @magentoDataFixture mediaImportImageFixture
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     */
    public function testImportConfigurableProductImages()
    {
        $this->importDataForMediaTest('import_configurable_product_multistore.csv');
        $expected = [
            'import-configurable-option-1' => [
                [
                    'file' => '/m/a/magento_image.jpg',
                    'label' => 'Base Image Label - Option 1',
                ],
                [
                    'file' => '/m/a/magento_small_image.jpg',
                    'label' => 'Small Image Label - Option 1',
                ],
                [
                    'file' => '/m/a/magento_thumbnail.jpg',
                    'label' => 'Thumbnail Image Label - Option 1',
                ],
                [
                    'file' => '/m/a/magento_additional_image_one.jpg',
                    'label' => '',
                ],
            ],
            'import-configurable-option-2' => [
                [
                    'file' => '/m/a/magento_image.jpg',
                    'label' => 'Base Image Label - Option 2',
                ],
                [
                    'file' => '/m/a/magento_small_image.jpg',
                    'label' => 'Small Image Label - Option 2',
                ],
                [
                    'file' => '/m/a/magento_thumbnail.jpg',
                    'label' => 'Thumbnail Image Label - Option 2',
                ],
                [
                    'file' => '/m/a/magento_additional_image_two.jpg',
                    'label' => '',
                ],
            ],
            'import-configurable' => [
                [
                    'file' => '/m/a/magento_image.jpg',
                    'label' => 'Base Image Label - Configurable',
                ],
                [
                    'file' => '/m/a/magento_small_image.jpg',
                    'label' => 'Small Image Label - Configurable',
                ],
                [
                    'file' => '/m/a/magento_thumbnail.jpg',
                    'label' => 'Thumbnail Image Label - Configurable',
                ],
                [
                    'file' => '/m/a/magento_additional_image_three.jpg',
                    'label' => '',
                ],
            ]
        ];
        $actual = [];
        $products = ['import-configurable-option-1', 'import-configurable-option-2', 'import-configurable'];
        foreach ($products as $sku) {
            $product = $this->getProductBySku($sku);
            $gallery = $product->getMediaGalleryImages();
            foreach ($gallery->getItems() as $item) {
                $actual[$sku][] = $item->toArray(['file', 'label']);
            }
        }
        $this->assertEquals($expected, $actual);

        $expected['import-configurable'] = [
            [
                'file' => '/m/a/magento_image.jpg',
                'label' => 'Base Image Label - Configurable (fixturestore)',
            ],
            [
                'file' => '/m/a/magento_small_image.jpg',
                'label' => 'Small Image Label - Configurable (fixturestore)',
            ],
            [
                'file' => '/m/a/magento_thumbnail.jpg',
                'label' => 'Thumbnail Image Label - Configurable (fixturestore)',
            ],
            [
                'file' => '/m/a/magento_additional_image_three.jpg',
                'label' => '',
            ],
        ];

        $actual = [];
        foreach ($products as $sku) {
            $product = $this->getProductBySku($sku, 'fixturestore');
            $gallery = $product->getMediaGalleryImages();
            foreach ($gallery->getItems() as $item) {
                $actual[$sku][] = $item->toArray(['file', 'label']);
            }
        }
        $this->assertEquals($expected, $actual);
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

    /**
     * Verify additional images url validation during import.
     *
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testImportInvalidAdditionalImages(): void
    {
        $pathToFile = __DIR__ . '/../_files/import_media_additional_images_with_wrong_url.csv';
        $filesystem = BootstrapHelper::getObjectManager()->create(Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(Csv::class, ['file' => $pathToFile, 'directory' => $directory]);
        $errors = $this->_model->setSource($source)->setParameters(['behavior' => Import::BEHAVIOR_APPEND])
            ->validateData();
        $this->assertEquals($errors->getErrorsCount(), 1);
        $this->assertEquals(
            "Wrong URL/path used for attribute additional_images",
            $errors->getErrorByRowNumber(0)[0]->getErrorMessage()
        );
    }
}
