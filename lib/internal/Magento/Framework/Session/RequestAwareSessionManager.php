<?php

namespace Magento\Framework\Session;

use Magento\Framework\ObjectManager\RegisterShutdownInterface;

/**
 * Session Manager instance used to register shutdown script for Application Server
 */
class RequestAwareSessionManager extends Generic implements RegisterShutdownInterface
{
    /**
     * @return void
     */
    public function registerShutDown()
    {
        $this->writeClose();
    }
}
