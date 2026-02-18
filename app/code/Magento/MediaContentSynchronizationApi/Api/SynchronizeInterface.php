<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronizationApi\Api;

/**
 * Synchronize assets and contents
 * @api
 */
interface SynchronizeInterface
{
    /**
     * Synchronize assets and contents
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(): void;
}
