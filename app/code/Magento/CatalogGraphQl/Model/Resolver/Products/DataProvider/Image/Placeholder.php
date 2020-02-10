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
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * @param PlaceholderFactory $placeholderFactory
     * @param AssetRepository $assetRepository
     * @param StoreManagerInterface $storeManager
     * @param Emulation $appEmulation
     */
    public function __construct(
        PlaceholderFactory $placeholderFactory,
        AssetRepository $assetRepository,
        StoreManagerInterface $storeManager,
        Emulation $appEmulation
    ) {
        $this->placeholderFactory = $placeholderFactory;
        $this->assetRepository = $assetRepository;
        $this->storeManager = $storeManager;
        $this->appEmulation = $appEmulation;
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

        $storeId = $this->storeManager->getStore()->getId();

        $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
        $url = $this->assetRepository->getUrl("Magento_Catalog::images/product/placeholder/{$imageType}.jpg");
        $this->appEmulation->stopEnvironmentEmulation();

        return $url;
    }
}
