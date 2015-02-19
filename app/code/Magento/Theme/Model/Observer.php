<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Model;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Theme\Model\Theme;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Theme Observer model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Observer
{
    /**
     * @var \Magento\Framework\View\Design\Theme\ImageFactory
     */
    protected $themeImageFactory;

    /**
     * @var \Magento\Widget\Model\Resource\Layout\Update\Collection
     */
    protected $updateCollection;

    /**
     * @var \Magento\Theme\Model\Config\Customization
     */
    protected $themeConfig;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventDispatcher;

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
     * @var \Magento\Theme\Model\Theme\Registration
     */
    protected $registration;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\View\Design\Theme\ImageFactory $themeImageFactory
     * @param \Magento\Widget\Model\Resource\Layout\Update\Collection $updateCollection
     * @param \Magento\Theme\Model\Config\Customization $themeConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventDispatcher
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\View\Asset\GroupedCollection $assets
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param Theme\Registration $registration
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\View\Design\Theme\ImageFactory $themeImageFactory,
        \Magento\Widget\Model\Resource\Layout\Update\Collection $updateCollection,
        \Magento\Theme\Model\Config\Customization $themeConfig,
        \Magento\Framework\Event\ManagerInterface $eventDispatcher,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\View\Asset\GroupedCollection $assets,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Theme\Model\Theme\Registration $registration,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->themeImageFactory = $themeImageFactory;
        $this->updateCollection = $updateCollection;
        $this->themeConfig = $themeConfig;
        $this->eventDispatcher = $eventDispatcher;
        $this->currentTheme = $design->getDesignTheme();
        $this->pageAssets = $assets;
        $this->assetRepo = $assetRepo;
        $this->registration = $registration;
        $this->logger = $logger;
    }

    /**
     * Clean related contents to a theme (before save)
     *
     * @param EventObserver $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function cleanThemeRelatedContent(EventObserver $observer)
    {
        $theme = $observer->getEvent()->getData('theme');
        if (!($theme instanceof \Magento\Framework\View\Design\ThemeInterface)) {
            return;
        }
        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        if ($this->themeConfig->isThemeAssignedToStore($theme)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Theme isn\'t deletable.'));
        }
        $this->themeImageFactory->create(['theme' => $theme])->removePreviewImage();
        $this->updateCollection->addThemeFilter($theme->getId())->delete();
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
            if ($this->themeConfig->isThemeAssignedToStore($theme)) {
                $this->eventDispatcher->dispatch('assigned_theme_changed', ['theme' => $theme]);
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
            $this->registration->register($pathPattern);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
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
