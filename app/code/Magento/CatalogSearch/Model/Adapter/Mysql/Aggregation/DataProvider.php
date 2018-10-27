<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation;

use Magento\Catalog\Model\Product;
<<<<<<< HEAD
use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider\SelectBuilderForAttribute;
=======
use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider\QueryBuilder;
use Magento\Customer\Model\Session;
>>>>>>> upstream/2.2-develop
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
 * @deprecated
 * @see \Magento\ElasticSearch
=======
 * DataProvider for Catalog search Mysql.
>>>>>>> upstream/2.2-develop
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
     * @param null $customerSession @deprecated
     * @param SelectBuilderForAttribute|null $selectBuilderForAttribute
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
=======
     * @param Session $customerSession
     * @param QueryBuilder|null $queryBuilder
>>>>>>> upstream/2.2-develop
     */
    public function __construct(
        Config $eavConfig,
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver,
<<<<<<< HEAD
        $customerSession,
        SelectBuilderForAttribute $selectBuilderForAttribute = null
=======
        Session $customerSession,
        QueryBuilder $queryBuilder = null
>>>>>>> upstream/2.2-develop
    ) {
        $this->eavConfig = $eavConfig;
        $this->connection = $resource->getConnection();
        $this->scopeResolver = $scopeResolver;
<<<<<<< HEAD
        $this->selectBuilderForAttribute = $selectBuilderForAttribute
            ?: ObjectManager::getInstance()->get(SelectBuilderForAttribute::class);
=======
        $this->customerSession = $customerSession;
        $this->queryBuilder = $queryBuilder ?: ObjectManager::getInstance()->get(QueryBuilder::class);
>>>>>>> upstream/2.2-develop
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
        $select = $this->getSelect();

        $select->joinInner(
            ['entities' => $entityIdsTable->getName()],
            'main_table.entity_id  = entities.entity_id',
            []
        );
        $select = $this->selectBuilderForAttribute->build($select, $attribute, $currentScope);
=======

        $select = $this->queryBuilder->build(
            $attribute,
            $entityIdsTable->getName(),
            $currentScope,
            $this->customerSession->getCustomerGroupId()
        );
>>>>>>> upstream/2.2-develop

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
