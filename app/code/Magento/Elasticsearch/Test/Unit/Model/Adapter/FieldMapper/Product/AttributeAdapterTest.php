<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper\Product;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @SuppressWarnings(PHPMD)
 */
class AttributeAdapterTest extends TestCase
{
    use MockCreationTrait;
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
        $this->attribute = $this->createPartialMockWithReflection(
            CustomAttributesDataInterface::class,
            ['getIsFilterable', 'setIsFilterable', 'getIsFilterableInSearch', 'setIsFilterableInSearch',
             'getIsSearchable', 'setIsSearchable', 'getIsVisibleInAdvancedSearch', 'setIsVisibleInAdvancedSearch',
             'getBackendType', 'setBackendType', 'getFrontendInput', 'setFrontendInput',
             'usesSource', 'setUsesSource', 'getCustomAttributes', 'setCustomAttributes',
             'getCustomAttribute', 'setCustomAttribute']
        );

        // Create stateful mock data storage
        $data = [
            'isFilterable' => false,
            'isFilterableInSearch' => false,
            'isSearchable' => false,
            'isVisibleInAdvancedSearch' => false,
            'backendType' => 'varchar',
            'frontendInput' => 'text',
            'usesSource' => false
        ];

        $this->attribute->method('getIsFilterable')->willReturnCallback(function () use (&$data) {
            return $data['isFilterable'];
        });
        $this->attribute->method('setIsFilterable')->willReturnCallback(function ($value) use (&$data) {
            $data['isFilterable'] = $value;
            return $this->attribute;
        });
        $this->attribute->method('getIsFilterableInSearch')->willReturnCallback(function () use (&$data) {
            return $data['isFilterableInSearch'];
        });
        $this->attribute->method('setIsFilterableInSearch')->willReturnCallback(function ($value) use (&$data) {
            $data['isFilterableInSearch'] = $value;
            return $this->attribute;
        });
        $this->attribute->method('getIsSearchable')->willReturnCallback(function () use (&$data) {
            return $data['isSearchable'];
        });
        $this->attribute->method('setIsSearchable')->willReturnCallback(function ($value) use (&$data) {
            $data['isSearchable'] = $value;
            return $this->attribute;
        });
        $this->attribute->method('getIsVisibleInAdvancedSearch')->willReturnCallback(function () use (&$data) {
            return $data['isVisibleInAdvancedSearch'];
        });
        $this->attribute->method('setIsVisibleInAdvancedSearch')->willReturnCallback(function ($value) use (&$data) {
            $data['isVisibleInAdvancedSearch'] = $value;
            return $this->attribute;
        });
        $this->attribute->method('getBackendType')->willReturnCallback(function () use (&$data) {
            return $data['backendType'];
        });
        $this->attribute->method('setBackendType')->willReturnCallback(function ($value) use (&$data) {
            $data['backendType'] = $value;
            return $this->attribute;
        });
        $this->attribute->method('getFrontendInput')->willReturnCallback(function () use (&$data) {
            return $data['frontendInput'];
        });
        $this->attribute->method('setFrontendInput')->willReturnCallback(function ($value) use (&$data) {
            $data['frontendInput'] = $value;
            return $this->attribute;
        });
        $this->attribute->method('usesSource')->willReturnCallback(function () use (&$data) {
            return $data['usesSource'];
        });
        $this->attribute->method('setUsesSource')->willReturnCallback(function ($value) use (&$data) {
            $data['usesSource'] = $value;
            return $this->attribute;
        });
        $this->attribute->method('getCustomAttributes')->willReturn([]);

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
     * @param $isFilterable
     * @param $isFilterableInSearch
     * @param $expected
     * @return void
     */
    #[DataProvider('isFilterableProvider')]
    public function testIsFilterable($isFilterable, $isFilterableInSearch, $expected)
    {
        $this->attribute->setIsFilterable($isFilterable);
        $this->attribute->setIsFilterableInSearch($isFilterableInSearch);
        $this->assertEquals(
            $expected,
            $this->adapter->isFilterable()
        );
    }

