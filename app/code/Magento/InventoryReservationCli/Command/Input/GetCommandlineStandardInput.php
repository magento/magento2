<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Command\Input;

/**
 * Fetches standard input for cli commands and retrieves as array
 */
class GetCommandlineStandardInput
{
    /**
     * @return array
     */
    public function execute(): array
    {
        $values = [];
        $handle = fopen('php://stdin', 'r');
        if ($handle) {
            while ($line = fgets($handle)) {
                $values[] = trim($line);
            }
            fclose($handle);
        }

        return array_filter($values);
    }
}
