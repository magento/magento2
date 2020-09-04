<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Controller\Adminhtml\Notification;

use Magento\Framework\App\Request\Http as HttpRequest;

class MassMarkAsReadTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    protected function setUp(): void
    {
        $this->resource = 'Magento_AdminNotification::mark_as_read';
        $this->uri = 'backend/admin/notification/massmarkasread';
        $this->httpMethod = HttpRequest::METHOD_POST;
        parent::setUp();
    }
}
