<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\ResourceModel\Rss\NotifyStock;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;

class ApplyStatusAttributeCondition
{
    /**
     * @var Status
     */
    private $productStatus;

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
     * @param Status $productStatus
     * @param ResourceConnection $resourceConnection
     * @param EavConfig $eavConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Status $productStatus,
        ResourceConnection $resourceConnection,
        EavConfig $eavConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->productStatus = $productStatus;
        $this->resourceConnection = $resourceConnection;
        $this->eavConfig = $eavConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Select $select
     * @return void
     */
    public function execute(Select $select)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $attributeId = $this->eavConfig->getAttribute(Product::ENTITY, ProductInterface::STATUS)->getAttributeId();
        $connection = $this->resourceConnection->getConnection();
        $statusVisibilityCondition = $connection->prepareSqlCondition(
            'product_int.value',
            ['in' => $this->productStatus->getVisibleStatusIds()]
        );
        $condition = implode(
            [
                $statusVisibilityCondition,
                $connection->prepareSqlCondition('product_int.store_id', $storeId),
                $connection->prepareSqlCondition('product_int.attribute_id', $attributeId),
            ],
            ' ' . Select::SQL_AND . ' '
        );

        $select->where($condition);
    }
}
