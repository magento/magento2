<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->_title->add(__('Newsletter Queue'));

        $this->_coreRegistry->register('current_queue', $this->_objectManager->get('Magento\Newsletter\Model\Queue'));

        $id = $this->getRequest()->getParam('id');
        $templateId = $this->getRequest()->getParam('template_id');

        if ($id) {
            $queue = $this->_coreRegistry->registry('current_queue')->load($id);
        } elseif ($templateId) {
            $template = $this->_objectManager->create('Magento\Newsletter\Model\Template')->load($templateId);
            $queue = $this->_coreRegistry->registry('current_queue')->setTemplateId($template->getId());
        }

        $this->_title->add(__('Edit Queue'));

        $this->_view->loadLayout();

        $this->_setActiveMenu('Magento_Newsletter::newsletter_queue');

        $this->_addBreadcrumb(__('Newsletter Queue'), __('Newsletter Queue'), $this->getUrl('*/*'));
        $this->_addBreadcrumb(__('Edit Queue'), __('Edit Queue'));

        $this->_view->renderLayout();
    }
}
