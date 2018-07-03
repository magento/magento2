<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Controller\Adminhtml\Agreement;

class Delete extends \Magento\CheckoutAgreements\Controller\Adminhtml\Agreement
{
    /**
     * @return void
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $repository = $this->_agreementRepository->get($id);
        if (!$repository->getAgreementId()) {
            $this->messageManager->addError(__('This condition no longer exists.'));
            $this->_redirect('checkout/*/');
            return;
        }

        try {
            $repository->delete();
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
