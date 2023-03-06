<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\Template as ChooserTemplate;
use Magento\Widget\Controller\Adminhtml\Widget\Instance;
use Magento\Widget\Model\Widget\Instance as ModelWidgetInstance;

class Template extends Instance implements HttpPostActionInterface
{
    /**
     * Templates Chooser Action (Ajax request)
     *
     * @return void
     */
    public function execute()
    {
        /* @var $widgetInstance ModelWidgetInstance */
        $widgetInstance = $this->_initWidgetInstance();
        $block = $this->getRequest()->getParam('block', '');
        $selected = $this->getRequest()->getParam('selected', null);
        $templateChooser = $this->_view->getLayout()->createBlock(
            ChooserTemplate::class
        )->setSelected(
            $selected
        )->setWidgetTemplates(
            $widgetInstance->getWidgetSupportedTemplatesByContainer($block)
        );
        $this->setBody($templateChooser->toHtml());
    }
}
