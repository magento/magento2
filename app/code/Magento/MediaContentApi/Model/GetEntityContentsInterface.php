<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaContentApi\Model;

use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;

/**
 * Get Entity Contents.
 * @api
 * @since 100.4.0
 */
interface GetEntityContentsInterface
{
    /**
     * Get concatenated content by the content identity
     *
     * @param ContentIdentityInterface $contentIdentity
     * @return string[]
     * @since 100.4.0
     */
    public function execute(ContentIdentityInterface $contentIdentity): array;
}
