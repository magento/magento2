<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Controller\Adminhtml;

use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @magentoAppArea adminhtml
 */
class ConsecutiveCallTest extends AbstractBackendController
{
    /**
     * Consecutive calls were failing due to `$request['dispatched']` not being reset before request
     */
    public function testConsecutiveCallShouldNotFail()
    {
        $this->dispatch('backend/admin/auth/login');
        $this->dispatch('backend/admin/auth/login');
    }
}
