<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget;

/**
 * Class \Magento\Widget\Controller\Adminhtml\Widget\BuildWidget
 *
 * @since 2.0.0
 */
class BuildWidget extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Widget\Model\Widget
     * @since 2.0.0
     */
    protected $_widget;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Widget\Model\Widget $widget
     * @since 2.0.0
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
     * @since 2.0.0
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
