<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\ResourceModel;

/**
 * Class GridPool
 * @api
 * @since 2.0.0
 */
class GridPool
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Grid[]
     * @since 2.0.0
     */
    protected $grids;

    /**
     * @param array $grids
     * @since 2.0.0
     */
    public function __construct(array $grids)
    {
        $this->grids = $grids;
    }

    /**
     * Refresh grids list
     *
     * @param int $orderId
     * @return $this
     * @since 2.0.0
     */
    public function refreshByOrderId($orderId)
    {
        foreach ($this->grids as $grid) {
            $grid->refresh($orderId, $grid->getOrderIdField());
        }

        return $this;
    }
}
