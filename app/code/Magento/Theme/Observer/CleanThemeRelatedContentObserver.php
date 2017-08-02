<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Theme\Model\Theme;

/**
 * Class \Magento\Theme\Observer\CleanThemeRelatedContentObserver
 *
 * @since 2.0.0
 */
class CleanThemeRelatedContentObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\View\Design\Theme\ImageFactory
     * @since 2.0.0
     */
    protected $themeImageFactory;

    /**
     * @var \Magento\Widget\Model\ResourceModel\Layout\Update\Collection
     * @since 2.0.0
     */
    protected $updateCollection;

    /**
     * @var \Magento\Theme\Model\Config\Customization
     * @since 2.0.0
     */
    protected $themeConfig;

    /**
     * @param \Magento\Framework\View\Design\Theme\ImageFactory $themeImageFactory
     * @param \Magento\Widget\Model\ResourceModel\Layout\Update\Collection $updateCollection
     * @param \Magento\Theme\Model\Config\Customization $themeConfig
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Design\Theme\ImageFactory $themeImageFactory,
        \Magento\Widget\Model\ResourceModel\Layout\Update\Collection $updateCollection,
        \Magento\Theme\Model\Config\Customization $themeConfig
    ) {
        $this->themeImageFactory = $themeImageFactory;
        $this->updateCollection = $updateCollection;
        $this->themeConfig = $themeConfig;
    }

    /**
     * Clean related contents to a theme (before save)
     *
     * @param EventObserver $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function execute(EventObserver $observer)
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
}
