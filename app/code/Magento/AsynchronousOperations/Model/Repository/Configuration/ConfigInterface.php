<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model\Repository\Configuration;

interface ConfigInterface
{
    public const OPERATIONS_NODE = 'queue_operations';
    public const STORAGE_NODE = 'storage';
    public const CONFIG_NODE = 'config';
    public const DEFAULT_STORAGE = "db";

    /**
     * Get current enabled storage for storing of asynchronous operations
     * statuses
     *
     * @return string
     */
    public function getStorage();

    /**
     * Get configuration for connection
     *
     * @return string[]
     */
    public function getConfig();
}
