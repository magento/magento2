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
 * @category    Magento
 * @package     Magento_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Observer for design editor module
 */
namespace Magento\DesignEditor\Model;

class Observer
{
    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\DesignEditor\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\DesignEditor\Helper\Data $helper
     */
    public function __construct(
        \Magento\ObjectManager $objectManager,
        \Magento\DesignEditor\Helper\Data $helper
    ) {
        $this->_objectManager = $objectManager;
        $this->_helper        = $helper;
    }

    /**
     * Remove non-VDE JavaScript assets in design mode
     * Applicable in combination with enabled 'vde_design_mode' flag for 'head' block
     *
     * @param \Magento\Event\Observer $event
     */
    public function clearJs(\Magento\Event\Observer $event)
    {
        /** @var $layout \Magento\Core\Model\Layout */
        $layout = $event->getEvent()->getLayout();
        $blockHead = $layout->getBlock('head');
        if (!$blockHead || !$blockHead->getData('vde_design_mode')) {
            return;
        }

        /** @var $page \Magento\Core\Model\Page */
        $page = $this->_objectManager->get('Magento\Core\Model\Page');

        /** @var $pageAssets \Magento\Page\Model\Asset\GroupedCollection */
        $pageAssets = $page->getAssets();

        $vdeAssets = array();
        foreach ($pageAssets->getGroups() as $group) {
            if ($group->getProperty('flag_name') == 'vde_design_mode') {
                $vdeAssets = array_merge($vdeAssets, $group->getAll());
            }
        }

        /** @var $nonVdeAssets \Magento\Core\Model\Page\Asset\AssetInterface[] */
        $nonVdeAssets = array_diff_key($pageAssets->getAll(), $vdeAssets);

        foreach ($nonVdeAssets as $assetId => $asset) {
            if ($asset->getContentType() == \Magento\Core\Model\View\Publisher::CONTENT_TYPE_JS) {
                $pageAssets->remove($assetId);
            }
        }
    }

    /**
     * Save quick styles
     *
     * @param \Magento\Event\Observer $event
     */
    public function saveQuickStyles($event)
    {
        /** @var $configuration \Magento\DesignEditor\Model\Editor\Tools\Controls\Configuration */
        $configuration = $event->getData('configuration');
        /** @var $theme \Magento\View\Design\ThemeInterface */
        $theme = $event->getData('theme');
        if ($configuration->getControlConfig() instanceof \Magento\DesignEditor\Model\Config\Control\QuickStyles) {
            /** @var $renderer \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer */
            $renderer = $this->_objectManager->create('Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer');
            $content = $renderer->render($configuration->getAllControlsData());
            /** @var $cssService \Magento\DesignEditor\Model\Theme\Customization\File\QuickStyleCss */
            $cssService = $this->_objectManager->create(
                'Magento\DesignEditor\Model\Theme\Customization\File\QuickStyleCss'
            );
            /** @var $singleFile \Magento\Theme\Model\Theme\SingleFile */
            $singleFile = $this->_objectManager->create('Magento\Theme\Model\Theme\SingleFile',
                array('fileService' => $cssService));
            $singleFile->update($theme, $content);
        }
    }

    /**
     * Save time stamp of last change
     *
     * @param \Magento\Event\Observer $event
     */
    public function saveChangeTime($event)
    {
        /** @var $theme \Magento\Core\Model\Theme|null */
        $theme = $event->getTheme() ?: $event->getDataObject()->getTheme();
        /** @var $change \Magento\DesignEditor\Model\Theme\Change */
        $change = $this->_objectManager->create('Magento\DesignEditor\Model\Theme\Change');
        if ($theme && $theme->getId()) {
            $change->loadByThemeId($theme->getId());
            $change->setThemeId($theme->getId())->setChangeTime(null);
            $change->save();
        }
    }

    /**
     * Determine if the vde specific translation class should be used.
     *
     * @param  \Magento\Event\Observer $observer
     * @return \Magento\DesignEditor\Model\Observer
     */
    public function initializeTranslation(\Magento\Event\Observer $observer)
    {
        if ($this->_helper->isVdeRequest()) {
            // Request is for vde.  Override the translation class.
            $observer->getResult()->setInlineType('Magento\DesignEditor\Model\Translate\InlineVde');
        }
        return $this;
    }
}
