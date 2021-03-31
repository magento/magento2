<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\View;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Gallery\UpdateHandler;
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
    protected function setUp(): void
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
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoConfigFixture default/web/url/catalog_media_url_format image_optimization_parameters
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testGetGalleryImagesJsonWithoutImagesWithImageOptimizationParametersInUrl(): void
    {
        $this->block->setData('product', $this->getProduct());
        $result = $this->serializer->unserialize($this->block->getGalleryImagesJson());
        $this->assertImages(reset($result), $this->placeholderExpectation);
    }

    /**
     * @dataProvider galleryDisabledImagesDataProvider
     * @magentoDataFixture Magento/Catalog/_files/product_with_multiple_images.php
     * @magentoConfigFixture default/web/url/catalog_media_url_format hash
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
     * @magentoConfigFixture default/web/url/catalog_media_url_format hash
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
     * Test default image generation format.
     *
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
     * @dataProvider galleryImagesWithImageOptimizationParametersInUrlDataProvider
     * @magentoDataFixture Magento/Catalog/_files/product_with_multiple_images.php
     * @magentoConfigFixture default/web/url/catalog_media_url_format image_optimization_parameters
     * @magentoDbIsolation enabled
     * @param array $images
     * @param array $expectation
     * @return void
     */
    public function testGetGalleryImagesJsonWithImageOptimizationParametersInUrl(
        array $images,
        array $expectation
    ): void {
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
    public function galleryImagesWithImageOptimizationParametersInUrlDataProvider(): array
    {

        $imageExpectation = [
            'thumb' => '/m/a/magento_image.jpg?width=88&height=110&store=default&image-type=thumbnail',
            'img' => '/m/a/magento_image.jpg?width=700&height=700&store=default&image-type=image',
            'full' => '/m/a/magento_image.jpg?store=default&image-type=image',
            'caption' => 'Image Alt Text',
            'position' => '1',
            'isMain' => false,
            'type' => 'image',
            'videoUrl' => null,
        ];

        $thumbnailExpectation = [
            'thumb' => '/m/a/magento_thumbnail.jpg?width=88&height=110&store=default&image-type=thumbnail',
            'img' => '/m/a/magento_thumbnail.jpg?width=700&height=700&store=default&image-type=image',
            'full' => '/m/a/magento_thumbnail.jpg?store=default&image-type=image',
            'caption' => 'Thumbnail Image',
            'position' => '2',
            'isMain' => false,
            'type' => 'image',
            'videoUrl' => null,
        ];

        return [
            'with_main_image' => [
                'images' => [
                    '/m/a/magento_image.jpg' => [],
                    '/m/a/magento_thumbnail.jpg' => ['main' => true],
                ],
                'expectation' => [
                    $imageExpectation,
                    array_merge($thumbnailExpectation, ['isMain' => true]),
                ],
            ],
            'without_main_image' => [
                'images' => [
                    '/m/a/magento_image.jpg' => [],
                    '/m/a/magento_thumbnail.jpg' => [],
                ],
                'expectation' => [
                    array_merge($imageExpectation, ['isMain' => true]),
                    $thumbnailExpectation,
                ],
            ],
            'with_changed_position' => [
                'images' => [
                    '/m/a/magento_image.jpg' => ['position' => '2'],
                    '/m/a/magento_thumbnail.jpg' => ['position' => '1'],
                ],
                'expectation' => [
                    array_merge($thumbnailExpectation, ['position' => '1']),
                    array_merge($imageExpectation, ['position' => '2', 'isMain' => true]),
                ],
            ],
        ];
    }

    /**
     * @dataProvider galleryImagesOnStoreViewDataProvider
     * @magentoDataFixture Magento/Catalog/_files/product_with_multiple_images.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoConfigFixture default/web/url/catalog_media_url_format hash
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
     * Tests images positions in store view
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoConfigFixture default/web/url/catalog_media_url_format image_optimization_parameters
     * @dataProvider imagesPositionStoreViewDataProvider
     * @param string $addFromStore
     * @param array $newImages
     * @param string $viewFromStore
     * @param array $expectedImages
     * @return void
     */
    public function testImagesPositionStoreView(
        string $addFromStore,
        array $newImages,
        string $viewFromStore,
        array $expectedImages
    ): void {
        $storeId = (int)$this->storeRepository->get($addFromStore)->getId();
        $product = $this->getProduct($storeId);
        $images = $product->getData('media_gallery')['images'];
        $images = array_merge($images, $newImages);
        $product->setData('media_gallery', ['images' => $images]);
        $updateHandler = Bootstrap::getObjectManager()->create(UpdateHandler::class);
        $updateHandler->execute($product);
        $storeId = (int)$this->storeRepository->get($viewFromStore)->getId();
        $product = $this->getProduct($storeId);
        $this->block->setData('product', $product);
        $actualImages = array_map(
            function ($item) {
                return [
                    'img' => parse_url($item['img'], PHP_URL_PATH),
                    'caption' => $item['caption'],
                    'position' => $item['position'],
                ];
            },
            $this->serializer->unserialize($this->block->getGalleryImagesJson())
        );
        $this->assertEquals($expectedImages, array_values($actualImages));
    }

    /**
     * @return array[]
     */
    public function imagesPositionStoreViewDataProvider(): array
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
                        'img' => '/media/catalog/product/m/a/magento_image.jpg',
                        'caption' => 'Image Alt Text',
                        'position' => 1,
                    ],
                    [
                        'img' => '/media/catalog/product/m/a/magento_small_image.jpg',
                        'caption' => 'Simple Product',
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
                        'img' => '/media/catalog/product/m/a/magento_image.jpg',
                        'caption' => 'Image Alt Text',
                        'position' => 1,
                    ],
                    [
                        'img' => '/media/catalog/product/m/a/magento_small_image.jpg',
                        'caption' => 'New Image Alt Text',
                        'position' => 2,
                    ],
                ]
            ]
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
