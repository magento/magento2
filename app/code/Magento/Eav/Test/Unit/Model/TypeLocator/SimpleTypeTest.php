<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\TypeLocator;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\TypeLocator\SimpleType;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Webapi\CustomAttribute\ServiceTypeListInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SimpleTypeTest extends TestCase
{
    /**
     * @var AttributeRepositoryInterface|MockObject
     */
    private $attributeRepository;

    /**
     * @var SimpleType
     */
    private $simpleType;

    protected function setUp(): void
    {
        $this->attributeRepository = $this->createMock(AttributeRepositoryInterface::class);
        $this->simpleType = new SimpleType(
            $this->attributeRepository,
            $this->createMock(ServiceTypeListInterface::class)
        );
    }

    public function testGetTypeReturnsAnyTypeForMultiselect(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);
        $attribute->method('getFrontendInput')->willReturn('multiselect');
        $this->attributeRepository->method('get')
            ->with('catalog_product', 'color_multi')
            ->willReturn($attribute);

        $this->assertSame(
            TypeProcessor::NORMALIZED_ANY_TYPE,
            $this->simpleType->getType('color_multi', 'catalog_product')
        );
    }

    public function testGetTypeReturnsIntForSelectIntAttribute(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);
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
        $attribute = $this->createMock(AttributeInterface::class);
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
