<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Collector\Config;

use Magento\Csp\Api\Data\PolicyInterface;

/**
 * Initiates a policy DTO based on a value found in Magento config.
 */
interface PolicyReaderInterface
{
    /**
     * Read a policy from a config value.
     *
     * @param string $id
     * @param string|array|bool $value
     * @return PolicyInterface
     */
    public function read(string $id, $value): PolicyInterface;

    /**
     * Can given policy be read by this reader?
     *
     * @param string $id
     * @return bool
     */
    public function canRead(string $id): bool;
}
