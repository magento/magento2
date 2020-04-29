<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MediaContentApi\Model;

use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;

/**
 * Get Entity Contents.
 */
interface GetEntityContentsInterface
{
    /**
     * @param ContentIdentityInterface $contentIdentity
     * @return string[]
     */
    public function execute(ContentIdentityInterface $contentIdentity): array;
}
