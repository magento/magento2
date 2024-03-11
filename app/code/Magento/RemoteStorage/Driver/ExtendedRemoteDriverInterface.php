<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained from
 * Adobe.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver;

use Magento\RemoteStorage\Driver\RemoteDriverInterface;

interface ExtendedRemoteDriverInterface extends RemoteDriverInterface
{
    /**
     * To check if directory exists
     *
     * @param string $path
     * @return bool
     */
    public function isDirectoryExists($path): bool;
}
