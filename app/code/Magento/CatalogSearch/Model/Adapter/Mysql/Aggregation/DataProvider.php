<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation;

use Magento\Catalog\Model\Product;
use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider\SelectBuilderForAttribute;
use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider\SelectBuilderForAttributeTypePrice;
use Magento\Customer\Model\Session;
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
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider implements DataProviderInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var Resource
     * @deprecated
     */
    private $resource;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var Session
     * @deprecated
     */
    private $customerSession;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var SelectBuilderForAttributeTypePrice
     */
    private $selectBuilderForAttributeTypePrice;

    /**
     * @var SelectBuilderForAttribute
     */
    private $selectBuilderForAttribute;

    /**
     * @param Config $eavConfig
     * @param ResourceConnection $resource
     * @param ScopeResolverInterface $scopeResolver
     * @param Session $customerSession
     * @param SelectBuilderForAttributeTypePrice|null $selectBuilderForAttributeTypePrice
     * @param SelectBuilderForAttribute|null $selectBuilderForAttribute
     */
    public function __construct(
        Config $eavConfig,
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver,
        Session $customerSession,
        SelectBuilderForAttributeTypePrice $selectBuilderForAttributeTypePrice = null,
        SelectBuilderForAttribute $selectBuilderForAttribute = null
    ) {
        $this->eavConfig = $eavConfig;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->scopeResolver = $scopeResolver;
        $this->customerSession = $customerSession;
        $this->selectBuilderForAttributeTypePrice = $selectBuilderForAttributeTypePrice
            ?: ObjectManager::getInstance()->get(SelectBuilderForAttributeTypePrice::class);
        $this->selectBuilderForAttribute = $selectBuilderForAttribute
            ?: ObjectManager::getInstance()->get(SelectBuilderForAttribute::class);
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
        $select = $this->getSelect();

        $select->joinInner(
            ['entities' => $entityIdsTable->getName()],
            'main_table.entity_id  = entities.entity_id',
            []
        );

        if ($attribute->getAttributeCode() === 'price') {
            $this->selectBuilderForAttributeTypePrice->execute($select, $currentScope);
        } else {
            $select = $this->selectBuilderForAttribute->execute($select, $attribute, $currentScope);
        }

        return $select;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Select $select)
    {
        return $this->connection->fetchAssoc($select);
    }

    /**
     * @return Select
     */
    private function getSelect()
    {
        return $this->connection->select();
    }
}
