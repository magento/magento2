<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Model\Metadata\Form;
use Magento\Framework\Exception\LocalizedException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * Reformat customer account data to be compatible with customer service interface
     *
     * @return array
     */
    protected function _extractCustomerData()
    {
        $customerData = [];
        if ($this->getRequest()->getPost('customer')) {
            $additionalAttributes = [
                CustomerInterface::DEFAULT_BILLING,
                CustomerInterface::DEFAULT_SHIPPING,
                'confirmation',
                'sendemail_store_id',
                'extension_attributes',
            ];

            $customerData = $this->_extractData(
                $this->getRequest(),
                'adminhtml_customer',
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                $additionalAttributes,
                'customer'
            );
        }

        if (isset($customerData['disable_auto_group_change'])) {
            $customerData['disable_auto_group_change'] = (int) filter_var(
                $customerData['disable_auto_group_change'],
                FILTER_VALIDATE_BOOLEAN
            );
        }

        return $customerData;
    }

    /**
     * Perform customer data filtration based on form code and form object
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $formCode The code of EAV form to take the list of attributes from
     * @param string $entityType entity type for the form
     * @param string[] $additionalAttributes The list of attribute codes to skip filtration for
     * @param string $scope scope of the request
     * @param \Magento\Customer\Model\Metadata\Form $metadataForm to use for extraction
     * @return array Filtered customer data
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _extractData(
        \Magento\Framework\App\RequestInterface $request,
        $formCode,
        $entityType,
        $additionalAttributes = [],
        $scope = null,
        \Magento\Customer\Model\Metadata\Form $metadataForm = null
    ) {
        $metadataForm = $metadataForm ? $metadataForm : $this->getMetadataForm($entityType, $formCode, $scope);
        $formData = $metadataForm->extractData($request, $scope);
        $formData = $metadataForm->compactData($formData);

        // Initialize additional attributes
        /** @var \Magento\Framework\DataObject $object */
        $object = $this->_objectFactory->create(['data' => $request->getPostValue()]);
        $requestData = $object->getData($scope);
        foreach ($additionalAttributes as $attributeCode) {
            $formData[$attributeCode] = isset($requestData[$attributeCode]) ? $requestData[$attributeCode] : false;
        }

        // Unset unused attributes
        $formAttributes = $metadataForm->getAttributes();
        foreach ($formAttributes as $attribute) {
            /** @var \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute */
            $attributeCode = $attribute->getAttributeCode();
            if ($attribute->getFrontendInput() != 'boolean'
                && $formData[$attributeCode] === false
            ) {
                unset($formData[$attributeCode]);
            }
        }

        if (empty($formData['extension_attributes'])) {
            unset($formData['extension_attributes']);
        }

        return $formData;
    }

    /**
     * Saves default_billing and default_shipping flags for customer address
     *
     * @param array $addressIdList
     * @param array $extractedCustomerData
     * @return array
     */
    protected function saveDefaultFlags(array $addressIdList, array & $extractedCustomerData)
    {
        $result = [];
        $extractedCustomerData[CustomerInterface::DEFAULT_BILLING] = null;
        $extractedCustomerData[CustomerInterface::DEFAULT_SHIPPING] = null;
        foreach ($addressIdList as $addressId) {
            $scope = sprintf('address/%s', $addressId);
            $addressData = $this->_extractData(
                $this->getRequest(),
                'adminhtml_customer_address',
                AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                ['default_billing', 'default_shipping'],
                $scope
            );

            if (is_numeric($addressId)) {
                $addressData['id'] = $addressId;
            }
            // Set default billing and shipping flags to customer
            if (!empty($addressData['default_billing']) && $addressData['default_billing'] === 'true') {
                $extractedCustomerData[CustomerInterface::DEFAULT_BILLING] = $addressId;
                $addressData['default_billing'] = true;
            } else {
                $addressData['default_billing'] = false;
            }
            if (!empty($addressData['default_shipping']) && $addressData['default_shipping'] === 'true') {
                $extractedCustomerData[CustomerInterface::DEFAULT_SHIPPING] = $addressId;
                $addressData['default_shipping'] = true;
            } else {
                $addressData['default_shipping'] = false;
            }
            $result[] = $addressData;
        }
        return $result;
    }

    /**
     * Reformat customer addresses data to be compatible with customer service interface
     *
     * @param array $extractedCustomerData
     * @return array
     */
    protected function _extractCustomerAddressData(array & $extractedCustomerData)
    {
        $addresses = $this->getRequest()->getPost('address');
        $result = [];
        if (is_array($addresses)) {
            if (isset($addresses['_template_'])) {
                unset($addresses['_template_']);
            }

            $addressIdList = array_keys($addresses);
            $result = $this->saveDefaultFlags($addressIdList, $extractedCustomerData);
        }

        return $result;
    }

    /**
     * Save customer action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $returnToEdit = false;
        $originalRequestData = $this->getRequest()->getPostValue();

        $customerId = $this->getCurrentCustomerId();

        if ($originalRequestData) {
            try {
                // optional fields might be set in request for future processing by observers in other modules
                $customerData = $this->_extractCustomerData();
                $addressesData = $this->_extractCustomerAddressData($customerData);

                if ($customerId) {
                    $currentCustomer = $this->_customerRepository->getById($customerId);
                    $customerData = array_merge(
                        $this->customerMapper->toFlatArray($currentCustomer),
                        $customerData
                    );
                    $customerData['id'] = $customerId;
                }

                /** @var CustomerInterface $customer */
                $customer = $this->customerDataFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $customer,
                    $customerData,
                    \Magento\Customer\Api\Data\CustomerInterface::class
                );
                $addresses = [];
                foreach ($addressesData as $addressData) {
                    $region = isset($addressData['region']) ? $addressData['region'] : null;
                    $regionId = isset($addressData['region_id']) ? $addressData['region_id'] : null;
                    $addressData['region'] = [
                        'region' => $region,
                        'region_id' => $regionId,
                    ];
                    $addressDataObject = $this->addressDataFactory->create();
                    $this->dataObjectHelper->populateWithArray(
                        $addressDataObject,
                        $addressData,
                        \Magento\Customer\Api\Data\AddressInterface::class
                    );
                    $addresses[] = $addressDataObject;
                }

                $this->_eventManager->dispatch(
                    'adminhtml_customer_prepare_save',
                    ['customer' => $customer, 'request' => $this->getRequest()]
                );
                $customer->setAddresses($addresses);
                if (isset($customerData['sendemail_store_id'])) {
                    $customer->setStoreId($customerData['sendemail_store_id']);
                }

                // Save customer
                if ($customerId) {
                    $this->_customerRepository->save($customer);

                    $this->getEmailNotification()->credentialsChanged($customer, $currentCustomer->getEmail());
                } else {
                    $customer = $this->customerAccountManagement->createAccount($customer);
                    $customerId = $customer->getId();
                }

                $isSubscribed = null;
                if ($this->_authorization->isAllowed(null)) {
                    $isSubscribed = $this->getRequest()->getPost('subscription');
                }
                if ($isSubscribed !== null) {
                    if ($isSubscribed !== '0') {
                        $this->_subscriberFactory->create()->subscribeCustomerById($customerId);
                    } else {
                        $this->_subscriberFactory->create()->unsubscribeCustomerById($customerId);
                    }
                }

                // After save
                $this->_eventManager->dispatch(
                    'adminhtml_customer_save_after',
                    ['customer' => $customer, 'request' => $this->getRequest()]
                );
                $this->_getSession()->unsCustomerFormData();
                // Done Saving customer, finish save action
                $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);
                $this->messageManager->addSuccess(__('You saved the customer.'));
                $returnToEdit = (bool)$this->getRequest()->getParam('back', false);
            } catch (\Magento\Framework\Validator\Exception $exception) {
                $messages = $exception->getMessages();
                if (empty($messages)) {
                    $messages = $exception->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setCustomerFormData($originalRequestData);
                $returnToEdit = true;
            } catch (LocalizedException $exception) {
                $this->_addSessionErrorMessages($exception->getMessage());
                $this->_getSession()->setCustomerFormData($originalRequestData);
                $returnToEdit = true;
            } catch (\Exception $exception) {
                $this->messageManager->addException($exception, __('Something went wrong while saving the customer.'));
                $this->_getSession()->setCustomerFormData($originalRequestData);
                $returnToEdit = true;
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($returnToEdit) {
            if ($customerId) {
                $resultRedirect->setPath(
                    'customer/*/edit',
                    ['id' => $customerId, '_current' => true]
                );
            } else {
                $resultRedirect->setPath(
                    'customer/*/new',
                    ['_current' => true]
                );
            }
        } else {
            $resultRedirect->setPath('customer/index');
        }
        return $resultRedirect;
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     * @deprecated
     */
    private function getEmailNotification()
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * Get metadata form
     *
     * @param string $entityType
     * @param string $formCode
     * @param string $scope
     * @return Form
     */
    private function getMetadataForm($entityType, $formCode, $scope)
    {
        $attributeValues = [];

        if ($entityType == CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER) {
            $customerId = $this->getCurrentCustomerId();
            if ($customerId) {
                $customer = $this->_customerRepository->getById($customerId);
                $attributeValues = $this->customerMapper->toFlatArray($customer);
            }
        }

        if ($entityType == AddressMetadataInterface::ENTITY_TYPE_ADDRESS) {
            $scopeData = explode('/', $scope);
            if (isset($scopeData[1]) && is_numeric($scopeData[1])) {
                $customerAddress = $this->addressRepository->getById($scopeData[1]);
                $attributeValues = $this->addressMapper->toFlatArray($customerAddress);
            }
        }

        $metadataForm = $this->_formFactory->create(
            $entityType,
            $formCode,
            $attributeValues,
            false,
            Form::DONT_IGNORE_INVISIBLE
        );

        return $metadataForm;
    }

    /**
     * Retrieve current customer ID
     *
     * @return int
     */
    private function getCurrentCustomerId()
    {
        $originalRequestData = $this->getRequest()->getPostValue(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);

        $customerId = isset($originalRequestData['entity_id'])
            ? $originalRequestData['entity_id']
            : null;

        return $customerId;
    }
}
