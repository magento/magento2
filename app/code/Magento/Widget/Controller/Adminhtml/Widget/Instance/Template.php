<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

/**
 * Class \Magento\Widget\Controller\Adminhtml\Widget\Instance\Template
 *
 * @since 2.0.0
 */
class Template extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{
    /**
     * Templates Chooser Action (Ajax request)
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        /* @var $widgetInstance \Magento\Widget\Model\Widget\Instance */
        $widgetInstance = $this->_initWidgetInstance();
        $block = $this->getRequest()->getParam('block');
        $selected = $this->getRequest()->getParam('selected', null);
        $templateChooser = $this->_view->getLayout()->createBlock(
            \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\Template::class
        )->setSelected(
            $selected
        )->setWidgetTemplates(
            $widgetInstance->getWidgetSupportedTemplatesByContainer($block)
        );
        $this->setBody($templateChooser->toHtml());
    }
}
