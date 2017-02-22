<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Customer edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        $customerId = $this->initCurrentCustomer();

        $customerData = [];
        $customerData['account'] = [];
        $customerData['address'] = [];
        $customer = null;
        $isExistingCustomer = (bool)$customerId;
        if ($isExistingCustomer) {
            try {
                $customer = $this->_customerRepository->getById($customerId);
                $customerData['account'] = $this->customerMapper->toFlatArray($customer);
                $customerData['account'][CustomerInterface::ID] = $customerId;
                try {
                    $addresses = $customer->getAddresses();
                    foreach ($addresses as $address) {
                        $customerData['address'][$address->getId()] = $this->addressMapper->toFlatArray($address);
                        $customerData['address'][$address->getId()]['id'] = $address->getId();
                    }
                } catch (NoSuchEntityException $e) {
                    //do nothing
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException($e, __('Something went wrong while editing the customer.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('customer/*/index');
                return $resultRedirect;
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
                $customer = $this->customerDataFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $customer,
                    $customerData['account'],
                    '\Magento\Customer\Api\Data\CustomerInterface'
                );
            }

            if (isset($data['address']) && is_array($data['address'])) {
                foreach (array_keys($data['address']) as $addressId) {
                    if ($addressId == '_template_') {
                        continue;
                    }

                    try {
                        $address = $this->addressRepository->getById($addressId);
                        if (empty($customerId) || $address->getCustomerId() != $customerId) {
                            //reinitialize address data object
                            $address = $this->addressDataFactory->create();
                        }
                    } catch (NoSuchEntityException $e) {
                        $address = $this->addressDataFactory->create();
                        $address->setId($addressId);
                    }
                    if (!empty($customerId)) {
                        $address->setCustomerId($customerId);
                    }
                    $address->setIsDefaultBilling(
                        !empty($data['account'][CustomerInterface::DEFAULT_BILLING]) &&
                        $data['account'][CustomerInterface::DEFAULT_BILLING] == $addressId
                    );
                    $address->setIsDefaultShipping(
                        !empty($data['account'][CustomerInterface::DEFAULT_SHIPPING]) &&
                        $data['account'][CustomerInterface::DEFAULT_SHIPPING] == $addressId
                    );
                    $requestScope = sprintf('address/%s', $addressId);
                    $addressForm = $this->_formFactory->create(
                        'customer_address',
                        'adminhtml_customer_address',
                        $this->addressMapper->toFlatArray($address)
                    );
                    $formData = $addressForm->extractData($request, $requestScope);
                    $customerData['address'][$addressId] = $addressForm->restoreData($formData);
                    $customerData['address'][$addressId][\Magento\Customer\Api\Data\AddressInterface::ID] = $addressId;
                }
            }
        }

        $this->_getSession()->setCustomerData($customerData);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Customer::customer_manage');
        $this->prepareDefaultCustomerTitle($resultPage);
        $resultPage->setActiveMenu('Magento_Customer::customer');
        if ($isExistingCustomer) {
            $resultPage->getConfig()->getTitle()->prepend($this->_viewHelper->getCustomerName($customer));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Customer'));
        }
        return $resultPage;
    }
}
