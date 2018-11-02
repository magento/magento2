<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Model;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventorySourceSelectionApi\Api\Data\AddressRequestInterface;

/**
 * Distance provider for distance based source selection
 *
 * @api
 */
interface DistanceProviderInterface
{
    /**
     * Return distance in kilometers between a source and a destination address
     *
     * @param SourceInterface $source
     * @param AddressRequestInterface $destination
     * @return float
     */
    public function execute(SourceInterface $source, AddressRequestInterface $destination): float;
}
