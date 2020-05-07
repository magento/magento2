<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentApi\Model;

use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;

/**
 * Get Entity Contents.
 * @api
 */
interface GetEntityContentsInterface
{
    /**
     * Get concatenated content by the content identity
     *
     * @param ContentIdentityInterface $contentIdentity
     * @return string[]
     */
    public function execute(ContentIdentityInterface $contentIdentity): array;
}
