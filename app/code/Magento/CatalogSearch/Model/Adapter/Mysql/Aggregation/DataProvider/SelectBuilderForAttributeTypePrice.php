<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Store\Model\Store;

/**
 * Build select for attribute "price".
 */
class SelectBuilderForAttributeTypePrice
{
    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     * @param ScopeResolverInterface $scopeResolver
     * @param Session $customerSession
     */
    public function __construct(
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver,
        Session $customerSession
    ) {
        $this->resource = $resource;
        $this->scopeResolver = $scopeResolver;
        $this->customerSession = $customerSession;
    }

    /**
     * @param Select $select
     * @param int $currentScope
     */
    public function execute(Select $select, int $currentScope)
    {
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
    }
}
