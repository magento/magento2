<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Image;

use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\View\Asset\Repository as AssetRepository;

/**
 * Image Placeholder provider
 */
class Placeholder
{
    /**
     * @var PlaceholderFactory
     */
    private $placeholderFactory;

    /**
     * @var AssetRepository
     */
    private $assetRepository;

    /**
     * @param PlaceholderFactory $placeholderFactory
     * @param AssetRepository $assetRepository
     */
    public function __construct(
        PlaceholderFactory $placeholderFactory,
        AssetRepository $assetRepository
    ) {
        $this->placeholderFactory = $placeholderFactory;
        $this->assetRepository = $assetRepository;
    }

    /**
     * Get placeholder
     *
     * @param string $imageType
     * @return string
     */
    public function getPlaceholder(string $imageType): string
    {
        $imageAsset = $this->placeholderFactory->create(['type' => $imageType]);

        // check if placeholder defined in config
        if ($imageAsset->getFilePath()) {
            return $imageAsset->getUrl();
        }

        return $this->assetRepository->getUrl(
            "Magento_Catalog::images/product/placeholder/{$imageType}.jpg"
        );
    }
}
