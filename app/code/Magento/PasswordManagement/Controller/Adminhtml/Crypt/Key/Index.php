<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PasswordManagement\Controller\Adminhtml\Crypt\Key;

class Index extends \Magento\PasswordManagement\Controller\Adminhtml\Crypt\Key
{
    /**
     * Render main page with form
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Framework\App\DeploymentConfig\Writer $writer */
        $writer = $this->_objectManager->get('Magento\Framework\App\DeploymentConfig\Writer');
        if (!$writer->checkIfWritable()) {
            $this->messageManager->addError(__('Deployment configuration file is not writable.'));
        }

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_PasswordManagement::system_crypt_key');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Encryption Key'));

        if (($formBlock = $this->_view->getLayout()->getBlock(
            'pm.crypt.key.form'
        )) && ($data = $this->_objectManager->get(
            'Magento\Backend\Model\Session'
        )->getFormData(
            true
        ))
        ) {
            /* @var \Magento\PasswordManagement\Block\Adminhtml\Crypt\Key\Form $formBlock */
            $formBlock->setFormData($data);
        }

        $this->_view->renderLayout();
    }
}
