<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EncryptionKey\Controller\Adminhtml\Crypt\Key;

use Magento\Backend\App\Action\Context;
use Magento\EncryptionKey\Block\Adminhtml\Crypt\Key\Form;
use Magento\EncryptionKey\Controller\Adminhtml\Crypt\Key;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\DeploymentConfig\Writer;

/**
 * Key Index action
 */
class Index extends Key implements HttpGetActionInterface
{
    /**
     * @var Writer
     */
    private $writer;

    /**
     * @param Context $context
     * @param Writer $writer
     */
    public function __construct(
        Context $context,
        Writer $writer
    ) {
        parent::__construct($context);
        $this->writer = $writer;
    }

    /**
     * Render main page with form
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->writer->checkIfWritable()) {
            $this->messageManager->addErrorMessage(__('Deployment configuration file is not writable.'));
        }

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_EncryptionKey::system_crypt_key');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Encryption Key'));

        if (($formBlock = $this->_view->getLayout()->getBlock('crypt.key.form')) &&
            ($data = $this->_session->getFormData(true))) {
            /* @var Form $formBlock */
            $formBlock->setFormData($data);
        }

        $this->_view->renderLayout();
    }
}
