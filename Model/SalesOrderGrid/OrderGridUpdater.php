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
 * @since 2.2.0
 */
class OrderGridUpdater
{
    /**
     * @var ScopeConfigInterface
     * @since 2.2.0
     */
    private $globalConfig;

    /**
     * @var GridInterface
     * @since 2.2.0
     */
    private $entityGrid;

    /**
     * @param GridInterface $entityGrid
     * @param ScopeConfigInterface $globalConfig
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function update($orderId)
    {
        if (!$this->globalConfig->getValue('dev/grid/async_indexing')) {
            $this->entityGrid->refresh($orderId);
        }
    }
}
