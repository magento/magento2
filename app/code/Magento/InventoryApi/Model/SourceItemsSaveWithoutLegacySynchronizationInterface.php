<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Model;

use Magento\InventoryApi\Api\SourceItemsSaveInterface;

/**
 * Prevent repetitive synchronization of Legacy-MSI-Legacy update against the original source item save implementation.
 * (Service Provider Interface - SPI)
 *
 * @api
 */
interface SourceItemsSaveWithoutLegacySynchronizationInterface extends SourceItemsSaveInterface
{
}
