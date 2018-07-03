<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Controller\Adminhtml\Agreement;

class Edit extends \Magento\CheckoutAgreements\Controller\Adminhtml\Agreement
{
    /**
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $agreementModel = $this->_agreementFactory->create();

        if ($id) {
            $agreementModel->load($id);
            if (!$agreementModel->getId()) {
                $this->messageManager->addError(__('This condition no longer exists.'));
                $this->_redirect('checkout/*/');
                return;
            }
        }

        $data = $this->_session->getAgreementData(true);
        if (!empty($data)) {
            $agreementModel->setData($data);
        }

        $this->_coreRegistry->register('checkout_agreement', $agreementModel);

        $this->_initAction()->_addBreadcrumb(
            $id ? __('Edit Condition') : __('New Condition'),
            $id ? __('Edit Condition') : __('New Condition')
        )->_addContent(
            $this->_view->getLayout()->createBlock(
                \Magento\CheckoutAgreements\Block\Adminhtml\Agreement\Edit::class
            )->setData(
                'action',
                $this->getUrl('checkout/*/save')
            )
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Terms and Conditions'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $agreementModel->getId() ? $agreementModel->getName() : __('New Condition')
        );
        $this->_view->renderLayout();
    }
}
