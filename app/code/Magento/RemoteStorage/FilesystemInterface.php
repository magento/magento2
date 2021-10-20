<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage;

/**
 * Provides extension for applicable directory codes.
 */
interface FilesystemInterface
{
    /**
     * Retrieve directory codes.
     */
    public function getDirectoryCodes(): array;
}
