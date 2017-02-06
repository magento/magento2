<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;

class LinkedProductSelectBuilderByTierPrice implements LinkedProductSelectBuilderInterface
{
    /**
     * Default website id
     */
    const DEFAULT_WEBSITE_ID = 0;

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
     * @var \Magento\Catalog\Helper\Data
     */
    private $catalogHelper;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var BaseSelectProcessorInterface
     */
    private $baseSelectProcessor;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Helper\Data $catalogHelper
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param BaseSelectProcessorInterface $baseSelectProcessor
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Helper\Data $catalogHelper,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        BaseSelectProcessorInterface $baseSelectProcessor = null
    ) {
        $this->storeManager = $storeManager;
        $this->resource = $resourceConnection;
        $this->customerSession = $customerSession;
        $this->catalogHelper = $catalogHelper;
        $this->metadataPool = $metadataPool;
        $this->baseSelectProcessor = (null !== $baseSelectProcessor)
            ? $baseSelectProcessor : ObjectManager::getInstance()->get(BaseSelectProcessorInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function build($productId)
    {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $productTable = $this->resource->getTableName('catalog_product_entity');

        $priceSelect = $this->resource->getConnection()->select()
            ->from(['parent' => $productTable], '')
            ->joinInner(
                ['link' => $this->resource->getTableName('catalog_product_relation')],
                "link.parent_id = parent.$linkField",
                []
            )->joinInner(
                [BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS => $productTable],
                sprintf('%s.entity_id = link.child_id', BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS),
                ['entity_id']
            )->joinInner(
                ['t' => $this->resource->getTableName('catalog_product_entity_tier_price')],
                sprintf('t.%s = %s.%1$s', $linkField, BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS),
                []
            )->where('parent.entity_id = ?', $productId)
            ->where('t.all_groups = 1 OR customer_group_id = ?', $this->customerSession->getCustomerGroupId())
            ->where('t.qty = ?', 1)
            ->order('t.value ' . Select::SQL_ASC)
            ->limit(1);
        $priceSelect = $this->baseSelectProcessor->process($priceSelect);

        $priceSelectDefault = clone $priceSelect;
        $priceSelectDefault->where('t.website_id = ?', self::DEFAULT_WEBSITE_ID);
        $select[] = $priceSelectDefault;

        if (!$this->catalogHelper->isPriceGlobal()) {
            $priceSelect->where('t.website_id = ?', $this->storeManager->getStore()->getWebsiteId());
            $select[] = $priceSelect;
        }

        return $select;
    }
}
