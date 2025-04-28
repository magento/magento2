<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Processor;
use Magento\Catalog\Api\Data\EavAttributeInterface;

/**
 * Check catalogsearch_fulltext index status after create product attribute.
 */
class AttributeTest extends TestCase
{
    /**
     * @var Processor
     */
    private $indexerProcessor;

    /**
     * @var Attribute
     */
    private $attribute;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->indexerProcessor = Bootstrap::getObjectManager()->create(Processor::class);
        $this->attribute = Bootstrap::getObjectManager()->create(Attribute::class);
    }

    /**
     * Check index status on clean database.
     *
     * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
     */
    public function testCheckIndexStatusOnCleanDatabase(): void
    {
        $this->assertTrue($this->indexerProcessor->getIndexer()->isValid());
    }

    /**
     * Check index status after create non searchable attribute.
     *
     * @return void
     * @magentoDataFixture Magento/Catalog/_files/dropdown_attribute.php
     * @magentoDbIsolation enabled
     * @depends testCheckIndexStatusOnCleanDatabase
     */
    public function testCheckIndexStatusAfterCreateNonSearchableAttribute(): void
    {
        $this->assertTrue($this->indexerProcessor->getIndexer()->isValid());
    }

    /**
     * Check index status after non searchable attribute update.
     *
     * @return void
     * @magentoDataFixture Magento/Catalog/_files/dropdown_attribute.php
     * @magentoDbIsolation enabled
     * @depends testCheckIndexStatusOnCleanDatabase
     */
    public function testCheckIndexStatusAfterNonSearchableAttributeUpdate(): void
    {
        $this->attribute->load('dropdown_attribute', 'attribute_code');
        $this->assertFalse($this->attribute->isObjectNew());
        $this->attribute->setIsGlobal(1)->save();
        $this->assertTrue($this->indexerProcessor->getIndexer()->isValid());
    }

    /**
     * Check index status after create searchable attribute.
     *
     * @return void
     * @magentoDataFixture Magento/CatalogSearch/_files/search_attributes.php
     * @magentoDbIsolation enabled
     * @depends testCheckIndexStatusOnCleanDatabase
     */
    public function testCheckIndexStatusAfterCreateSearchableAttribute(): void
    {
        $this->assertTrue($this->indexerProcessor->getIndexer()->isInvalid());
    }

    /**
     * Check index status after update non searchable attribute to searchable.
     *
     * @param string $field
     * @return void
     * @dataProvider searchableAttributesDataProvider
     * @magentoDataFixture Magento/Catalog/_files/dropdown_attribute.php
     * @magentoDbIsolation enabled
     */
    public function testCheckIndexStatusAfterUpdateNonSearchableAttributeToSearchable(string $field): void
    {
        $this->indexerProcessor->reindexAll();
        $this->assertTrue($this->indexerProcessor->getIndexer()->isValid());
        $this->attribute->loadByCode(Product::ENTITY, 'dropdown_attribute');
        $this->assertFalse($this->attribute->isObjectNew());
        $this->attribute->setData($field, true)->save();
        $this->assertFalse($this->indexerProcessor->getIndexer()->isValid());
    }

    /**
     * @return array
     */
    public static function searchableAttributesDataProvider(): array
    {
        return [
            [EavAttributeInterface::IS_SEARCHABLE],
            [EavAttributeInterface::IS_FILTERABLE],
            [EavAttributeInterface::IS_VISIBLE_IN_ADVANCED_SEARCH]
        ];
    }

    /**
     * Check index status after update searchable attribute to non searchable.
     *
     * @return void
     * @magentoDataFixture Magento/CatalogSearch/_files/search_attributes.php
     * @magentoDbIsolation enabled
     * @depends testCheckIndexStatusOnCleanDatabase
     */
    public function testCheckIndexStatusAfterUpdateSearchableAttributeToNonSearchable(): void
    {
        $this->indexerProcessor->reindexAll();
        $this->assertTrue($this->indexerProcessor->getIndexer()->isValid());
        $this->attribute->loadByCode(Product::ENTITY, 'test_catalog_view');
        $this->assertFalse($this->attribute->isObjectNew());
        $this->attribute->setIsFilterable(false)->save();
        $this->assertFalse($this->indexerProcessor->getIndexer()->isValid());
    }
}
