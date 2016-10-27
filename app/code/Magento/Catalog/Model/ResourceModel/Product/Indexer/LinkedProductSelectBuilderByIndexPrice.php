<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Indexer;

use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
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
     * @var BaseSelectProcessorInterface
     */
    private $baseSelectProcessor;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Customer\Model\Session $customerSession
     * @param BaseSelectProcessorInterface $baseSelectProcessor
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Customer\Model\Session $customerSession,
        BaseSelectProcessorInterface $baseSelectProcessor = null
    ) {
        $this->storeManager = $storeManager;
        $this->resource = $resourceConnection;
        $this->customerSession = $customerSession;
        $this->baseSelectProcessor = (null !== $baseSelectProcessor)
            ? $baseSelectProcessor : ObjectManager::getInstance()->get(BaseSelectProcessorInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function build($productId)
    {
        $priceSelect = $this->resource->getConnection()->select()
            ->from(['t' => $this->resource->getTableName('catalog_product_index_price')], 'entity_id')
            ->joinInner(
                [
                    BaseSelectProcessorInterface::PRODUCT_RELATION_ALIAS
                        => $this->resource->getTableName('catalog_product_relation')
                ],
                BaseSelectProcessorInterface::PRODUCT_RELATION_ALIAS . '.child_id = t.entity_id',
                []
            )->where(BaseSelectProcessorInterface::PRODUCT_RELATION_ALIAS . '.parent_id = ? ', $productId)
            ->where('t.website_id = ?', $this->storeManager->getStore()->getWebsiteId())
            ->where('t.customer_group_id = ?', $this->customerSession->getCustomerGroupId())
            ->order('t.min_price ' . Select::SQL_ASC)
            ->limit(1);
        $priceSelect = $this->baseSelectProcessor->process($priceSelect);

        return [$priceSelect];
    }
}
