<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SalesOrderGrid;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\ResourceModel\GridInterface;

/**
 * Perfoms sales order grid updating operations.
 *
 * Serves order grid updates in both synchronous and asynchronous modes.
 */
class OrderGridUpdater
{
    /**
     * @var ScopeConfigInterface
     */
    private $globalConfig;

    /**
     * @var GridInterface
     */
    private $entityGrid;

    /**
     * @param GridInterface $entityGrid
     * @param ScopeConfigInterface $globalConfig
     */
    public function __construct(
        GridInterface $entityGrid,
        ScopeConfigInterface $globalConfig
    ) {
        $this->globalConfig = $globalConfig;
        $this->entityGrid = $entityGrid;
    }

    /**
     * Handles synchronous updating order entity in grid.
     *
     * Works only if asynchronous grid indexing is disabled
     * in global settings.
     *
     * @param int $orderId
     * @return void
     */
    public function update($orderId)
    {
        if (!$this->globalConfig->getValue('dev/grid/async_indexing')) {
            $this->entityGrid->refresh($orderId);
        }
    }
}
