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
 * Provide asset file size
 */
class Size implements AssetDetailsProviderInterface
{
    /**
     * Provide asset file size
     *
     * @param AssetInterface $asset
     * @return array
     * @throws IntegrationException
     */
    public function execute(AssetInterface $asset): array
    {
        return [
            'title' => __('Size'),
            'value' => $this->formatImageSize($asset->getSize())
        ];
    }

    /**
     * Format image size
     *
     * @param int $imageSize
     *
     * @return string
     */
    private function formatImageSize(int $imageSize): string
    {
        if ($imageSize === 0) {
            return '';
        }

        return sprintf('%sKb', $imageSize / 1000);
    }
}
