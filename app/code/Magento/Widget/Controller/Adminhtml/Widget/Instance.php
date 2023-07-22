<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml Manage Widgets Instance Controller
 */
namespace Magento\Widget\Controller\Adminhtml\Widget;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Math\Random;
use Magento\Framework\Registry;
use Magento\Framework\Translate\InlineInterface;
use Magento\Widget\Model\Widget\Instance as ModelWidgetInstance;
use Magento\Widget\Model\Widget\InstanceFactory;
use Psr\Log\LoggerInterface;

abstract class Instance extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Widget::widget_instance';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var InstanceFactory
     */
    protected $_widgetFactory;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var InlineInterface
     */
    protected $_translateInline;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param InstanceFactory $widgetFactory
     * @param LoggerInterface $logger
     * @param Random $mathRandom
     * @param InlineInterface $translateInline
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        InstanceFactory $widgetFactory,
        LoggerInterface $logger,
        protected readonly Random $mathRandom,
        InlineInterface $translateInline
    ) {
        $this->_translateInline = $translateInline;
        $this->_coreRegistry = $coreRegistry;
        $this->_widgetFactory = $widgetFactory;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /**
     * Load layout, set active menu and breadcrumbs
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_Widget::cms_widget_instance'
        )->_addBreadcrumb(
            __('CMS'),
            __('CMS')
        )->_addBreadcrumb(
            __('Manage Widget Instances'),
            __('Manage Widget Instances')
        );
        return $this;
    }

    /**
     * Init widget instance object and set it to registry
     *
     * @return ModelWidgetInstance|boolean
     */
    protected function _initWidgetInstance()
    {
        /** @var $widgetInstance ModelWidgetInstance */
        $widgetInstance = $this->_widgetFactory->create();

        $code = $this->getRequest()->getParam('code', null);
        $instanceId = $this->getRequest()->getParam('instance_id', null);
        if ($instanceId) {
            $widgetInstance->load($instanceId)->setCode($code);
            if (!$widgetInstance->getId()) {
                $this->messageManager->addError(__('Please specify a correct widget.'));
                return false;
            }
        } else {
            // Widget id was not provided on the query-string.  Locate the widget instance
            // type (namespace\classname) based upon the widget code (aka, widget id).
            $themeId = $this->getRequest()->getParam('theme_id', null);
            $type = $code != null ? $widgetInstance->getWidgetReference('code', $code, 'type') : null;
            $widgetInstance->setType($type)->setCode($code)->setThemeId($themeId);
        }
        $this->_coreRegistry->register('current_widget_instance', $widgetInstance);
        return $widgetInstance;
    }

    /**
     * Set body to response
     *
     * @param string $body
     * @return void
     */
    protected function setBody($body)
    {
        $this->_translateInline->processResponseBody($body);

        $this->getResponse()->setBody($body);
    }
}
