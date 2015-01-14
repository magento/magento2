<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Model;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Model\Exception;
use Magento\Theme\Model\Theme;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Theme Observer model
 */
class Observer
{
    /**
     * @var \Magento\Framework\View\Design\Theme\ImageFactory
     */
    protected $_themeImageFactory;

    /**
     * @var \Magento\Core\Model\Resource\Layout\Update\Collection
     */
    protected $_updateCollection;

    /**
     * @var \Magento\Theme\Model\Config\Customization
     */
    protected $_themeConfig;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventDispatcher;

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
     * @var \Magento\Theme\Model\Theme\Registration
     */
    protected $_registration;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\View\Design\Theme\ImageFactory $themeImageFactory
     * @param \Magento\Core\Model\Resource\Layout\Update\Collection $updateCollection
     * @param \Magento\Theme\Model\Config\Customization $themeConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventDispatcher
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\View\Asset\GroupedCollection $assets
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $config
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param Theme\Registration $registration
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\View\Design\Theme\ImageFactory $themeImageFactory,
        \Magento\Core\Model\Resource\Layout\Update\Collection $updateCollection,
        \Magento\Theme\Model\Config\Customization $themeConfig,
        \Magento\Framework\Event\ManagerInterface $eventDispatcher,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\View\Asset\GroupedCollection $assets,
        \Magento\Framework\App\Config\ReinitableConfigInterface $config,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Theme\Model\Theme\Registration $registration,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_themeImageFactory = $themeImageFactory;
        $this->_updateCollection = $updateCollection;
        $this->_themeConfig = $themeConfig;
        $this->_eventDispatcher = $eventDispatcher;
        $this->_currentTheme = $design->getDesignTheme();
        $this->_pageAssets = $assets;
        $this->_assetRepo = $assetRepo;
        $this->_registration = $registration;
        $this->_logger = $logger;
    }

    /**
     * Clean related contents to a theme (before save)
     *
     * @param EventObserver $observer
     * @return void
     * @throws Exception
     */
    public function cleanThemeRelatedContent(EventObserver $observer)
    {
        $theme = $observer->getEvent()->getData('theme');
        if ($theme instanceof \Magento\Framework\View\Design\ThemeInterface) {
            return;
        }
        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        if ($this->_themeConfig->isThemeAssignedToStore($theme)) {
            throw new Exception(__('Theme isn\'t deletable.'));
        }
        $this->_themeImageFactory->create(['theme' => $theme])->removePreviewImage();
        $this->_updateCollection->addThemeFilter($theme->getId())->delete();
    }

    /**
     * Check a theme, it's assigned to any of store
     *
     * @param EventObserver $observer
     * @return void
     */
    public function checkThemeIsAssigned(EventObserver $observer)
    {
        $theme = $observer->getEvent()->getData('theme');
        if ($theme instanceof \Magento\Framework\View\Design\ThemeInterface) {
            /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
            if ($this->_themeConfig->isThemeAssignedToStore($theme)) {
                $this->_eventDispatcher->dispatch('assigned_theme_changed', ['theme' => $this]);
            }
        }
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
        /** @var $themeFile \Magento\Theme\Model\Theme\File */
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
