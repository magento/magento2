<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem;

/**
 * Representation of message queue handler configuration.
 * @since 2.2.0
 */
interface HandlerInterface
{
    /**
     * Get handler type name.
     *
     * @return string
     * @since 2.2.0
     */
    public function getType();

    /**
     * Get handler method name.
     *
     * @return string
     * @since 2.2.0
     */
    public function getMethod();
}
