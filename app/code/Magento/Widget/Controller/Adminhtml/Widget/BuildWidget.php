<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget;

use Magento\Framework\App\ObjectManager;

class BuildWidget extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_Widget::widget_instance';

    /**
     * @var \Magento\Widget\Model\Widget
     */
    protected $_widget;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Widget\Model\Widget $widget
     * @param \Magento\Framework\Serialize\SerializerInterface|null $serializer
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Widget\Model\Widget $widget,
        \Magento\Framework\Serialize\SerializerInterface $serializer = null
    ) {
        $this->_widget = $widget;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(
            \Magento\Framework\Serialize\SerializerInterface::class
        );
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
                $this->serializer->serialize(['error' => true, 'message' => 'Invalid request'])
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
