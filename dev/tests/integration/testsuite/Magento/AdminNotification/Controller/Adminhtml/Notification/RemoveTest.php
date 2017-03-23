<?php
/***
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Controller\Adminhtml\Notification;

class RemoveTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function setUp()
    {
        $this->resource = 'Magento_AdminNotification::adminnotification_remove';
        $this->uri = 'backend/admin/notification/remove';
        parent::setUp();
    }
}
