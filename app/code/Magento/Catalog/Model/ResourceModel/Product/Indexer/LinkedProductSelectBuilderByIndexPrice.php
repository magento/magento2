<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Indexer;

use Magento\Catalog\Model\Product;
use Magento\Framework\DB\Select;
use Magento\Store\Model\Store;
use Magento\Catalog\Model\ResourceModel\Product\LinkedProductSelectBuilderInterface;

class LinkedProductSelectBuilderByIndexPrice implements LinkedProductSelectBuilderInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->storeManager = $storeManager;
        $this->resource = $resourceConnection;
        $this->customerSession = $customerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function build($productId)
    {
        return [$this->resource->getConnection()->select()
            ->from(['t' => $this->resource->getTableName('catalog_product_index_price')], 'entity_id')
            ->joinInner(
                ['link' => $this->resource->getTableName('catalog_product_relation')],
                'link.child_id = t.entity_id',
                []
            )->where('link.parent_id = ? ', $productId)
            ->where('t.website_id = ?', $this->storeManager->getStore()->getWebsiteId())
            ->where('t.customer_group_id = ?', $this->customerSession->getCustomerGroupId())
            ->order('t.min_price ' . Select::SQL_ASC)
            ->limit(1)];
    }
}
