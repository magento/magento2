<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider;

use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider\SelectBuilderForAttribute\
ApplyStockConditionToSelect;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

/**
 * Build select for attribute.
 *
 * @deprecated CatalogSearch will be removed in 2.4, and {@see \Magento\ElasticSearch}
 *             will replace it as the default search engine.
 */
class SelectBuilderForAttribute
{
    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var ApplyStockConditionToSelect
     */
    private $applyStockConditionToSelect;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ResourceConnection $resource
     * @param ScopeResolverInterface $scopeResolver
     * @param ApplyStockConditionToSelect $applyStockConditionToSelect
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver,
        ApplyStockConditionToSelect $applyStockConditionToSelect,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resource = $resource;
        $this->scopeResolver = $scopeResolver;
        $this->applyStockConditionToSelect = $applyStockConditionToSelect;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param Select $select
     * @param AbstractAttribute $attribute
     * @param int $currentScope
     *
     * @return Select
     */
    public function build(Select $select, AbstractAttribute $attribute, int $currentScope): Select
    {
        if ($attribute->getAttributeCode() === 'price') {
            /** @var Store $store */
            $store = $this->scopeResolver->getScope($currentScope);
            if (!$store instanceof Store) {
                throw new \RuntimeException('Illegal scope resolved');
            }
            $table = $this->resource->getTableName('catalog_product_index_price');
            $select->from(['main_table' => $table], null)
                ->columns([BucketInterface::FIELD_VALUE => 'main_table.min_price'])
                ->where('main_table.customer_group_id = ?', $this->customerSession->getCustomerGroupId())
                ->where('main_table.website_id = ?', $store->getWebsiteId());
        } else {
            $currentScopeId = $this->scopeResolver->getScope($currentScope)->getId();
            $table = $this->resource->getTableName(
                'catalog_product_index_eav' . ($attribute->getBackendType() === 'decimal' ? '_decimal' : '')
            );
            $subSelect = $select;
            $subSelect->from(['main_table' => $table], ['main_table.entity_id', 'main_table.value'])
                ->distinct()
                ->where('main_table.attribute_id = ?', $attribute->getAttributeId())
                ->where('main_table.store_id = ? ', $currentScopeId);
            if ($this->isAddStockFilter()) {
                $subSelect = $this->applyStockConditionToSelect->execute($subSelect);
            }

            $parentSelect = $this->resource->getConnection()->select();
            $parentSelect->from(['main_table' => $subSelect], ['main_table.value']);
            $select = $parentSelect;
        }

        return $select;
    }

    /**
     * @return bool
     */
    private function isAddStockFilter()
    {
        $isShowOutOfStock = $this->scopeConfig->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            ScopeInterface::SCOPE_STORE
        );

        return false === $isShowOutOfStock;
    }
}
