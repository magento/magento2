<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;

class ArrayBackendTest extends TestCase
{
    /**
     * @var ArrayBackend
     */
    private $_model;

    /**
     * @var Attribute
     */
    private $_attribute;

    protected function setUp(): void
    {
        $this->_attribute = $this->createPartialMock(
            Attribute::class,
            ['getAttributeCode', 'getDefaultValue', '__wakeup']
        );
        $this->_model = new ArrayBackend();
        $this->_model->setAttribute($this->_attribute);
    }

    /**
     * @dataProvider validateDataProvider
     * @param array $productData
     * @param bool $hasData
     * @param string|int|float|null $expectedValue
     */
    public function testValidate(array $productData, bool $hasData, $expectedValue)
    {
        $this->_attribute->expects($this->atLeastOnce())
            ->method('getAttributeCode')
            ->willReturn('attr');

        $product = new DataObject($productData);
        $this->_model->validate($product);
        $this->assertEquals($hasData, $product->hasData('attr'));
        $this->assertEquals($expectedValue, $product->getAttr());
    }

    /**
     * @return array
     */
    public static function validateDataProvider(): array
    {
        return [
            [
                ['sku' => 'test1', 'attr' => [1, 2, 3]],
                true,
                '1,2,3',
            ],
            [
                ['sku' => 'test1', 'attr' => '1,2,3'],
                true,
                '1,2,3',
            ],
            [
                ['sku' => 'test1', 'attr' => null],
                true,
                null,
            ],
            [
                ['sku' => 'test1'],
                false,
                null,
            ],
        ];
    }

    /**
     * @dataProvider beforeSaveDataProvider
     * @param array $productData
     * @param string $defaultValue
     * @param string $expectedValue
     */
    public function testBeforeSave(
        array $productData,
        string $defaultValue,
        string $expectedValue
    ) {
        $this->_attribute->expects($this->atLeastOnce())
            ->method('getAttributeCode')
            ->willReturn('attr');
        $this->_attribute->expects($this->any())
            ->method('getDefaultValue')
            ->willReturn($defaultValue);

        $product = new DataObject($productData);
        $this->_model->beforeSave($product);
        $this->assertEquals($expectedValue, $product->getAttr());
    }

    /**
     * @return array
     */
    public function beforeSaveDataProvider(): array
    {
        return [
            [
                ['sku' => 'test1', 'attr' => 'Value 2'],
                'Default value 1',
                'Value 2',
            ],
            [
                ['sku' => 'test1'],
                'Default value 1',
                'Default value 1',
            ],
        ];
    }
}
