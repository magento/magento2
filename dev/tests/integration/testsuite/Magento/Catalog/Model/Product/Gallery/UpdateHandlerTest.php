<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Provides tests for media gallery images update during product save.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateHandlerTest extends \PHPUnit\Framework\TestCase
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
        $this->mediaDirectory = $this->objectManager->get(Filesystem::class)
            ->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaDirectory->writeFile($this->fileName, 'Test');
    }

    /**
     * Tests updating image with illegal filename during product save.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/product_image.php
     *
     * @return void
     */
    public function testExecuteWithIllegalFilename(): void
    {
        $product = $this->getProduct();
        $product->setData(
            'media_gallery',
            [
                'images' => [
                    'image' => [
                        'value_id' => '100',
                        'file' => '/../..' . DIRECTORY_SEPARATOR . $this->fileName,
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
     * Tests updating image roles during product save.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_multiple_images.php
     * @dataProvider executeWithTwoImagesAndRolesDataProvider
     * @magentoDbIsolation enabled
     * @param array $roles
     * @return void
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
     */
    public function testExecuteWithTwoImagesAndDifferentRolesOnStoreView(array $roles): void
    {
        $secondStoreId = (int)$this->storeRepository->get('fixture_second_store')->getId();
        $imageRoles = ['image', 'small_image', 'thumbnail', 'swatch_image'];
        $product = $this->getProduct($secondStoreId);
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
     */
    public function testExecuteWithImageToDelete(): void
    {
        $product = $this->getProduct();
        $image = $product->getImage();
        $this->updateProductGalleryImages($product, ['removed' => '1']);
        $this->updateHandler->execute($product);
        $productImages = $this->galleryResource->loadProductGalleryByAttributeId($product, $this->mediaAttributeId);
        $this->assertCount(0, $productImages);
        $this->assertFileNotExists(
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
     * @inheritdoc
     */
    protected function tearDown(): void
    {
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
     * @return ProductInterface|Product
     */
    private function getProduct(?int $storeId = null): ProductInterface
    {
        return $this->productRepository->get('simple', false, $storeId, true);
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
}
