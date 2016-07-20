<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem;

/**
 * Representation of message queue handler configuration.
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

    /**
     * Set item data.
     *
     * @param array $data
     * @return void
     */
    public function setData(array $data);
}
