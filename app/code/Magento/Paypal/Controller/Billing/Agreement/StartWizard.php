<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Controller\Billing\Agreement;

class StartWizard extends \Magento\Paypal\Controller\Billing\Agreement
{
    /**
     * Wizard start action
     *
     * @return \Zend_Controller_Response_Abstract
     */
    public function execute()
    {
        $agreement = $this->_objectManager->create('Magento\Paypal\Model\Billing\Agreement');
        $paymentCode = $this->getRequest()->getParam('payment_method');
        if ($paymentCode) {
            try {
                $agreement->setStoreId(
                    $this->_objectManager->get('Magento\Store\Model\StoreManager')->getStore()->getId()
                )->setMethodCode(
                    $paymentCode
                )->setReturnUrl(
                    $this->_objectManager->create(
                        'Magento\Framework\UrlInterface'
                    )->getUrl('*/*/returnWizard', ['payment_method' => $paymentCode])
                )->setCancelUrl(
                    $this->_objectManager->create('Magento\Framework\UrlInterface')
                        ->getUrl('*/*/cancelWizard', ['payment_method' => $paymentCode])
                );

                return $this->getResponse()->setRedirect($agreement->initToken());
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
                $this->messageManager->addError(__('We couldn\'t start the billing agreement wizard.'));
            }
        }
        $this->_redirect('*/*/');
    }
}
