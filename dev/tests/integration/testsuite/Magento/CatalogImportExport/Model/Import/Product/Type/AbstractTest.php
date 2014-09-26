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
namespace Magento\CatalogImportExport\Model\Import\Product\Type;

class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected $_model;

    /**
     * On product import abstract class methods level it doesn't matter what product type is using.
     * That is why current tests are using simple product entity type by default
     */
    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $params = array($objectManager->create('Magento\CatalogImportExport\Model\Import\Product'), 'simple');
        $this->_model = $this->getMockForAbstractClass(
            'Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType',
            array(
                $objectManager->get('Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory'),
                $objectManager->get('Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory'),
                $params
            )
        );
    }

    /**
     * @dataProvider prepareAttributesWithDefaultValueForSaveDataProvider
     */
    public function testPrepareAttributesWithDefaultValueForSave($rowData, $withDefaultValue, $expectedAttributes)
    {
        $actualAttributes = $this->_model->prepareAttributesWithDefaultValueForSave($rowData, $withDefaultValue);
        foreach ($expectedAttributes as $key => $value) {
            $this->assertArrayHasKey($key, $actualAttributes);
            $this->assertEquals($value, $actualAttributes[$key]);
        }
    }

    public function prepareAttributesWithDefaultValueForSaveDataProvider()
    {
        return array(
            'Updating existing product with attributes that do not have default values' => array(
                array('sku' => 'simple_product_1', 'price' => 55, '_attribute_set' => 'Default', '_type' => 'simple'),
                false,
                array('price' => 55)
            ),
            'Updating existing product with attributes that have default values' => array(
                array(
                    'sku' => 'simple_product_2',
                    'price' => 65,
                    '_attribute_set' => 'Default',
                    '_type' => 'simple',
                    'visibility' => 1,
                    'tax_class_id' => ''
                ),
                false,
                array('price' => 65, 'visibility' => 1, 'tax_class_id' => '')
            ),
            'Adding new product with attributes that do not have default values' => array(
                array(
                    'sku' => 'simple_product_3',
                    '_store' => '',
                    '_attribute_set' => 'Default',
                    '_type' => 'simple',
                    '_category' => '_root_category',
                    '_product_websites' => 'base',
                    'name' => 'Simple Product 3',
                    'price' => 150,
                    'status' => 1,
                    'tax_class_id' => '2',
                    'weight' => 1,
                    'description' => 'a',
                    'short_description' => 'a',
                    'visibility' => 1
                ),
                true,
                array(
                    'name' => 'Simple Product 3',
                    'price' => 150,
                    'status' => 1,
                    'tax_class_id' => '2',
                    'weight' => 1,
                    'description' => 'a',
                    'short_description' => 'a',
                    'visibility' => 1,
                    'options_container' => 'container2',
                    'msrp_display_actual_price_type' => 0
                )
            ),
            'Adding new product with attributes that have default values' => array(
                array(
                    'sku' => 'simple_product_4',
                    '_store' => '',
                    '_attribute_set' => 'Default',
                    '_type' => 'simple',
                    '_category' => '_root_category',
                    '_product_websites' => 'base',
                    'name' => 'Simple Product 4',
                    'price' => 100,
                    'status' => 1,
                    'tax_class_id' => '2',
                    'weight' => 1,
                    'description' => 'a',
                    'short_description' => 'a',
                    'visibility' => 2,
                    'msrp_display_actual_price_type' => 'In Cart'
                ),
                true,
                array(
                    'name' => 'Simple Product 4',
                    'price' => 100,
                    'status' => 1,
                    'tax_class_id' => '2',
                    'weight' => 1,
                    'description' => 'a',
                    'short_description' => 'a',
                    'visibility' => 2,
                    'options_container' => 'container2',
                    'msrp_display_actual_price_type' => 2
                )
            )
        );
    }

    /**
     * @dataProvider clearEmptyDataDataProvider
     */
    public function testClearEmptyData($rowData, $expectedAttributes)
    {
        $actualAttributes = $this->_model->clearEmptyData($rowData);
        foreach ($expectedAttributes as $key => $value) {
            $this->assertArrayHasKey($key, $actualAttributes);
            $this->assertEquals($value, $actualAttributes[$key]);
        }
    }

    public function clearEmptyDataDataProvider()
    {
        return array(
            array(
                array(
                    'sku' => 'simple1',
                    '_store' => '',
                    '_attribute_set' => 'Default',
                    '_type' => 'simple',
                    'name' => 'Simple 01',
                    'price' => 10
                ),
                array(
                    'sku' => 'simple1',
                    '_store' => '',
                    '_attribute_set' => 'Default',
                    '_type' => 'simple',
                    'name' => 'Simple 01',
                    'price' => 10
                )
            ),
            array(
                array(
                    'sku' => '',
                    '_store' => 'German',
                    '_attribute_set' => 'Default',
                    '_type' => '',
                    'name' => 'Simple 01 German',
                    'price' => ''
                ),
                array(
                    'sku' => '',
                    '_store' => 'German',
                    '_attribute_set' => 'Default',
                    '_type' => '',
                    'name' => 'Simple 01 German'
                )
            )
        );
    }
}
