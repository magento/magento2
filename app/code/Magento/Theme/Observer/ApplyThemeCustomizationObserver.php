<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Theme\Model\Theme;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Theme Observer model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ApplyThemeCustomizationObserver implements ObserverInterface
{
    /**
     * @var Theme
     */
    private $currentTheme;

    /**
     * @var \Magento\Framework\View\Asset\GroupedCollection
     */
    private $pageAssets;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\View\Asset\GroupedCollection $assets
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\View\Asset\GroupedCollection $assets,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->currentTheme = $design->getDesignTheme();
        $this->pageAssets = $assets;
        $this->assetRepo = $assetRepo;
        $this->logger = $logger;
    }

    /**
     * Apply customized static files to frontend
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $themeFile \Magento\Theme\Model\Theme\File */
        foreach ($this->currentTheme->getCustomization()->getFiles() as $themeFile) {
            try {
                $service = $themeFile->getCustomizationService();
                if ($service instanceof \Magento\Framework\View\Design\Theme\Customization\FileAssetInterface) {
                    $identifier = $themeFile->getData('file_path');
                    $dirPath = \Magento\Framework\View\Design\Theme\Customization\Path::DIR_NAME
                        . '/' . $this->currentTheme->getId();
                    $asset = $this->assetRepo->createArbitrary(
                        $identifier,
                        $dirPath,
                        DirectoryList::MEDIA,
                        \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                    );
                    $this->pageAssets->add($identifier, $asset);
                }
            } catch (\InvalidArgumentException $e) {
                $this->logger->critical($e);
            }
        }
    }
}
