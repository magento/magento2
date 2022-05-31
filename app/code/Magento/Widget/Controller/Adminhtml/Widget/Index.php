<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

class Index extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Magento_Widget::widget_instance';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Widget\Model\Widget\Config
     */
    protected $_widgetConfig;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Widget\Model\Widget\Config $widgetConfig
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Widget\Model\Widget\Config $widgetConfig,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_widgetConfig = $widgetConfig;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Wysiwyg widget plugin main page
     *
     * @return void
     */
    public function execute()
    {
        // save extra params for widgets insertion form
        $skipped = $this->getRequest()->getParam('skip_widgets', '');
        $skipped = $this->_widgetConfig->decodeWidgetsFromQuery($skipped);
        $this->_coreRegistry->register('skip_widgets', $skipped);

        // phpcs:ignore Magento2.Legacy.ObsoleteResponse
        $this->_view->loadLayout('empty')->renderLayout();
    }
}
