<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Core Observer model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Observer
{
    /**
     * @var Theme
     */
    private $_currentTheme;

    /**
     * @var \Magento\Framework\View\Asset\GroupedCollection
     */
    private $_pageAssets;

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Core\Model\Theme\Registration
     */
    protected $_registration;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\View\Asset\GroupedCollection $assets
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $config
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param Theme\Registration $registration
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\View\Asset\GroupedCollection $assets,
        \Magento\Framework\App\Config\ReinitableConfigInterface $config,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Core\Model\Theme\Registration $registration,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_currentTheme = $design->getDesignTheme();
        $this->_pageAssets = $assets;
        $this->_assetRepo = $assetRepo;
        $this->_registration = $registration;
        $this->_logger = $logger;
    }

    /**
     * Theme registration
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function themeRegistration(\Magento\Framework\Event\Observer $observer)
    {
        $pathPattern = $observer->getEvent()->getPathPattern();
        try {
            $this->_registration->register($pathPattern);
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->_logger->critical($e);
        }
        return $this;
    }

    /**
     * Apply customized static files to frontend
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function applyThemeCustomization(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $themeFile \Magento\Core\Model\Theme\File */
        foreach ($this->_currentTheme->getCustomization()->getFiles() as $themeFile) {
            try {
                $service = $themeFile->getCustomizationService();
                if ($service instanceof \Magento\Framework\View\Design\Theme\Customization\FileAssetInterface) {
                    $identifier = $themeFile->getData('file_path');
                    $dirPath = \Magento\Framework\View\Design\Theme\Customization\Path::DIR_NAME
                        . '/' . $this->_currentTheme->getId();
                    $asset = $this->_assetRepo->createArbitrary(
                        $identifier,
                        $dirPath,
                        DirectoryList::MEDIA,
                        \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                    );
                    $this->_pageAssets->add($identifier, $asset);
                }
            } catch (\InvalidArgumentException $e) {
                $this->_logger->critical($e);
            }
        }
    }
}
