<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Block\Product\Renderer\Configurable;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Swatches\Block\Product\Renderer\Configurable;
use Magento\Swatches\Model\Swatch;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test class to check configurable product with swatch attributes view behaviour on product page
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ProductPageViewTest extends TestCase
{
    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var Configurable */
    protected $block;

    /** @var string */
    protected $template;

    /** @var ProductAttributeRepositoryInterface */
    protected $productAttributeRepository;

    /** @var LayoutInterface */
    protected $layout;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var Registry */
    private $registry;

    /** @var SerializerInterface */
    private $json;

    /** @var ProductResource */
    private $productResource;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $this->layout->createBlock(Configurable::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->productAttributeRepository = $this->objectManager->create(ProductAttributeRepositoryInterface::class);
        $this->productResource = $this->objectManager->create(ProductResource::class);
        $this->template = Configurable::SWATCH_RENDERER_TEMPLATE;
    }

    /**
     * @magentoDataFixture Magento/Swatches/_files/configurable_product_text_swatch_attribute.php
     *
     * @dataProvider expectedTextSwatchDataProvider
     *
     * @param array $expectedConfig
     * @param array $expectedSwatchConfig
     * @return void
     */
    public function testProductPageTextSwatchAttributeView(array $expectedConfig, array $expectedSwatchConfig): void
    {
        $this->checkProductView($expectedConfig, $expectedSwatchConfig);
    }

    /**
     * @return array
     */
    public function expectedTextSwatchDataProvider(): array
    {
        return [
            [
                'json_config' => [
                    'text_swatch_attribute' => [
                        'label' => 'Text swatch attribute',
                        'options' => [
                            ['label' => 'Option 3', 'skus' => ['simple_option_3']],
                            ['label' => 'Option 1', 'skus' => ['simple_option_1']],
                            ['label' => 'Option 2', 'skus' => ['simple_option_2']],
                        ],
                    ],
                ],
                'json_swatch_config' => [
                    Swatch::SWATCH_INPUT_TYPE_TEXT => [
                        [
                            'type' => Swatch::SWATCH_TYPE_TEXTUAL,
                            'value' => 'Swatch 3',
                            'label' => 'Option 3',
                        ],
                        [
                            'type' => Swatch::SWATCH_TYPE_TEXTUAL,
                            'value' => 'Swatch 1',
                            'label' => 'Option 1',
                        ],
                        [
                            'type' => Swatch::SWATCH_TYPE_TEXTUAL,
                            'value' => 'Swatch 2',
                            'label' => 'Option 2',
                        ],
                        'additional_data' => "{\"swatch_input_type\":\"text\"}",
                    ],

                ],
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Swatches/_files/configurable_product_visual_swatch_attribute.php
     *
     * @dataProvider expectedVisualSwatchDataProvider
     *
     * @param array $expectedConfig
     * @param array $expectedSwatchConfig
     * @return void
     */
    public function testProductPageVisualSwatchAttributeView(array $expectedConfig, array $expectedSwatchConfig): void
    {
        $this->checkProductView($expectedConfig, $expectedSwatchConfig);
    }

    /**
     * @return array
     */
    public function expectedVisualSwatchDataProvider(): array
    {
        return [
            [
                'json_config' => [
                    'visual_swatch_attribute' => [
                        'label' => 'Visual swatch attribute',
                        'options' => [
                            ['label' => 'option 3', 'skus' => ['simple_option_3']],
                            ['label' => 'option 2', 'skus' => ['simple_option_2']],
                            ['label' => 'option 1', 'skus' => ['simple_option_1']],
                        ],
                    ],
                ],
                'json_swatch_config' => [
                    Swatch::SWATCH_INPUT_TYPE_VISUAL => [
                        [
                            'type' => Swatch::SWATCH_TYPE_VISUAL_COLOR,
                            'value' => '#555555',
                            'label' => 'option 1',
                        ],
                        [
                            'type' => Swatch::SWATCH_TYPE_VISUAL_COLOR,
                            'value' => '#aaaaaa',
                            'label' => 'option 2',
                        ],
                        [
                            'type' => Swatch::SWATCH_TYPE_VISUAL_COLOR,
                            'value' => '#ffffff',
                            'label' => 'option 3',
                        ],
                        'additional_data' => "{\"swatch_input_type\":\"visual\"}",
                    ],
                ],
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Swatches/_files/configurable_product_two_attributes.php
     *
     * @dataProvider expectedTwoAttributesProvider
     *
     * @param array $expectedConfig
     * @param array $expectedSwatchConfig
     * @return void
     */
    public function testProductPageTwoAttributesView(array $expectedConfig, array $expectedSwatchConfig): void
    {
        $this->checkProductView($expectedConfig, $expectedSwatchConfig);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function expectedTwoAttributesProvider(): array
    {
        return [
            [
                'json_config' => [
                    'visual_swatch_attribute' => [
                        'label' => 'Visual swatch attribute',
                        'options' => [
                            [
                                'label' => 'option 3',
                                'skus' => [
                                    'simple_option_3_option_3',
                                    'simple_option_1_option_3',
                                    'simple_option_2_option_3',
                                ],
                            ],
                            [
                                'label' => 'option 2',
                                'skus' => [
                                    'simple_option_3_option_2',
                                    'simple_option_1_option_2',
                                    'simple_option_2_option_2',
                                ],
                            ],
                            [
                                'label' => 'option 1',
                                'skus' => [
                                    'simple_option_3_option_1',
                                    'simple_option_1_option_1',
                                    'simple_option_2_option_1',
                                ],
                            ],
                        ],
                    ],
                    'text_swatch_attribute' => [
                        'label' => 'Text swatch attribute',
                        'options' => [
                            [
                                'label' => 'Option 3',
                                'skus' => [
                                    'simple_option_3_option_1',
                                    'simple_option_3_option_2',
                                    'simple_option_3_option_3',
                                ],
                            ],
                            [
                                'label' => 'Option 2',
                                'skus' => [
                                    'simple_option_2_option_1',
                                    'simple_option_2_option_2',
                                    'simple_option_2_option_3',
                                ],
                            ],
                            [
                                'label' => 'Option 1',
                                'skus' => [
                                    'simple_option_1_option_1',
                                    'simple_option_1_option_2',
                                    'simple_option_1_option_3',
                                ],
                            ],
                        ],
                    ],

                ],
                'json_swatch_config' => [
                    Swatch::SWATCH_INPUT_TYPE_VISUAL => [
                        [
                            'type' => Swatch::SWATCH_TYPE_VISUAL_COLOR,
                            'value' => '#555555',
                            'label' => 'option 1',
                        ],
                        [
                            'type' => Swatch::SWATCH_TYPE_VISUAL_COLOR,
                            'value' => '#aaaaaa',
                            'label' => 'option 2',
                        ],
                        [
                            'type' => Swatch::SWATCH_TYPE_VISUAL_COLOR,
                            'value' => '#ffffff',
                            'label' => 'option 3',
                        ],
                        'additional_data' => "{\"swatch_input_type\":\"visual\"}",
                    ],
                    Swatch::SWATCH_INPUT_TYPE_TEXT => [
                        [
                            'type' => Swatch::SWATCH_TYPE_TEXTUAL,
                            'value' => 'Swatch 3',
                            'label' => 'Option 3',
                        ],
                        [
                            'type' => Swatch::SWATCH_TYPE_TEXTUAL,
                            'value' => 'Swatch 1',
                            'label' => 'Option 1',
                        ],
                        [
                            'type' => Swatch::SWATCH_TYPE_TEXTUAL,
                            'value' => 'Swatch 2',
                            'label' => 'Option 2',
                        ],
                        'additional_data' => "{\"swatch_input_type\":\"text\"}",
                    ],
                ],
            ],
        ];
    }

    /**
     * Check configurable product view
     *
     * @param $expectedConfig
     * @param $expectedSwatchConfig
     * @return void
     */
    protected function checkProductView($expectedConfig, $expectedSwatchConfig): void
    {
        $actualConfig = $this->generateBlockJsonConfigData();
        $this->checkResultIsNotEmpty($actualConfig);
        $this->assertConfig($actualConfig['json_config'], $expectedConfig);
        $this->assertSwatchConfig($actualConfig['json_swatch_config'], $expectedSwatchConfig);
    }

    /**
     * Generate block config data
     *
     * @return array
     */

    private function generateBlockJsonConfigData(): array
    {
        $product = $this->productRepository->get('configurable');
        $this->block->setProduct($product);
        $this->block->setTemplate($this->template);
        $jsonConfig = $this->json->unserialize($this->block->getJsonConfig())['attributes'] ?? [];
        $jsonSwatchConfig = $this->json->unserialize($this->block->getJsonSwatchConfig());

        return ['json_config' => $jsonConfig, 'json_swatch_config' => $jsonSwatchConfig];
    }

    /**
     * Assert that correct data was generated
     *
     * @param array $actualData
     * @param array $expectedData
     * @return void
     */
    private function assertSwatchConfig(array $actualData, array $expectedData): void
    {
        foreach ($actualData as $actualDataItem) {
            $currentType = $this->json->unserialize($actualDataItem['additional_data'])['swatch_input_type'] ?? null;
            $this->assertNotNull($currentType);
            $this->assertEquals($expectedData[$currentType]['additional_data'], $actualDataItem['additional_data']);
            unset($actualDataItem['additional_data']);
            foreach ($actualDataItem as $item) {
                $this->assertContainsEquals($item, $expectedData[$currentType]);
            }
        }
    }

    /**
     * Assert that correct swatch data was generated
     *
     * @param array $actualData
     * @param array $expectedData
     * @return void
     */
    private function assertConfig(array $actualData, array $expectedData): void
    {
        foreach ($actualData as $actualDataItem) {
            $expectedItem = $expectedData[$actualDataItem['code']];
            $this->assertEquals($expectedItem['label'], $actualDataItem['label']);
            $this->checkOptions($actualDataItem, $expectedItem);
        }
    }

    /**
     * Check result is not not empty
     *
     * @param array $result
     */
    private function checkResultIsNotEmpty(array $result): void
    {
        foreach ($result as $item) {
            $this->assertNotEmpty($item);
        }
    }

    /**
     * Check attribute options
     *
     * @param array $actualDataItem
     * @param array $expectedItem
     * @return void
     */
    private function checkOptions(array $actualDataItem, array $expectedItem): void
    {
        foreach ($expectedItem['options'] as $expectedOption) {
            $expectedSkus = array_values($expectedOption['skus']);
            $expectedIds = array_values($this->productResource->getProductsIdsBySkus($expectedSkus));
            foreach ($actualDataItem['options'] as $option) {
                if ($option['label'] === $expectedOption['label']) {
                    $this->assertEquals(
                        sort($expectedIds),
                        sort($option['products']),
                        'Wrong product linked as option'
                    );
                }
            }
        }
    }
}
