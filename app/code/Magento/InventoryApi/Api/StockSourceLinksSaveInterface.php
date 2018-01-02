<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;

/**
 * Save StockSourceLink data API
 *
 * @api
 */
interface StockSourceLinksSaveInterface
{
    /**
     * Save StockSourceLink data
     *
     * @param StockSourceLinkInterface[] $links
     * @return array
     * @throws ValidationException
     * @throws CouldNotSaveException
     */
    public function execute(array $links): array;
}
