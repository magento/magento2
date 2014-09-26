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

use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Service\V1\Data\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Service\V1\CustomerMetadataServiceInterface as CustomerMetadata;
use Magento\Customer\Service\V1\AddressMetadataServiceInterface as AddressMetadata;

class Save extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Reformat customer account data to be compatible with customer service interface
     *
     * @return array
     */
    protected function _extractCustomerData()
    {
        $customerData = array();
        if ($this->getRequest()->getPost('account')) {
            $serviceAttributes = array(
                Customer::DEFAULT_BILLING,
                Customer::DEFAULT_SHIPPING,
                'confirmation',
                'sendemail'
            );
            /** @var \Magento\Customer\Helper\Data $customerHelper */
            $customerHelper = $this->_objectManager->get('Magento\Customer\Helper\Data');
            $customerData = $customerHelper->extractCustomerData(
                $this->getRequest(),
                'adminhtml_customer',
                CustomerMetadata::ENTITY_TYPE_CUSTOMER,
                $serviceAttributes,
                'account'
            );
        }

        if (isset($customerData['disable_auto_group_change'])) {
            $customerData['disable_auto_group_change'] = empty($customerData['disable_auto_group_change']) ? '0' : '1';
        }

        return $customerData;
    }

    /**
     * Reformat customer addresses data to be compatible with customer service interface
     *
     * @return array
     */
    protected function _extractCustomerAddressData()
    {
        $addresses = $this->getRequest()->getPost('address');
        $customerData = $this->getRequest()->getPost('account');
        $result = array();
        if ($addresses) {
            if (isset($addresses['_template_'])) {
                unset($addresses['_template_']);
            }

            $addressIdList = array_keys($addresses);
            /** @var \Magento\Customer\Helper\Data $customerHelper */
            $customerHelper = $this->_objectManager->get('Magento\Customer\Helper\Data');
            foreach ($addressIdList as $addressId) {
                $scope = sprintf('address/%s', $addressId);
                $addressData = $customerHelper->extractCustomerData(
                    $this->getRequest(),
                    'adminhtml_customer_address',
                    AddressMetadata::ENTITY_TYPE_ADDRESS,
                    array(),
                    $scope
                );
                if (is_numeric($addressId)) {
                    $addressData['id'] = $addressId;
                }
                // Set default billing and shipping flags to address
                $addressData[Customer::DEFAULT_BILLING] = isset(
                    $customerData[Customer::DEFAULT_BILLING]
                    ) &&
                    $customerData[Customer::DEFAULT_BILLING] &&
                    $customerData[Customer::DEFAULT_BILLING] == $addressId;
                $addressData[Customer::DEFAULT_SHIPPING] = isset(
                    $customerData[Customer::DEFAULT_SHIPPING]
                    ) &&
                    $customerData[Customer::DEFAULT_SHIPPING] &&
                    $customerData[Customer::DEFAULT_SHIPPING] == $addressId;

                $result[] = $addressData;
            }
        }

        return $result;
    }

    /**
     * Save customer action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $returnToEdit = false;
        $customerId = (int)$this->getRequest()->getPost('customer_id');
        $originalRequestData = $this->getRequest()->getPost();
        if ($originalRequestData) {
            try {
                // optional fields might be set in request for future processing by observers in other modules
                $customerData = $this->_extractCustomerData();
                $addressesData = $this->_extractCustomerAddressData();
                $request = $this->getRequest();
                $isExistingCustomer = (bool)$customerId;
                $customerBuilder = $this->_customerBuilder;
                if ($isExistingCustomer) {
                    $savedCustomerData = $this->_customerAccountService->getCustomer($customerId);
                    $customerData = array_merge(
                        \Magento\Framework\Service\ExtensibleDataObjectConverter::toFlatArray($savedCustomerData),
                        $customerData
                    );
                }
                unset($customerData[Customer::DEFAULT_BILLING]);
                unset($customerData[Customer::DEFAULT_SHIPPING]);
                $customerBuilder->populateWithArray($customerData);
                $addresses = array();
                foreach ($addressesData as $addressData) {
                    $addresses[] = $this->_addressBuilder->populateWithArray($addressData)->create();
                }

                $this->_eventManager->dispatch(
                    'adminhtml_customer_prepare_save',
                    array('customer' => $customerBuilder, 'request' => $request)
                );
                $customer = $customerBuilder->create();

                // Save customer
                $customerDetails = $this->_customerDetailsBuilder->setCustomer(
                    $customer
                )->setAddresses($addresses)->create();
                if ($isExistingCustomer) {
                    $this->_customerAccountService->updateCustomer($customerId, $customerDetails);
                } else {
                    $customer = $this->_customerAccountService->createCustomer($customerDetails);
                    $customerId = $customer->getId();
                }

                $isSubscribed = false;
                if ($this->_authorization->isAllowed(null)) {
                    $isSubscribed = $this->getRequest()->getPost('subscription') !== null;
                }
                if ($isSubscribed) {
                    $this->_subscriberFactory->create()->subscribeCustomerById($customerId);
                } else {
                    $this->_subscriberFactory->create()->unsubscribeCustomerById($customerId);
                }

                // After save
                $this->_eventManager->dispatch(
                    'adminhtml_customer_save_after',
                    array('customer' => $customer, 'request' => $request)
                );
                $this->_getSession()->unsCustomerData();
                // Done Saving customer, finish save action
                $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);
                $this->messageManager->addSuccess(__('You saved the customer.'));
                $returnToEdit = (bool)$this->getRequest()->getParam('back', false);
            } catch (\Magento\Framework\Validator\ValidatorException $exception) {
                $this->_addSessionErrorMessages($exception->getMessages());
                $this->_getSession()->setCustomerData($originalRequestData);
                $returnToEdit = true;
            } catch (\Magento\Framework\Model\Exception $exception) {
                $messages = $exception->getMessages(\Magento\Framework\Message\MessageInterface::TYPE_ERROR);
                if (!count($messages)) {
                    $messages = $exception->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setCustomerData($originalRequestData);
                $returnToEdit = true;
            } catch (LocalizedException $exception) {
                $this->_addSessionErrorMessages($exception->getMessage());
                $this->_getSession()->setCustomerData($originalRequestData);
                $returnToEdit = true;
            } catch (\Exception $exception) {
                $this->messageManager->addException($exception, __('An error occurred while saving the customer.'));
                $this->_getSession()->setCustomerData($originalRequestData);
                $returnToEdit = true;
            }
        }
        if ($returnToEdit) {
            if ($customerId) {
                $this->_redirect('customer/*/edit', array('id' => $customerId, '_current' => true));
            } else {
                $this->_redirect('customer/*/new', array('_current' => true));
            }
        } else {
            $this->_redirect('customer/index');
        }
    }
}
