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
                array('edit_form', 'upload_button', 'messages')
            )->addSuccess(
                __('Import successfully done')
            );
            $this->_view->renderLayout();
        } else {
            $this->_redirect('adminhtml/*/index');
        }
    }
}
