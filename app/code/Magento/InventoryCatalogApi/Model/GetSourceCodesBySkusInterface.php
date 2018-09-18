<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogApi\Model;

/**
 * Returns a list of source codes associated to a list of provided skus
 * @api
 */
interface GetSourceCodesBySkusInterface
{
    /**
     * @param array $skus
     * @return array
     */
    public function execute(array $skus): array;
}
