<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

class Template extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{
    /**
     * Templates Chooser Action (Ajax request)
     *
     * @return void
     */
    public function execute(): void
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
