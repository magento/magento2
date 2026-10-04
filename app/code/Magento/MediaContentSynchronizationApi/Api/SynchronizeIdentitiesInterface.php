<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronizationApi\Api;

use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;

/**
 * Synchronize bulk assets and contents
 * @api
 */
interface SynchronizeIdentitiesInterface
{
    /**
     * Synchronize media contents
     *
     * @param ContentIdentityInterface[] $contentIdentities
     */
    public function execute(array $contentIdentities): void;
}
