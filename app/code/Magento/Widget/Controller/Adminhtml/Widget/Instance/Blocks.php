<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

class Blocks extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{
    /**
     * Render page containers
     *
     * @return void
     */
    public function renderPageContainers()
    {
        /* @var $widgetInstance \Magento\Widget\Model\Widget\Instance */
        $widgetInstance = $this->_initWidgetInstance();
        $layout = $this->getRequest()->getParam('layout');
        $selected = $this->getRequest()->getParam('selected', null);
        $blocksChooser = $this->_view->getLayout()->createBlock(
            \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\Container::class
        )->setValue(
            $selected
        )->setArea(
            $widgetInstance->getArea()
        )->setTheme(
            $widgetInstance->getThemeId()
        )->setLayoutHandle(
            $layout
        )->setAllowedContainers(
            $widgetInstance->getWidgetSupportedContainers()
        );
        $this->setBody($blocksChooser->toHtml());
    }

    /**
     * Blocks Action (Ajax request)
     *
     * @return void
     */
    public function execute()
    {
        $this->_objectManager->get(
            \Magento\Framework\App\State::class
        )->emulateAreaCode(
            'frontend',
            [$this, 'renderPageContainers']
        );
    }
}
