<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Model;

use Magento\Framework\Event\Observer as EventObserver;

/**
 * Observer for design editor module
 */
class Observer
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\DesignEditor\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\DesignEditor\Helper\Data $helper
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\DesignEditor\Helper\Data $helper,
        \Magento\Framework\Registry $registry
    ) {
        $this->objectManager = $objectManager;
        $this->helper = $helper;
        $this->registry = $registry;
    }

    /**
     * Remove non-VDE JavaScript assets in design mode
     * Applicable in combination with enabled 'vde_design_mode' flag for 'head' block
     *
     * @param EventObserver $event
     * @return void
     */
    public function clearJs(EventObserver $event)
    {
        /** @var $pageAssets \Magento\Framework\View\Asset\GroupedCollection */
        $pageAssets = $this->objectManager->get('Magento\Framework\View\Asset\GroupedCollection');

        /** @todo Temporary solution for vde mode should be verified with PO and refactored */
        if (!$this->registry->registry('vde_design_mode')) {
            return;
        }

        $vdeAssets = [];
        foreach ($pageAssets->getGroups() as $group) {
            if ($group->getProperty('flag_name') == 'vde_design_mode') {
                $vdeAssets = array_merge($vdeAssets, $group->getAll());
            }
        }

        /** @var $nonVdeAssets \Magento\Framework\View\Asset\AssetInterface[] */
        $nonVdeAssets = array_diff_key($pageAssets->getAll(), $vdeAssets);

        foreach ($nonVdeAssets as $assetId => $asset) {
            if ($asset->getContentType() == 'js') {
                $pageAssets->remove($assetId);
            }
        }
    }

    /**
     * Save quick styles
     *
     * @param EventObserver $event
     * @return void
     */
    public function saveQuickStyles($event)
    {
        /** @var $configuration \Magento\DesignEditor\Model\Editor\Tools\Controls\Configuration */
        $configuration = $event->getData('configuration');
        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        $theme = $event->getData('theme');
        if ($configuration->getControlConfig() instanceof \Magento\DesignEditor\Model\Config\Control\QuickStyles) {
            /** @var $renderer \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer */
            $renderer = $this->objectManager->create('Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer');
            $content = $renderer->render($configuration->getAllControlsData());
            /** @var $cssService \Magento\DesignEditor\Model\Theme\Customization\File\QuickStyleCss */
            $cssService = $this->objectManager->create(
                'Magento\DesignEditor\Model\Theme\Customization\File\QuickStyleCss'
            );
            /** @var $singleFile \Magento\Theme\Model\Theme\SingleFile */
            $singleFile = $this->objectManager->create(
                'Magento\Theme\Model\Theme\SingleFile',
                ['fileService' => $cssService]
            );
            $singleFile->update($theme, $content);
        }
    }

    /**
     * Save time stamp of last change
     *
     * @param EventObserver $event
     * @return void
     */
    public function saveChangeTime($event)
    {
        /** @var $theme \Magento\Core\Model\Theme|null */
        $theme = $event->getTheme() ?: $event->getDataObject()->getTheme();
        /** @var $change \Magento\DesignEditor\Model\Theme\Change */
        $change = $this->objectManager->create('Magento\DesignEditor\Model\Theme\Change');
        if ($theme && $theme->getId()) {
            $change->loadByThemeId($theme->getId());
            $change->setThemeId($theme->getId())->setChangeTime(null);
            $change->save();
        }
    }
}
