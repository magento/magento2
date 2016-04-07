<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableImportExport\Test\Unit\Model\Export;

use \Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\ImportExport\Model\Import;

class RowCustomizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test existing product id.
     *
     * @var int
     */
    protected $initiatedProductId = 11;

    /**
     * @var \Magento\ConfigurableImportExport\Model\Export\RowCustomizer
     */
    protected $_model;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_collectionMock;

    protected function setUp()
    {
        $this->_collectionMock = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Product\Collection',
            ['addAttributeToFilter', 'fetchItem', '__wakeup'],
            [],
            '',
            false
        );
        $this->_model = new \Magento\ConfigurableImportExport\Model\Export\RowCustomizer();
    }

    public function testPrepareData()
    {
        $this->_initConfigurableData();
    }

    public function testAddHeaderColumns()
    {
        $this->_initConfigurableData();
        $this->assertEquals(
            [
                'column_1',
                'column_2',
                'column_3',
                'configurable_variations',
                'configurable_variation_labels',
            ],
            $this->_model->addHeaderColumns(
                ['column_1', 'column_2', 'column_3']
            )
        );
    }

    /**
     * @param array $expected
     * @param array $data
     * @dataProvider addDataDataProvider
     */
    public function testAddData(array $expected, array $data)
    {
        $this->_initConfigurableData();
        $this->assertEquals(
            $expected,
            $this->_model->addData($data['data_row'], $data['product_id'])
        );
    }

    /**
     * @param array $expected
     * @param array $data
     * @dataProvider getAdditionalRowsCountDataProvider
     */
    public function testGetAdditionalRowsCount(array $expected, array $data)
    {
        $this->_initConfigurableData();
        $this->assertEquals(
            $expected,
            $this->_model->getAdditionalRowsCount($data['row_count'], $data['product_id'])
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
                ],
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

    /**
     * @return array
     */
    public function addDataDataProvider()
    {
        $expectedConfigurableData = $this->getExpectedConfigurableData();
        $data = $expectedConfigurableData[$this->initiatedProductId];

        return [
            [
                '$expected' => [
                    'key_1' => 'value_1',
                    'key_2' => 'value_2',
                    'key_3' => 'value_3',
                ],
                '$data' => [
                    'data_row' => [
                        'key_1' => 'value_1',
                        'key_2' => 'value_2',
                        'key_3' => 'value_3',
                    ],
                    'product_id' => 1
                ],
            ],
            [
                '$expected' => [
                    'key_1' => 'value_1',
                    'key_2' => 'value_2',
                    'key_3' => 'value_3',
                    'configurable_variations' => $data['configurable_variations'],
                    'configurable_variation_labels' => $data['configurable_variation_labels'],
                ],
                '$data' => [
                    'data_row' => [
                        'key_1' => 'value_1',
                        'key_2' => 'value_2',
                        'key_3' => 'value_3',
                    ],
                    'product_id' => $this->initiatedProductId
                ]
            ]
        ];
    }

    protected function _initConfigurableData()
    {
        $productIds = [1, 2, 3];
        $productAttributesOptions = [
            [//1 $productAttributeOption
                [//1opt $optValue
                    'pricing_is_percent'    => true,
                    'sku'                   => '_sku_',
                    'attribute_code'        => 'code_of_attribute',
                    'option_title'          => 'Option Title',
                    'pricing_value'         => 112345,
                    'super_attribute_label' => 'Super attribute label',
                ],
                [//2opt $optValue
                    'pricing_is_percent'    => false,
                    'sku'                   => '_sku_',
                    'attribute_code'        => 'code_of_attribute',
                    'option_title'          => 'Option Title',
                    'pricing_value'         => 212345,
                    'super_attribute_label' => '',
                ],
                [//3opt $optValue
                    'pricing_is_percent'    => false,
                    'sku'                   => '_sku_2',
                    'attribute_code'        => 'code_of_attribute_2',
                    'option_title'          => 'Option Title 2',
                    'pricing_value'         => 312345,
                    'super_attribute_label' => 'Super attribute label 2',
                ],
            ],
        ];

        $expectedConfigurableData = $this->getExpectedConfigurableData();

        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getId', 'getTypeInstance', '__wakeup'],
            [],
            '',
            false
        );
        $productMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($this->initiatedProductId));

        $typeInstanceMock = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable',
            [],
            [],
            '',
            false
        );
        $typeInstanceMock->expects($this->any())
            ->method('getConfigurableOptions')
            ->will($this->returnValue($productAttributesOptions));

        $productMock->expects($this->any())
            ->method('getTypeInstance')
            ->will($this->returnValue($typeInstanceMock));

        $this->_collectionMock->expects($this->at(0))
            ->method('addAttributeToFilter')
            ->with('entity_id', ['in' => $productIds])
            ->will($this->returnSelf());
        $this->_collectionMock->expects($this->at(1))
            ->method('addAttributeToFilter')
            ->with('type_id', ['eq' => \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE])
            ->will($this->returnSelf());
        $this->_collectionMock->expects($this->at(2))
            ->method('fetchItem')
            ->will($this->returnValue($productMock));
        $this->_collectionMock->expects($this->at(3))
            ->method('fetchItem')
            ->will($this->returnValue(false));


        $this->_model->prepareData($this->_collectionMock, $productIds);

        $configurableData = $this->getPropertyValue($this->_model, 'configurableData');

        $this->assertEquals($expectedConfigurableData, $configurableData);
    }

    /**
     * Return expected configurable data
     *
     * @return array
     */
    protected function getExpectedConfigurableData()
    {
        return [
            $this->initiatedProductId => [
                'configurable_variations' => implode(ImportProduct::PSEUDO_MULTI_LINE_SEPARATOR, [
                    '_sku_' => 'sku=_sku_'  . Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR
                        . implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, [
                            'code_of_attribute=Option Title',
                            'code_of_attribute=Option Title',
                        ]),
                    '_sku_2' => 'sku=_sku_2' . Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR
                        . implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, [
                            'code_of_attribute_2=Option Title 2',
                        ])
                ]),
                'configurable_variation_labels' => implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, [
                    'code_of_attribute' => 'code_of_attribute=Super attribute label',
                    'code_of_attribute_2' => 'code_of_attribute_2=Super attribute label 2',
                ]),
            ],
        ];
    }

    /**
     * @param $object
     * @param $property
     * @param $value
     */
    protected function setPropertyValue(&$object, $property, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
        return $object;
    }

    /**
     * @param $object
     * @param $property
     * @return mixed
     */
    protected function getPropertyValue(&$object, $property)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }
}
