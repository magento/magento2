<?php
/***
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Controller\Adminhtml\Notification;

class MarkAsReadTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function setUp()
    {
        $this->resource = 'Magento_AdminNotification::mark_as_read';
        $this->uri = 'backend/admin/notification/markasread';
        parent::setUp();
    }
}
