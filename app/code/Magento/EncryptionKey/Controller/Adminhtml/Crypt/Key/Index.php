<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\EncryptionKey\Controller\Adminhtml\Crypt\Key;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Key Index action
 */
class Index extends \Magento\EncryptionKey\Controller\Adminhtml\Crypt\Key implements HttpGetActionInterface
{
    /**
     * Render main page with form
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Framework\App\DeploymentConfig\Writer $writer */
        $writer = $this->_objectManager->get(\Magento\Framework\App\DeploymentConfig\Writer::class);
        if (!$writer->checkIfWritable()) {
            $this->messageManager->addError(__('Deployment configuration file is not writable.'));
        }

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_EncryptionKey::system_crypt_key');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Encryption Key'));

        if (($formBlock = $this->_view->getLayout()->getBlock('crypt.key.form')) &&
            ($data = $this->_objectManager->get(\Magento\Backend\Model\Session::class)->getFormData(true))) {
            /* @var \Magento\EncryptionKey\Block\Adminhtml\Crypt\Key\Form $formBlock */
            $formBlock->setFormData($data);
        }

        $this->_view->renderLayout();
    }
}
