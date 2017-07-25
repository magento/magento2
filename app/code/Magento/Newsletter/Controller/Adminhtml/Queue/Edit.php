<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Queue;

class Edit extends \Magento\Newsletter\Controller\Adminhtml\Queue
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Registry $coreRegistry)
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Edit Newsletter queue
     *
     * @return void
     */
    public function execute()
    {
        $this->_coreRegistry->register('current_queue', $this->_objectManager->get(
            \Magento\Newsletter\Model\Queue::class
        ));

        $id = $this->getRequest()->getParam('id');
        $templateId = $this->getRequest()->getParam('template_id');

        if ($id) {
            $queue = $this->_coreRegistry->registry('current_queue')->load($id);
        } elseif ($templateId) {
            $template = $this->_objectManager->create(\Magento\Newsletter\Model\Template::class)->load($templateId);
            $queue = $this->_coreRegistry->registry('current_queue')->setTemplateId($template->getId());
        }

        $this->_view->loadLayout();

        $this->_setActiveMenu('Magento_Newsletter::newsletter_queue');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Newsletter Queue'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Edit Queue'));

        $this->_addBreadcrumb(__('Newsletter Queue'), __('Newsletter Queue'), $this->getUrl('*/*'));
        $this->_addBreadcrumb(__('Edit Queue'), __('Edit Queue'));

        $this->_view->renderLayout();
    }
}
