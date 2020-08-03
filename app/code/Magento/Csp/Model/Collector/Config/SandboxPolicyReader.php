<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Collector\Config;

use Magento\Csp\Api\Data\PolicyInterface;
use Magento\Csp\Model\Policy\SandboxPolicy;

/**
 * @inheritDoc
 */
class SandboxPolicyReader implements PolicyReaderInterface
{
    /**
     * @inheritDoc
     */
    public function read(string $id, $value): PolicyInterface
    {
        return new SandboxPolicy(
            !empty($value['forms']),
            !empty($value['modals']),
            !empty($value['orientation']),
            !empty($value['pointer']),
            !empty($value['popup']),
            !empty($value['popups_to_escape']),
            !empty($value['presentation']),
            !empty($value['same_origin']),
            !empty($value['scripts']),
            !empty($value['navigation']),
            !empty($value['navigation_by_user'])
        );
    }

    /**
     * @inheritDoc
     */
    public function canRead(string $id): bool
    {
        return $id === 'sandbox';
    }
}
