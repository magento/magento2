<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Collector\Config;

use Magento\Csp\Api\Data\PolicyInterface;
use Magento\Csp\Model\Policy\FetchPolicy;

/**
 * Reads fetch directives.
 */
class FetchPolicyReader implements PolicyReaderInterface
{
    /**
     * @inheritDoc
     */
    public function read(string $id, $value): PolicyInterface
    {
        return new FetchPolicy(
            $id,
            !empty($value['none']),
            !empty($value['hosts']) ? array_values($value['hosts']) : [],
            !empty($value['schemes']) ? array_values($value['schemes']) : [],
            !empty($value['self']),
            !empty($value['inline']),
            !empty($value['eval']),
            [],
            [],
            !empty($value['dynamic']),
            !empty($value['event_handlers'])
        );
    }

    /**
     * @inheritDoc
     */
    public function canRead(string $id): bool
    {
        return in_array($id, FetchPolicy::POLICIES, true);
    }
}
