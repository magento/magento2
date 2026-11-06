<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Config;

use ArrayIterator;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as AttributeCollection;
use Magento\CatalogGraphQl\Model\Config\AttributeReader;
use Magento\CatalogGraphQl\Model\Resolver\Product\ModelAttributeValue;
use Magento\CatalogGraphQl\Model\Resolver\Products\Attributes\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Products\Attributes\CollectionFactory;
use Magento\EavGraphQl\Model\Resolver\Query\Type;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Schema\Type\Entity\MapperInterface;
use PHPUnit\Framework\TestCase;

class AttributeReaderTest extends TestCase
{
    public function testModelAttributeFieldGetsDedicatedResolver(): void
    {
        $config = $this->createReader(['model', 'manufacturer'])->read();

        foreach (['SimpleProduct', 'ConfigurableProduct'] as $typeName) {
            $fields = $config[$typeName]['fields'];
            self::assertSame(ModelAttributeValue::class, $fields['model']['resolver'] ?? null);
            self::assertSame('String', $fields['model']['type']);
            self::assertArrayNotHasKey('resolver', $fields['manufacturer']);
        }
    }

    /**
     * @param string[] $attributeCodes
     * @return AttributeReader
     */
    private function createReader(array $attributeCodes): AttributeReader
    {
        $mapper = $this->createStub(MapperInterface::class);
        $mapper->method('getMappedTypes')
            ->willReturn(['simple_product' => 'SimpleProduct', 'configurable_product' => 'ConfigurableProduct']);

        $typeLocator = $this->createStub(Type::class);
        $typeLocator->method('getType')->willReturn('string');

        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('isSetFlag')
            ->willReturn(true);

        $attributes = [];
        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->createStub(Attribute::class);
            $attribute->method('getAttributeCode')->willReturn($attributeCode);
            $attributes[] = $attribute;
        }
        $attributeCollection = $this->createStub(AttributeCollection::class);
        $attributeCollection->method('getIterator')->willReturn(new ArrayIterator($attributes));

        $collection = $this->createStub(Collection::class);
        $collection->method('getAttributes')->willReturn($attributeCollection);
        $collectionFactory = $this->createStub(CollectionFactory::class);
        $collectionFactory->method('create')->willReturn($collection);

        return new AttributeReader($mapper, $typeLocator, $collection, $scopeConfig, $collectionFactory);
    }
}
