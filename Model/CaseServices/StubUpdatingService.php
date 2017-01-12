<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\CaseServices;

/**
 * Stub implementation for case updating service interface and might be used
 * for test Signifyd webhooks
 */
class StubUpdatingService implements UpdatingServiceInterface
{

    /**
     * @inheritdoc
     */
    public function update(array $data)
    {
        // just stub method and doesn't contain any logic
    }
}
