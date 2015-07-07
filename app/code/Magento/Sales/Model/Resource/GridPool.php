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
     * @param array $grids
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
     */
    public function refreshByOrderId($orderId)
    {
        foreach ($this->grids as $grid) {
            $grid->refresh($orderId, 'sfo.entity_id');
        }
        return $this;
    }
}
