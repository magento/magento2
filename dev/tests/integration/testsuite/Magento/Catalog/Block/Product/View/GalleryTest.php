<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\View;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/**
 * Provide tests for displaying images on product page.
 *
 * @magentoAppArea frontend
 */
class GalleryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var Gallery
     */
    private $block;

    /**
     * @var array
     */
    private $imageExpectation = [
        'thumb' => '/m/a/magento_image.jpg',
        'img' => '/m/a/magento_image.jpg',
        'full' => '/m/a/magento_image.jpg',
        'caption' => 'Image Alt Text',
        'position' => '1',
        'isMain' => false,
        'type' => 'image',
        'videoUrl' => null,
    ];

    /**
     * @var array
     */
    private $thumbnailExpectation = [
        'thumb' => '/m/a/magento_thumbnail.jpg',
        'img' => '/m/a/magento_thumbnail.jpg',
        'full' => '/m/a/magento_thumbnail.jpg',
        'caption' => 'Thumbnail Image',
        'position' => '2',
        'isMain' => false,
        'type' => 'image',
        'videoUrl' => null,
    ];

    /**
     * @var array
     */
    private $placeholderExpectation = [
        'thumb' => '/placeholder/thumbnail.jpg',
        'img' => '/placeholder/image.jpg',
        'full' => '/placeholder/image.jpg',
        'caption' => '',
        'position' => '0',
        'isMain' => true,
        'type' => 'image',
        'videoUrl' => null,
    ];

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->productResource = $this->objectManager->get(ProductResource::class);
        $this->storeRepository = $this->objectManager->create(StoreRepositoryInterface::class);
        $this->serializer = $this->objectManager->get(Json::class);
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Gallery::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testGetGalleryImagesJsonWithoutImages(): void
    {
        $this->block->setData('product', $this->getProduct());
        $result = $this->serializer->unserialize($this->block->getGalleryImagesJson());
        $this->assertImages(reset($result), $this->placeholderExpectation);
    }

    /**
     * @dataProvider galleryDisabledImagesDataProvider
     * @magentoDataFixture Magento/Catalog/_files/product_with_multiple_images.php
     * @magentoDbIsolation enabled
     * @param array $images
     * @param array $expectation
     * @return void
     */
    public function testGetGalleryImagesJsonWithDisabledImage(array $images, array $expectation): void
    {
        $product = $this->getProduct();
        $this->setGalleryImages($product, $images);
        $this->block->setData('product', $this->getProduct());
        $firstImage = $this->serializer->unserialize($this->block->getGalleryImagesJson());
        $this->assertImages(reset($firstImage), $expectation);
    }

    /**
     * @dataProvider galleryDisabledImagesDataProvider
     * @magentoDataFixture Magento/Catalog/_files/product_with_multiple_images.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDbIsolation disabled
     * @param array $images
     * @param array $expectation
     * @return void
     */
    public function testGetGalleryImagesJsonOnStoreWithDisabledImage(array $images, array $expectation): void
    {
        $secondStoreId = (int)$this->storeRepository->get('fixture_second_store')->getId();
        $product = $this->getProduct($secondStoreId);
        $this->setGalleryImages($product, $images);
        $this->block->setData('product', $this->getProduct($secondStoreId));
        $firstImage = $this->serializer->unserialize($this->block->getGalleryImagesJson());
        $this->assertImages(reset($firstImage), $expectation);
    }

    /**
     * @return array
     */
    public function galleryDisabledImagesDataProvider(): array
    {
        return [
            [
                'images' => [
                    '/m/a/magento_image.jpg' => ['disabled' => true],
                    '/m/a/magento_thumbnail.jpg' => [],
                ],
                'expectation' => $this->thumbnailExpectation,
            ],
        ];
    }

    /**
     * @dataProvider galleryImagesDataProvider
     * @magentoDataFixture Magento/Catalog/_files/product_with_multiple_images.php
     * @magentoDbIsolation enabled
     * @param array $images
     * @param array $expectation
     * @return void
     */
    public function testGetGalleryImagesJson(array $images, array $expectation): void
    {
        $product = $this->getProduct();
        $this->setGalleryImages($product, $images);
        $this->block->setData('product', $this->getProduct());
        [$firstImage, $secondImage] = $this->serializer->unserialize($this->block->getGalleryImagesJson());
        [$firstExpectedImage, $secondExpectedImage] = $expectation;
        $this->assertImages($firstImage, $firstExpectedImage);
        $this->assertImages($secondImage, $secondExpectedImage);
    }

    /**
     * @return array
     */
    public function galleryImagesDataProvider(): array
    {
        return [
            'with_main_image' => [
                'images' => [
                    '/m/a/magento_image.jpg' => [],
                    '/m/a/magento_thumbnail.jpg' => ['main' => true],
                ],
                'expectation' => [
                    $this->imageExpectation,
                    array_merge($this->thumbnailExpectation, ['isMain' => true]),
                ],
            ],
            'without_main_image' => [
                'images' => [
                    '/m/a/magento_image.jpg' => [],
                    '/m/a/magento_thumbnail.jpg' => [],
                ],
                'expectation' => [
                    array_merge($this->imageExpectation, ['isMain' => true]),
                    $this->thumbnailExpectation,
                ],
            ],
            'with_changed_position' => [
                'images' => [
                    '/m/a/magento_image.jpg' => ['position' => '2'],
                    '/m/a/magento_thumbnail.jpg' => ['position' => '1'],
                ],
                'expectation' => [
                    array_merge($this->thumbnailExpectation, ['position' => '1']),
                    array_merge($this->imageExpectation, ['position' => '2', 'isMain' => true]),
                ],
            ],
        ];
    }

    /**
     * @dataProvider galleryImagesOnStoreViewDataProvider
     * @magentoDataFixture Magento/Catalog/_files/product_with_multiple_images.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDbIsolation disabled
     * @param array $images
     * @param array $expectation
     * @return void
     */
    public function testGetGalleryImagesJsonOnStoreView(array $images, array $expectation): void
    {
        $secondStoreId = (int)$this->storeRepository->get('fixture_second_store')->getId();
        $product = $this->getProduct($secondStoreId);
        $this->setGalleryImages($product, $images);
        $this->block->setData('product', $this->getProduct($secondStoreId));
        [$firstImage, $secondImage] = $this->serializer->unserialize($this->block->getGalleryImagesJson());
        [$firstExpectedImage, $secondExpectedImage] = $expectation;
        $this->assertImages($firstImage, $firstExpectedImage);
        $this->assertImages($secondImage, $secondExpectedImage);
    }

    /**
     * @return array
     */
    public function galleryImagesOnStoreViewDataProvider(): array
    {
        return [
            'with_store_labels' => [
                'images' => [
                    '/m/a/magento_image.jpg' => ['label' => 'Some store label'],
                    '/m/a/magento_thumbnail.jpg' => [],
                ],
                'expectation' => [
                    array_merge($this->imageExpectation, ['isMain' => true, 'caption' => 'Some store label']),
                    $this->thumbnailExpectation,
                ],
            ],
            'with_changed_position' => [
                'images' => [
                    '/m/a/magento_image.jpg' => ['position' => '3'],
                    '/m/a/magento_thumbnail.jpg' => [],
                ],
                'expectation' => [
                    array_merge($this->thumbnailExpectation, ['position' => '2']),
                    array_merge($this->imageExpectation, ['position' => '3', 'isMain' => true]),
                ],
            ],
            'with_main_store_image' => [
                'images' => [
                    '/m/a/magento_image.jpg' => [],
                    '/m/a/magento_thumbnail.jpg' => ['main' => true],
                ],
                'expectation' => [
                    $this->imageExpectation,
                    array_merge($this->thumbnailExpectation, ['isMain' => true]),
                ],
            ],
        ];
    }

    /**
     * Updates product gallery images and saves product.
     *
     * @param ProductInterface $product
     * @param array $images
     * @param int|null $storeId
     * @return void
     */
    private function setGalleryImages(ProductInterface $product, array $images, int $storeId = null): void
    {
        $product->setImage(null);
        foreach ($images as $file => $data) {
            $mediaGalleryData = $product->getData('media_gallery');
            foreach ($mediaGalleryData['images'] as &$image) {
                if ($image['file'] == $file) {
                    foreach ($data as $key => $value) {
                        $image[$key] = $value;
                    }
                }
            }

            $product->setData('media_gallery', $mediaGalleryData);

            if (!empty($data['main'])) {
                $product->setImage($file);
            }
        }

        if ($storeId) {
            $product->setStoreId($storeId);
        }

        $this->productResource->save($product);
    }

    /**
     * Returns current product.
     *
     * @param int|null $storeId
     * @return ProductInterface
     */
    private function getProduct(?int $storeId = null): ProductInterface
    {
        return $this->productRepository->get('simple', false, $storeId, true);
    }

    /**
     * Asserts gallery image data.
     *
     * @param array $image
     * @param array $expectedImage
     * @return void
     */
    private function assertImages(array $image, array $expectedImage): void
    {
        $this->assertStringEndsWith($expectedImage['thumb'], $image['thumb']);
        $this->assertStringEndsWith($expectedImage['img'], $image['img']);
        $this->assertStringEndsWith($expectedImage['full'], $image['full']);
        $this->assertEquals($expectedImage['caption'], $image['caption']);
        $this->assertEquals($expectedImage['position'], $image['position']);
        $this->assertEquals($expectedImage['isMain'], $image['isMain']);
        $this->assertEquals($expectedImage['type'], $image['type']);
        $this->assertEquals($expectedImage['videoUrl'], $image['videoUrl']);
    }
}
