<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Collector\Config;

use Magento\Csp\Api\Data\PolicyInterface;
use Magento\Csp\Model\Policy\FlagPolicy;

/**
 * @inheritDoc
 */
class FlagPolicyReader implements PolicyReaderInterface
{
    /**
     * @inheritDoc
     */
    public function read(string $id, $value): PolicyInterface
    {
        return new FlagPolicy($id);
    }

    /**
     * @inheritDoc
     */
    public function canRead(string $id): bool
    {
        return in_array($id, FlagPolicy::POLICIES, true);
    }
}
