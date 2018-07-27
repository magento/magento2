<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Controller\Adminhtml\Email;

/**
 * System Template admin controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Template extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Email::template';

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
     * Load email template from request
     *
     * @param string $idFieldName
     * @return \Magento\Email\Model\BackendTemplate $model
     */
    protected function _initTemplate($idFieldName = 'template_id')
    {
        $id = (int)$this->getRequest()->getParam($idFieldName);
        $model = $this->_objectManager->create('Magento\Email\Model\BackendTemplate');
        if ($id) {
            $model->load($id);
        }
        if (!$this->_coreRegistry->registry('email_template')) {
            $this->_coreRegistry->register('email_template', $model);
        }
        if (!$this->_coreRegistry->registry('current_email_template')) {
            $this->_coreRegistry->register('current_email_template', $model);
        }
        return $model;
    }
}
