<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation;

use Magento\Catalog\Model\Product;
<<<<<<< HEAD
use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider\QueryBuilder;
use Magento\Customer\Model\Session;
=======
use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider\SelectBuilderForAttribute;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
 * DataProvider for Catalog search Mysql.
=======
 * Data Provider for catalog search.
 *
 * @deprecated
 * @see \Magento\ElasticSearch
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
     * @var QueryBuilder;
     */
    private $queryBuilder;

    /**
     * @param Config $eavConfig
     * @param ResourceConnection $resource
     * @param ScopeResolverInterface $scopeResolver
<<<<<<< HEAD
     * @param Session $customerSession
     * @param QueryBuilder|null $queryBuilder
=======
     * @param null $customerSession @deprecated
     * @param SelectBuilderForAttribute|null $selectBuilderForAttribute
     * @param Manager|null $eventManager
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    public function __construct(
        Config $eavConfig,
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver,
<<<<<<< HEAD
        Session $customerSession,
        QueryBuilder $queryBuilder = null
=======
        $customerSession,
        SelectBuilderForAttribute $selectBuilderForAttribute = null,
        Manager $eventManager = null
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    ) {
        $this->eavConfig = $eavConfig;
        $this->connection = $resource->getConnection();
        $this->scopeResolver = $scopeResolver;
<<<<<<< HEAD
        $this->customerSession = $customerSession;
        $this->queryBuilder = $queryBuilder ?: ObjectManager::getInstance()->get(QueryBuilder::class);
=======
        $this->selectBuilderForAttribute = $selectBuilderForAttribute
            ?: ObjectManager::getInstance()->get(SelectBuilderForAttribute::class);
        $this->eventManager = $eventManager ?: ObjectManager::getInstance()->get(Manager::class);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD

        $select = $this->queryBuilder->build(
            $attribute,
            $entityIdsTable->getName(),
            $currentScope,
            $this->customerSession->getCustomerGroupId()
        );
=======
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

        return $select;
    }

    /**
     * @inheritdoc
     */
    public function execute(Select $select)
    {
        return $this->connection->fetchAssoc($select);
    }
<<<<<<< HEAD
=======

    /**
     * Get select.
     *
     * @return Select
     */
    private function getSelect()
    {
        return $this->connection->select();
    }
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
}
