<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer;

use RuntimeException;

/**
 * Exception thrown when index lock could not be acquired
 */
class IndexMutexException extends RuntimeException
{
    /**
     * @param string $indexerName
     */
    public function __construct(string $indexerName)
    {
        parent::__construct('Could not acquire lock for index: ' . $indexerName);
    }
}
