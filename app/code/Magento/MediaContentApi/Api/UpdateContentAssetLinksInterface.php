<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaContentApi\Api;

use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;

/**
 * Update the media assets to content relations. Assign new media assets and unassign media assets no longer used
 * @api
 */
interface UpdateContentAssetLinksInterface
{
    /**
     * Update the media assets to content relations. Assign new media assets and unassign media assets no longer used
     *
     * @param ContentIdentityInterface $contentIdentity
     * @param string $content
     */
    public function execute(ContentIdentityInterface $contentIdentity, string $content): void;
}
