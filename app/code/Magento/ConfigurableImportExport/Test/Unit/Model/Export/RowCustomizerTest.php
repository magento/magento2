<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableImportExport\Test\Unit\Model\Export;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\ConfigurableImportExport\Model\Export\RowCustomizer as ExportRowCustomizer;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\ImportExport\Model\Import;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RowCustomizerTest extends TestCase
{
    /**
     * @var ExportRowCustomizer
     */
    private $exportRowCustomizer;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ProductCollection|MockObject
     */
    private $productCollectionMock;

    /**
     * @var ConfigurableProductType|MockObject
     */
    private $configurableProductTypeMock;

    /**
     * @var int
     */
    private $productId = 11;

    protected function setUp(): void
    {
        $this->productCollectionMock = $this->getMockBuilder(ProductCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configurableProductTypeMock = $this->getMockBuilder(ConfigurableProductType::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->exportRowCustomizer = $this->objectManagerHelper->getObject(ExportRowCustomizer::class);
    }

    public function testAddHeaderColumns()
    {
        $this->initConfigurableData();

        $this->assertEquals(
            [
                'column_1',
                'column_2',
                'column_3',
                'configurable_variations',
                'configurable_variation_labels',
            ],
            $this->exportRowCustomizer->addHeaderColumns(['column_1', 'column_2', 'column_3'])
        );
    }

    /**
     * @param array $expected
     * @param array $data
     *
     * @dataProvider addDataDataProvider
     */
    public function testAddData(array $expected, array $data)
    {
        $this->initConfigurableData();

        $this->assertEquals($expected, $this->exportRowCustomizer->addData($data['data_row'], $data['product_id']));
    }

    /**
     * @return array
     */
    public function addDataDataProvider()
    {
        $expectedConfigurableData = $this->getExpectedConfigurableData();
        $data = $expectedConfigurableData[$this->productId];

        return [
            [
                '$expected' => [
                    'key_1' => 'value_1',
                    'key_2' => 'value_2',
                    'key_3' => 'value_3'
                ],
                '$data' => [
                    'data_row' => [
                        'key_1' => 'value_1',
                        'key_2' => 'value_2',
                        'key_3' => 'value_3'
                    ],
                    'product_id' => 1
                ]
            ],
            [
                '$expected' => [
                    'key_1' => 'value_1',
                    'key_2' => 'value_2',
                    'key_3' => 'value_3',
                    'configurable_variations' => $data['configurable_variations'],
                    'configurable_variation_labels' => $data['configurable_variation_labels']
                ],
                '$data' => [
                    'data_row' => [
                        'key_1' => 'value_1',
                        'key_2' => 'value_2',
                        'key_3' => 'value_3'
                    ],
                    'product_id' => $this->productId
                ]
            ]
        ];
    }

    /**
     * @param array $expected
     * @param array $data
     *
     * @dataProvider getAdditionalRowsCountDataProvider
     */
    public function testGetAdditionalRowsCount(array $expected, array $data)
    {
        $this->initConfigurableData();

        $this->assertEquals(
            $expected,
            $this->exportRowCustomizer->getAdditionalRowsCount($data['row_count'], $data['product_id'])
        );
    }

    /**
     * @return array
     */
    public function getAdditionalRowsCountDataProvider()
    {
        return [
            [
                [1, 2, 3],
                [
                    'row_count' => [1, 2, 3],
                    'product_id' => 1
                ]
            ],
            [
                [1, 2, 3],
                [
                    'row_count' => [1, 2, 3],
                    'product_id' => 11
                ]
            ],
            [
                [],
                [
                    'row_count' => [],
                    'product_id' => 11
                ]
            ]
        ];
    }

    private function initConfigurableData()
    {
        $productIds = [1, 2, 3];
        $expectedConfigurableData = $this->getExpectedConfigurableData();
        $productMock = $this->createProductMock();
        $productAttributesOptions = [
            [
                [
                    'pricing_is_percent'    => true,
                    'sku'                   => '_sku_',
                    'attribute_code'        => 'code_of_attribute',
                    'option_title'          => 'Option Title',
                    'pricing_value'         => 112345,
                    'super_attribute_label' => 'Super attribute label'
                ],
                [
                    'pricing_is_percent'    => false,
                    'sku'                   => '_sku_',
                    'attribute_code'        => 'code_of_attribute',
                    'option_title'          => 'Option Title',
                    'pricing_value'         => 212345,
                    'super_attribute_label' => ''
                ],
                [
                    'pricing_is_percent'    => false,
                    'sku'                   => '_sku_2',
                    'attribute_code'        => 'code_of_attribute_2',
                    'option_title'          => 'Option Title 2',
                    'pricing_value'         => 312345,
                    'super_attribute_label' => 'Super attribute label 2'
                ]
            ]
        ];

        $productMock->expects(static::any())
            ->method('getId')
            ->willReturn($this->productId);
        $productMock->expects(static::any())
            ->method('getTypeInstance')
            ->willReturn($this->configurableProductTypeMock);
        $this->configurableProductTypeMock->expects(static::any())
            ->method('getConfigurableOptions')
            ->willReturn($productAttributesOptions);
        $this->productCollectionMock->expects(static::atLeastOnce())
            ->method('addAttributeToFilter')
            ->willReturnMap(
                [
                    ['entity_id', ['in' => $productIds], 'inner', $this->productCollectionMock],
                    ['type_id', ['eq' => ConfigurableProductType::TYPE_CODE], 'inner', $this->productCollectionMock]
                ]
            );
        $this->productCollectionMock->expects(static::atLeastOnce())
            ->method('fetchItem')
            ->willReturnOnConsecutiveCalls($productMock, false);

        $this->exportRowCustomizer->prepareData($this->productCollectionMock, $productIds);
        $this->assertEquals(
            $expectedConfigurableData,
            $this->getPropertyValue($this->exportRowCustomizer, 'configurableData')
        );
    }

    /**
     * Return expected configurable data
     *
     * @return array
     */
    private function getExpectedConfigurableData()
    {
        return [
            $this->productId => [
                'configurable_variations' => implode(
                    ImportProduct::PSEUDO_MULTI_LINE_SEPARATOR,
                    [
                        '_sku_' => 'sku=_sku_' . Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR
                            . implode(
                                Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR,
                                ['code_of_attribute=Option Title', 'code_of_attribute=Option Title']
                            ),
                        '_sku_2' => 'sku=_sku_2' . Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR
                            . implode(
                                Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR,
                                ['code_of_attribute_2=Option Title 2']
                            )
                    ]
                ),
                'configurable_variation_labels' => implode(
                    Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR,
                    [
                        'code_of_attribute' => 'code_of_attribute=Super attribute label',
                        'code_of_attribute_2' => 'code_of_attribute_2=Super attribute label 2'
                    ]
                )
            ]
        ];
    }

    /**
     * Create product mock object
     *
     * @return Product|MockObject
     */
    private function createProductMock()
    {
        return $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Get value of protected property
     *
     * @param object $object
     * @param string $property
     * @return mixed
     */
    private function getPropertyValue($object, $property)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }
}
