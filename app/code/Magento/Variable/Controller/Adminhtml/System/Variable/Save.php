<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Controller\Adminhtml\System\Variable;

class Save extends \Magento\Variable\Controller\Adminhtml\System\Variable
{
    /**
     * Save Action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $variable = $this->_initVariable();
        $data = $this->getRequest()->getPost('variable');
        $back = $this->getRequest()->getParam('back', false);
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data['variable_id'] = $variable->getId();
            $variable->setData($data);
            try {
                $variable->save();
                $this->messageManager->addSuccess(__('You saved the custom variable.'));
                if ($back) {
                    $resultRedirect->setPath(
                        'adminhtml/*/edit',
                        ['_current' => true, 'variable_id' => $variable->getId()]
                    );
                } else {
                    $resultRedirect->setPath('adminhtml/*/');
                }
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('adminhtml/*/edit', ['_current' => true]);
            }
        }
        return $resultRedirect->setPath('adminhtml/*/');
    }
}
