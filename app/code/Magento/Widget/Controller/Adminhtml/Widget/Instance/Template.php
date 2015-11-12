<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

class Template extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{
    /**
     * Templates Chooser Action (Ajax request)
     *
     * @return void
     */
    public function execute()
    {
        /* @var $widgetInstance \Magento\Widget\Model\Widget\Instance */
        $widgetInstance = $this->_initWidgetInstance();
        $block = $this->getRequest()->getParam('block');
        $selected = $this->getRequest()->getParam('selected', null);
        $templateChooser = $this->_view->getLayout()->createBlock(
            'Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\Template'
        )->setSelected(
            $selected
        )->setWidgetTemplates(
            $widgetInstance->getWidgetSupportedTemplatesByContainer($block)
        );
        $this->setBody($templateChooser->toHtml());
    }
}
