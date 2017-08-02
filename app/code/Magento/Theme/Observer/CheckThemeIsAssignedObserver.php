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
 * Class \Magento\Theme\Observer\CheckThemeIsAssignedObserver
 *
 * @since 2.0.0
 */
class CheckThemeIsAssignedObserver implements ObserverInterface
{
    /**
     * @var \Magento\Theme\Model\Config\Customization
     * @since 2.0.0
     */
    protected $themeConfig;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $eventDispatcher;

    /**
     * @param \Magento\Theme\Model\Config\Customization $themeConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventDispatcher
     * @since 2.0.0
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
     * @since 2.0.0
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
