<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Image;

use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Placeholder
 * @package Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Image
 */
class Placeholder
{
    /**
     * @var PlaceholderFactory
     */
    private $_placeholderFactory;
    /**
     * @var AssetRepository
     */
    private $_assetRepository;
    /**
     * @var ScopeConfigInterface
     */
    private $_scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;
    /**
     * @var ThemeProviderInterface
     */
    private $_themeProvider;

    /**
     * Placeholder constructor.
     * @param PlaceholderFactory $placeholderFactory
     * @param AssetRepository $assetRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ThemeProviderInterface $themeProvider
     */
    public function __construct(
        PlaceholderFactory $placeholderFactory,
        AssetRepository $assetRepository,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ThemeProviderInterface $themeProvider
    ) {
        $this->_placeholderFactory = $placeholderFactory;
        $this->_assetRepository = $assetRepository;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_themeProvider = $themeProvider;
    }

    /**
     * Get placeholder
     *
     * @param $imageType
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPlaceholder($imageType): string
    {
        $imageAsset = $this->_placeholderFactory->create(['type' => $imageType]);

        // check if placeholder defined in config
        if ($imageAsset->getFilePath()) {
            return $imageAsset->getUrl();
        }

        return $this->_assetRepository->createAsset(
            "Magento_Catalog::images/product/placeholder/{$imageType}.jpg",
            $this->getThemeData()
        )->getUrl();
    }

    /**
     * Get theme model
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getThemeData()
    {
        $themeId = $this->_scopeConfig->getValue(
            \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId()
        );

        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        $theme = $this->_themeProvider->getThemeById($themeId);

        $data = $theme->getData();
        $data['themeModel'] = $theme;

        return $data;
    }
}
