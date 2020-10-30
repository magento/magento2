<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Collector\Config;

use Magento\Csp\Api\Data\PolicyInterface;
use Magento\Csp\Model\Policy\PluginTypesPolicy;

/**
 * @inheritDoc
 */
class PluginTypesPolicyReader implements PolicyReaderInterface
{
    /**
     * @inheritDoc
     */
    public function read(string $id, $value): PolicyInterface
    {
        return new PluginTypesPolicy(array_values($value['types']));
    }

    /**
     * @inheritDoc
     */
    public function canRead(string $id): bool
    {
        return $id === 'plugin-types';
    }
}
