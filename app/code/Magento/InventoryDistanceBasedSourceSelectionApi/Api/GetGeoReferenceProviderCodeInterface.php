<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelectionApi\Api;

/**
 * Service returns Default geo reference provider Code
 *
 * @api
 */
interface GetGeoReferenceProviderCodeInterface
{
    /**
     * Get Default distance provider code
     *
     * @return string
     */
    public function execute(): string;
}
