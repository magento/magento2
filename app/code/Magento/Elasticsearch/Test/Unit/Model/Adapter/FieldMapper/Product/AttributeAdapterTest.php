<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper\Product;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD)
 */
class AttributeAdapterTest extends TestCase
{
    /**
     * @var AttributeAdapter
     */
    private $adapter;

    /**
     * @var AbstractExtensibleModel
     */
    private $attribute;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->attribute = $this->getMockBuilder(CustomAttributesDataInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getIsFilterable',
                'getIsFilterableInSearch',
                'getIsSearchable',
                'getIsVisibleInAdvancedSearch',
                'getBackendType',
                'getFrontendInput',
                'usesSource',
            ])
            ->getMockForAbstractClass();

        $objectManager = new ObjectManagerHelper($this);

        $this->adapter = $objectManager->getObject(
            AttributeAdapter::class,
            [
                'attribute' => $this->attribute,
                'attributeCode' => 'code',
            ]
        );
    }

    /**
     * @dataProvider isFilterableProvider
     * @param $isFilterable
     * @param $isFilterableInSearch
     * @param $expected
     * @return void
     */
    public function testIsFilterable($isFilterable, $isFilterableInSearch, $expected)
    {
        $this->attribute
            ->method('getIsFilterable')
            ->willReturn($isFilterable);
        $this->attribute
            ->method('getIsFilterableInSearch')
            ->willReturn($isFilterableInSearch);
        $this->assertEquals(
            $expected,
            $this->adapter->isFilterable()
        );
    }

    /**
     * @dataProvider isSearchableProvider
     * @param $isSearchable
     * @param $isVisibleInAdvancedSearch
     * @param $isFilterable
     * @param $isFilterableInSearch
     * @param $expected
     * @return void
     */
    public function testIsSearchable(
        $isSearchable,
        $isVisibleInAdvancedSearch,
        $isFilterable,
        $isFilterableInSearch,
        $expected
    ) {
        $this->attribute
            ->method('getIsSearchable')
            ->willReturn($isSearchable);
        $this->attribute
            ->method('getIsVisibleInAdvancedSearch')
            ->willReturn($isVisibleInAdvancedSearch);
        $this->attribute
            ->method('getIsFilterable')
            ->willReturn($isFilterable);
        $this->attribute
            ->method('getIsFilterableInSearch')
            ->willReturn($isFilterableInSearch);
        $this->assertEquals(
            $expected,
            $this->adapter->isSearchable()
        );
    }

    /**
     * @dataProvider isAlwaysIndexableProvider
     * @param $expected
     * @return void
     */
    public function testIsAlwaysIndexable($expected)
    {
        $this->assertEquals(
            $expected,
            $this->adapter->isAlwaysIndexable()
        );
    }

    /**
     * @dataProvider isDateTimeTypeProvider
     * @param $backendType
     * @param $expected
     * @return void
     */
    public function testIsDateTimeType($backendType, $expected)
    {
        $this->attribute
            ->method('getBackendType')
            ->willReturn($backendType);
        $this->assertEquals(
            $expected,
            $this->adapter->isDateTimeType()
        );
    }

    /**
     * @dataProvider isFloatTypeProvider
     * @param $backendType
     * @param $expected
     * @return void
     */
    public function testIsFloatType($backendType, $expected)
    {
        $this->attribute
            ->method('getBackendType')
            ->willReturn($backendType);
        $this->assertEquals(
            $expected,
            $this->adapter->isFloatType()
        );
    }

    /**
     * @dataProvider isIntegerTypeProvider
     * @param $backendType
     * @param $expected
     * @return void
     */
    public function testIsIntegerType($backendType, $expected)
    {
        $this->attribute
            ->method('getBackendType')
            ->willReturn($backendType);
        $this->assertEquals(
            $expected,
            $this->adapter->isIntegerType()
        );
    }

    /**
     * @dataProvider isBooleanTypeProvider
     * @param $frontendInput
     * @param $backendType
     * @param $expected
     * @return void
     */
    public function testIsBooleanType($frontendInput, $backendType, $expected)
    {
        $this->attribute
            ->method('getBackendType')
            ->willReturn($backendType);
        $this->attribute
            ->method('getFrontendInput')
            ->willReturn($frontendInput);
        $this->assertEquals(
            $expected,
            $this->adapter->isBooleanType()
        );
    }

    /**
     * @dataProvider isComplexTypeProvider
     * @param $frontendInput
     * @param $usesSource
     * @param $expected
     * @return void
     */
    public function testIsComplexType($frontendInput, $usesSource, $expected)
    {
        $this->attribute
            ->method('usesSource')
            ->willReturn($usesSource);
        $this->attribute
            ->method('getFrontendInput')
            ->willReturn($frontendInput);
        $this->assertEquals(
            $expected,
            $this->adapter->isComplexType()
        );
    }

    /**
     * @dataProvider isEavAttributeProvider
     * @param $expected
     * @return void
     */
    public function testIsEavAttribute($expected)
    {
        $this->assertEquals(
            $expected,
            $this->adapter->isEavAttribute()
        );
    }

    /**
     * @return array
     */
    public function isEavAttributeProvider()
    {
        return [
            [false],
        ];
    }

    /**
     * @return array
     */
    public function isComplexTypeProvider()
    {
        return [
            ['select', true, true],
            ['multiselect', true, true],
            ['multiselect', false, true],
            ['int', false, false],
            ['int', true, true],
            ['boolean', true, false],
        ];
    }

    /**
     * @return array
     */
    public function isBooleanTypeProvider()
    {
        return [
            ['select', 'int', true],
            ['boolean', 'int', true],
            ['boolean', 'varchar', false],
            ['select', 'varchar', false],
            ['int', 'varchar', false],
            ['int', 'int', false],
        ];
    }

    /**
     * @return array
     */
    public function isIntegerTypeProvider()
    {
        return [
            ['smallint', true],
            ['int', true],
            ['string', false],
        ];
    }

    /**
     * @return array
     */
    public function isFloatTypeProvider()
    {
        return [
            ['decimal', true],
            ['int', false],
        ];
    }

    /**
     * @return array
     */
    public function isDateTimeTypeProvider()
    {
        return [
            ['timestamp', true],
            ['datetime', true],
            ['int', false],
        ];
    }

    /**
     * @return array
     */
    public function isAlwaysIndexableProvider()
    {
        return [
            [false]
        ];
    }

    /**
     * @return array
     */
    public function isSearchableProvider()
    {
        return [
            [true, false, false, false, true],
            [false, false, false, false, false],
            [false, true, false, false, true],
            [false, false, true, false, true],
            [true, true, true, false, true],
            [true, true, false, false, true],
        ];
    }

    /**
     * @return array
     */
    public function isFilterableProvider()
    {
        return [
            [true, false, true],
            [true, false, true],
            [false, false, false]
        ];
    }

    /**
     * @return array
     */
    public function isStringServiceFieldTypeProvider()
    {
        return [
            ['string', 'text', false],
            ['text', 'text', true]
        ];
    }

    /**
     * @return array
     */
    public function getFieldNameProvider()
    {
        return [
            ['name', [], 'name']
        ];
    }

    /**
     * @return array
     */
    public function getFieldTypeProvider()
    {
        return [
            ['type', 'type']
        ];
    }

    /**
     * @return array
     */
    public function getFieldIndexProvider()
    {
        return [
            ['type', 'no', 'no']
        ];
    }
}
