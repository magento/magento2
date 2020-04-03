<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentApi\Api;

use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;

/**
 * Process relation managing between media asset and content: assign or unassign relation if exists.
 */
interface UpdateRelationsInterface
{
    /**
     * Create new relation between media asset and content or updated existing
     *
     * @param ContentIdentityInterface $contentIdentity
     * @param string $content
     */
    public function execute(ContentIdentityInterface $contentIdentity, string $content): void;
}
