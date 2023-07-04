<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Model\Metadata\Form;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\Exception\AbstractAggregateException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Validator\Exception;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Save customer action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Customer\Controller\Adminhtml\Index implements HttpPostActionInterface
{
    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var SubscriptionManagerInterface
     */
    private $subscriptionManager;

    /**
     * @var AddressRegistry
     */
    private $addressRegistry;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param FileFactory $fileFactory
     * @param CustomerFactory $customerFactory
     * @param AddressFactory $addressFactory
     * @param FormFactory $formFactory
     * @param SubscriberFactory $subscriberFactory
     * @param View $viewHelper
     * @param Random $random
     * @param CustomerRepositoryInterface $customerRepository
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Mapper $addressMapper
     * @param AccountManagementInterface $customerAccountManagement
     * @param AddressRepositoryInterface $addressRepository
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Customer\Model\Customer\Mapper $customerMapper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param ObjectFactory $objectFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param JsonFactory $resultJsonFactory
     * @param SubscriptionManagerInterface $subscriptionManager
     * @param AddressRegistry|null $addressRegistry
     * @param StoreManagerInterface|null $storeManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory,
        FormFactory $formFactory,
        SubscriberFactory $subscriberFactory,
        View $viewHelper,
        Random $random,
        CustomerRepositoryInterface $customerRepository,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Mapper $addressMapper,
        AccountManagementInterface $customerAccountManagement,
        AddressRepositoryInterface $addressRepository,
        CustomerInterfaceFactory $customerDataFactory,
        AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        ObjectFactory $objectFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        LayoutFactory $resultLayoutFactory,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        JsonFactory $resultJsonFactory,
        SubscriptionManagerInterface $subscriptionManager,
        AddressRegistry $addressRegistry = null,
        ?StoreManagerInterface $storeManager = null
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $customerFactory,
            $addressFactory,
            $formFactory,
            $subscriberFactory,
            $viewHelper,
            $random,
            $customerRepository,
            $extensibleDataObjectConverter,
            $addressMapper,
            $customerAccountManagement,
            $addressRepository,
            $customerDataFactory,
            $addressDataFactory,
            $customerMapper,
            $dataObjectProcessor,
            $dataObjectHelper,
            $objectFactory,
            $layoutFactory,
            $resultLayoutFactory,
            $resultPageFactory,
            $resultForwardFactory,
            $resultJsonFactory
        );
        $this->subscriptionManager = $subscriptionManager;
        $this->addressRegistry = $addressRegistry ?: ObjectManager::getInstance()->get(AddressRegistry::class);
        $this->storeManager = $storeManager ?? ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

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
     * @param string $formCode The code of EAV form to take the list of attributes from
     * @param string $entityType entity type for the form
     * @param string[] $additionalAttributes The list of attribute codes to skip filtration for
     * @param string $scope scope of the request
     * @return array
     */
    protected function _extractData(
        $formCode,
        $entityType,
        $additionalAttributes = [],
        $scope = null
    ) {
        $metadataForm = $this->getMetadataForm($entityType, $formCode, $scope);
        $formData = $metadataForm->extractData($this->getRequest(), $scope);
        $formData = $metadataForm->compactData($formData);

        // Initialize additional attributes
        /** @var DataObject $object */
        $object = $this->_objectFactory->create(['data' => $this->getRequest()->getPostValue()]);
        $requestData = $object->getData($scope);
        foreach ($additionalAttributes as $attributeCode) {
            $formData[$attributeCode] = isset($requestData[$attributeCode]) ? $requestData[$attributeCode] : false;
        }

        // Unset unused attributes
        $formAttributes = $metadataForm->getAttributes();
        foreach ($formAttributes as $attribute) {
            /** @var AttributeMetadataInterface $attribute */
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
     * @deprecated 102.0.1 must be removed because addresses are save separately for now
     * @see \Magento\Customer\Controller\Adminhtml\Address\Save
     */
    protected function saveDefaultFlags(array $addressIdList, array &$extractedCustomerData)
    {
        $result = [];
        $extractedCustomerData[CustomerInterface::DEFAULT_BILLING] = null;
        $extractedCustomerData[CustomerInterface::DEFAULT_SHIPPING] = null;
        foreach ($addressIdList as $addressId) {
            $scope = sprintf('address/%s', $addressId);
            $addressData = $this->_extractData(
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
     * @deprecated 102.0.1 addresses are saved separately for now
     * @see \Magento\Customer\Controller\Adminhtml\Address\Save
     */
    protected function _extractCustomerAddressData(array &$extractedCustomerData)
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
     * @return Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $returnToEdit = false;
        $customerId = $this->getCurrentCustomerId();
        $customer = $this->customerDataFactory->create();

        if ($this->getRequest()->getPostValue()) {
            try {
                // optional fields might be set in request for future processing by observers in other modules
                $customerData = $this->_extractCustomerData();

                if ($customerId) {
                    $currentCustomer = $this->_customerRepository->getById($customerId);
                    // No need to validate customer address while editing customer profile
                    $this->disableAddressValidation($currentCustomer);
                    $customerData = array_merge(
                        $this->customerMapper->toFlatArray($currentCustomer),
                        $customerData
                    );
                    $customerData['id'] = $customerId;
                }

                $this->dataObjectHelper->populateWithArray(
                    $customer,
                    $customerData,
                    CustomerInterface::class
                );

                $this->_eventManager->dispatch(
                    'adminhtml_customer_prepare_save',
                    ['customer' => $customer, 'request' => $this->getRequest()]
                );

                if (isset($customerData['sendemail_store_id']) && $customerData['sendemail_store_id'] !== false) {
                    $customer->setStoreId($customerData['sendemail_store_id']);
                    try {
                        $this->customerAccountManagement->validateCustomerStoreIdByWebsiteId($customer);
                    } catch (LocalizedException $exception) {
                        throw new LocalizedException(__("The Store View selected for sending Welcome email from" .
                            " is not related to the customer's associated website."));
                    }
                }

                $storeId = $customer->getStoreId();
                if (empty($storeId)) {
                    $website = $this->storeManager->getWebsite($customer->getWebsiteId());
                    $storeId = current($website->getStoreIds());
                }
                $this->storeManager->setCurrentStore($storeId);

                // Save customer
                if ($customerId) {
                    $this->_customerRepository->save($customer);
                    $this->getEmailNotification()->credentialsChanged($customer, $currentCustomer->getEmail());
                } else {
                    $customer = $this->customerAccountManagement->createAccount($customer);
                    $customerId = $customer->getId();
                }

                $this->updateSubscriptions($customer);

                // After save
                $this->_eventManager->dispatch(
                    'adminhtml_customer_save_after',
                    ['customer' => $customer, 'request' => $this->getRequest()]
                );
                $this->_getSession()->unsCustomerFormData();
                // Done Saving customer, finish save action
                $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);
                $this->messageManager->addSuccessMessage(__('You saved the customer.'));
                $returnToEdit = (bool)$this->getRequest()->getParam('back', false);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Something went wrong while saving the customer.')
                );
                $returnToEdit = false;
            } catch (Exception $exception) {
                $messages = $exception->getMessages();
                if (empty($messages)) {
                    $messages = $exception->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setCustomerFormData($this->retrieveFormattedFormData($customer));
                $returnToEdit = true;
            } catch (AbstractAggregateException $exception) {
                $errors = $exception->getErrors();
                $messages = [];
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setCustomerFormData($this->retrieveFormattedFormData($customer));
                $returnToEdit = true;
            } catch (LocalizedException $exception) {
                $this->_addSessionErrorMessages($exception->getMessage());
                $this->_getSession()->setCustomerFormData($this->retrieveFormattedFormData($customer));
                $returnToEdit = true;
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Something went wrong while saving the customer.')
                );
                $this->_getSession()->setCustomerFormData($this->retrieveFormattedFormData($customer));
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
     * Update customer website subscriptions
     *
     * @param CustomerInterface $customer
     * @return void
     */
    private function updateSubscriptions(CustomerInterface $customer): void
    {
        $subscriptionStatus = (array)$this->getRequest()->getParam('subscription_status');
        $subscriptionStore = (array)$this->getRequest()->getParam('subscription_store');
        if (empty($subscriptionStatus)) {
            return;
        }

        foreach ($subscriptionStatus as $websiteId => $status) {
            $storeId = $subscriptionStore[$websiteId] ?? $customer->getStoreId();
            if ($status) {
                $this->subscriptionManager->subscribeCustomer((int)$customer->getId(), $storeId);
            } else {
                $this->subscriptionManager->unsubscribeCustomer((int)$customer->getId(), $storeId);
            }
        }
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     * @deprecated 100.1.0
     * @see no alternative
     */
    private function getEmailNotification()
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return ObjectManager::getInstance()->get(
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
            $scopeData = $scope !== null ? explode('/', $scope) : [];
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

    /**
     * Disable Customer Address Validation
     *
     * @param CustomerInterface $customer
     * @throws NoSuchEntityException
     */
    private function disableAddressValidation($customer)
    {
        foreach ($customer->getAddresses() as $address) {
            $addressModel = $this->addressRegistry->retrieve($address->getId());
            $addressModel->setShouldIgnoreValidation(true);
        }
    }

    /**
     * Retrieve formatted form data
     *
     * @param CustomerInterface $customer
     * @return array
     */
    private function retrieveFormattedFormData(CustomerInterface $customer): array
    {
        $originalRequestData = $this->getRequest()->getPostValue();
        $customerData = $this->customerMapper->toFlatArray($customer);

        /* Customer data filtration */
        if (isset($originalRequestData['customer'])) {
            $customerData = array_intersect_key($customerData, $originalRequestData['customer']);
            $originalRequestData['customer'] = array_merge($originalRequestData['customer'], $customerData);
        }

        return $originalRequestData;
    }
}
