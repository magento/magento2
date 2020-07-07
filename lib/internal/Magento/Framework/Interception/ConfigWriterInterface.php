<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception;

use Magento\Framework\Exception\FileSystemException;

/**
 * Interception config writer interface.
 */
interface ConfigWriterInterface
{
    /**
     * Write interception configuration for scopes.
     *
     * @param array $scopes
     * @return void
     * @throws FileSystemException
     */
    public function write(array $scopes): void;
}
