<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem;

/**
 * Representation of message queue handler configuration.
 * @api
 */
interface HandlerInterface
{
    /**
     * Get handler type name.
     *
     * @return string
     */
    public function getType();

    /**
     * Get handler method name.
     *
     * @return string
     */
    public function getMethod();
}
