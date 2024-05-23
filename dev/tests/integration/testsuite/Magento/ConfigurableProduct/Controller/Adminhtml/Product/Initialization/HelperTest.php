<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Initialization;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests for image processing plugins for child products by saving a configurable product.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HelperTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var ProductInterface
     */
    private $configurableProduct;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->request = $this->objectManager->get(RequestInterface::class);
        $this->helper = $this->objectManager->get(Helper::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->productResource =$this->objectManager->get(ProductResource::class);
        $this->productAttributeRepository = $this->objectManager->get(ProductAttributeRepositoryInterface::class);
        $this->jsonSerializer = $this->objectManager->get(SerializerInterface::class);
        $this->searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $this->config = $this->objectManager->get(Config::class);
        $this->mediaDirectory = $this->objectManager->get(Filesystem::class)->getDirectoryWrite(DirectoryList::MEDIA);
        $this->configurableProduct = $this->productRepository->get('configurable');
    }

    /**
     * Tests adding images with various roles to child products by saving a configurable product.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_image.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @dataProvider initializeDataProvider
     * @param array $childProducts
     * @param array $expectedImages
     * @return void
     */
    public function testInitialize(array $childProducts, array $expectedImages): void
    {
        $this->setRequestParams($childProducts);
        $this->helper->initialize($this->configurableProduct);
        $this->assertChildProductImages($expectedImages);
    }

    /**
     * Tests replacing images with various roles to child products by saving a configurable product.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_image.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @dataProvider initializeWithExistingChildImagesDataProvider
     * @param array $childProducts
     * @param array $expectedImages
     * @return void
     */
    public function testInitializeWithExistingChildImages(array $childProducts, array $expectedImages): void
    {
        $this->updateChildProductsImages(
            [
                'simple_10' => '/m/a/magento_thumbnail.jpg.tmp',
                'simple_20' => '/m/a/magento_small_image.jpg.tmp',
            ]
        );
        $this->setRequestParams($childProducts);
        $this->helper->initialize($this->configurableProduct);
        $this->assertChildProductImages($expectedImages);
    }

    /**
     * @return array
     */
    public function initializeDataProvider(): array
    {
        return [
            'children_with_same_image_and_roles' => [
                'child_products' => [
                    'simple_10' => [
                        'media_gallery' => $this->getMediaGallery(['ben062bdw2v' => '/m/a/magento_image.jpg.tmp']),
                        'images' => [
                            '/m/a/magento_image.jpg.tmp' => ['swatch_image', 'small_image', 'image', 'thumbnail'],
                        ],
                    ],
                    'simple_20' => [
                        'media_gallery' => $this->getMediaGallery(['ben062bdw2v' => '/m/a/magento_image.jpg.tmp']),
                        'images' => [
                            '/m/a/magento_image.jpg.tmp' => ['swatch_image', 'small_image', 'image', 'thumbnail'],
                        ],
                    ],
                ],
                'expected_images' => [
                    'simple_10' => [
                        '/m/a/magento_image_1.jpg' => ['swatch_image', 'small_image', 'image', 'thumbnail'],
                    ],
                    'simple_20' => [
                        '/m/a/magento_image_2.jpg' => ['swatch_image', 'small_image', 'image', 'thumbnail'],
                    ],
                ],
            ],
            'children_with_different_images' => [
                'child_products' => [
                    'simple_10' => [
                        'media_gallery' => $this->getMediaGallery(['ben062bdw2v' => '/m/a/magento_image.jpg.tmp']),
                        'images' => [
                            '/m/a/magento_image.jpg.tmp' => ['swatch_image', 'small_image', 'image', 'thumbnail'],
                        ],
                    ],
                    'simple_20' => [
                        'media_gallery' => $this->getMediaGallery(
                            ['lrwuv5ukisn' => '/m/a/magento_small_image.jpg.tmp']
                        ),
                        'images' => [
                            '/m/a/magento_small_image.jpg.tmp' => ['swatch_image', 'small_image', 'image', 'thumbnail'],
                        ],
                    ],
                ],
                'expected_images' => [
                    'simple_10' => [
                        '/m/a/magento_image_1.jpg' => ['swatch_image', 'small_image', 'image', 'thumbnail'],
                    ],
                    'simple_20' => [
                        '/m/a/magento_small_image_1.jpg' => ['swatch_image', 'small_image', 'image', 'thumbnail'],
                    ],
                ],
            ],
            'children_with_different_image_roles' => [
                'child_products' => [
                    'simple_10' => [
                        'media_gallery' => $this->getMediaGallery(
                            [
                                'ben062bdw2v' => '/m/a/magento_image.jpg.tmp',
                                'lrwuv5ukisn' => '/m/a/magento_small_image.jpg.tmp',
                            ]
                        ),
                        'images' => [
                            '/m/a/magento_image.jpg.tmp' => ['swatch_image', 'small_image'],
                            '/m/a/magento_small_image.jpg.tmp' => ['image', 'thumbnail'],
                        ],
                    ],
                    'simple_20' => [
                        'media_gallery' => $this->getMediaGallery(
                            [
                                'ben062bdw2v' => '/m/a/magento_image.jpg.tmp',
                                'lrwuv5ukisn' => '/m/a/magento_small_image.jpg.tmp',
                            ]
                        ),
                        'images' => [
                            '/m/a/magento_small_image.jpg.tmp' => ['swatch_image', 'small_image'],
                            '/m/a/magento_image.jpg.tmp' => ['image', 'thumbnail'],
                        ],
                    ],
                ],
                'expected_images' => [
                    'simple_10' => [
                        '/m/a/magento_image_1.jpg' => ['swatch_image', 'small_image'],
                        '/m/a/magento_small_image_1.jpg' => ['image', 'thumbnail'],
                    ],
                    'simple_20' => [
                        '/m/a/magento_small_image_2.jpg' => ['swatch_image', 'small_image'],
                        '/m/a/magento_image_2.jpg' => ['image', 'thumbnail'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function initializeWithExistingChildImagesDataProvider(): array
    {
        $dataProvider = $this->initializeDataProvider();
        unset($dataProvider['children_with_different_images'], $dataProvider['children_with_different_image_roles']);

        return array_values($dataProvider);
    }

    /**
     * Sets configurable product params to request.
     *
     * @param array $childProducts
     * @return void
     */
    private function setRequestParams(array $childProducts): void
    {
        $matrix = $associatedProductIds = [];
        $attribute = $this->productAttributeRepository->get('test_configurable');

        foreach ($childProducts as $sku => $product) {
            $simpleProduct = $this->productRepository->get($sku);
            $attributeValue = $simpleProduct->getData('test_configurable');
            foreach ($product['images'] as $image => $roles) {
                foreach ($roles as $role) {
                    $product[$role] = $image;
                }
            }
            unset($product['images']);
            $product['configurable_attribute'] = $this->jsonSerializer->serialize(
                ['test_configurable' => $attributeValue]
            );
            $product['variationKey'] = $attributeValue;
            $product['id'] = $simpleProduct->getId();
            $product['sku'] = $sku;
            $product['was_changed'] = true;
            $product['newProduct'] = 0;
            $matrix[] = $product;
            $associatedProductIds[] = $simpleProduct->getId();
        }
        $this->request->setParams(
            [
                'attributes' => [$attribute->getAttributeId()],
                'configurable-matrix-serialized' => $this->jsonSerializer->serialize($matrix),
            ]
        );
        $this->request->setPostValue(
            'associated_product_ids_serialized',
            $this->jsonSerializer->serialize($associatedProductIds)
        );
    }

    /**
     * Asserts child products images.
     *
     * @param array $expectedImages
     * @return void
     */
    private function assertChildProductImages(array $expectedImages): void
    {
        $simpleIds = $this->configurableProduct->getExtensionAttributes()->getConfigurableProductLinks();
        $criteria = $this->searchCriteriaBuilder->addFilter('entity_id', $simpleIds, 'in')->create();
        foreach ($this->productRepository->getList($criteria)->getItems() as $simpleProduct) {
            $images = $expectedImages[$simpleProduct->getSku()];
            foreach ($images as $image => $roles) {
                foreach ($roles as $role) {
                    $this->assertEquals($image, $simpleProduct->getData($role));
                }
                $this->assertTrue(
                    $this->mediaDirectory->isExist($this->config->getBaseMediaPath() . $image)
                );
            }
        }
    }

    /**
     * Returns media gallery product param.
     *
     * @param array $imageNames
     * @return array
     */
    private function getMediaGallery(array $imageNames): array
    {
        $images = [];
        foreach ($imageNames as $key => $item) {
            $images[$key] = ['file' => $item, 'label' => '', 'media_type' => 'image'];
        }

        return ['images' => $images];
    }

    /**
     * Sets image to child products.
     *
     * @param array $imageNames
     * @return void
     */
    private function updateChildProductsImages(array $imageNames): void
    {
        $simpleIds = $this->configurableProduct->getExtensionAttributes()->getConfigurableProductLinks();
        $criteria = $this->searchCriteriaBuilder->addFilter('entity_id', $simpleIds, 'in')->create();
        $products = $this->productRepository->getList($criteria)->getItems();
        foreach ($products as $simpleProduct) {
            $simpleProduct->setStoreId(Store::DEFAULT_STORE_ID)
                ->setImage($imageNames[$simpleProduct->getSku()])
                ->setSmallImage($imageNames[$simpleProduct->getSku()])
                ->setThumbnail($imageNames[$simpleProduct->getSku()])
                ->setSwatchImage($imageNames[$simpleProduct->getSku()])
                ->setData(
                    'media_gallery',
                    [
                        'images' => [
                            ['file' => $imageNames[$simpleProduct->getSku()], 'label' => '', 'media_type' => 'image']
                        ]
                    ]
                );
            $this->productResource->save($simpleProduct);
        }
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $reflection = new \ReflectionObject($this);
        foreach ($reflection->getProperties() as $property) {
            if (!$property->isStatic() && 0 !== strpos($property->getDeclaringClass()->getName(), 'PHPUnit')) {
                $property->setAccessible(true);
                $property->setValue($this, null);
            }
        }
    }
}
