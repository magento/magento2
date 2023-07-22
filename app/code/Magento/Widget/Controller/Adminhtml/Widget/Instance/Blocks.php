<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

use Magento\Framework\App\State;
use Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\Container;
use Magento\Widget\Model\Widget\Instance;

class Blocks extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{
    /**
     * Render page containers
     *
     * @return void
     */
    public function renderPageContainers()
    {
        /* @var $widgetInstance Instance */
        $widgetInstance = $this->_initWidgetInstance();
        $layout = $this->getRequest()->getParam('layout');
        $selected = $this->getRequest()->getParam('selected', null);
        $blocksChooser = $this->_view->getLayout()->createBlock(
            Container::class
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
            State::class
        )->emulateAreaCode(
            'frontend',
            [$this, 'renderPageContainers']
        );
    }
}
