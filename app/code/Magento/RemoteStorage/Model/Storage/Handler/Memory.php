<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Model\Storage\Handler;

/**
 * Memory cache model.
 */
class Memory implements CacheStorageHandlerInterface
{
    /**
     * @inheritdoc
     */
    public function save(): void
    {
        // There is nothing to save
    }

    /**
     * @inheritdoc
     */
    public function load(): void
    {
        // There is nothing to load
    }
}
