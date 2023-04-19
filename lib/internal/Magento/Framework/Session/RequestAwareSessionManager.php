<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Session;

use Magento\Framework\ObjectManager\RegisterShutdownInterface;

/**
 * Session Manager instance used to register shutdown script for Application Server
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class RequestAwareSessionManager extends Generic implements RegisterShutdownInterface
{
    /**
     * @inheritDoc
     */
    public function registerShutDown()
    {
        $this->writeClose();
    }
}
