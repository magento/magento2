<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Model\Import\Command;

/**
 * It is extension point to implement import/export functionality (Service Provider Interface - SPI)
 *
 * @api
 */
interface CommandInterface
{
    /**
     * Executes the current command.
     *
     * @param array $bunch
     * @return void
     */
    public function execute(array $bunch);
}
