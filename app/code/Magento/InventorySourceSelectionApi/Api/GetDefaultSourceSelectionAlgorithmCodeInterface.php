<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Api;

/**
 * Service returns Default Source Selection Algorithm Code
 *
 * @api
 */
interface GetDefaultSourceSelectionAlgorithmCodeInterface
{
    /**
     * Get Default Algorithm code
     *
     * @return string
     */
    public function execute(): string;
}
