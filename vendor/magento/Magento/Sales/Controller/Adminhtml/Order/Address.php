<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Controller\Adminhtml\Order;


class Address extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Edit order address form
     *
     * @return void
     */
    public function execute()
    {
        $addressId = $this->getRequest()->getParam('address_id');
        $address = $this->_objectManager->create('Magento\Sales\Model\Order\Address')->load($addressId);
        if ($address->getId()) {
            $this->_coreRegistry->register('order_address', $address);
            $this->_view->loadLayout();
            // Do not display VAT validation button on edit order address form
            $addressFormContainer = $this->_view->getLayout()->getBlock('sales_order_address.form.container');
            if ($addressFormContainer) {
                $addressFormContainer->getChildBlock('form')->setDisplayVatValidationButton(false);
            }

            $this->_view->renderLayout();
        } else {
            $this->_redirect('sales/*/');
        }
    }
}
