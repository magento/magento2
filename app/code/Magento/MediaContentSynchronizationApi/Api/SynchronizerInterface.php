<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronizationApi\Api;

/**
 * Synchronize assets and contents
 */
interface SynchronizerInterface
{
    /**
     * Synchronize assets and contents
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(): void;
}
