<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\ResourceModel\Rss\NotifyStock;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;

class ApplyNameAttributeCondition
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourceConnection $resourceConnection
     * @param EavConfig $eavConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        EavConfig $eavConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param Select $select
     * @return void
     */
    public function execute(Select $select)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $attributeId = $this->eavConfig->getAttribute(Product::ENTITY, ProductInterface::NAME)->getAttributeId();
        $connection = $this->resourceConnection->getConnection();

        $condition = implode(
            [
                $connection->prepareSqlCondition('product_varchar.store_id', $storeId),
                $connection->prepareSqlCondition('product_varchar.attribute_id', $attributeId),
            ],
            ' ' . Select::SQL_AND . ' '
        );

        $select->where($condition);
    }
}
