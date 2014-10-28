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
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Customer\Service\V1\Data\Customer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Service\V1\Data\AddressConverter;
use Magento\Framework\Service\ExtensibleDataObjectConverter;

class Edit extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Customer edit action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        $customerId = $this->_initCustomer();
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Customer::customer_manage');

        $customerData = array();
        $customerData['account'] = array();
        $customerData['address'] = array();
        $customer = null;
        $isExistingCustomer = (bool)$customerId;
        if ($isExistingCustomer) {
            try {
                $customer = $this->_customerAccountService->getCustomer($customerId);
                $customerData['account'] = ExtensibleDataObjectConverter::toFlatArray($customer);
                $customerData['account']['id'] = $customerId;
                try {
                    $addresses = $this->_addressService->getAddresses($customerId);
                    foreach ($addresses as $address) {
                        $customerData['address'][$address->getId()] = AddressConverter::toFlatArray($address);
                        $customerData['address'][$address->getId()]['id'] = $address->getId();
                    }
                } catch (NoSuchEntityException $e) {
                    //do nothing
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException($e, __('An error occurred while editing the customer.'));
                $this->_redirect('customer/*/index');
                return;
            }
        }
        $customerData['customer_id'] = $customerId;

        // set entered data if was error when we do save
        $data = $this->_getSession()->getCustomerData(true);

        // restore data from SESSION
        if ($data && (!isset(
                $data['customer_id']
                ) || isset(
                $data['customer_id']
                ) && $data['customer_id'] == $customerId)
        ) {
            $request = clone $this->getRequest();
            $request->setParams($data);

            if (isset($data['account']) && is_array($data['account'])) {
                $customerForm = $this->_formFactory->create(
                    'customer',
                    'adminhtml_customer',
                    $customerData['account'],
                    true
                );
                $formData = $customerForm->extractData($request, 'account');
                $customerData['account'] = $customerForm->restoreData($formData);
                $customer = $this->_customerBuilder->populateWithArray($customerData['account'])->create();
            }

            if (isset($data['address']) && is_array($data['address'])) {
                foreach (array_keys($data['address']) as $addressId) {
                    if ($addressId == '_template_') {
                        continue;
                    }

                    try {
                        $address = $this->_addressService->getAddress($addressId);
                        if (!empty($customerId) && $address->getCustomerId() == $customerId) {
                            $this->_addressBuilder->populate($address);
                        }
                    } catch (NoSuchEntityException $e) {
                        $this->_addressBuilder->setId($addressId);
                    }
                    if (!empty($customerId)) {
                        $this->_addressBuilder->setCustomerId($customerId);
                    }
                    $this->_addressBuilder->setDefaultBilling(
                        !empty($data['account'][Customer::DEFAULT_BILLING]) &&
                        $data['account'][Customer::DEFAULT_BILLING] == $addressId
                    );
                    $this->_addressBuilder->setDefaultShipping(
                        !empty($data['account'][Customer::DEFAULT_SHIPPING]) &&
                        $data['account'][Customer::DEFAULT_SHIPPING] == $addressId
                    );
                    $address = $this->_addressBuilder->create();
                    $requestScope = sprintf('address/%s', $addressId);
                    $addressForm = $this->_formFactory->create(
                        'customer_address',
                        'adminhtml_customer_address',
                        AddressConverter::toFlatArray($address)
                    );
                    $formData = $addressForm->extractData($request, $requestScope);
                    $customerData['address'][$addressId] = $addressForm->restoreData($formData);
                    $customerData['address'][$addressId]['id'] = $addressId;
                }
            }
        }

        $this->_getSession()->setCustomerData($customerData);

        if ($isExistingCustomer) {
            $this->_title->add($this->_viewHelper->getCustomerName($customer));
        } else {
            $this->_title->add(__('New Customer'));
        }
        /**
         * Set active menu item
         */
        $this->_setActiveMenu('Magento_Customer::customer');

        $this->_view->renderLayout();
    }
}
