<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Provides tests for media gallery images update during product save.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateHandlerTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var UpdateHandler
     */
    private $updateHandler;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var Gallery
     */
    private $galleryResource;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var int
     */
    private $mediaAttributeId;
    /**
     * @var \Magento\Eav\Model\ResourceModel\UpdateHandler
     */
    private $eavUpdateHandler;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var int
     */
    private $currentStoreId;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->fileName = 'image.txt';
        $this->objectManager = Bootstrap::getObjectManager();
        $this->updateHandler = $this->objectManager->create(UpdateHandler::class);
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->storeRepository = $this->objectManager->create(StoreRepositoryInterface::class);
        $this->galleryResource = $this->objectManager->create(Gallery::class);
        $this->productResource = $this->objectManager->create(ProductResource::class);
        $this->mediaAttributeId = (int)$this->productResource->getAttribute('media_gallery')->getAttributeId();
        $this->config = $this->objectManager->get(Config::class);
        $this->storeManager = $this->objectManager->create(StoreManagerInterface::class);
        $this->currentStoreId = $this->storeManager->getStore()->getId();
        $this->mediaDirectory = $this->objectManager->get(Filesystem::class)
            ->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaDirectory->writeFile($this->fileName, 'Test');
        $this->updateHandler = $this->objectManager->create(UpdateHandler::class);
        $this->eavUpdateHandler = $this->objectManager->create(\Magento\Eav\Model\ResourceModel\UpdateHandler::class);
        $this->metadataPool = $this->objectManager->get(MetadataPool::class);
    }

    /**
     * Tests updating image with illegal filename during product save.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/product_image.php
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testExecuteWithIllegalFilename(): void
    {
        $this->expectException(ValidatorException::class);
        $product = $this->getProduct();
        $product->setData(
            'media_gallery',
            [
                'images' => [
                    'image' => [
                        'value_id' => '100',
                        'file' => '/../../..' . DIRECTORY_SEPARATOR . $this->fileName,
                        'label' => 'New image',
                        'removed' => 1,
                    ],
                ],
            ]
        );
        $this->updateHandler->execute($product);
        $this->assertFileExists($this->mediaDirectory->getAbsolutePath($this->fileName));
    }

    /**
     * Tests updating image label, position and disabling during product save.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     * @magentoDbIsolation enabled
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testExecuteWithOneImage(): void
    {
        $product = $this->getProduct();
        $this->updateProductGalleryImages($product, ['label' => 'New image', 'disabled' => '1']);
        $this->updateHandler->execute($product);
        $productImages = $this->galleryResource->loadProductGalleryByAttributeId($product, $this->mediaAttributeId);
        $updatedImage = reset($productImages);
        $this->assertIsArray($updatedImage);
        $this->assertEquals('New image', $updatedImage['label']);
        $this->assertEquals('New image', $updatedImage['label_default']);
        $this->assertEquals('1', $updatedImage['disabled']);
        $this->assertEquals('1', $updatedImage['disabled_default']);
    }

    /**
     * Tests updating image label and label default during product save.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     * @magentoDbIsolation enabled
     * @return void
     * @throws LocalizedException
     */
    public function testExecuteImageWithUpdatedAttributeLabel(): void
    {
        $product = $this->getProduct();
        $productImages = $this->galleryResource->loadProductGalleryByAttributeId($product, $this->mediaAttributeId);
        $updatedImage = reset($productImages);
        $this->assertIsArray($updatedImage);
        $this->assertEquals('Image Alt Text', $updatedImage['label']);
        $this->assertEquals('Image Alt Text', $updatedImage['label_default']);
        $this->updateProductGalleryImages($product, ['label' => 'New image']);
        $this->updateHandler->execute($product);
        $productImages = $this->galleryResource->loadProductGalleryByAttributeId($product, $this->mediaAttributeId);
        $updatedImage = reset($productImages);
        $this->assertIsArray($updatedImage);
        $this->assertEquals('New image', $updatedImage['label']);
        $this->assertEquals('New image', $updatedImage['label_default']);
        $this->updateProductGalleryImages($product, ['label' => '']);
        $this->updateHandler->execute($product);
        $productImages = $this->galleryResource->loadProductGalleryByAttributeId($product, $this->mediaAttributeId);
        $updatedImage = reset($productImages);
        $this->assertIsArray($updatedImage);
        $this->assertEquals('', $updatedImage['label']);
        $this->assertEquals('', $updatedImage['label_default']);
    }

    /**
     * Tests updating image roles during product save.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_multiple_images.php
     * @dataProvider executeWithTwoImagesAndRolesDataProvider
     * @magentoDbIsolation enabled
     * @param array $roles
     * @return void
     * @throws LocalizedException
     */
    public function testExecuteWithTwoImagesAndDifferentRoles(array $roles): void
    {
        $imageRoles = ['image', 'small_image', 'thumbnail', 'swatch_image'];
        $product = $this->getProduct();
        $product->addData($roles);
        $this->updateHandler->execute($product);
        $productsImageData = $this->productResource->getAttributeRawValue(
            $product->getId(),
            $imageRoles,
            $product->getStoreId()
        );
        $this->assertEquals($roles, $productsImageData);
    }

    /**
     * Tests updating image roles during product save on non default store view.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_multiple_images.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @dataProvider executeWithTwoImagesAndRolesDataProvider
     * @magentoDbIsolation enabled
     * @param array $roles
     * @return void
     * @throws LocalizedException
     * @throws ConfigurationMismatchException
     * @throws NoSuchEntityException
     */
    public function testExecuteWithTwoImagesAndDifferentRolesOnStoreView(array $roles): void
    {
        $secondStoreId = (int)$this->storeRepository->get('fixture_second_store')->getId();
        $imageRoles = ['image', 'small_image', 'thumbnail', 'swatch_image'];
        $product = $this->getProduct($secondStoreId);
        $entityIdField = $product->getResource()->getLinkField();
        $entityData = [];
        $entityData['store_id'] = $product->getStoreId();
        $entityData[$entityIdField] = $product->getData($entityIdField);
        $entityData = array_merge($entityData, $roles);
        $this->eavUpdateHandler->execute(
            \Magento\Catalog\Api\Data\ProductInterface::class,
            $entityData
        );
        $product->addData($roles);
        $this->updateHandler->execute($product);

        $storeImages = $this->productResource->getAttributeRawValue(
            $product->getId(),
            $imageRoles,
            $secondStoreId
        );
        $this->assertEquals($roles, $storeImages);

        $defaultImages = $this->productResource->getAttributeRawValue(
            $product->getId(),
            $imageRoles,
            Store::DEFAULT_STORE_ID
        );
        $this->assertEquals('/m/a/magento_image.jpg', $defaultImages['image']);
        $this->assertEquals('/m/a/magento_image.jpg', $defaultImages['small_image']);
        $this->assertEquals('/m/a/magento_thumbnail.jpg', $defaultImages['thumbnail']);
        $this->assertEquals('/m/a/magento_thumbnail.jpg', $defaultImages['swatch_image']);
    }

    /**
     * @return array
     */
    public function executeWithTwoImagesAndRolesDataProvider(): array
    {
        return [
            'unassign_all_roles' => [
                'roles' => [
                    'image' => 'no_selection',
                    'small_image' =>'no_selection',
                    'thumbnail' => 'no_selection',
                    'swatch_image' => 'no_selection',
                ],
            ],
            'assign_already_used_role' => [
                'roles' => [
                    'image' => '/m/a/magento_image.jpg',
                    'small_image' => '/m/a/magento_thumbnail.jpg',
                    'thumbnail' => '/m/a/magento_thumbnail.jpg',
                    'swatch_image' => '/m/a/magento_image.jpg',
                ],
            ],
        ];
    }

    /**
     * Tests updating image position during product save.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_multiple_images.php
     * @magentoDbIsolation enabled
     * @return void
     * @throws LocalizedException
     */
    public function testExecuteWithTwoImagesAndChangedPosition(): void
    {
        $positionMap = [
            '/m/a/magento_image.jpg' => '2',
            '/m/a/magento_thumbnail.jpg' => '1',
        ];
        $product = $this->getProduct();
        $images = $product->getData('media_gallery')['images'];
        foreach ($images as &$image) {
            $image['position'] = $positionMap[$image['file']];
        }
        $product->setData('store_id', Store::DEFAULT_STORE_ID);
        $product->setData('media_gallery', ['images' => $images]);
        $this->updateHandler->execute($product);
        $productImages = $this->galleryResource->loadProductGalleryByAttributeId($product, $this->mediaAttributeId);
        foreach ($productImages as $updatedImage) {
            $this->assertEquals($positionMap[$updatedImage['file']], $updatedImage['position']);
            $this->assertEquals($positionMap[$updatedImage['file']], $updatedImage['position_default']);
        }
    }

    /**
     * Tests image remove during product save.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     * @magentoDbIsolation enabled
     * @return void
     * @throws LocalizedException
     */
    public function testExecuteWithImageToDelete(): void
    {
        $product = $this->getProduct();
        $image = $product->getImage();
        $this->updateProductGalleryImages($product, ['removed' => '1']);
        $this->updateHandler->execute($product);
        $productImages = $this->galleryResource->loadProductGalleryByAttributeId($product, $this->mediaAttributeId);
        $this->assertCount(0, $productImages);
        $this->assertFileDoesNotExist(
            $this->mediaDirectory->getAbsolutePath($this->config->getBaseMediaPath() . $image)
        );
        $defaultImages = $this->productResource->getAttributeRawValue(
            $product->getId(),
            ['image', 'small_image', 'thumbnail', 'swatch_image'],
            Store::DEFAULT_STORE_ID
        );
        $this->assertEquals('no_selection', $defaultImages['image']);
        $this->assertEquals('no_selection', $defaultImages['small_image']);
        $this->assertEquals('no_selection', $defaultImages['thumbnail']);
    }

    /**
     * Tests updating images data during product save on non default store view.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_multiple_images.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDbIsolation enabled
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testExecuteWithTwoImagesOnStoreView(): void
    {
        $secondStoreId = (int)$this->storeRepository->get('fixture_second_store')->getId();
        $storeImages = [
            '/m/a/magento_image.jpg' => [
                'label' => 'Store image',
                'label_default' => 'Image Alt Text',
                'disabled' => '1',
                'disabled_default' => '0',
                'position' => '2',
                'position_default' => '1',
            ],
            '/m/a/magento_thumbnail.jpg' => [
                'label' => 'Store thumbnail',
                'label_default' => 'Thumbnail Image',
                'disabled' => '0',
                'disabled_default' => '0',
                'position' => '1',
                'position_default' => '2',
            ],
        ];
        $product = $this->getProduct($secondStoreId);
        $images = $product->getData('media_gallery')['images'];
        foreach ($images as &$image) {
            $image['label'] = $storeImages[$image['file']]['label'];
            $image['disabled'] = $storeImages[$image['file']]['disabled'];
            $image['position'] = $storeImages[$image['file']]['position'];
        }
        $product->setData('media_gallery', ['images' => $images]);
        $this->updateHandler->execute($product);
        $productImages = $this->galleryResource->loadProductGalleryByAttributeId($product, $this->mediaAttributeId);
        foreach ($productImages as $image) {
            $imageToAssert = [
                'label' => $image['label'],
                'label_default' =>$image['label_default'],
                'disabled' =>$image['disabled'],
                'disabled_default' => $image['disabled_default'],
                'position' => $image['position'],
                'position_default' => $image['position_default'],
            ];
            $this->assertEquals($storeImages[$image['file']], $imageToAssert);
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @return void
     * @throws LocalizedException
     */
    public function testDeleteSharedImage(): void
    {
        $product = $this->getProduct(null, 'simple');
        $this->duplicateMediaGalleryForProduct('/m/a/magento_image.jpg', 'simple2');
        $secondProduct = $this->getProduct(null, 'simple2');
        $this->updateHandler->execute($this->prepareRemoveImage($product), []);
        $product = $this->getProduct(null, 'simple');
        $this->assertEmpty($product->getMediaGalleryImages()->getItems());
        $this->checkProductImageExist($secondProduct, '/m/a/magento_image.jpg');
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->storeManager->setCurrentStore($this->currentStoreId);
        parent::tearDown();
        $this->mediaDirectory->getDriver()->deleteFile($this->mediaDirectory->getAbsolutePath($this->fileName));
        $this->galleryResource->getConnection()
            ->delete($this->galleryResource->getTable(Gallery::GALLERY_TABLE));
        $this->galleryResource->getConnection()
            ->delete($this->galleryResource->getTable(Gallery::GALLERY_VALUE_TABLE));
        $this->galleryResource->getConnection()
            ->delete($this->galleryResource->getTable(Gallery::GALLERY_VALUE_TO_ENTITY_TABLE));
    }

    /**
     * Returns current product.
     *
     * @param int|null $storeId
     * @param string|null $sku
     * @return ProductInterface|Product
     * @throws NoSuchEntityException
     */
    private function getProduct(?int $storeId = null, ?string $sku = null): ProductInterface
    {
        $sku = $sku ?: 'simple';
        return $this->productRepository->get($sku, false, $storeId, true);
    }

    /**
     * @param ProductInterface|Product $product
     * @param array $imageData
     * @return void
     */
    private function updateProductGalleryImages(ProductInterface $product, array $imageData): void
    {
        $images = $product->getData('media_gallery')['images'];
        $image = reset($images) ?: [];
        $product->setData('store_id', Store::DEFAULT_STORE_ID);
        $product->setData('media_gallery', ['images' => ['image' => array_merge($image, $imageData)]]);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoDbIsolation disabled
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */
    public function testDeleteWithMultiWebsites(): void
    {
        $defaultWebsiteId = (int) $this->storeManager->getWebsite('base')->getId();
        $secondWebsiteId = (int) $this->storeManager->getWebsite('test')->getId();
        $defaultStoreId = (int) $this->storeManager->getStore('default')->getId();
        $secondStoreId = (int) $this->storeManager->getStore('fixture_second_store')->getId();
        $imageRoles = ['image', 'small_image', 'thumbnail'];
        $globalScopeId = Store::DEFAULT_STORE_ID;
        $this->storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);
        $product = $this->getProduct($globalScopeId);
        // Assert that product has images
        $this->assertNotEmpty($product->getMediaGalleryEntries());
        $image = $product->getImage();
        $path = $this->mediaDirectory->getAbsolutePath($this->config->getBaseMediaPath() . $image);
        $this->assertTrue($this->mediaDirectory->isExist($path));
        // Assign product to default and second website and save changes
        $product->setWebsiteIds([$defaultWebsiteId, $secondWebsiteId]);
        $this->productRepository->save($product);
        // Assert that product image has roles in global scope only
        $imageRolesPerStore = $this->getProductStoreImageRoles($product, $imageRoles);
        $this->assertEquals($image, $imageRolesPerStore[$globalScopeId]['image']);
        $this->assertEquals($image, $imageRolesPerStore[$globalScopeId]['small_image']);
        $this->assertEquals($image, $imageRolesPerStore[$globalScopeId]['thumbnail']);
        $this->assertArrayNotHasKey($defaultStoreId, $imageRolesPerStore);
        $this->assertArrayNotHasKey($secondStoreId, $imageRolesPerStore);
        // Assign roles to product image on second store and save changes
        $this->storeManager->setCurrentStore($secondStoreId);
        $product = $this->getProduct($secondStoreId);
        $product->addData(array_fill_keys($imageRoles, $image));
        $this->productRepository->save($product);
        // Assert that roles are assigned to product image for second store
        $imageRolesPerStore = $this->getProductStoreImageRoles($product, $imageRoles);
        $this->assertEquals($image, $imageRolesPerStore[$globalScopeId]['image']);
        $this->assertEquals($image, $imageRolesPerStore[$globalScopeId]['small_image']);
        $this->assertEquals($image, $imageRolesPerStore[$globalScopeId]['thumbnail']);
        $this->assertArrayNotHasKey($defaultStoreId, $imageRolesPerStore);
        $this->assertEquals($image, $imageRolesPerStore[$secondStoreId]['image']);
        $this->assertEquals($image, $imageRolesPerStore[$secondStoreId]['small_image']);
        $this->assertEquals($image, $imageRolesPerStore[$secondStoreId]['thumbnail']);
        // Delete existing images and save changes
        $this->storeManager->setCurrentStore($globalScopeId);
        $product = $this->getProduct($globalScopeId);
        $product->setMediaGalleryEntries([]);
        $this->productRepository->save($product);
        $product = $this->getProduct($globalScopeId);
        // Assert that image was not deleted as it has roles in second store
        $this->assertNotEmpty($product->getMediaGalleryEntries());
        $this->assertTrue($this->mediaDirectory->isExist($path));
        // Unlink second website, delete existing images and save changes
        $product->setWebsiteIds([$defaultWebsiteId]);
        $product->setMediaGalleryEntries([]);
        $this->productRepository->save($product);
        $product = $this->getProduct($globalScopeId);
        // Assert that image was deleted and product has no images
        $this->assertEmpty($product->getMediaGalleryEntries());
        $this->assertFileDoesNotExist($path);
        // Load image roles
        $imageRolesPerStore = $this->getProductStoreImageRoles($product, $imageRoles);
        // Assert that image roles are reset on global scope and removed on second store
        // as the product is no longer assigned to second website
        $this->assertEquals('no_selection', $imageRolesPerStore[$globalScopeId]['image']);
        $this->assertEquals('no_selection', $imageRolesPerStore[$globalScopeId]['small_image']);
        $this->assertEquals('no_selection', $imageRolesPerStore[$globalScopeId]['thumbnail']);
        $this->assertArrayNotHasKey($defaultStoreId, $imageRolesPerStore);
        $this->assertArrayNotHasKey($secondStoreId, $imageRolesPerStore);
    }

    /**
     * Check that product images should be updated successfully regardless if the existing images exist or not
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     * @dataProvider updateImageDataProvider
     * @param string $newFile
     * @param string $expectedFile
     * @param bool $exist
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws FileSystemException
     */
    public function testUpdateImage(string $newFile, string $expectedFile, bool $exist): void
    {
        $product = $this->getProduct(Store::DEFAULT_STORE_ID);
        $images = $product->getData('media_gallery')['images'];
        $this->assertCount(1, $images);
        $oldImage = reset($images) ?: [];
        $this->assertEquals($oldImage['file'], $product->getImage());
        $this->assertEquals($oldImage['file'], $product->getSmallImage());
        $this->assertEquals($oldImage['file'], $product->getThumbnail());
        $path = $this->mediaDirectory->getAbsolutePath($this->config->getBaseMediaPath() . $oldImage['file']);
        $tmpPath = $this->mediaDirectory->getAbsolutePath($this->config->getBaseTmpMediaPath() . $oldImage['file']);
        $this->assertTrue($this->mediaDirectory->isExist($path));
        $this->mediaDirectory->getDriver()->copy($path, $tmpPath);
        if (!$exist) {
            $this->mediaDirectory->getDriver()->deleteFile($path);
            $this->assertFileDoesNotExist($path);
        }
        // delete old image
        $oldImage['removed'] = 1;
        $newImage = [
            'file' => $newFile,
            'position' => 1,
            'label' => 'New Image Alt Text',
            'disabled' => 0,
            'media_type' => 'image'
        ];
        $newImageRoles = [
            'image' => $newFile,
            'small_image' => 'no_selection',
            'thumbnail' => 'no_selection',
        ];
        $product->setData('media_gallery', ['images' => [$oldImage, $newImage]]);
        $product->addData($newImageRoles);
        $this->updateHandler->execute($product);
        $product = $this->getProduct(Store::DEFAULT_STORE_ID);
        $images = $product->getData('media_gallery')['images'];
        $this->assertCount(1, $images);
        $image = reset($images) ?: [];
        $this->assertEquals($newImage['label'], $image['label']);
        $this->assertEquals($expectedFile, $product->getImage());
        $this->assertEquals($newImageRoles['small_image'], $product->getSmallImage());
        $this->assertEquals($newImageRoles['thumbnail'], $product->getThumbnail());
        $path = $this->mediaDirectory->getAbsolutePath($this->config->getBaseMediaPath() . $product->getImage());
        // Assert that the image exists on disk.
        $this->assertTrue($this->mediaDirectory->isExist($path));
    }

    /**
     * @return array[]
     */
    public function updateImageDataProvider(): array
    {
        return [
            [
                '/m/a/magento_image.jpg',
                '/m/a/magento_image_1.jpg',
                true
            ],
            [
                '/m/a/magento_image.jpg',
                '/m/a/magento_image.jpg',
                false
            ],
            [
                '/m/a/magento_small_image.jpg',
                '/m/a/magento_small_image.jpg',
                true
            ],
            [
                '/m/a/magento_small_image.jpg',
                '/m/a/magento_small_image.jpg',
                false
            ]
        ];
    }

    /**
     * Tests that images are added correctly
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @dataProvider addImagesDataProvider
     * @param string $addFromStore
     * @param array $newImages
     * @param string $viewFromStore
     * @param array $expectedImages
     * @param array $select
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testAddImages(
        string $addFromStore,
        array $newImages,
        string $viewFromStore,
        array $expectedImages,
        array $select = ['file', 'label', 'position']
    ): void {
        $storeId = (int)$this->storeRepository->get($addFromStore)->getId();
        $product = $this->getProduct($storeId);
        $images = $product->getData('media_gallery')['images'];
        $images = array_merge($images, $newImages);
        $product->setData('media_gallery', ['images' => $images]);
        $this->updateHandler->execute($product);
        $storeId = (int)$this->storeRepository->get($viewFromStore)->getId();
        $product = $this->getProduct($storeId);
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
    public function addImagesDataProvider(): array
    {
        return [
            [
                'fixture_second_store',
                [
                    [
                        'file' => '/m/a/magento_small_image.jpg',
                        'position' => 2,
                        'label' => 'New Image Alt Text',
                        'disabled' => 0,
                        'media_type' => 'image'
                    ]
                ],
                'default',
                [
                    [
                        'file' => '/m/a/magento_image.jpg',
                        'label' => 'Image Alt Text',
                        'position' => 1,
                    ],
                    [
                        'file' => '/m/a/magento_small_image.jpg',
                        'label' => null,
                        'position' => 2,
                    ],
                ]
            ],
            [
                'fixture_second_store',
                [
                    [
                        'file' => '/m/a/magento_small_image.jpg',
                        'position' => 2,
                        'label' => 'New Image Alt Text',
                        'disabled' => 0,
                        'media_type' => 'image'
                    ]
                ],
                'fixture_second_store',
                [
                    [
                        'file' => '/m/a/magento_image.jpg',
                        'label' => 'Image Alt Text',
                        'position' => 1,
                    ],
                    [
                        'file' => '/m/a/magento_small_image.jpg',
                        'label' => 'New Image Alt Text',
                        'position' => 2,
                    ],
                ]
            ]
        ];
    }

    /**
     * Check product image link and product image exist
     *
     * @param ProductInterface $product
     * @param string $imagePath
     * @return void
     * @throws FileSystemException
     */
    private function checkProductImageExist(ProductInterface $product, string $imagePath): void
    {
        $productImageItem = $product->getMediaGalleryImages()->getFirstItem();
        $this->assertEquals($imagePath, $productImageItem->getFile());
        $productImageFile = $productImageItem->getPath();
        $this->assertNotEmpty($productImageFile);
        $this->assertTrue($this->mediaDirectory->getDriver()->isExists($productImageFile));
        $this->fileName = $productImageFile;
    }

    /**
     * Prepare the product to remove image
     *
     * @param ProductInterface $product
     * @return ProductInterface
     */
    private function prepareRemoveImage(ProductInterface $product): ProductInterface
    {
        $item = $product->getMediaGalleryImages()->getFirstItem();
        $item->setRemoved('1');
        $galleryData = [
            'images' => [
                (int)$item->getValueId() => $item->getData(),
            ]
        ];
        $product->setData(ProductInterface::MEDIA_GALLERY, $galleryData);
        $product->setStoreId(0);

        return $product;
    }

    /**
     * Duplicate media gallery entries for a product
     *
     * @param string $imagePath
     * @param string $productSku
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function duplicateMediaGalleryForProduct(string $imagePath, string $productSku): void
    {
        $product = $this->getProduct(null, $productSku);
        $connect = $this->galleryResource->getConnection();
        $select = $connect->select()->from($this->galleryResource->getMainTable())->where('value = ?', $imagePath);
        $result = $connect->fetchRow($select);
        $value_id = $result['value_id'];
        unset($result['value_id']);
        $rows = [
            'attribute_id' => $result['attribute_id'],
            'value' => $result['value'],
            ProductAttributeMediaGalleryEntryInterface::MEDIA_TYPE => $result['media_type'],
            ProductAttributeMediaGalleryEntryInterface::DISABLED => $result['disabled'],
        ];
        $connect->insert($this->galleryResource->getMainTable(), $rows);
        $select = $connect->select()
            ->from($this->galleryResource->getTable(Gallery::GALLERY_VALUE_TABLE))
            ->where('value_id = ?', $value_id);
        $result = $connect->fetchRow($select);
        $newValueId = (int)$value_id + 1;
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();
        $rows = [
            'value_id' => $newValueId,
            'store_id' => $result['store_id'],
            ProductAttributeMediaGalleryEntryInterface::LABEL => $result['label'],
            ProductAttributeMediaGalleryEntryInterface::POSITION => $result['position'],
            ProductAttributeMediaGalleryEntryInterface::DISABLED => $result['disabled'],
            $linkField => $product->getData($linkField),
        ];
        $connect->insert($this->galleryResource->getTable(Gallery::GALLERY_VALUE_TABLE), $rows);
        $rows = ['value_id' => $newValueId, $linkField => $product->getData($linkField)];
        $connect->insert($this->galleryResource->getTable(Gallery::GALLERY_VALUE_TO_ENTITY_TABLE), $rows);
    }

    /**
     * @param Product $product
     * @param array $roles
     * @return array
     */
    private function getProductStoreImageRoles(Product $product, array $roles = []): array
    {
        $imageRolesPerStore = [];
        $stores = array_keys($this->storeManager->getStores(true));
        foreach ($this->galleryResource->getProductImages($product, $stores) as $role) {
            if (empty($roles) || in_array($role['attribute_code'], $roles)) {
                $imageRolesPerStore[$role['store_id']][$role['attribute_code']] = $role['filepath'];
            }
        }
        return $imageRolesPerStore;
    }
}
