<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Registry;
use Magento\Widget\Model\Widget\Config;

class Index extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Magento_Widget::widget_instance';

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Config
     */
    protected $_widgetConfig;

    /**
     * @param Context $context
     * @param Config $widgetConfig
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        Config $widgetConfig,
        Registry $coreRegistry
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
