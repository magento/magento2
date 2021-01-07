<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronizationApi\Api;

use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;

/**
 * Synchronize bulk assets and contents
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
