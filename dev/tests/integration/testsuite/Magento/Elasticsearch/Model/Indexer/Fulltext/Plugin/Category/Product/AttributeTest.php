<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Indexer\Fulltext\Plugin\Category\Product;

use Magento\AdvancedSearch\Model\Client\ClientInterface;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Processor;
use Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Check Elasticsearch indexer mapping when working with attributes.
 */
class AttributeTest extends TestCase
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var IndexNameResolver
     */
    private $indexNameResolver;

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
        parent::setUp();

        $connectionManager = Bootstrap::getObjectManager()->get(ConnectionManager::class);
        $this->client = $connectionManager->getConnection();
        $this->arrayManager = Bootstrap::getObjectManager()->get(ArrayManager::class);
        $this->indexNameResolver = Bootstrap::getObjectManager()->get(IndexNameResolver::class);
        $this->indexerProcessor = Bootstrap::getObjectManager()->get(Processor::class);
        $this->attribute = Bootstrap::getObjectManager()->get(Attribute::class);
    }

    /**
     * Check Elasticsearch indexer mapping is updated after changing non searchable attribute to searchable.
     *
     * @return void
     * @magentoConfigFixture default/catalog/search/engine elasticsearch7
     * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
     * @magentoDataFixture Magento/Catalog/_files/dropdown_attribute.php
     */
    public function testCheckElasticsearchMappingAfterUpdateAttributeToSearchable(): void
    {
        $mappedAttributesBefore = $this->getMappingProperties();

        $this->attribute->loadByCode(Product::ENTITY, 'dropdown_attribute');
        $this->attribute->setData(EavAttributeInterface::IS_SEARCHABLE, true)->save();
        $this->assertTrue($this->indexerProcessor->getIndexer()->isInvalid());

        $mappedAttributesAfter = $this->getMappingProperties();
        $expectedResult = [
            'dropdown_attribute' => [
                'type' => 'keyword',
                'copy_to' => ['_search'],
            ],
            'dropdown_attribute_value' => [
                'type' => 'text',
                'copy_to' => ['_search'],
            ],
        ];

        $this->assertEquals($expectedResult, array_diff_key($mappedAttributesAfter, $mappedAttributesBefore));
    }

    /**
     * Retrieve Elasticsearch indexer mapping.
     *
     * @return array
     */
    private function getMappingProperties(): array
    {
        $mappedIndexerId = $this->indexNameResolver->getIndexMapping(Processor::INDEXER_ID);
        $indexName = $this->indexNameResolver->getIndexFromAlias(1, $mappedIndexerId);
        $mappedAttributes = $this->client->getMapping(['index' => $indexName]);
        $pathField = $this->arrayManager->findPath('properties', $mappedAttributes);

        return $this->arrayManager->get($pathField, $mappedAttributes, []);
    }
}
