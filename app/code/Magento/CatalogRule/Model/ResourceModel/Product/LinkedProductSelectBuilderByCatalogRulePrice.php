<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\DB\Select;
use Magento\Catalog\Model\ResourceModel\Product\LinkedProductSelectBuilderInterface;

class LinkedProductSelectBuilderByCatalogRulePrice implements LinkedProductSelectBuilderInterface
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
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool
    ) {
        $this->storeManager = $storeManager;
        $this->resource = $resourceConnection;
        $this->customerSession = $customerSession;
        $this->dateTime = $dateTime;
        $this->localeDate = $localeDate;
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     */
    public function build($productId)
    {
        $timestamp = $this->localeDate->scopeTimeStamp($this->storeManager->getStore());
        $currentDate = $this->dateTime->formatDate($timestamp, false);
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $productTable = $this->resource->getTableName('catalog_product_entity');

        return [$this->resource->getConnection()->select()
                ->from(['parent' => $productTable], '')
                ->joinInner(
                    ['link' => $this->resource->getTableName('catalog_product_relation')],
                    "link.parent_id = parent.$linkField",
                    []
                )->joinInner(
                    ['child' => $productTable],
                    "child.entity_id = link.child_id",
                    ['entity_id']
                )->joinInner(
                    ['t' => $this->resource->getTableName('catalogrule_product_price')],
                    't.product_id = child.entity_id',
                    []
                )->where('parent.entity_id = ? ', $productId)
            ->where('t.website_id = ?', $this->storeManager->getStore()->getWebsiteId())
            ->where('t.customer_group_id = ?', $this->customerSession->getCustomerGroupId())
            ->where('t.rule_date = ?', $currentDate)
            ->order('t.rule_price ' . Select::SQL_ASC)
            ->limit(1)];
    }
}
