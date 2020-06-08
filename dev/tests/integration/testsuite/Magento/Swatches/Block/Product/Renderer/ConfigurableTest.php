<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Block\Product\Renderer;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\Store;
use Magento\Swatches\Block\Product\Renderer\Configurable as ConfigurableBlock;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\Swatch;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests for configurable products options block with swatch attribute.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @magentoAppArea frontend
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var Media
     */
    private $swatchHelper;

    /**
     * @var UrlBuilder
     */
    private $imageUrlBuilder;

    /**
     * @var Configurable
     */
    private $block;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var ProductAttributeInterface
     */
    private $configurableAttribute;

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->serializer = $this->objectManager->get(SerializerInterface::class);
        $this->swatchHelper = $this->objectManager->get(Media::class);
        $this->imageUrlBuilder = $this->objectManager->get(UrlBuilder::class);
        $this->productAttributeRepository = $this->objectManager->get(ProductAttributeRepositoryInterface::class);
        $this->configurableAttribute = $this->productAttributeRepository->get('test_configurable');
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->product = $this->productRepository->get('configurable');
        $this->productResource = $this->objectManager->get(ProductResource::class);
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(ConfigurableBlock::class);
        $this->block->setProduct($this->product);
    }

    /**
     * @magentoDataFixture Magento/Swatches/_files/configurable_product_with_visual_swatch_attribute.php
     * @return void
     */
    public function testGetJsonSwatchConfig(): void
    {
        $expectedOptions = $this->getDefaultOptionsList();
        $expectedOptions['option 2']['value'] = $this->swatchHelper->getSwatchAttributeImage(
            Swatch::SWATCH_IMAGE_NAME,
            '/visual_swatch_attribute_option_type_image.jpg'
        );
        $expectedOptions['option 2']['thumb'] = $this->swatchHelper->getSwatchAttributeImage(
            Swatch::SWATCH_THUMBNAIL_NAME,
            '/visual_swatch_attribute_option_type_image.jpg'
        );
        $config = $this->serializer->unserialize($this->block->getJsonSwatchConfig());
        $this->assertOptionsData($config, $expectedOptions, ['swatch_input_type' => 'visual']);
    }

    /**
     * @magentoDataFixture Magento/Swatches/_files/configurable_product_with_visual_swatch_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_image.php
     * @return void
     */
    public function testGetJsonSwatchConfigUsedProductImage(): void
    {
        $this->updateAttributeUseProductImageFlag();
        $this->updateProductImage('simple_option_2', '/m/a/magento_image.jpg');
        $expectedOptions = $this->getDefaultOptionsList();
        $expectedOptions['option 2']['value'] = $this->imageUrlBuilder->getUrl(
            '/m/a/magento_image.jpg',
            'swatch_image_base'
        );
        $expectedOptions['option 2']['thumb'] = $this->imageUrlBuilder->getUrl(
            '/m/a/magento_image.jpg',
            'swatch_thumb_base'
        );
        $this->assertOptionsData(
            $this->serializer->unserialize($this->block->getJsonSwatchConfig()),
            $expectedOptions,
            ['swatch_input_type' => 'visual', 'use_product_image_for_swatch' => 1]
        );
    }

    /**
     * @magentoDataFixture Magento/Swatches/_files/configurable_product_with_visual_swatch_attribute.php
     * @return void
     */
    public function testGetJsonSwatchConfigUsedEmptyProductImage(): void
    {
        $this->updateAttributeUseProductImageFlag();
        $expectedOptions = $this->getDefaultOptionsList();
        $expectedOptions['option 2']['value'] = $this->swatchHelper->getSwatchAttributeImage(
            Swatch::SWATCH_IMAGE_NAME,
            '/visual_swatch_attribute_option_type_image.jpg'
        );
        $expectedOptions['option 2']['thumb'] = $this->swatchHelper->getSwatchAttributeImage(
            Swatch::SWATCH_THUMBNAIL_NAME,
            '/visual_swatch_attribute_option_type_image.jpg'
        );
        $this->assertOptionsData(
            $this->serializer->unserialize($this->block->getJsonSwatchConfig()),
            $expectedOptions,
            ['swatch_input_type' => 'visual', 'use_product_image_for_swatch' => 1]
        );
    }

    /**
     * @return array
     */
    private function getDefaultOptionsList(): array
    {
        return [
            'option 1' => ['type' => '1', 'value' => '#000000', 'label' => 'option 1'],
            'option 2' => ['type' => '2', 'value' => '', 'thumb' => '', 'label' => 'option 2'],
            'option 3' => ['type' => '3', 'value' => null, 'label' => 'option 3'],
        ];
    }

    /**
     * Asserts swatch options data.
     *
     * @param array $config
     * @param array $expectedOptions
     * @param array $expectedAdditional
     * @return void
     */
    private function assertOptionsData(array $config, array $expectedOptions, array $expectedAdditional): void
    {
        $this->assertNotEmpty($config);
        $resultOptions = $config[$this->configurableAttribute->getAttributeId()];
        foreach ($expectedOptions as $label => $data) {
            $resultOption = $resultOptions[$this->configurableAttribute->getSource()->getOptionId($label)];
            $this->assertEquals($data['type'], $resultOption['type']);
            $this->assertEquals($data['label'], $resultOption['label']);
            $this->assertEquals($data['value'], $resultOption['value']);
            if (!empty($data['thumb'])) {
                $this->assertEquals($data['thumb'], $resultOption['thumb']);
            }
        }
        $this->assertEquals($expectedAdditional, $this->serializer->unserialize($resultOptions['additional_data']));
    }

    /**
     * Updates attribute 'use_product_image_for_swatch' flag.
     *
     * @return void
     */
    private function updateAttributeUseProductImageFlag(): void
    {
        $this->configurableAttribute->setData('use_product_image_for_swatch', 1);
        $this->configurableAttribute = $this->productAttributeRepository->save($this->configurableAttribute);
    }

    /**
     * Updates Product image.
     *
     * @param string $sku
     * @param string $imageName
     * @return void
     */
    private function updateProductImage(string $sku, string $imageName): void
    {
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
                    ]
                ]
            )
            ->setCanSaveCustomOptions(true);
        $this->productResource->save($product);
    }
}
