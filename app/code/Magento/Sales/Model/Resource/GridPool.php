<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Resource;

/**
 * Class GridPool
 */
class GridPool
{
    /**
     * @var GridInterface[]
     */
    protected $grids;

    /**
     * @param \Magento\Sales\Model\Resource\Grid $orderGrid
     * @param \Magento\Sales\Model\Resource\Grid $invoiceGrid
     * @param \Magento\Sales\Model\Resource\Grid $shipmentGrid
     * @param \Magento\Sales\Model\Resource\Grid $creditmemoGrid
     */
    public function __construct(
        \Magento\Sales\Model\Resource\Grid $orderGrid,
        \Magento\Sales\Model\Resource\Grid $invoiceGrid,
        \Magento\Sales\Model\Resource\Grid $shipmentGrid,
        \Magento\Sales\Model\Resource\Grid $creditmemoGrid
    ) {
        $this->grids = [
            'order_grid' => $orderGrid,
            'invoice_grid' => $invoiceGrid,
            'shipment_grid' => $shipmentGrid,
            'creditmemo_grid' => $creditmemoGrid,
        ];
    }

    /**
     * Refresh grids list
     *
     * @param int $orderId
     * @return $this
     */
    public function refreshByOrderId($orderId)
    {
        foreach ($this->grids as $grid) {
            $grid->refresh($orderId, 'sfo.entity_id');
        }
        return $this;
    }
}
