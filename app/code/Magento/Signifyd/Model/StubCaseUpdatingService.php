<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\DataObject;

/**
 * Stub implementation for case updating service interface and might be used
 * for test Signifyd webhooks
 */
class StubCaseUpdatingService implements CaseUpdatingServiceInterface
{

    /**
     * @inheritdoc
     */
    public function update(DataObject $data)
    {
        // just stub method and doesn't contain any logic
    }
}
