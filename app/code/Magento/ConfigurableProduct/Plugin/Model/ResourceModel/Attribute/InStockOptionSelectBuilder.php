<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Plugin\Model\ResourceModel\Attribute;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilderInterface;
use Magento\Framework\DB\Select;

/**
 * Plugin for OptionSelectBuilderInterface to add stock status filter.
 * @since 2.2.0
 */
class InStockOptionSelectBuilder
{
    /**
     * CatalogInventory Stock Status Resource Model.
     *
     * @var Status
     * @since 2.2.0
     */
    private $stockStatusResource;
    
    /**
     * @param Status $stockStatusResource
     * @since 2.2.0
     */
    public function __construct(Status $stockStatusResource)
    {
        $this->stockStatusResource = $stockStatusResource;
    }

    /**
     * Add stock status filter to select.
     *
     * @param OptionSelectBuilderInterface $subject
     * @param Select $select
     * @return Select
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterGetSelect(OptionSelectBuilderInterface $subject, Select $select)
    {
        $select->joinInner(
            ['stock' => $this->stockStatusResource->getMainTable()],
            'stock.product_id = entity.entity_id',
            []
        )->where(
            'stock.stock_status = ?',
            \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK
        );
        
        return $select;
    }
}
