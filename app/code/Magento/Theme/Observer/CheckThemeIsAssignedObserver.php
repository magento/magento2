<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Observer;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\Config\Customization;
use Magento\Theme\Model\Theme;

class CheckThemeIsAssignedObserver implements ObserverInterface
{
    /**
     * @param Customization $themeConfig
     * @param ManagerInterface $eventDispatcher
     */
    public function __construct(
        protected readonly Customization $themeConfig,
        protected readonly ManagerInterface $eventDispatcher
    ) {
    }

    /**
     * Check a theme, it's assigned to any of store
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $theme = $observer->getEvent()->getData('theme');
        if ($theme instanceof ThemeInterface) {
            /** @var ThemeInterface $theme */
            if ($this->themeConfig->isThemeAssignedToStore($theme)) {
                $this->eventDispatcher->dispatch('assigned_theme_changed', ['theme' => $theme]);
            }
        }
    }
}
