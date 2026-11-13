<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\TypeLocator;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\TypeLocator\SimpleType;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Webapi\CustomAttribute\ServiceTypeListInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class SimpleTypeTest extends TestCase
{
    /**
     * @var AttributeRepositoryInterface&Stub
     */
    private $attributeRepository;

    /**
     * @var SimpleType
     */
    private $simpleType;

    protected function setUp(): void
    {
        $this->attributeRepository = $this->createStub(AttributeRepositoryInterface::class);
        $this->simpleType = new SimpleType(
            $this->attributeRepository,
            $this->createStub(ServiceTypeListInterface::class)
        );
    }

    /**
     * @return array<string, array{string}>
     */
    public static function multiselectBackendTypeDataProvider(): array
    {
        return [
            'varchar' => ['varchar'],
            'text' => ['text'],
        ];
    }

    #[DataProvider('multiselectBackendTypeDataProvider')]
    public function testGetTypeReturnsAnyTypeForMultiselect(string $backendType): void
    {
        $attribute = $this->createStub(AttributeInterface::class);
        $attribute->method('getFrontendInput')->willReturn('multiselect');
        $attribute->method('getBackendModel')->willReturn(ArrayBackend::class);
        $attribute->method('getBackendType')->willReturn($backendType);
        $this->attributeRepository->method('get')
            ->willReturnMap([['catalog_product', 'color_multi', $attribute]]);

        $this->assertSame(
            TypeProcessor::NORMALIZED_ANY_TYPE,
            $this->simpleType->getType('color_multi', 'catalog_product')
        );
    }

    public function testGetTypeReturnsStringForMultiselectWithoutArrayBackend(): void
    {
        $attribute = $this->createStub(AttributeInterface::class);
        $attribute->method('getFrontendInput')->willReturn('multiselect');
        $attribute->method('getBackendModel')->willReturn(null);
        $attribute->method('getBackendType')->willReturn('text');
        $this->attributeRepository->method('get')->willReturn($attribute);

        $this->assertSame(
            TypeProcessor::NORMALIZED_STRING_TYPE,
            $this->simpleType->getType('color_multi', 'catalog_product')
        );
    }

    public function testGetTypeReturnsIntForSelectIntAttribute(): void
    {
        $attribute = $this->createStub(AttributeInterface::class);
        $attribute->method('getFrontendInput')->willReturn('select');
        $attribute->method('getBackendType')->willReturn('int');
        $this->attributeRepository->method('get')->willReturn($attribute);

        $this->assertSame(
            TypeProcessor::NORMALIZED_INT_TYPE,
            $this->simpleType->getType('color', 'catalog_product')
        );
    }

    public function testGetTypeReturnsStringForVarcharAttribute(): void
    {
        $attribute = $this->createStub(AttributeInterface::class);
        $attribute->method('getFrontendInput')->willReturn('text');
        $attribute->method('getBackendType')->willReturn('varchar');
        $this->attributeRepository->method('get')->willReturn($attribute);

        $this->assertSame(
            TypeProcessor::NORMALIZED_STRING_TYPE,
            $this->simpleType->getType('description', 'catalog_product')
        );
    }

    public function testGetTypeReturnsAnyTypeForUnknownAttribute(): void
    {
        $this->attributeRepository->method('get')->willThrowException(new NoSuchEntityException());

        $this->assertSame(
            TypeProcessor::NORMALIZED_ANY_TYPE,
            $this->simpleType->getType('unknown', 'catalog_product')
        );
    }
}
