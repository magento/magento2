<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Theme;

use Magento\Framework\Model\Exception;
use Magento\Framework\Event\Observer as EventObserver;

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
     * @param \Magento\Framework\View\Design\Theme\ImageFactory $themeImageFactory
     * @param \Magento\Core\Model\Resource\Layout\Update\Collection $updateCollection
     * @param \Magento\Theme\Model\Config\Customization $themeConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventDispatcher
     */
    public function __construct(
        \Magento\Framework\View\Design\Theme\ImageFactory $themeImageFactory,
        \Magento\Core\Model\Resource\Layout\Update\Collection $updateCollection,
        \Magento\Theme\Model\Config\Customization $themeConfig,
        \Magento\Framework\Event\ManagerInterface $eventDispatcher
    ) {
        $this->_themeImageFactory = $themeImageFactory;
        $this->_updateCollection = $updateCollection;
        $this->_themeConfig = $themeConfig;
        $this->_eventDispatcher = $eventDispatcher;
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
        $this->_themeImageFactory->create(array('theme' => $theme))->removePreviewImage();
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
                $this->_eventDispatcher->dispatch('assigned_theme_changed', array('theme' => $this));
            }
        }
    }
}
