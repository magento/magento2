<?php
/**
/***
 *
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\AdminNotification\Controller\Adminhtml\Notification;

/**
 * Testing markAsRead controller.
 *
 * @magentoAppArea adminhtml
 */
class MarkAsReadTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->resource = 'Magento_AdminNotification::mark_as_read';
        $this->uri = 'backend/admin/notification/markasread';
        parent::setUp();
    }
}
