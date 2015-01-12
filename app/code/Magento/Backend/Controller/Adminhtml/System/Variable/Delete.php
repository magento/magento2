<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Variable;

class Delete extends \Magento\Backend\Controller\Adminhtml\System\Variable
{
    /**
     * Delete Action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $variable = $this->_initVariable();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($variable->getId()) {
            try {
                $variable->delete();
                $this->messageManager->addSuccess(__('You deleted the custom variable.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('adminhtml/*/edit', ['_current' => true]);
            }
        }
        return $resultRedirect->setPath('adminhtml/*/');
    }
}
