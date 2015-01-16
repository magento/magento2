<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml\Import;

class Start extends \Magento\ImportExport\Controller\Adminhtml\Import
{
    /**
     * Start import process action
     *
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            $this->_view->loadLayout(false);

            /** @var $resultBlock \Magento\ImportExport\Block\Adminhtml\Import\Frame\Result */
            $resultBlock = $this->_view->getLayout()->getBlock('import.frame.result');
            /** @var $importModel \Magento\ImportExport\Model\Import */
            $importModel = $this->_objectManager->create('Magento\ImportExport\Model\Import');

            try {
                $importModel->importSource();
                $importModel->invalidateIndex();
                $resultBlock->addAction(
                    'show',
                    'import_validation_container'
                )->addAction(
                    'innerHTML',
                    'import_validation_container_header',
                    __('Status')
                );
            } catch (\Exception $e) {
                $resultBlock->addError($e->getMessage());
                $this->_view->renderLayout();
                return;
            }
            $resultBlock->addAction(
                'hide',
                ['edit_form', 'upload_button', 'messages']
            )->addSuccess(
                __('Import successfully done')
            );
            $this->_view->renderLayout();
        } else {
            $this->_redirect('adminhtml/*/index');
        }
    }
}
