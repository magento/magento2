<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Controller\Adminhtml\Agreement;

/**
 * Class \Magento\CheckoutAgreements\Controller\Adminhtml\Agreement\Edit
 *
 * @since 2.0.0
 */
class Edit extends \Magento\CheckoutAgreements\Controller\Adminhtml\Agreement
{
    /**
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $agreementModel = $this->_objectManager->create(\Magento\CheckoutAgreements\Model\Agreement::class);

        if ($id) {
            $agreementModel->load($id);
            if (!$agreementModel->getId()) {
                $this->messageManager->addError(__('This condition no longer exists.'));
                $this->_redirect('checkout/*/');
                return;
            }
        }

        $data = $this->_objectManager->get(\Magento\Backend\Model\Session::class)->getAgreementData(true);
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
