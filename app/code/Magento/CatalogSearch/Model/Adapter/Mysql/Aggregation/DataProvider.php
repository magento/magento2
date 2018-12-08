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
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
use Magento\Eav\Model\Config;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface;

/**
<<<<<<< HEAD
 * DataProvider for Catalog search Mysql.
=======
 * @deprecated
 * @see \Magento\ElasticSearch
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
        SelectBuilderForAttribute $selectBuilderForAttribute = null
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    }

    /**
     * {@inheritdoc}
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
        $select = $this->selectBuilderForAttribute->build($select, $attribute, $currentScope);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

        return $select;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Select $select)
    {
        return $this->connection->fetchAssoc($select);
    }
}
