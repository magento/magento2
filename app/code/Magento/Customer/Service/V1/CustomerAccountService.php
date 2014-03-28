<?php
/**
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
namespace Magento\Customer\Service\V1;

use Magento\Core\Model\StoreManagerInterface;
use Magento\Customer\Model\Converter;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Metadata\Validator;
use Magento\Customer\Model\Resource\Customer\Collection;
use Magento\Event\ManagerInterface;
use Magento\Exception\InputException;
use Magento\Exception\AuthenticationException;
use Magento\Exception\NoSuchEntityException;
use Magento\Exception\StateException;
use Magento\Mail\Exception as MailException;
use Magento\Math\Random;
use Magento\UrlInterface;
use Magento\Logger;

/**
 * Handle various customer account actions
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerAccountService implements CustomerAccountServiceInterface
{
    /**
     * @var CustomerFactory
     */
    private $_customerFactory;

    /** @var Data\CustomerBuilder */
    private $_customerBuilder;

    /** @var Data\CustomerDetailsBuilder */
    private $_customerDetailsBuilder;

    /** @var Data\SearchResultsBuilder */
    private $_searchResultsBuilder;

    /**
     * Core event manager proxy
     *
     * @var ManagerInterface
     */
    private $_eventManager;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var Random
     */
    private $_mathRandom;

    /**
     * @var Converter
     */
    private $_converter;

    /**
     * @var Validator
     */
    private $_validator;

    /**
     * @var CustomerAddressServiceInterface
     */
    private $_customerAddressService;

    /**
     * @var CustomerMetadataServiceInterface
     */
    private $_customerMetadataService;

    /**
     * @var UrlInterface
     */
    private $_url;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * Constructor
     *
     * @param CustomerFactory $customerFactory
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Random $mathRandom
     * @param Converter $converter
     * @param Validator $validator
     * @param Data\CustomerBuilder $customerBuilder
     * @param Data\CustomerDetailsBuilder $customerDetailsBuilder
     * @param Data\SearchResultsBuilder $searchResultsBuilder,
     * @param CustomerAddressServiceInterface $customerAddressService
     * @param CustomerMetadataServiceInterface $customerMetadataService
     * @param UrlInterface $url
     * @param Logger $logger
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CustomerFactory $customerFactory,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Random $mathRandom,
        Converter $converter,
        Validator $validator,
        Data\CustomerBuilder $customerBuilder,
        Data\CustomerDetailsBuilder $customerDetailsBuilder,
        Data\SearchResultsBuilder $searchResultsBuilder,
        CustomerAddressServiceInterface $customerAddressService,
        CustomerMetadataServiceInterface $customerMetadataService,
        UrlInterface $url,
        Logger $logger
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_eventManager = $eventManager;
        $this->_storeManager = $storeManager;
        $this->_mathRandom = $mathRandom;
        $this->_converter = $converter;
        $this->_validator = $validator;
        $this->_customerBuilder = $customerBuilder;
        $this->_customerDetailsBuilder = $customerDetailsBuilder;
        $this->_searchResultsBuilder = $searchResultsBuilder;
        $this->_customerAddressService = $customerAddressService;
        $this->_customerMetadataService = $customerMetadataService;
        $this->_url = $url;
        $this->_logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function resendConfirmation($email, $websiteId, $redirectUrl = '')
    {
        $customer = $this->_customerFactory->create();
        $customer->setWebsiteId($websiteId)->loadByEmail($email);
        if (!$customer->getId()) {
            throw (new NoSuchEntityException('email', $email))->addField('websiteId', $websiteId);
        }
        if ($customer->getConfirmation()) {
            try {
                $customer->sendNewAccountEmail(
                    self::NEW_ACCOUNT_EMAIL_CONFIRMATION,
                    $redirectUrl,
                    $this->_storeManager->getStore()->getId()
                );
            } catch (MailException $e) {
                // If we are not able to send a new account email, this should be ignored
                $this->_logger->logException($e);
            }
        } else {
            throw new StateException('No confirmation needed.', StateException::INVALID_STATE);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function activateCustomer($customerId, $confirmationKey)
    {
        // load customer by id
        $customer = $this->_converter->getCustomerModel($customerId);

        // check if customer is inactive
        if ($customer->getConfirmation()) {
            if ($customer->getConfirmation() !== $confirmationKey) {
                throw new StateException('Invalid confirmation token', StateException::INPUT_MISMATCH);
            }
            // activate customer
            $customer->setConfirmation(null);
            $customer->save();
            $customer->sendNewAccountEmail('confirmed', '', $this->_storeManager->getStore()->getId());
        } else {
            throw new StateException('Account already active', StateException::INVALID_STATE);
        }

        return $this->_converter->createCustomerFromModel($customer);
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate($username, $password)
    {
        $customerModel = $this->_customerFactory->create();
        $customerModel->setWebsiteId($this->_storeManager->getStore()->getWebsiteId());
        try {
            $customerModel->authenticate($username, $password);
        } catch (\Magento\Model\Exception $e) {
            switch ($e->getCode()) {
                case CustomerModel::EXCEPTION_EMAIL_NOT_CONFIRMED:
                    $code = AuthenticationException::EMAIL_NOT_CONFIRMED;
                    break;
                case CustomerModel::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                    $code = AuthenticationException::INVALID_EMAIL_OR_PASSWORD;
                    break;
                default:
                    $code = AuthenticationException::UNKNOWN;
            }
            throw new AuthenticationException($e->getMessage(), $code, $e);
        }

        $this->_eventManager->dispatch('customer_login', array('customer' => $customerModel));

        return $this->_converter->createCustomerFromModel($customerModel);
    }

    /**
     * {@inheritdoc}
     */
    public function validateResetPasswordLinkToken($customerId, $resetPasswordLinkToken)
    {
        $this->_validateResetPasswordToken($customerId, $resetPasswordLinkToken);
    }

    /**
     * {@inheritdoc}
     */
    public function initiatePasswordReset($email, $websiteId, $template)
    {
        $customer = $this->_customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($email);

        if (!$customer->getId()) {
            throw (new NoSuchEntityException('email', $email))->addField('websiteId', $websiteId);
        }
        $newPasswordToken = $this->_mathRandom->getUniqueHash();
        $customer->changeResetPasswordLinkToken($newPasswordToken);
        $resetUrl = $this->_url->getUrl(
            'customer/account/createPassword',
            array(
                '_query' => array('id' => $customer->getId(), 'token' => $newPasswordToken),
                '_store' => $customer->getStoreId()
            )
        );

        $customer->setResetPasswordUrl($resetUrl);
        try {
            switch ($template) {
                case CustomerAccountServiceInterface::EMAIL_REMINDER:
                    $customer->sendPasswordReminderEmail();
                    break;
                case CustomerAccountServiceInterface::EMAIL_RESET:
                    $customer->sendPasswordResetConfirmationEmail();
                    break;
                default:
                    throw new InputException(__('Invalid email type.'), InputException::INVALID_FIELD_VALUE);
            }
        } catch (MailException $e) {
            // If we are not able to send a reset password email, this should be ignored
            $this->_logger->logException($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resetPassword($customerId, $resetToken, $newPassword)
    {
        $customerModel = $this->_validateResetPasswordToken($customerId, $resetToken);
        $customerModel->setRpToken(null);
        $customerModel->setRpTokenCreatedAt(null);
        $customerModel->setPassword($newPassword);
        $customerModel->save();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmationStatus($customerId)
    {
        $customerModel = $this->_converter->getCustomerModel($customerId);
        if (!$customerModel->getConfirmation()) {
            return CustomerAccountServiceInterface::ACCOUNT_CONFIRMED;
        }
        if ($customerModel->isConfirmationRequired()) {
            return CustomerAccountServiceInterface::ACCOUNT_CONFIRMATION_REQUIRED;
        }
        return CustomerAccountServiceInterface::ACCOUNT_CONFIRMATION_NOT_REQUIRED;
    }

    /**
     * {@inheritdoc}
     */
    public function createAccount(Data\CustomerDetails $customerDetails, $password = null, $redirectUrl = '')
    {
        $customer = $customerDetails->getCustomer();

        // This logic allows an existing customer to be added to a different store.  No new account is created.
        // The plan is to move this logic into a new method called something like 'registerAccountWithStore'
        if ($customer->getId()) {
            $customerModel = $this->_converter->getCustomerModel($customer->getId());
            if ($customerModel->isInStore($customer->getStoreId())) {
                throw new InputException(__('Customer already exists in this store.'));
            }
        }
        // Make sure we have a storeId to associate this customer with.
        if (!$customer->getStoreId()) {
            if ($customer->getWebsiteId()) {
                $storeId = $this->_storeManager->getWebsite($customer->getWebsiteId())->getDefaultStore()->getId();
            } else {
                $storeId = $this->_storeManager->getStore()->getId();
            }
            $customer = $this->_customerBuilder->populate($customer)->setStoreId($storeId)->create();
        }

        try {
            $customerId = $this->saveCustomer($customer, $password);
        } catch (\Magento\Customer\Exception $e) {
            if ($e->getCode() === CustomerModel::EXCEPTION_EMAIL_EXISTS) {
                throw new StateException(
                    __('Customer with the same email already exists in associated website.'),
                    StateException::INPUT_MISMATCH
                );
            }
            throw $e;
        }

        $this->_customerAddressService->saveAddresses($customerId, $customerDetails->getAddresses());

        $customerModel = $this->_converter->getCustomerModel($customerId);

        $newLinkToken = $this->_mathRandom->getUniqueHash();
        $customerModel->changeResetPasswordLinkToken($newLinkToken);

        try {
            if ($customerModel->isConfirmationRequired()) {
                $customerModel->sendNewAccountEmail(
                    self::NEW_ACCOUNT_EMAIL_CONFIRMATION,
                    $redirectUrl,
                    $customer->getStoreId()
                );
            } else {
                $customerModel->sendNewAccountEmail(
                    self::NEW_ACCOUNT_EMAIL_REGISTERED,
                    $redirectUrl,
                    $customer->getStoreId()
                );
            }
        } catch (MailException $e) {
            // If we are not able to send a new account email, this should be ignored
            $this->_logger->logException($e);
        }
        return $this->_converter->createCustomerFromModel($customerModel);
    }

    /**
     * {@inheritdoc}
     */
    public function updateCustomer(Data\CustomerDetails $customerDetails)
    {
        $customer = $customerDetails->getCustomer();
        // Making this call first will ensure the customer already exists.
        $this->getCustomer($customer->getId());
        $this->saveCustomer($customer);

        $addresses = $customerDetails->getAddresses();
        // If $address is null, no changes must made to the list of addresses
        // be careful $addresses != null would be true of $addresses is an empty array
        if ($addresses !== null) {
            $existingAddresses = $this->_customerAddressService->getAddresses($customer->getId());
            /** @var Data\Address[] $deletedAddresses */
            $deletedAddresses = array_udiff(
                $existingAddresses,
                $addresses,
                function (Data\Address $existing, Data\Address $replacement) {
                    return $existing->getId() - $replacement->getId();
                }
            );

            // If $addresses is an empty array, all addresses are removed.
            // array_udiff would return the entire $existing array
            foreach ($deletedAddresses as $address) {
                $this->_customerAddressService->deleteAddress($address->getId());
            }
            $this->_customerAddressService->saveAddresses($customer->getId(), $addresses);
        }
    }

    /**
     * (@inheritdoc)
     */
    public function searchCustomers(Data\SearchCriteria $searchCriteria)
    {
        $this->_searchResultsBuilder->setSearchCriteria($searchCriteria);

        /** @var Collection $collection */
        $collection = $this->_customerFactory->create()->getCollection();
        // This is needed to make sure all the attributes are properly loaded
        foreach ($this->_customerMetadataService->getAllCustomerAttributeMetadata() as $metadata) {
            $collection->addAttributeToSelect($metadata->getAttributeCode());
        }
        // Needed to enable filtering on name as a whole
        $collection->addNameToSelect();
        // Needed to enable filtering based on billing address attributes
        $collection->joinAttribute(
            'billing_postcode',
            'customer_address/postcode',
            'default_billing',
            null,
            'left'
        )->joinAttribute(
            'billing_city',
            'customer_address/city',
            'default_billing',
            null,
            'left'
        )->joinAttribute(
            'billing_telephone',
            'customer_address/telephone',
            'default_billing',
            null,
            'left'
        )->joinAttribute(
            'billing_region',
            'customer_address/region',
            'default_billing',
            null,
            'left'
        )->joinAttribute(
            'billing_country_id',
            'customer_address/country_id',
            'default_billing',
            null,
            'left'
        );
        $this->addFiltersToCollection($searchCriteria->getFilters(), $collection);
        $this->_searchResultsBuilder->setTotalCount($collection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            foreach ($searchCriteria->getSortOrders() as $field => $direction) {
                $collection->addOrder($field, $direction == Data\SearchCriteria::SORT_ASC ? 'ASC' : 'DESC');
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $customersDetails = array();

        /** @var CustomerModel $customerModel */
        foreach ($collection as $customerModel) {
            $customer = $this->_converter->createCustomerFromModel($customerModel);
            $addresses = $this->_customerAddressService->getAddresses($customer->getId());
            $customerDetails = $this->_customerDetailsBuilder->setCustomer(
                $customer
            )->setAddresses(
                $addresses
            )->create();
            $customersDetails[] = $customerDetails;
        }
        $this->_searchResultsBuilder->setItems($customersDetails);
        return $this->_searchResultsBuilder->create();
    }

    /**
     * Adds some filters from a filter group to a collection.
     *
     * @param Data\Search\FilterGroupInterface $filterGroup
     * @param Collection $collection
     * @return void
     * @throws \Magento\Exception\InputException
     */
    protected function addFiltersToCollection(Data\Search\FilterGroupInterface $filterGroup, Collection $collection)
    {
        if (strcasecmp($filterGroup->getGroupType(), 'AND')) {
            throw new InputException('Only AND grouping is currently supported for filters.');
        }

        foreach ($filterGroup->getFilters() as $filter) {
            $this->addFilterToCollection($collection, $filter);
        }

        foreach ($filterGroup->getGroups() as $group) {
            $this->addFilterGroupToCollection($collection, $group);
        }
    }

    /**
     * Helper function that adds a filter to the collection
     *
     * @param Collection $collection
     * @param Data\Filter $filter
     * @return void
     */
    protected function addFilterToCollection(Collection $collection, Data\Filter $filter)
    {
        $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
        $collection->addFieldToFilter($filter->getField(), array($condition => $filter->getValue()));
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param Collection $collection
     * @param Data\Search\FilterGroupInterface $group
     * @return void
     * @throws \Magento\Exception\InputException
     */
    protected function addFilterGroupToCollection(Collection $collection, Data\Search\FilterGroupInterface $group)
    {
        if (strcasecmp($group->getGroupType(), 'OR')) {
            throw new InputException('The only nested groups currently supported for filters are of type OR.');
        }
        $fields = array();
        $conditions = array();
        foreach ($group->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = array('attribute' => $filter->getField(), $condition => $filter->getValue());
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveCustomer(Data\Customer $customer, $password = null)
    {
        $customerModel = $this->_converter->createCustomerModel($customer);

        if ($password) {
            $customerModel->setPassword($password);
        } elseif (!$customerModel->getId()) {
            $customerModel->setPassword($customerModel->generatePassword());
        }

        // Shouldn't we be calling validateCustomerData/Details here?
        $this->_validate($customerModel);

        $customerModel->save();

        return $customerModel->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomer($customerId)
    {
        $customerModel = $this->_converter->getCustomerModel($customerId);
        return $this->_converter->createCustomerFromModel($customerModel);
    }

    /**
     * {@inheritdoc}
     */
    public function changePassword($customerId, $currentPassword, $newPassword)
    {
        $customerModel = $this->_converter->getCustomerModel($customerId);
        if (!$customerModel->validatePassword($currentPassword)) {
            throw new AuthenticationException(
                __("Password doesn't match for this account."),
                AuthenticationException::INVALID_EMAIL_OR_PASSWORD
            );
        }
        $customerModel->setRpToken(null);
        $customerModel->setRpTokenCreatedAt(null);
        $customerModel->setPassword($newPassword);
        $customerModel->save();
        // FIXME: Are we using the proper template here?
        $customerModel->sendPasswordResetNotificationEmail();
    }

    /**
     * {@inheritdoc}
     */
    public function validateCustomerData(Data\Customer $customer, array $attributes = array())
    {
        $customerErrors = $this->_validator->validateData(
            \Magento\Service\DataObjectConverter::toFlatArray($customer),
            $attributes,
            'customer'
        );

        if ($customerErrors !== true) {
            return array('error' => -1, 'message' => implode(', ', $this->_validator->getMessages()));
        }

        $customerModel = $this->_converter->createCustomerModel($customer);

        $result = $customerModel->validate();
        if (true !== $result && is_array($result)) {
            return array('error' => -1, 'message' => implode(', ', $result));
        }
        return true;
    }

    /**
     * Validate customer attribute values.
     *
     * @param CustomerModel $customerModel
     * @throws InputException
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function _validate(CustomerModel $customerModel)
    {
        $exception = new InputException();
        if (!\Zend_Validate::is(trim($customerModel->getFirstname()), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, 'firstname', '');
        }

        if (!\Zend_Validate::is(trim($customerModel->getLastname()), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, 'lastname', '');
        }

        if (!\Zend_Validate::is($customerModel->getEmail(), 'EmailAddress')) {
            $exception->addError(InputException::INVALID_FIELD_VALUE, 'email', $customerModel->getEmail());
        }

        $dob = $this->_getAttributeMetadata('dob');
        if (!is_null($dob) && $dob->isRequired() && '' == trim($customerModel->getDob())) {
            $exception->addError(InputException::REQUIRED_FIELD, 'dob', '');
        }

        $taxvat = $this->_getAttributeMetadata('taxvat');
        if (!is_null($taxvat) && $taxvat->isRequired() && '' == trim($customerModel->getTaxvat())) {
            $exception->addError(InputException::REQUIRED_FIELD, 'taxvat', '');
        }

        $gender = $this->_getAttributeMetadata('gender');
        if (!is_null($gender) && $gender->isRequired() && '' == trim($customerModel->getGender())) {
            $exception->addError(InputException::REQUIRED_FIELD, 'gender', '');
        }

        if ($exception->getErrors()) {
            throw $exception;
        }
    }

    /**
     * Validate the Reset Password Token for a customer.
     *
     * @param int $customerId
     * @param string $resetPasswordLinkToken
     * @return CustomerModel
     * @throws \Magento\Exception\StateException If token is expired or mismatched
     * @throws \Magento\Exception\InputException If token or customer id is invalid
     * @throws \Magento\Exception\NoSuchEntityException If customer doesn't exist
     */
    private function _validateResetPasswordToken($customerId, $resetPasswordLinkToken)
    {
        if (!is_int($customerId) || empty($customerId) || $customerId < 0) {
            throw InputException::create(InputException::INVALID_FIELD_VALUE, 'customerId', $customerId);
        }
        if (!is_string($resetPasswordLinkToken) || empty($resetPasswordLinkToken)) {
            throw InputException::create(
                InputException::INVALID_FIELD_VALUE,
                'resetPasswordLinkToken',
                $resetPasswordLinkToken
            );
        }

        $customerModel = $this->_converter->getCustomerModel($customerId);
        $customerToken = $customerModel->getRpToken();

        if (strcmp($customerToken, $resetPasswordLinkToken) !== 0) {
            throw new StateException('Reset password token mismatch.', StateException::INPUT_MISMATCH);
        } else if ($customerModel->isResetPasswordLinkTokenExpired($customerId)) {
            throw new StateException('Reset password token expired.', StateException::EXPIRED);
        }

        return $customerModel;
    }

    /**
     * @param string $attributeCode
     * @return Data\Eav\AttributeMetadata|null
     */
    private function _getAttributeMetadata($attributeCode)
    {
        try {
            return $this->_customerMetadataService->getCustomerAttributeMetadata($attributeCode);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function canModify($customerId)
    {
        $customerModel = $this->_converter->getCustomerModel($customerId);
        return !$customerModel->isReadonly();
    }

    /**
     * {@inheritdoc}
     */
    public function canDelete($customerId)
    {
        $customerModel = $this->_converter->getCustomerModel($customerId);
        return $customerModel->isDeleteable();
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerDetails($customerId)
    {
        return $this->_customerDetailsBuilder->setCustomer(
            $this->getCustomer($customerId)
        )->setAddresses(
            $this->_customerAddressService->getAddresses($customerId)
        )->create();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteCustomer($customerId)
    {
        $customerModel = $this->_converter->getCustomerModel($customerId);
        $customerModel->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function isEmailAvailable($customerEmail, $websiteId)
    {
        try {
            $this->_converter->getCustomerModelByEmail($customerEmail, $websiteId);
            return false;
        } catch (NoSuchEntityException $e) {
            return true;
        }
    }
}
