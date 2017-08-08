<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Type;

class VirtualTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test for method prepareAttributesWithDefaultValueForSave
     */
    public function testPrepareAttributesWithDefaultValueForSave()
    {
        $virtualModelMock = $this->createPartialMock(\Magento\CatalogImportExport\Model\Import\Product\Type\Virtual::class, []);

        $this->setPropertyValue(
            $virtualModelMock,
            '_attributes',
            [
                'Default' => [
                    'name' => [
                        'id' => '69',
                        'code' => 'name',
                        'is_global' => '0',
                        'is_required' => '1',
                        'is_unique' => '0',
                        'frontend_label' => 'Name',
                        'is_static' => false,
                        'apply_to' => [],
                        'type' => 'varchar',
                        'default_value' => null,
                        'options' => [],
                    ],
                    'sku' => [
                        'id' => '70',
                        'code' => 'sku',
                        'is_global' => '1',
                        'is_required' => '1',
                        'is_unique' => '1',
                        'frontend_label' => 'SKU',
                        'is_static' => true,
                        'apply_to' => [],
                        'type' => 'varchar',
                        'default_value' => null,
                        'options' => [],
                    ]
                ]
            ]
        );

        $rowData = [
            '_attribute_set' => 'Default',
            'sku' => 'downloadablesku1',
            'product_type' => 'virtual',
            'name' => 'Downloadable Product 1'
        ];

        $expectedResult = [
            'name' => 'Downloadable Product 1',
            'weight' => null
        ];

        $result = $virtualModelMock->prepareAttributesWithDefaultValueForSave($rowData);
        $this->assertEquals($expectedResult, $result);
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
}
