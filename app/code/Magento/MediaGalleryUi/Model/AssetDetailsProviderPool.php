<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model;

use Magento\MediaGalleryApi\Api\Data\AssetInterface;

/**
 * Provides asset detail for view details section
 */
class AssetDetailsProviderPool
{
    /**
     * @var AssetDetailsProviderInterface[]
     */
    private $detailsProviders;

    /**
     * @param AssetDetailsProviderInterface[] $detailsProviders
     */
    public function __construct(array $detailsProviders = [])
    {
        $this->detailsProviders = $detailsProviders;
    }

    /**
     * Get a piece of asset details
     *
     * @param AssetInterface $asset
     * @return array
     */
    public function execute(AssetInterface $asset): array
    {
        $details = [];
        foreach ($this->detailsProviders as $detailsProvider) {
            if ($detailsProvider instanceof AssetDetailsProviderInterface) {
                $details[] = $detailsProvider->execute($asset);
            }
        }
        return $details;
    }
}
