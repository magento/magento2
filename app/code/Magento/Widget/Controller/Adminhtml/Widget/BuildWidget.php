<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget;

class BuildWidget extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Widget::widget_instance';

    /**
     * @var \Magento\Widget\Model\Widget
     */
    protected $_widget;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Widget\Model\Widget $widget
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Widget\Model\Widget $widget
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
        if (!$this->getRequest()->isPost()) {
            $this->getResponse()->representJson(
                \json_encode(['error' => true, 'message' => 'Invalid request'])
            );
            return;
        }

        $type = $this->getRequest()->getPost('widget_type');
        $params = $this->getRequest()->getPost('parameters', []);
        $asIs = $this->getRequest()->getPost('as_is');
        $html = $this->_widget->getWidgetDeclaration($type, $params, $asIs);
        $this->getResponse()->setBody($html);
    }
}
