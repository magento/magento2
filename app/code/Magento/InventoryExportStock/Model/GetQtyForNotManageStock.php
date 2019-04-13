<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model;

/**
 * Class GetQtyForNotManageStock provides qtyForNotManageStock from di configuration
 */
class GetQtyForNotManageStock
{
    /**
     * @var int
     */
    private $qtyForNotManageStock;

    /**
     * GetQtyForNotManageStock constructor
     *
     * @param int $qtyForNotManageStock
     */
    public function __construct(
        int $qtyForNotManageStock
    ) {
        $this->qtyForNotManageStock = $qtyForNotManageStock;
    }

    /**
     * Provides qtyForNotManageStock from di configuration
     *
     * @return int
     */
    public function execute(): int
    {
        return $this->qtyForNotManageStock;
    }
}