    /**
     * @param $isSearchable
     * @param $isVisibleInAdvancedSearch
     * @param $isFilterable
     * @param $isFilterableInSearch
     * @param $expected
     * @return void
     */
    #[DataProvider('isSearchableProvider')]
    public function testIsSearchable(
        $isSearchable,
        $isVisibleInAdvancedSearch,
        $isFilterable,
        $isFilterableInSearch,
        $expected
    ) {
        $this->attribute->setIsSearchable($isSearchable);
        $this->attribute->setIsVisibleInAdvancedSearch($isVisibleInAdvancedSearch);
        $this->attribute->setIsFilterable($isFilterable);
        $this->attribute->setIsFilterableInSearch($isFilterableInSearch);
        $this->assertEquals(
            $expected,
            $this->adapter->isSearchable()
        );
    }

    /**
     * @param $expected
     * @return void
     */
    #[DataProvider('isAlwaysIndexableProvider')]
    public function testIsAlwaysIndexable($expected)
    {
        $this->assertEquals(
            $expected,
            $this->adapter->isAlwaysIndexable()
        );
    }

    /**
     * @param $backendType
     * @param $expected
     * @return void
     */
    #[DataProvider('isDateTimeTypeProvider')]
    public function testIsDateTimeType($backendType, $expected)
    {
        $this->attribute->setBackendType($backendType);
        $this->assertEquals(
            $expected,
            $this->adapter->isDateTimeType()
        );
    }

    /**
     * @param $backendType
     * @param $expected
     * @return void
     */
    #[DataProvider('isFloatTypeProvider')]
    public function testIsFloatType($backendType, $expected)
    {
        $this->attribute->setBackendType($backendType);
        $this->assertEquals(
            $expected,
            $this->adapter->isFloatType()
        );
    }

    /**
     * @param $backendType
     * @param $expected
     * @return void
     */
    #[DataProvider('isIntegerTypeProvider')]
    public function testIsIntegerType($backendType, $expected)
    {
        $this->attribute->setBackendType($backendType);
        $this->assertEquals(
            $expected,
            $this->adapter->isIntegerType()
        );
    }

    /**
     * @param $frontendInput
     * @param $backendType
     * @param $expected
     * @return void
     */
    #[DataProvider('isBooleanTypeProvider')]
    public function testIsBooleanType($frontendInput, $backendType, $expected)
    {
        $this->attribute->setBackendType($backendType);
        $this->attribute->setFrontendInput($frontendInput);
        $this->assertEquals(
            $expected,
            $this->adapter->isBooleanType()
        );
    }

    /**
     * @param $frontendInput
     * @param $usesSource
     * @param $expected
     * @return void
     */
    #[DataProvider('isComplexTypeProvider')]
    public function testIsComplexType($frontendInput, $usesSource, $expected)
    {
        $this->attribute->setUsesSource($usesSource);
        $this->attribute->setFrontendInput($frontendInput);
        $this->assertEquals(
            $expected,
            $this->adapter->isComplexType()
        );
    }

    /**
     * @param $expected
     * @return void
     */
    #[DataProvider('isEavAttributeProvider')]
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
    public static function isEavAttributeProvider()
    {
        return [
            [false],
        ];
    }

    /**
     * @return array
     */
    public static function isComplexTypeProvider()
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
    public static function isBooleanTypeProvider()
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
    public static function isIntegerTypeProvider()
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
    public static function isFloatTypeProvider()
    {
        return [
            ['decimal', true],
            ['int', false],
        ];
    }

    /**
     * @return array
     */
    public static function isDateTimeTypeProvider()
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
    public static function isAlwaysIndexableProvider()
    {
        return [
            [false]
        ];
    }

    /**
     * @return array
     */
    public static function isSearchableProvider()
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
    public static function isFilterableProvider()
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
    public static function isStringServiceFieldTypeProvider()
    {
        return [
            ['string', 'text', false],
            ['text', 'text', true]
        ];
    }

    /**
     * @return array
     */
    public static function getFieldNameProvider()
    {
        return [
            ['name', [], 'name']
        ];
    }

    /**
     * @return array
     */
    public static function getFieldTypeProvider()
    {
        return [
            ['type', 'type']
        ];
    }

    /**
     * @return array
     */
    public static function getFieldIndexProvider()
    {
        return [
            ['type', 'no', 'no']
        ];
    }
}
