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
namespace Magento\CheckoutAgreements\Controller\Adminhtml\Agreement;

class Edit extends \Magento\CheckoutAgreements\Controller\Adminhtml\Agreement
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_title->add(__('Terms and Conditions'));

        $id = $this->getRequest()->getParam('id');
        $agreementModel = $this->_objectManager->create('Magento\CheckoutAgreements\Model\Agreement');

        if ($id) {
            $agreementModel->load($id);
            if (!$agreementModel->getId()) {
                $this->messageManager->addError(__('This condition no longer exists.'));
                $this->_redirect('checkout/*/');
                return;
            }
        }

        $this->_title->add($agreementModel->getId() ? $agreementModel->getName() : __('New Condition'));

        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getAgreementData(true);
        if (!empty($data)) {
            $agreementModel->setData($data);
        }

        $this->_coreRegistry->register('checkout_agreement', $agreementModel);

        $this->_initAction()->_addBreadcrumb(
            $id ? __('Edit Condition') : __('New Condition'),
            $id ? __('Edit Condition') : __('New Condition')
        )->_addContent(
            $this->_view->getLayout()->createBlock(
                'Magento\CheckoutAgreements\Block\Adminhtml\Agreement\Edit'
            )->setData(
                'action',
                $this->getUrl('checkout/*/save')
            )
        );
        $this->_view->renderLayout();
    }
}
