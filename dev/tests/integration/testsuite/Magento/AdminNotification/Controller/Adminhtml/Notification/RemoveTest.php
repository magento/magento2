<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\AdminNotification\Controller\Adminhtml\Notification;

class RemoveTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    protected function setUp(): void
    {
        $this->resource = 'Magento_AdminNotification::adminnotification_remove';
        $this->uri = 'backend/admin/notification/remove';
        parent::setUp();
    }
}
