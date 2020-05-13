<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Image;

use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\App\Area;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\DesignInterface;

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
     * @var DesignInterface
     */
    private $themeDesign;

    /**
     * @param PlaceholderFactory $placeholderFactory
     * @param AssetRepository $assetRepository
     * @param DesignInterface $themeDesign
     */
    public function __construct(
        PlaceholderFactory $placeholderFactory,
        AssetRepository $assetRepository,
        DesignInterface $themeDesign
    ) {
        $this->placeholderFactory = $placeholderFactory;
        $this->assetRepository = $assetRepository;
        $this->themeDesign = $themeDesign;
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

        $params = [
            'area' => Area::AREA_FRONTEND,
            'themeId' => $this->themeDesign->getConfigurationDesignTheme(Area::AREA_FRONTEND),
        ];

        return $this->assetRepository->getUrlWithParams(
            "Magento_Catalog::images/product/placeholder/{$imageType}.jpg",
            $params
        );
    }
}
