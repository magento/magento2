<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryImportExport\Model\Import\Command;

/**
 * @api
 */
interface CommandInterface
{
    /**
     * @param array $bunch
     * @return void
     * @throws CommandException
     */
    public function execute(array $bunch);
}
