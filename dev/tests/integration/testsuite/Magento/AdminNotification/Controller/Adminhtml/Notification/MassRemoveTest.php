<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Controller\Adminhtml\Notification;

class MassRemoveTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    protected function setUp(): void
    {
        $this->resource = 'Magento_AdminNotification::adminnotification_remove';
        $this->uri = 'backend/admin/notification/massremove';
        parent::setUp();
    }
}
