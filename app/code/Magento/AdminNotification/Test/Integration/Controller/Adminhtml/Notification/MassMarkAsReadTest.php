<?php

declare(strict_types=1);

/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Test\Integration\Controller\Adminhtml\Notification;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\AuthenticationException;
use Magento\TestFramework\TestCase\AbstractBackendController;

class MassMarkAsReadTest extends AbstractBackendController
{
    /**
     * @return void
     * @throws AuthenticationException
     */
    public function setUp(): void
    {
        $this->resource = 'Magento_AdminNotification::mark_as_read';
        $this->uri = 'backend/admin/notification/massmarkasread';
        $this->httpMethod = HttpRequest::METHOD_POST;
        parent::setUp();
    }
}
