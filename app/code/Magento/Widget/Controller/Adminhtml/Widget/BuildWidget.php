<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Widget\Model\Widget;

class BuildWidget extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_Widget::widget_instance';

    /**
     * @var Widget
     */
    protected $_widget;

    /**
     * @param Context $context
     * @param Widget $widget
     */
    public function __construct(
        Context $context,
        Widget $widget
    ) {
        $this->_widget = $widget;
        parent::__construct($context);
    }

    /**
     * Format widget pseudo-code for inserting into wysiwyg editor
     *
     * @return void
     */
    public function execute()
    {
        $type = $this->getRequest()->getPost('widget_type');
        $params = $this->getRequest()->getPost('parameters', []);
        $asIs = $this->getRequest()->getPost('as_is');
        $html = $this->_widget->getWidgetDeclaration($type, $params, $asIs);
        $this->getResponse()->setBody($html);
    }
}
