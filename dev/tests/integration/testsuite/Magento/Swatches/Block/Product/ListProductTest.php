<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Block\Product;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\Store;
use Magento\Swatches\Model\Plugin\ProductImage;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests for displaying configurable product image with swatch attributes.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @magentoAppArea frontend
 */
class ListProductTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
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
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var ListProduct
     */
    private $listingBlock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productResource = $this->objectManager->get(ProductResource::class);
        $this->attributeRepository = $this->objectManager->get(ProductAttributeRepositoryInterface::class);
        $this->request = $this->objectManager->get(RequestInterface::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->listingBlock = $this->layout->createBlock(ListProduct::class);
    }

    /**
     * @magentoDataFixture Magento/Swatches/_files/configurable_product_text_swatch_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_image.php
     * @dataProvider getImageDataProvider
     * @param array $images
     * @param string $area
     * @param array $expectation
     * @return void
     */
    public function testGetImageForTextSwatchConfigurable(array $images, string $area, array $expectation): void
    {
        $this->updateAttributePreviewImageFlag('text_swatch_attribute');
        $this->addFilterToRequest('text_swatch_attribute', 'option 1');
        $this->assertProductImage($images, $area, $expectation);
    }

    /**
     * @magentoDataFixture Magento/Swatches/_files/configurable_product_visual_swatch_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_image.php
     * @dataProvider getImageDataProvider
     * @param array $images
     * @param string $area
     * @param array $expectation
     * @return void
     */
    public function testGetImageForVisualSwatchConfigurable(array $images, string $area, array $expectation): void
    {
        $this->updateAttributePreviewImageFlag('visual_swatch_attribute');
        $this->addFilterToRequest('visual_swatch_attribute', 'option 1');
        $this->assertProductImage($images, $area, $expectation);
    }

    /**
     * @return array
     */
    public function getImageDataProvider(): array
    {
        return [
            'without_images_and_display_grid' => [
                'images' => [],
                'display_area' => ProductImage::CATEGORY_PAGE_GRID_LOCATION,
                'expectation' => ['image_url' => 'placeholder/small_image.jpg', 'label' => 'Configurable Product'],
            ],
            'without_images_and_display_list' => [
                'images' => [],
                'display_area' => ProductImage::CATEGORY_PAGE_LIST_LOCATION,
                'expectation' => ['image_url' => 'placeholder/small_image.jpg', 'label' => 'Configurable Product'],
            ],
            'with_image_on_configurable_and_display_grid' => [
                'images' => ['configurable' => '/m/a/magento_image.jpg'],
                'display_area' => ProductImage::CATEGORY_PAGE_GRID_LOCATION,
                'expectation' => ['image_url' => '/m/a/magento_image.jpg', 'label' => 'Image Alt Text'],
            ],
            'with_image_on_configurable_and_display_list' => [
                'images' => ['configurable' => '/m/a/magento_image.jpg'],
                'display_area' => ProductImage::CATEGORY_PAGE_LIST_LOCATION,
                'expectation' => ['image_url' => '/m/a/magento_image.jpg', 'label' => 'Image Alt Text'],
            ],
            'with_image_on_simple' => [
                'images' => ['simple_option_1' => '/m/a/magento_small_image.jpg'],
                'display_area' => ProductImage::CATEGORY_PAGE_GRID_LOCATION,
                'expectation' => ['image_url' => '/m/a/magento_small_image.jpg', 'label' => 'Image Alt Text'],
            ],
            'with_image_on_simple_and_configurable' => [
                'images' => [
                    'configurable' => '/m/a/magento_image.jpg',
                    'simple_option_1' => '/m/a/magento_small_image.jpg',
                ],
                'display_area' => ProductImage::CATEGORY_PAGE_GRID_LOCATION,
                'expectation' => ['image_url' => '/m/a/magento_small_image.jpg', 'label' => 'Image Alt Text'],
            ],
        ];
    }

    /**
     * Asserts image data.
     *
     * @param array $images
     * @param string $area
     * @param array $expectation
     * @return void
     */
    private function assertProductImage(array $images, string $area, array $expectation): void
    {
        $this->updateProductImages($images);
        $productImage = $this->listingBlock->getImage($this->productRepository->get('configurable'), $area);
        $this->assertInstanceOf(Image::class, $productImage);
        $this->assertEquals($productImage->getCustomAttributes(), '');
        $this->assertEquals($productImage->getClass(), 'product-image-photo');
        $this->assertEquals($productImage->getRatio(), 1.25);
        $this->assertEquals($productImage->getLabel(), $expectation['label']);
        $this->assertStringEndsWith($expectation['image_url'], $productImage->getImageUrl());
        $this->assertEquals($productImage->getWidth(), 240);
        $this->assertEquals($productImage->getHeight(), 300);
    }

    /**
     * Updates products images.
     *
     * @param array $images
     * @return void
     */
    private function updateProductImages(array $images): void
    {
        foreach ($images as $sku => $imageName) {
            $product = $this->productRepository->get($sku);
            $product->setStoreId(Store::DEFAULT_STORE_ID)
                ->setImage($imageName)
                ->setSmallImage($imageName)
                ->setThumbnail($imageName)
                ->setData(
                    'media_gallery',
                    [
                        'images' => [
                            [
                                'file' => $imageName,
                                'position' => 1,
                                'label' => 'Image Alt Text',
                                'disabled' => 0,
                                'media_type' => 'image'
                            ],
                        ],
                    ]
                )
                ->setCanSaveCustomOptions(true);
            $this->productResource->save($product);
        }
    }

    /**
     * Updates attribute "Update Product Preview Image" flag.
     *
     * @param string $attributeCode
     * @return void
     */
    private function updateAttributePreviewImageFlag(string $attributeCode): void
    {
        $attribute = $this->attributeRepository->get($attributeCode);
        $attribute->setData('update_product_preview_image', 1);
        $this->attributeRepository->save($attribute);
    }

    /**
     * Adds attribute param to request.
     *
     * @param string $attributeCode
     * @param string $optionLabel
     * @return void
     */
    private function addFilterToRequest(string $attributeCode, string $optionLabel): void
    {
        $attribute = $this->attributeRepository->get($attributeCode);
        $this->request->setParams(
            [$attributeCode => $attribute->getSource()->getOptionId($optionLabel)]
        );
    }
}
