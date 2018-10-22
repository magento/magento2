<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Model\ResourceModel\Product;

use Magento\Framework\DB\Select;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Stock\Status as StockStatus;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatusResource;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\StockStatusInterface as StockStatusConfigurableInterface;

/**
 * A Select object processor.
 *
 * Adds stock status limitations to a given Select object.
 */
class StockStatusBaseSelectProcessor implements BaseSelectProcessorInterface
{
    /**
     * @var StockConfigurationInterface
     */
    private $stockConfig;

    /**
     * @var StockStatusResource
     */
    private $stockStatusResource;

    /**
     * @var StockStatusConfigurableInterface
     */
    private $stockStatusConfigurableResource;

    /**
     * @param StockConfigurationInterface $stockConfig
     * @param StockStatusResource $stockStatusResource
     * @param StockStatusConfigurableInterface $stockStatusConfigurableResource
     */
    public function __construct(
        StockConfigurationInterface $stockConfig,
        StockStatusResource $stockStatusResource,
        StockStatusConfigurableInterface $stockStatusConfigurableResource
    )
    {
        $this->stockConfig = $stockConfig;
        $this->stockStatusResource = $stockStatusResource;
        $this->stockStatusConfigurableResource = $stockStatusConfigurableResource;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Select $select, $productId)
    {
        if ($this->stockConfig->isShowOutOfStock() &&
            !$this->isAllChildOutOfStock($productId)
        ) {
            $select->joinInner(
                ['stock' => $this->stockStatusResource->getMainTable()],
                sprintf(
                    'stock.product_id = %s.entity_id',
                    BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS
                ),
                []
            )->where(
                'stock.stock_status = ?',
                StockStatus::STATUS_IN_STOCK
            );
        }

        return $select;
    }

    /**
     * @param int $productId
     * @return bool
     * @throws \Exception
     */
    protected function isAllChildOutOfStock($productId)
    {
        return $this->stockStatusConfigurableResource->isAllChildOutOfStock($productId);
    }
}
