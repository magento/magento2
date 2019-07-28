<?php
/**
 * List of plugins configured in application
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception;

/**
 * Interface \Magento\Framework\Interception\PluginListInterface
 *
 */
interface PluginListInterface
{
    /**
     * Retrieve next plugins in chain
     *
     * @param string $type
     * @param string $method
     * @param string $code
     * @return array
     */
    public function getNext($type, $method, $code = null);

    /**
     * Retrieve plugin instance by code
     *
     * @param string $type
     * @param string $code
     * @return mixed
     */
    public function getPlugin($type, $code);
}
