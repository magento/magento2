<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            array('addAttributeToFilter', 'fetchItem', '__wakeup'),
            array(),
            '',
            false
        );
        $this->_model = new \Magento\ConfigurableImportExport\Model\Export\RowCustomizer;
    }

    public function testPrepareData()
    {
        $this->_initConfigurableData();
    }

    public function testAddHeaderColumns()
    {
        $this->_initConfigurableData();
        $this->assertEquals(
            array(
                'column_1',
                'column_2',
                'column_3',
                '_super_products_sku',
                '_super_attribute_code',
                '_super_attribute_option',
                '_super_attribute_price_corr'
            ),
            $this->_model->addHeaderColumns(
                array('column_1', 'column_2', 'column_3')
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
        return array(
            array(
                array(1, 2, 3),
                array(
                    'row_count' => array(1, 2, 3),
                    'product_id' => 1
                )
            ),
            array(
                array(1, 2, 3),
                array(
                    'row_count' => array(1, 2, 3),
                    'product_id' => 11
                )
            ),
            array(
                array(),
                array(
                    'row_count' => array(),
                    'product_id' => 11
                )
            )
        );
    }

    /**
     * @return array
     */
    public function addDataDataProvider()
    {
        return array(
            array(
                array(
                    'key_1' => 'value_1',
                    'key_2' => 'value_2',
                    'key_3' => 'value_3'
                ),
                array(
                    'data_row' => array(
                        'key_1' => 'value_1',
                        'key_2' => 'value_2',
                        'key_3' => 'value_3'
                    ),
                    'product_id' => 1
                )
            ),
            array(
                array(
                    'key_1' => 'value_1',
                    'key_2' => 'value_2',
                    'key_3' => 'value_3',
                    '_super_products_sku' => '_sku_',
                    '_super_attribute_code' => 'code_of_attribute',
                    '_super_attribute_option' => 'Option Title',
                    '_super_attribute_price_corr' => '12345%'
                ),
                array(
                    'data_row' => array(
                        'key_1' => 'value_1',
                        'key_2' => 'value_2',
                        'key_3' => 'value_3'
                    ),
                    'product_id' => 11
                )
            )
        );
    }

    protected function _initConfigurableData()
    {
        $productIds = array(1, 2, 3);
        $attributes = array(
            array(
                array(
                    'pricing_is_percent' => true,
                    'sku' => '_sku_',
                    'attribute_code' => 'code_of_attribute',
                    'option_title' => 'Option Title',
                    'pricing_value' => 12345,
                ),
                array(
                    'pricing_is_percent' => false,
                    'sku' => '_sku_',
                    'attribute_code' => 'code_of_attribute',
                    'option_title' => 'Option Title',
                    'pricing_value' => 12345,
                )
            )
        );

        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            array('getId', 'getTypeInstance', '__wakeup'),
            array(),
            '',
            false
        );
        $productMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(11));

        $typeInstanceMock = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable', array(), array(), '', false
        );
        $typeInstanceMock->expects($this->any())
            ->method('getConfigurableOptions')
            ->will($this->returnValue($attributes));

        $productMock->expects($this->any())
            ->method('getTypeInstance')
            ->will($this->returnValue($typeInstanceMock));

        $this->_collectionMock->expects($this->at(0))
            ->method('addAttributeToFilter')
            ->with('entity_id', array('in' => $productIds))
            ->will($this->returnSelf());
        $this->_collectionMock->expects($this->at(1))
            ->method('addAttributeToFilter')
            ->with('type_id', array('eq' => \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE))
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
