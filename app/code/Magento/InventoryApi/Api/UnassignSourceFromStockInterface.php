<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api;

/**
 * Unassign Source from Stock
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface UnassignSourceFromStockInterface
{
    /**
     * Unassign source from stock
     *
     * If Source or Stock with given code doesn't exist then do nothing
     *
     * @param string $sourceCode
     * @param int $stockId
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function execute(string $sourceCode, int $stockId);
}
