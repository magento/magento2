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

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Convert\ConvertArray;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

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

        $customerData = array();
        $customerData['account'] = array();
        $customerData['address'] = array();
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
                $customer = $this->customerDataBuilder->populateWithArray($customerData['account'])->create();
            }

            if (isset($data['address']) && is_array($data['address'])) {
                foreach (array_keys($data['address']) as $addressId) {
                    if ($addressId == '_template_') {
                        continue;
                    }

                    try {
                        $address = $this->addressRepository->getById($addressId);
                        if (!empty($customerId) && $address->getCustomerId() == $customerId) {
                            $this->addressDataBuilder->populateWithArray($this->addressMapper->toFlatArray($address));
                        }
                    } catch (NoSuchEntityException $e) {
                        $this->addressDataBuilder->setId($addressId);
                    }
                    if (!empty($customerId)) {
                        $this->addressDataBuilder->setCustomerId($customerId);
                    }
                    $this->addressDataBuilder->setDefaultBilling(
                        !empty($data['account'][CustomerInterface::DEFAULT_BILLING]) &&
                        $data['account'][CustomerInterface::DEFAULT_BILLING] == $addressId
                    );
                    $this->addressDataBuilder->setDefaultShipping(
                        !empty($data['account'][CustomerInterface::DEFAULT_SHIPPING]) &&
                        $data['account'][CustomerInterface::DEFAULT_SHIPPING] == $addressId
                    );
                    $address = $this->addressDataBuilder->create();
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

        $this->_view->loadLayout();

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
