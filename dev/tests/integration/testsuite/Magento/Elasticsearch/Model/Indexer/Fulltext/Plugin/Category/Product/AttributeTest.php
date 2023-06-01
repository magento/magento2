<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Indexer\Fulltext\Plugin\Category\Product;

use Magento\AdvancedSearch\Model\Client\ClientInterface;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Catalog\Setup\CategorySetup;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Processor;
use Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Check Search engine indexer mapping when working with attributes.
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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CategorySetup
     */
    private $installer;

    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

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
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->installer = Bootstrap::getObjectManager()->get(CategorySetup::class);
        $this->attributeFactory = Bootstrap::getObjectManager()->get(AttributeFactory::class);
        $this->attributeRepository = Bootstrap::getObjectManager()->get(ProductAttributeRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        /** @var ProductAttributeInterface $attribute */
        $attribute = $this->attributeRepository->get('dropdown_attribute');
        $this->attributeRepository->delete($attribute);
    }

    /**
     * Check Search engine indexer mapping is updated after creating attribute.
     *
     * @return void
     * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
     */
    public function testCheckElasticsearchMappingAfterUpdateAttributeToSearchable(): void
    {
        $mappedAttributesBefore = $this->getMappingProperties();
        $expectedResult = [
            'dropdown_attribute' => [
                'type' => 'integer',
                'index' => false,
            ],
            'dropdown_attribute_value' => [
                'type' => 'text',
                'copy_to' => ['_search'],
            ],
        ];

        /** @var ProductAttributeInterface $dropDownAttribute */
        $dropDownAttribute = $this->attributeFactory->create();
        $dropDownAttribute->setData($this->getAttributeData());
        $this->attributeRepository->save($dropDownAttribute);
        $this->assertTrue($this->indexerProcessor->getIndexer()->isValid());

        $mappedAttributesAfter = $this->getMappingProperties();
        $this->assertEquals($expectedResult, array_diff_key($mappedAttributesAfter, $mappedAttributesBefore));

        $dropDownAttribute->setData(EavAttributeInterface::IS_SEARCHABLE, true);
        $this->attributeRepository->save($dropDownAttribute);
        $this->assertTrue($this->indexerProcessor->getIndexer()->isInvalid());

        $this->assertEquals($mappedAttributesAfter, $this->getMappingProperties());
    }

    /**
     * Retrieve Search engine indexer mapping.
     *
     * @return array
     */
    private function getMappingProperties(): array
    {
        $storeId = $this->storeManager->getStore()->getId();
        $mappedIndexerId = $this->indexNameResolver->getIndexMapping(Processor::INDEXER_ID);
        $indexName = $this->indexNameResolver->getIndexFromAlias($storeId, $mappedIndexerId);
        $mappedAttributes = $this->client->getMapping(['index' => $indexName]);
        $pathField = $this->arrayManager->findPath('properties', $mappedAttributes);

        return $this->arrayManager->get($pathField, $mappedAttributes, []);
    }

    /**
     * Retrieve drop-down attribute data.
     *
     * @return array
     */
    private function getAttributeData(): array
    {
        $entityTypeId = $this->installer->getEntityTypeId(ProductAttributeInterface::ENTITY_TYPE_CODE);

        return [
            'attribute_code'                => 'dropdown_attribute',
            'entity_type_id'                => $entityTypeId,
            'is_global'                     => 0,
            'is_user_defined'               => 1,
            'frontend_input'                => 'select',
            'is_unique'                     => 0,
            'is_required'                   => 0,
            'is_searchable'                 => 0,
            'is_visible_in_advanced_search' => 0,
            'is_comparable'                 => 0,
            'is_filterable'                 => 0,
            'is_filterable_in_search'       => 0,
            'is_used_for_promo_rules'       => 0,
            'is_html_allowed_on_front'      => 1,
            'is_visible_on_front'           => 1,
            'used_in_product_listing'       => 1,
            'used_for_sort_by'              => 0,
            'frontend_label'                => ['Drop-Down Attribute'],
            'backend_type'                  => 'varchar',
            'option'                        => [
                'value' => [
                    'option_1' => ['Option 1'],
                    'option_2' => ['Option 2'],
                    'option_3' => ['Option 3'],
                ],
                'order' => [
                    'option_1' => 1,
                    'option_2' => 2,
                    'option_3' => 3,
                ],
            ],
        ];
    }
}
