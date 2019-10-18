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
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/**
 * Provide tests for loading gallery images on product load.
 */
class ReadHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ReadHandler
     */
    private $readHandler;

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
     * @var Processor
     */
    private $galleryProcessor;

    /**
     * @var EntityMetadata
     */
    private $metadata;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->readHandler = $this->objectManager->create(ReadHandler::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productResource = $this->objectManager->get(ProductResource::class);
        $this->storeRepository = $this->objectManager->create(StoreRepositoryInterface::class);
        $this->galleryProcessor = $this->objectManager->create(Processor::class);
        $this->metadata = $this->objectManager->get(MetadataPool::class)->getMetadata(ProductInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testExecuteWithoutImages(): void
    {
        $product = $this->getProductInstance();
        $this->readHandler->execute($product);
        $data = $product->getData();
        $this->assertArrayHasKey('media_gallery', $data);
        $this->assertArrayHasKey('images', $data['media_gallery']);
        $this->assertCount(0, $data['media_gallery']['images']);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testExecuteWithOneImage(): void
    {
        $product = $this->getProductInstance();
        $this->readHandler->execute($product);
        $data = $product->getData();
        $this->assertArrayHasKey('media_gallery', $data);
        $this->assertArrayHasKey('images', $data['media_gallery']);
        $this->assertCount(1, $data['media_gallery']['images']);
        $galleryImage = reset($data['media_gallery']['images']);
        $this->assertEquals('/m/a/magento_image.jpg', $galleryImage['file']);
        $this->assertEquals(1, $galleryImage['position']);
        $this->assertEquals('Image Alt Text', $galleryImage['label']);
        $this->assertEquals(0, $galleryImage['disabled']);
    }

    /**
     * @dataProvider executeWithTwoImagesDataProvider
     * @magentoDataFixture Magento/Catalog/_files/product_with_multiple_images.php
     * @magentoDbIsolation enabled
     * @param array $images
     * @param array $expectation
     * @return void
     */
    public function testExecuteWithTwoImages(array $images, array $expectation): void
    {
        $product = $this->getProduct();
        foreach ($images as $file => $data) {
            if (!empty($data)) {
                $this->galleryProcessor->updateImage($product, $file, $data);
            }
        }
        $this->saveProduct($product);
        $productInstance = $this->getProductInstance();
        $this->readHandler->execute($productInstance);
        $data = $productInstance->getData();
        $this->assertArrayHasKey('media_gallery', $data);
        $this->assertArrayHasKey('images', $data['media_gallery']);
        $this->assertCount(count($expectation), $data['media_gallery']['images']);
        $imagesToAssert = [];
        foreach ($data['media_gallery']['images'] as $valueId => $imageData) {
            $imagesToAssert[] = [
                'file' => $imageData['file'],
                'label' => $imageData['label'],
                'position' => $imageData['position'],
                'disabled' => $imageData['disabled'],
            ];
            $this->assertEquals(
                $imageData['value_id'],
                $valueId
            );
        }
        $this->assertEquals($expectation, $imagesToAssert);
    }

    /**
     * @return array
     */
    public function executeWithTwoImagesDataProvider(): array
    {
        return [
            'with_two_images' => [
                'images' => [
                    '/m/a/magento_image.jpg' => [],
                    '/m/a/magento_thumbnail.jpg' => [],
                ],
                'expectation' => [
                    [
                        'file' => '/m/a/magento_image.jpg',
                        'label' => 'Image Alt Text',
                        'position' => '1',
                        'disabled' => '0',
                    ],
                    [
                        'file' => '/m/a/magento_thumbnail.jpg',
                        'label' => 'Thumbnail Image',
                        'position' => '2',
                        'disabled' => '0',
                    ],
                ],
            ],
            'with_two_images_and_changed_position_and_one_disabled' => [
                'images' => [
                    '/m/a/magento_image.jpg' => [
                        'position' => '2',
                        'disabled' => '0',
                    ],
                    '/m/a/magento_thumbnail.jpg' => [
                        'position' => '1',
                        'disabled' => '1',
                    ],
                ],
                'expectation' => [
                    [
                        'file' => '/m/a/magento_thumbnail.jpg',
                        'label' => 'Thumbnail Image',
                        'position' => '1',
                        'disabled' => '1',
                    ],
                    [
                        'file' => '/m/a/magento_image.jpg',
                        'label' => 'Image Alt Text',
                        'position' => '2',
                        'disabled' => '0',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider executeOnStoreViewDataProvider
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDbIsolation enabled
     * @param string $file
     * @param string $field
     * @param string $value
     * @param array $expectation
     * @return void
     */
    public function testExecuteOnStoreView(string $file, string $field, string $value, array $expectation): void
    {
        $product = $this->getProduct();
        $this->galleryProcessor->updateImage($product, $file, [$field => $value]);
        $secondStoreId = (int)$this->storeRepository->get('fixture_second_store')->getId();
        $this->saveProduct($product, (int)$secondStoreId);
        $productInstance = $this->getProductInstance($secondStoreId);
        $this->readHandler->execute($productInstance);
        $data = $productInstance->getData();
        $this->assertArrayHasKey('media_gallery', $data);
        $this->assertArrayHasKey('images', $data['media_gallery']);
        $image = reset($data['media_gallery']['images']);
        $dataToAssert = [
            $field => $image[$field],
            $field . '_default' => $image[$field . '_default'],
        ];
        $this->assertEquals($expectation, $dataToAssert);
    }

    /**
     * @return array
     */
    public function executeOnStoreViewDataProvider(): array
    {
        return [
            'with_store_label' => [
                'file' => '/m/a/magento_image.jpg',
                'field' => 'label',
                'value' => 'Some store label',
                'expectation' => [
                    'label' => 'Some store label',
                    'label_default' => 'Image Alt Text',
                ],
            ],
            'with_store_position' => [
                'file' => '/m/a/magento_image.jpg',
                'field' => 'position',
                'value' => '2',
                'expectation' => [
                    'position' => '2',
                    'position_default' => '1',
                ],
            ],
            'with_store_disabled' => [
                'file' => '/m/a/magento_image.jpg',
                'field' => 'disabled',
                'value' => '1',
                'expectation' => [
                    'disabled' => '1',
                    'disabled_default' => '0',
                ],
            ],
        ];
    }

    /**
     * Returns product for testing.
     *
     * @return Product
     */
    private function getProduct(): Product
    {
        /** @var Product $product */
        $product = $this->productRepository->get('simple', false, Store::DEFAULT_STORE_ID);

        return $product;
    }

    /**
     * Saves product via resource model.
     * Uses product resource, because saving via repository requires image in base64 format.
     *
     * @param Product $product
     * @param int|null $storeId
     * @return void
     */
    private function saveProduct(Product $product, int $storeId = null): void
    {
        if ($storeId) {
            $product->setStoreId($storeId);
        }

        $this->productResource->save($product);
    }

    /**
     * Returns empty product instance.
     *
     * @param int|null $storeId
     * @return Product
     */
    private function getProductInstance(int $storeId = null): Product
    {
        /** @var Product $product */
        $product = $this->objectManager->create(Product::class);
        $product->setData(
            $this->metadata->getLinkField(),
            $this->getProduct()->getData($this->metadata->getLinkField())
        );

        if ($storeId) {
            $product->setStoreId($storeId);
        }

        return $product;
    }
}
