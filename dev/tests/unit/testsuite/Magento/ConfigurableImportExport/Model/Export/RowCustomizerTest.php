<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableImportExport\Model\Export;

class RowCustomizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableImportExport\Model\Export\RowCustomizer
     */
    protected $_model;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_collectionMock;

    protected function setUp()
    {
        $this->_collectionMock = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\Collection',
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
                '_super_products_sku',
                '_super_attribute_code',
                '_super_attribute_option',
                '_super_attribute_price_corr',
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
        return [
            [
                [
                    'key_1' => 'value_1',
                    'key_2' => 'value_2',
                    'key_3' => 'value_3',
                ],
                [
                    'data_row' => [
                        'key_1' => 'value_1',
                        'key_2' => 'value_2',
                        'key_3' => 'value_3',
                    ],
                    'product_id' => 1
                ],
            ],
            [
                [
                    'key_1' => 'value_1',
                    'key_2' => 'value_2',
                    'key_3' => 'value_3',
                    '_super_products_sku' => '_sku_',
                    '_super_attribute_code' => 'code_of_attribute',
                    '_super_attribute_option' => 'Option Title',
                    '_super_attribute_price_corr' => '12345%',
                ],
                [
                    'data_row' => [
                        'key_1' => 'value_1',
                        'key_2' => 'value_2',
                        'key_3' => 'value_3',
                    ],
                    'product_id' => 11
                ]
            ]
        ];
    }

    protected function _initConfigurableData()
    {
        $productIds = [1, 2, 3];
        $attributes = [
            [
                [
                    'pricing_is_percent' => true,
                    'sku' => '_sku_',
                    'attribute_code' => 'code_of_attribute',
                    'option_title' => 'Option Title',
                    'pricing_value' => 12345,
                ],
                [
                    'pricing_is_percent' => false,
                    'sku' => '_sku_',
                    'attribute_code' => 'code_of_attribute',
                    'option_title' => 'Option Title',
                    'pricing_value' => 12345,
                ],
            ],
        ];

        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getId', 'getTypeInstance', '__wakeup'],
            [],
            '',
            false
        );
        $productMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(11));

        $typeInstanceMock = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable', [], [], '', false
        );
        $typeInstanceMock->expects($this->any())
            ->method('getConfigurableOptions')
            ->will($this->returnValue($attributes));

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
    }
}
