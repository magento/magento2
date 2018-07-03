<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurationApi\Model;

/**
 * Get allowed product types for source items management
 *
 * @api
 */
interface GetAllowedProductTypesForSourceItemManagementInterface
{
    /**
     * @return array
     */
    public function execute(): array;
}
