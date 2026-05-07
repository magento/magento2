<?php
/**
 * Copyright 2021 Adobe
 * All rights reserved.
 */

declare(strict_types=1);

namespace Magento\GraphQl\ConfigurableProduct;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as ConfigurableAttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\ConfigurableProductGraphQl\Model\Options\SelectionUidFormatter;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Indexer\Model\IndexerFactory;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test configurable product option selection.
 */
class ConfigurableOptionsSelectionTest extends GraphQlAbstract
{
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var SelectionUidFormatter
     */
    private $selectionUidFormatter;

    /**
     * @var IndexerFactory
     */
    private $indexerFactory;

    /**
     * @var Uid
     */
    private $idEncoder;

    /**
     * @var AttributeInterface
     */
    private $firstConfigurableAttribute;

    /**
     * @var AttributeInterface
     */
    private $secondConfigurableAttribute;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->attributeRepository = Bootstrap::getObjectManager()->create(AttributeRepository::class);
        $this->selectionUidFormatter = Bootstrap::getObjectManager()->create(SelectionUidFormatter::class);
        $this->indexerFactory = Bootstrap::getObjectManager()->create(IndexerFactory::class);
        $this->idEncoder = Bootstrap::getObjectManager()->create(Uid::class);
    }

    #[
        DataFixture(
            ConfigurableAttributeFixture::class,
            [
                'attribute_code' => 'test_configurable_first',
                'default_frontend_label' => 'Test Configurable First',
                'options' => [
                    ['label' => 'Option 1', 'sort_order' => 0],
                    ['label' => 'Option 2', 'sort_order' => 1],
                    ['label' => 'Option 3', 'sort_order' => 2],
                    ['label' => 'Option 4', 'sort_order' => 3]
                ]
            ],
            as: 'first_attribute'
        ),
        DataFixture(
            ConfigurableAttributeFixture::class,
            [
                'attribute_code' => 'test_configurable_second',
                'default_frontend_label' => 'Test Configurable Second',
                'options' => [
                    ['label' => 'Option 1', 'sort_order' => 0],
                    ['label' => 'Option 2', 'sort_order' => 1],
                    ['label' => 'Option 3', 'sort_order' => 2],
                    ['label' => 'Option 4', 'sort_order' => 3]
                ]
            ],
            as: 'second_attribute'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 1',
                'sku' => 'simple_1',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ],
                'media_gallery_entries' => [
                    [
                        'media_type' => 'image',
                        'label' => 'Test Image',
                        'position' => 1,
                        'disabled' => false,
                        'types' => ['image', 'small_image', 'thumbnail'],
                        'content' => [
                            'type' => 'image/jpeg',
                            'name' => 'test_image.jpg'
                        ]
                    ]
                ]
            ],
            as: 'simple_1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 2',
                'sku' => 'simple_2',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ],
                'media_gallery_entries' => [
                    [
                        'media_type' => 'image',
                        'label' => 'Test Image',
                        'position' => 1,
                        'disabled' => false,
                        'types' => ['image', 'small_image', 'thumbnail'],
                        'content' => [
                            'type' => 'image/jpeg',
                            'name' => 'test_image.jpg'
                        ]
                    ]
                ]
            ],
            as: 'simple_2'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 3',
                'sku' => 'simple_3',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ],
                'media_gallery_entries' => [
                    [
                        'media_type' => 'image',
                        'label' => 'Test Image',
                        'position' => 1,
                        'disabled' => false,
                        'types' => ['image', 'small_image', 'thumbnail'],
                        'content' => [
                            'type' => 'image/jpeg',
                            'name' => 'test_image.jpg'
                        ]
                    ]
                ]
            ],
            as: 'simple_3'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 4',
                'sku' => 'simple_4',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ],
                'media_gallery_entries' => [
                    [
                        'media_type' => 'image',
                        'label' => 'Test Image',
                        'position' => 1,
                        'disabled' => false,
                        'types' => ['image', 'small_image', 'thumbnail'],
                        'content' => [
                            'type' => 'image/jpeg',
                            'name' => 'test_image.jpg'
                        ]
                    ]
                ]
            ],
            as: 'simple_4'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'name' => 'Configurable Product',
                'sku' => 'configurable_12345',
                '_options' => ['$first_attribute$', '$second_attribute$'],
                '_links' => ['$simple_1$', '$simple_2$', '$simple_3$', '$simple_4$']
            ]
        )
    ]
    public function testSelectedFirstAttributeFirstOption(): void
    {
        $attribute = $this->getFirstConfigurableAttribute();
        $options = $attribute->getOptions();
        $sku = 'configurable_12345';
        $firstOptionUid = $this->selectionUidFormatter->encode(
            (int)$attribute->getAttributeId(),
            (int)$options[1]->getValue()
        );

        $this->reindexAll();
        $response = $this->graphQlQuery($this->getQuery($sku, [$firstOptionUid]));

        self::assertNotEmpty($response['products']['items']);
        $product = current($response['products']['items']);
        self::assertEquals('ConfigurableProduct', $product['__typename']);
        self::assertEquals($sku, $product['sku']);
        self::assertNotEmpty($product['configurable_product_options_selection']['configurable_options']);
        self::assertNull($product['configurable_product_options_selection']['variant']);
        self::assertCount(1, $product['configurable_product_options_selection']['configurable_options']);
        self::assertCount(4, $product['configurable_product_options_selection']['configurable_options'][0]['values']);

        $secondAttributeOptions = $this->getSecondConfigurableAttribute()->getOptions();
        $this->assertAvailableOptionUids(
            $this->getSecondConfigurableAttribute()->getAttributeId(),
            $secondAttributeOptions,
            $this->getOptionsUids(
                $product['configurable_product_options_selection']['configurable_options'][0]['values']
            )
        );

        $this->assertMediaGallery($product);
    }

    #[
        DataFixture(
            ConfigurableAttributeFixture::class,
            [
                'attribute_code' => 'test_configurable_first',
                'default_frontend_label' => 'Test Configurable First',
                'options' => [
                    ['label' => 'Option 1', 'sort_order' => 0],
                    ['label' => 'Option 2', 'sort_order' => 1]
                ]
            ],
            as: 'first_attribute'
        ),
        DataFixture(
            ConfigurableAttributeFixture::class,
            [
                'attribute_code' => 'test_configurable_second',
                'default_frontend_label' => 'Test Configurable Second',
                'options' => [
                    ['label' => 'Option 1', 'sort_order' => 0],
                    ['label' => 'Option 2', 'sort_order' => 1]
                ]
            ],
            as: 'second_attribute'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 1',
                'sku' => 'simple_1',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'url_key' => 'configurable-option-first-option-1-second-option-1',
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ],
                'media_gallery_entries' => [
                    [
                        'media_type' => 'image',
                        'label' => 'Test Image',
                        'position' => 1,
                        'disabled' => false,
                        'types' => ['image', 'small_image', 'thumbnail'],
                        'content' => [
                            'type' => 'image/jpeg',
                            'name' => 'test_image.jpg'
                        ]
                    ]
                ]
            ],
            as: 'simple_1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 2',
                'sku' => 'simple_2',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ],
                'media_gallery_entries' => [
                    [
                        'media_type' => 'image',
                        'label' => 'Test Image',
                        'position' => 1,
                        'disabled' => false,
                        'types' => ['image', 'small_image', 'thumbnail'],
                        'content' => [
                            'type' => 'image/jpeg',
                            'name' => 'test_image.jpg'
                        ]
                    ]
                ]
            ],
            as: 'simple_2'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 3',
                'sku' => 'simple_3',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ],
                'media_gallery_entries' => [
                    [
                        'media_type' => 'image',
                        'label' => 'Test Image',
                        'position' => 1,
                        'disabled' => false,
                        'types' => ['image', 'small_image', 'thumbnail'],
                        'content' => [
                            'type' => 'image/jpeg',
                            'name' => 'test_image.jpg'
                        ]
                    ]
                ]
            ],
            as: 'simple_3'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 4',
                'sku' => 'simple_4',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ],
                'media_gallery_entries' => [
                    [
                        'media_type' => 'image',
                        'label' => 'Test Image',
                        'position' => 1,
                        'disabled' => false,
                        'types' => ['image', 'small_image', 'thumbnail'],
                        'content' => [
                            'type' => 'image/jpeg',
                            'name' => 'test_image.jpg'
                        ]
                    ]
                ]
            ],
            as: 'simple_4'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'name' => 'Configurable Product',
                'sku' => 'configurable_12345',
                '_options' => ['$first_attribute$', '$second_attribute$'],
                '_links' => ['$simple_1$', '$simple_2$', '$simple_3$', '$simple_4$']
            ]
        )
    ]
    public function testSelectedVariant(): void
    {
        $firstAttribute = $this->getFirstConfigurableAttribute();
        $firstOptions = $firstAttribute->getOptions();
        $firstAttributeFirstOptionUid = $this->selectionUidFormatter->encode(
            (int)$firstAttribute->getAttributeId(),
            (int)$firstOptions[1]->getValue()
        );
        $secondAttribute = $this->getSecondConfigurableAttribute();
        $secondOptions = $secondAttribute->getOptions();
        $secondAttributeFirstOptionUid = $this->selectionUidFormatter->encode(
            (int)$secondAttribute->getAttributeId(),
            (int)$secondOptions[1]->getValue()
        );

        $sku = 'configurable_12345';

        $this->reindexAll();
        $response = $this->graphQlQuery(
            $this->getQuery($sku, [$firstAttributeFirstOptionUid, $secondAttributeFirstOptionUid])
        );

        self::assertNotEmpty($response['products']['items']);
        $product = current($response['products']['items']);
        self::assertEquals('ConfigurableProduct', $product['__typename']);
        self::assertEquals($sku, $product['sku']);
        self::assertEmpty($product['configurable_product_options_selection']['configurable_options']);
        self::assertNotNull($product['configurable_product_options_selection']['variant']);

        $variantId = $this->idEncoder->decode($product['configurable_product_options_selection']['variant']['uid']);
        self::assertIsNumeric($variantId);
        self::assertIsString($product['configurable_product_options_selection']['variant']['sku']);
        $urlKey = 'configurable-option-first-option-1-second-option-1';
        self::assertEquals($urlKey, $product['configurable_product_options_selection']['variant']['url_key']);

        $this->assertMediaGallery($product);
    }

    #[
        DataFixture(
            ConfigurableAttributeFixture::class,
            [
                'attribute_code' => 'test_configurable_first',
                'default_frontend_label' => 'Test Configurable First',
                'options' => [
                    ['label' => 'Option 1', 'sort_order' => 0],
                    ['label' => 'Option 2', 'sort_order' => 1],
                    ['label' => 'Option 3', 'sort_order' => 2],
                    ['label' => 'Option 4', 'sort_order' => 3]
                ]
            ],
            as: 'first_attribute'
        ),
        DataFixture(
            ConfigurableAttributeFixture::class,
            [
                'attribute_code' => 'test_configurable_second',
                'default_frontend_label' => 'Test Configurable Second',
                'options' => [
                    ['label' => 'Option 1', 'sort_order' => 0],
                    ['label' => 'Option 2', 'sort_order' => 1],
                    ['label' => 'Option 3', 'sort_order' => 2],
                    ['label' => 'Option 4', 'sort_order' => 3]
                ]
            ],
            as: 'second_attribute'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 1',
                'sku' => 'simple_1',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ],
                'media_gallery_entries' => [
                    [
                        'media_type' => 'image',
                        'label' => 'Test Image',
                        'position' => 1,
                        'disabled' => false,
                        'types' => ['image', 'small_image', 'thumbnail'],
                        'content' => [
                            'type' => 'image/jpeg',
                            'name' => 'test_image.jpg'
                        ]
                    ]
                ]
            ],
            as: 'simple_1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 2',
                'sku' => 'simple_2',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ],
                'media_gallery_entries' => [
                    [
                        'media_type' => 'image',
                        'label' => 'Test Image',
                        'position' => 1,
                        'disabled' => false,
                        'types' => ['image', 'small_image', 'thumbnail'],
                        'content' => [
                            'type' => 'image/jpeg',
                            'name' => 'test_image.jpg'
                        ]
                    ]
                ]
            ],
            as: 'simple_2'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 3',
                'sku' => 'simple_3',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ],
                'media_gallery_entries' => [
                    [
                        'media_type' => 'image',
                        'label' => 'Test Image',
                        'position' => 1,
                        'disabled' => false,
                        'types' => ['image', 'small_image', 'thumbnail'],
                        'content' => [
                            'type' => 'image/jpeg',
                            'name' => 'test_image.jpg'
                        ]
                    ]
                ]
            ],
            as: 'simple_3'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 4',
                'sku' => 'simple_4',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ],
                'media_gallery_entries' => [
                    [
                        'media_type' => 'image',
                        'label' => 'Test Image',
                        'position' => 1,
                        'disabled' => false,
                        'types' => ['image', 'small_image', 'thumbnail'],
                        'content' => [
                            'type' => 'image/jpeg',
                            'name' => 'test_image.jpg'
                        ]
                    ]
                ]
            ],
            as: 'simple_4'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 5',
                'sku' => 'simple_5',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ]
            ],
            as: 'simple_5'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 6',
                'sku' => 'simple_6',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ]
            ],
            as: 'simple_6'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 7',
                'sku' => 'simple_7',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ]
            ],
            as: 'simple_7'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 8',
                'sku' => 'simple_8',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ]
            ],
            as: 'simple_8'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 9',
                'sku' => 'simple_9',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ]
            ],
            as: 'simple_9'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 10',
                'sku' => 'simple_10',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ]
            ],
            as: 'simple_10'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 11',
                'sku' => 'simple_11',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ]
            ],
            as: 'simple_11'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 12',
                'sku' => 'simple_12',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ]
            ],
            as: 'simple_12'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 13',
                'sku' => 'simple_13',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ]
            ],
            as: 'simple_13'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 14',
                'sku' => 'simple_14',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ]
            ],
            as: 'simple_14'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 15',
                'sku' => 'simple_15',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ]
            ],
            as: 'simple_15'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 16',
                'sku' => 'simple_16',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ]
            ],
            as: 'simple_16'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'name' => 'Configurable Product',
                'sku' => 'configurable_12345',
                '_options' => ['$first_attribute$', '$second_attribute$'],
                '_links' => [
                    '$simple_1$', '$simple_2$', '$simple_3$', '$simple_4$',
                    '$simple_5$', '$simple_6$', '$simple_7$', '$simple_8$',
                    '$simple_9$', '$simple_10$', '$simple_11$', '$simple_12$',
                    '$simple_13$', '$simple_14$', '$simple_15$', '$simple_16$'
                ]
            ]
        )
    ]
    public function testWithoutSelectedOption(): void
    {
        $sku = 'configurable_12345';
        $this->reindexAll();
        $response = $this->graphQlQuery($this->getQuery($sku));

        self::assertNotEmpty($response['products']['items']);
        $product = current($response['products']['items']);
        self::assertEquals('ConfigurableProduct', $product['__typename']);
        self::assertEquals($sku, $product['sku']);

        self::assertNotEmpty($product['configurable_product_options_selection']['configurable_options']);
        self::assertNull($product['configurable_product_options_selection']['variant']);
        self::assertCount(2, $product['configurable_product_options_selection']['configurable_options']);
        self::assertCount(4, $product['configurable_product_options_selection']['configurable_options'][0]['values']);
        self::assertCount(4, $product['configurable_product_options_selection']['configurable_options'][1]['values']);

        $firstAttributeOptions = $this->getFirstConfigurableAttribute()->getOptions();
        $this->assertAvailableOptionUids(
            $this->getFirstConfigurableAttribute()->getAttributeId(),
            $firstAttributeOptions,
            $this->getOptionsUids(
                $product['configurable_product_options_selection']['configurable_options'][0]['values']
            )
        );

        $secondAttributeOptions = $this->getSecondConfigurableAttribute()->getOptions();
        $this->assertAvailableOptionUids(
            $this->getSecondConfigurableAttribute()->getAttributeId(),
            $secondAttributeOptions,
            $this->getOptionsUids(
                $product['configurable_product_options_selection']['configurable_options'][1]['values']
            )
        );

        $this->assertMediaGallery($product);
    }

    #[
        DataFixture(
            ConfigurableAttributeFixture::class,
            [
                'attribute_code' => 'test_configurable_first',
                'default_frontend_label' => 'Test Configurable First',
                'options' => [
                    ['label' => 'Option 1', 'sort_order' => 0],
                    ['label' => 'Option 2', 'sort_order' => 1]
                ]
            ],
            as: 'first_attribute'
        ),
        DataFixture(
            ConfigurableAttributeFixture::class,
            [
                'attribute_code' => 'test_configurable_second',
                'default_frontend_label' => 'Test Configurable Second',
                'options' => [
                    ['label' => 'Option 1', 'sort_order' => 0],
                    ['label' => 'Option 2', 'sort_order' => 1]
                ]
            ],
            as: 'second_attribute'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 1',
                'sku' => 'simple_1',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ]
            ],
            as: 'simple_1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 2',
                'sku' => 'simple_2',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ]
            ],
            as: 'simple_2'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 3',
                'sku' => 'simple_3',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ]
            ],
            as: 'simple_3'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product 4',
                'sku' => 'simple_4',
                'price' => 10.00,
                'weight' => 1,
                'visibility' => 1,
                'status' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100,
                    'is_in_stock' => 1
                ]
            ],
            as: 'simple_4'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'name' => 'Configurable Product',
                'sku' => 'configurable_12345',
                '_options' => ['$first_attribute$', '$second_attribute$'],
                '_links' => ['$simple_1$', '$simple_2$', '$simple_3$', '$simple_4$']
            ]
        )
    ]
    public function testWithWrongSelectedOptions(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('configurableOptionValueUids values are incorrect');

        $attribute = $this->getFirstConfigurableAttribute();
        $options = $attribute->getOptions();
        $sku = 'configurable_12345';
        $firstOptionUid = $this->selectionUidFormatter->encode(
            (int)$attribute->getAttributeId(),
            $options[1]->getValue() + 100
        );

        $this->reindexAll();
        $this->graphQlQuery($this->getQuery($sku, [$firstOptionUid]));
    }

    /**
     * Get GraphQL query to test configurable product options selection
     *
     * @param string $productSku
     * @param array $optionValueUids
     * @param int $pageSize
     * @param int $currentPage
     * @return string
     */
    private function getQuery(
        string $productSku,
        array $optionValueUids = [],
        int $pageSize = 20,
        int $currentPage = 1
    ): string {
        if (empty($optionValueUids)) {
            $configurableOptionValueUids = '';
        } else {
            $configurableOptionValueUids = '(configurableOptionValueUids: [';
            foreach ($optionValueUids as $configurableOptionValueUid) {
                $configurableOptionValueUids .= '"' . $configurableOptionValueUid . '",';
            }
            $configurableOptionValueUids .= '])';
        }

        return <<<QUERY
{
products(filter:{
     sku: {eq: "{$productSku}"}
     },
     pageSize: {$pageSize}, currentPage: {$currentPage}
  )
  {
    items {
      __typename
      sku
      ... on ConfigurableProduct {
        configurable_product_options_selection {$configurableOptionValueUids} {
          configurable_options {
            uid
            attribute_code
            label
            values {
              uid
              is_available
              is_use_default
              label
              swatch {
                value
              }
            }
          }
          variant {
            uid
            sku
            url_key
          }
          media_gallery {
            url
            label
            disabled
          }
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * Get first configurable attribute.
     *
     * @return AttributeInterface
     * @throws NoSuchEntityException
     */
    private function getFirstConfigurableAttribute(): AttributeInterface
    {
        if (!$this->firstConfigurableAttribute) {
            $this->firstConfigurableAttribute = $this->attributeRepository->get(
                'catalog_product',
                'test_configurable_first'
            );
        }

        return $this->firstConfigurableAttribute;
    }

    /**
     * Get second configurable attribute.
     *
     * @return AttributeInterface
     * @throws NoSuchEntityException
     */
    private function getSecondConfigurableAttribute(): AttributeInterface
    {
        if (!$this->secondConfigurableAttribute) {
            $this->secondConfigurableAttribute = $this->attributeRepository->get(
                'catalog_product',
                'test_configurable_second'
            );
        }

        return $this->secondConfigurableAttribute;
    }

    /**
     * Assert option uid.
     *
     * @param $attributeId
     * @param $expectedOptions
     * @param $selectedOptions
     */
    private function assertAvailableOptionUids($attributeId, $expectedOptions, $selectedOptions): void
    {
        unset($expectedOptions[0]);
        foreach ($expectedOptions as $option) {
            self::assertContains(
                $this->selectionUidFormatter->encode((int)$attributeId, (int)$option->getValue()),
                $selectedOptions
            );
        }
    }

    /**
     * Make fulltext catalog search reindex
     *
     * @return void
     * @throws \Throwable
     */
    private function reindexAll(): void
    {
        $indexLists = [
            'catalog_category_product',
            'catalog_product_category',
            'catalog_product_attribute',
            'cataloginventory_stock',
            'catalogsearch_fulltext',
        ];

        foreach ($indexLists as $indexerId) {
            $indexer = $this->indexerFactory->create();
            $indexer->load($indexerId)->reindexAll();
        }
    }

    /**
     * Retrieve options UIDs
     *
     * @param array $options
     * @return array
     */
    private function getOptionsUids(array $options): array
    {
        $uids = [];
        foreach ($options as $option) {
            $uids[] = $option['uid'];
        }
        return $uids;
    }

    /**
     * Assert media gallery fields
     *
     * @param array $product
     */
    private function assertMediaGallery(array $product): void
    {
        self::assertNotEmpty($product['configurable_product_options_selection']['media_gallery']);
        $image = current($product['configurable_product_options_selection']['media_gallery']);
        self::assertIsString($image['url']);
        self::assertEquals(false, $image['disabled']);
    }
}
