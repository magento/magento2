<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Model;

use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * It is extension point for carrier links storage replacing (Service Provider Interface - SPI)
 * Provide own implementation of this interface if you would like to replace storage
 *
 * @api
 */
interface SourceCarrierLinkManagementInterface
{
    /**
     * Save carrier links by source
     *
     * Get carrier links from source object and save its. If carrier links are equal to null do nothing
     *
     * @param SourceInterface $source
     * @return void
     */
    public function saveCarrierLinksBySource(SourceInterface $source): void;

    /**
     * Load carrier links by source and set its to source object
     *
     * @param SourceInterface $source
     * @return void
     */
    public function loadCarrierLinksBySource(SourceInterface $source): void;
}
