<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Config;

use Magento\Framework\GraphQl\Schema\Type\Entity\MapperInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class AttributeReaderTest extends TestCase
{
    /** @var AttributeReader  */
    private $attributeReader;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $mapper = $this->createMock(MapperInterface::class);
        $mapper->expects($this->any())
            ->method('getMappedTypes')
            ->willReturn([
                'product' => 'ProductInterface',
                'simple' => 'SimpleProduct',
                'virtual' => 'VirtualProduct',
                'downloadable' => 'DownloadableProduct',
                'bundle' => 'BundleProduct',
                'grouped' => 'GroupedProduct',
                'configurable' => 'ConfigurableProduct',
            ]);
        $this->attributeReader = $objectManager->create(AttributeReader::class, ['mapper' => $mapper]);
    }

    /**
     * @magentoConfigFixture current_store web_api/graphql/include_dynamic_attributes_as_entity_type_fields 1
     */
    public function testReadWithIncludeDynamicAttributesOptionEnabled()
    {
        $result = $this->attributeReader->read();
        $this->assertCount(7, $result);

        //Adding custom attribute dynamically to the schema is deprecated.
        foreach ($result as $typeName) {
            if (!isset($typeName['fields'])) {
                $this->fail('Invalid config');
            }

            array_map(fn ($attribute) => $this->assertArrayHasKey('deprecated', $attribute), $typeName['fields']);
        }
    }

    /**
     * @magentoConfigFixture current_store web_api/graphql/include_dynamic_attributes_as_entity_type_fields 0
     */
    public function testReadWithIncludeDynamicAttributesOptionDisabled()
    {
        $this->assertEmpty($this->attributeReader->read());
    }
}
