<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model;

/**
 * Get allowed product types for source items management
 *
 * @api
 */
interface GetAllowedProductTypesForSourceItemsInterface
{
    /**
     * @return array
     */
    public function execute(): array;
}
