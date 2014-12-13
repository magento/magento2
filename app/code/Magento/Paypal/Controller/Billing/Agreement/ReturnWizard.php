<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Controller\Billing\Agreement;

class ReturnWizard extends \Magento\Paypal\Controller\Billing\Agreement
{
    /**
     * Wizard return action
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Paypal\Model\Billing\Agreement $agreement */
        $agreement = $this->_objectManager->create('Magento\Paypal\Model\Billing\Agreement');
        $paymentCode = $this->getRequest()->getParam('payment_method');
        $token = $this->getRequest()->getParam('token');
        if ($token && $paymentCode) {
            try {
                $agreement->setStoreId(
                    $this->_objectManager->get('Magento\Store\Model\StoreManager')->getStore()->getId()
                )->setToken(
                    $token
                )->setMethodCode(
                    $paymentCode
                )->setCustomerId(
                    $this->_getSession()->getCustomerId()
                )->place();

                $this->messageManager->addSuccess(
                    __('The billing agreement "%1" has been created.', $agreement->getReferenceId())
                );
                $this->_redirect('*/*/view', ['agreement' => $agreement->getId()]);
                return;
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
                $this->messageManager->addError(__('We couldn\'t finish the billing agreement wizard.'));
            }
            $this->_redirect('*/*/index');
        }
    }
}
