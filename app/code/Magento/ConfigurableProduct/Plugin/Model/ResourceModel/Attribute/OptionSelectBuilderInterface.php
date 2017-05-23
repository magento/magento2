<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Plugin\Model\ResourceModel\Attribute;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilderInterface as OptionSelectBuilder;
use Magento\Framework\DB\Select;


/**
 * Plugin for Class OptionSelectBuilderInterface.
 */
class OptionSelectBuilderInterface
{
    /**
     * CatalogInventory Stock Status Resource Model
     *
     * @var Status
     */
    private $stockStatusResource;
    
    /**
     * @param Status $stockStatusResource
     */
    public function __construct(Status $stockStatusResource)
    {
        $this->stockStatusResource = $stockStatusResource;
    }

    /**
     * Add stock status filter to select.
     *
     * @param OptionSelectBuilder $subject
     * @param Select $select
     * @return Select
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSelect(OptionSelectBuilder $subject, Select $select)
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
