<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Theme\Model\Theme;

class CheckThemeIsAssignedObserver implements ObserverInterface
{
    /**
     * @var \Magento\Theme\Model\Config\Customization
     */
    protected $themeConfig;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventDispatcher;

    /**
     * @param \Magento\Theme\Model\Config\Customization $themeConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventDispatcher
     */
    public function __construct(
        \Magento\Theme\Model\Config\Customization $themeConfig,
        \Magento\Framework\Event\ManagerInterface $eventDispatcher
    ) {
        $this->themeConfig = $themeConfig;
        $this->eventDispatcher = $eventDispatcher;
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
        if ($theme instanceof \Magento\Framework\View\Design\ThemeInterface) {
            /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
            if ($this->themeConfig->isThemeAssignedToStore($theme)) {
                $this->eventDispatcher->dispatch('assigned_theme_changed', ['theme' => $theme]);
            }
        }
    }
}
