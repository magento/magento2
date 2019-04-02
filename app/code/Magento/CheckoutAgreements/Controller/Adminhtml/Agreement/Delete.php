<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Controller\Adminhtml\Agreement;

use Magento\Framework\Exception\NotFoundException;

class Delete extends \Magento\CheckoutAgreements\Controller\Adminhtml\Agreement
{
    /**
     * @return void
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            throw new NotFoundException(__('Page not found'));
        }

        $id = (int)$this->getRequest()->getParam('id');
        $model = $this->_objectManager->get(\Magento\CheckoutAgreements\Model\Agreement::class)->load($id);
        if (!$model->getId()) {
            $this->messageManager->addError(__('This condition no longer exists.'));
            $this->_redirect('checkout/*/');
            return;
        }

        try {
            $model->delete();
            $this->messageManager->addSuccess(__('You deleted the condition.'));
            $this->_redirect('checkout/*/');
            return;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Something went wrong  while deleting this condition.'));
        }

        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
    }
}
