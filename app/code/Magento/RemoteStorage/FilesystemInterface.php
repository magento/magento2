<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage;

/**
 * Provides extension for applicable directory codes.
 * @api
 */
interface FilesystemInterface
{
    /**
     * Retrieve directory codes.
     */
    public function getDirectoryCodes(): array;
}
