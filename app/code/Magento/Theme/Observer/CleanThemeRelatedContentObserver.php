<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Design\Theme\ImageFactory;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\Config\Customization;
use Magento\Theme\Model\Theme;
use Magento\Widget\Model\ResourceModel\Layout\Update\Collection;

class CleanThemeRelatedContentObserver implements ObserverInterface
{
    /**
     * @param ImageFactory $themeImageFactory
     * @param Collection $updateCollection
     * @param Customization $themeConfig
     */
    public function __construct(
        protected readonly ImageFactory $themeImageFactory,
        protected readonly Collection $updateCollection,
        protected readonly Customization $themeConfig
    ) {
    }

    /**
     * Clean related contents to a theme (before save)
     *
     * @param EventObserver $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $theme = $observer->getEvent()->getData('theme');
        if (!($theme instanceof ThemeInterface)) {
            return;
        }
        /** @var $theme ThemeInterface */
        if ($this->themeConfig->isThemeAssignedToStore($theme)) {
            throw new LocalizedException(__('Theme isn\'t deletable.'));
        }
        $this->themeImageFactory->create(['theme' => $theme])->removePreviewImage();
        $this->updateCollection->addThemeFilter($theme->getId())->delete();
    }
}
