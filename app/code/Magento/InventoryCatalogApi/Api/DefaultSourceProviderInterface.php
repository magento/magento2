<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogApi\Api;

/**
 * Service returns Default Source Id
 *
 * @api
 */
interface DefaultSourceProviderInterface
{
    /**
     * Get Default Source code
     *
     * @return string
     */
    public function getCode(): string;
}
