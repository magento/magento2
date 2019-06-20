<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Dto;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Dto\DtoProcessor\DtoReflection;
use Magento\Framework\Dto\Mock\ImmutableDto;
use Magento\Framework\Dto\Mock\ImmutableNestedDto;
use Magento\Framework\Dto\Mock\MockDtoConfig;
use Magento\Framework\Dto\Mock\TestSimpleObject;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class DtoReflectionTest extends TestCase
{
    /**
     * @var DtoReflection
     */
    private $dtoReflection;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();

        $objectManager->configure([
            'preferences' => [
                DtoConfig::class => MockDtoConfig::class
            ]
        ]);

        $this->dtoReflection = $objectManager->get(DtoReflection::class);
    }

    public function testIsDataObject(): void
    {
        self::assertTrue($this->dtoReflection->isDataObject(TestSimpleObject::class));
        self::assertFalse($this->dtoReflection->isDataObject(ImmutableDto::class));
    }

    /**
     * @throws ReflectionException
     */
    public function testIsExtensibleObject(): void
    {
        self::assertTrue($this->dtoReflection->isExtensibleObject(ImmutableDto::class));
        self::assertFalse($this->dtoReflection->isExtensibleObject(TestSimpleObject::class));
    }

    public function testIsCustomAttributesObject(): void
    {
        self::assertTrue($this->dtoReflection->isCustomAttributesObject(Product::class));
        self::assertFalse($this->dtoReflection->isCustomAttributesObject(ImmutableDto::class));
    }

    public function testIsDataModel(): void
    {
        self::assertTrue($this->dtoReflection->isDataModel(Product::class));
        self::assertFalse($this->dtoReflection->isDataModel(ImmutableDto::class));
    }

    public function testGetRealClassName(): void
    {
        self::assertSame(
            Product::class,
            $this->dtoReflection->getRealClassName(ProductInterface::class)
        );
    }

    /**
     * @return array
     */
    public function getPropertyTypeFromGetterMethodDataProvider(): array
    {
        return [
            'ImmutableDto::prop1' => [
                'className' => ImmutableDto::class,
                'property' => 'prop1',
                'expected' => 'int'
            ],
            'ImmutableDto::prop2' => [
                'className' => ImmutableDto::class,
                'property' => 'prop2',
                'expected' => 'string'
            ],
            'ImmutableDto::prop3' => [
                'className' => ImmutableDto::class,
                'property' => 'prop3',
                'expected' => 'array'
            ],
            'ImmutableDto::prop4' => [
                'className' => ImmutableDto::class,
                'property' => 'prop4',
                'expected' => 'int[]'
            ],
            'ImmutableNestedDto::testDto1' => [
                'className' => ImmutableNestedDto::class,
                'property' => 'testDto1',
                'expected' => '\\' . ImmutableDto::class
            ],
            'ImmutableNestedDto::testDtoArray' => [
                'className' => ImmutableNestedDto::class,
                'property' => 'testDtoArray',
                'expected' => '\\' . ImmutableDto::class . '[]'
            ]
        ];
    }

    /**
     * @dataProvider getPropertyTypeFromGetterMethodDataProvider
     * @param string $className
     * @param string $property
     * @param string $expected
     * @throws ReflectionException
     */
    public function testGetPropertyTypeFromGetterMethod(string $className, string $property, string $expected): void
    {
        self::assertSame(
            $expected,
            $this->dtoReflection->getPropertyTypeFromGetterMethod($className, $property)
        );
    }
}
