<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation;

use Magento\Catalog\Model\Product;
use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider\SelectBuilderForAttribute;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Event\Manager;

/**
 * Data Provider for catalog search.
 *
 * @deprecated 101.0.0
 * @see \Magento\ElasticSearch
 */
class DataProvider implements DataProviderInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var SelectBuilderForAttribute
     */
    private $selectBuilderForAttribute;

    /**
     * @var Manager
     */
    private $eventManager;

    /**
     * @param Config $eavConfig
     * @param ResourceConnection $resource
     * @param ScopeResolverInterface $scopeResolver
     * @param null $customerSession @deprecated
     * @param SelectBuilderForAttribute|null $selectBuilderForAttribute
     * @param Manager|null $eventManager
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Config $eavConfig,
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver,
        $customerSession,
        SelectBuilderForAttribute $selectBuilderForAttribute = null,
        Manager $eventManager = null
    ) {
        $this->eavConfig = $eavConfig;
        $this->connection = $resource->getConnection();
        $this->scopeResolver = $scopeResolver;
        $this->selectBuilderForAttribute = $selectBuilderForAttribute
            ?: ObjectManager::getInstance()->get(SelectBuilderForAttribute::class);
        $this->eventManager = $eventManager ?: ObjectManager::getInstance()->get(Manager::class);
    }

    /**
     * @inheritdoc
     */
    public function getDataSet(
        BucketInterface $bucket,
        array $dimensions,
        Table $entityIdsTable
    ) {
        $currentScope = $this->scopeResolver->getScope($dimensions['scope']->getValue())->getId();
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $bucket->getField());
        $select = $this->getSelect();

        $select->joinInner(
            ['entities' => $entityIdsTable->getName()],
            'main_table.entity_id  = entities.entity_id',
            []
        );
        $this->eventManager->dispatch(
            'catalogsearch_query_add_filter_after',
            ['bucket' => $bucket, 'select' => $select]
        );
        $select = $this->selectBuilderForAttribute->build($select, $attribute, $currentScope);

        return $select;
    }

    /**
     * @inheritdoc
     */
    public function execute(Select $select)
    {
        return $this->connection->fetchAssoc($select);
    }

    /**
     * Get select.
     *
     * @return Select
     */
    private function getSelect()
    {
        return $this->connection->select();
    }
}
