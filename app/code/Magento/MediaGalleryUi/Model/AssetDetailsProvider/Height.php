<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model\AssetDetailsProvider;

use Magento\Framework\Exception\IntegrationException;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryUi\Model\AssetDetailsProviderInterface;

/**
 * Provide asset height
 */
class Height implements AssetDetailsProviderInterface
{
    /**
     * Provide asset height
     *
     * @param AssetInterface $asset
     * @return array
     * @throws IntegrationException
     */
    public function execute(AssetInterface $asset): array
    {
        return [
            'title' => __('Height'),
            'value' => sprintf('%spx', $asset->getHeight())
        ];
    }
}
