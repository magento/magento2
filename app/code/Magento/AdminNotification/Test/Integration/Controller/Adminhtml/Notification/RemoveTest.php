<?php

declare(strict_types=1);

/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Test\Integration\Controller\Adminhtml\Notification;

use Magento\Framework\Exception\AuthenticationException;
use Magento\TestFramework\TestCase\AbstractBackendController;

class RemoveTest extends AbstractBackendController
{
    /**
     * @return void
     * @throws AuthenticationException
     */
    public function setUp(): void
    {
        $this->resource = 'Magento_AdminNotification::adminnotification_remove';
        $this->uri = 'backend/admin/notification/remove';
        parent::setUp();
    }
}
