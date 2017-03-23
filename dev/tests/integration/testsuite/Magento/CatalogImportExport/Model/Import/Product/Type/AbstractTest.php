<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $params = [$objectManager->create(\Magento\CatalogImportExport\Model\Import\Product::class), 'simple'];
        $this->_model = $this->getMockForAbstractClass(
            \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType::class,
            [
                $objectManager->get(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory::class),
                $objectManager->get(\Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory::class),
                $objectManager->get(\Magento\Framework\App\ResourceConnection::class),
                $params
            ]
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
        return [
            'Updating existing product with attributes that do not have default values' => [
                ['sku' => 'simple_product_1', 'price' => 55, '_attribute_set' => 'Default', 'product_type' => 'simple'],
                false,
                ['price' => 55],
            ],
            'Updating existing product with attributes that have default values' => [
                [
                    'sku' => 'simple_product_2',
                    'price' => 65,
                    '_attribute_set' => 'Default',
                    'product_type' => 'simple',
                    'visibility' => 'not visible individually',
                    'tax_class_id' => '',
                ],
                false,
                ['price' => 65, 'visibility' => 1, 'tax_class_id' => ''],
            ],
            'Adding new product with attributes that do not have default values' => [
                [
                    'sku' => 'simple_product_3',
                    'store_view_code' => '',
                    '_attribute_set' => 'Default',
                    'product_type' => 'simple',
                    'categories' => '_root_category',
                    'website_code' => '',
                    'name' => 'Simple Product 3',
                    'price' => 150,
                    'status' => 1,
                    'tax_class_id' => '2',
                    'weight' => 1,
                    'description' => 'a',
                    'short_description' => 'a',
                    'visibility' => 'not visible individually',
                ],
                true,
                [
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
                ],
            ],
            'Adding new product with attributes that have default values' => [
                [
                    'sku' => 'simple_product_4',
                    'store_view_code' => '',
                    '_attribute_set' => 'Default',
                    'product_type' => 'simple',
                    'categories' => '_root_category',
                    'website_code' => 'base',
                    'name' => 'Simple Product 4',
                    'price' => 100,
                    'status' => 1,
                    'tax_class_id' => '2',
                    'weight' => 1,
                    'description' => 'a',
                    'short_description' => 'a',
                    'visibility' => 'catalog',
                    'msrp_display_actual_price_type' => 'In Cart',
                ],
                true,
                [
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
                ],
            ]
        ];
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
        return [
            [
                [
                    'sku' => 'simple1',
                    'store_view_code' => '',
                    '_attribute_set' => 'Default',
                    'product_type' => 'simple',
                    'name' => 'Simple 01',
                    'price' => 10,
                ],
                [
                    'sku' => 'simple1',
                    'store_view_code' => '',
                    '_attribute_set' => 'Default',
                    'product_type' => 'simple',
                    'name' => 'Simple 01',
                    'price' => 10
                ],
            ],
            [
                [
                    'sku' => '',
                    'store_view_code' => 'German',
                    '_attribute_set' => 'Default',
                    'product_type' => '',
                    'name' => 'Simple 01 German',
                    'price' => '',
                ],
                [
                    'sku' => '',
                    'store_view_code' => 'German',
                    '_attribute_set' => 'Default',
                    'product_type' => '',
                    'name' => 'Simple 01 German'
                ]
            ]
        ];
    }
}
