<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Observer;

use InvalidArgumentException;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Design\Theme\Customization\FileAssetInterface;
use Magento\Framework\View\Design\Theme\Customization\Path;
use Magento\Framework\View\DesignInterface;
use Magento\Theme\Model\Theme;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Theme\Model\Theme\File;
use Psr\Log\LoggerInterface;

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
     * @var GroupedCollection
     */
    private $pageAssets;

    /**
     * @param DesignInterface $design
     * @param GroupedCollection $assets
     * @param Repository $assetRepo
     * @param LoggerInterface $logger
     */
    public function __construct(
        DesignInterface $design,
        GroupedCollection $assets,
        protected readonly Repository $assetRepo,
        protected readonly LoggerInterface $logger
    ) {
        $this->currentTheme = $design->getDesignTheme();
        $this->pageAssets = $assets;
    }

    /**
     * Apply customized static files to frontend
     *
     * @param EventObserver $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(EventObserver $observer)
    {
        /** @var $themeFile File */
        foreach ($this->currentTheme->getCustomization()->getFiles() as $themeFile) {
            try {
                $service = $themeFile->getCustomizationService();
                if ($service instanceof FileAssetInterface) {
                    $identifier = $themeFile->getData('file_path');
                    $dirPath = Path::DIR_NAME
                        . '/' . $this->currentTheme->getId();
                    $asset = $this->assetRepo->createArbitrary(
                        $identifier,
                        $dirPath,
                        DirectoryList::MEDIA,
                        UrlInterface::URL_TYPE_MEDIA
                    );
                    $this->pageAssets->add($identifier, $asset);
                }
            } catch (InvalidArgumentException $e) {
                $this->logger->critical($e);
            }
        }
    }
}
