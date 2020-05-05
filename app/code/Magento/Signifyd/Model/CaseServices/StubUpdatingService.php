<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\CaseServices;

use Magento\Signifyd\Api\Data\CaseInterface;

/**
 * Stub implementation for case updating service interface and might be used
 * for test Signifyd webhooks
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
 */
class StubUpdatingService implements UpdatingServiceInterface
{
    /**
     * @inheritdoc
     */
    public function update(CaseInterface $case, array $data)
    {
        // just stub method and doesn't contain any logic
    }
}
