<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Test\Integration\Controller\Adminhtml\Notification;

use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Testing markAsRead controller.
 *
 * @magentoAppArea adminhtml
 */
class MarkAsReadTest extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->resource = 'Magento_AdminNotification::mark_as_read';
        $this->uri = 'backend/admin/notification/markasread';
    }
}
