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
use Magento\Event\ManagerInterface;
use Magento\Exception\InputException;
use Magento\Exception\AuthenticationException;
use Magento\Exception\NoSuchEntityException;
use Magento\Exception\StateException;
use Magento\Math\Random;
use Magento\UrlInterface;

/**
 *  Handle various customer account actions
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerAccountService implements CustomerAccountServiceInterface
{
    /** @var CustomerFactory */
    private $_customerFactory;

    /**
     * Core event manager proxy
     *
     * @var ManagerInterface
     */
    private $_eventManager;

    /** @var StoreManagerInterface */
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
     * @var CustomerServiceInterface
     */
    private $_customerService;

    /**
     * @var CustomerAddressServiceInterface
     */
    private $_customerAddressService;

    /**
     * @var UrlInterface
     */
    private $_url;

    /**
     * Constructor
     *
     * @param CustomerFactory $customerFactory
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Random $mathRandom
     * @param Converter $converter
     * @param Validator $validator
     * @param Dto\CustomerBuilder $customerBuilder
     * @param CustomerServiceInterface $customerService
     * @param CustomerAddressServiceInterface $customerAddressService
     * @param UrlInterface $url
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
        Dto\CustomerBuilder $customerBuilder,
        CustomerServiceInterface $customerService,
        CustomerAddressServiceInterface $customerAddressService,
        UrlInterface $url
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_eventManager = $eventManager;
        $this->_storeManager = $storeManager;
        $this->_mathRandom = $mathRandom;
        $this->_converter = $converter;
        $this->_validator = $validator;
        $this->_customerBuilder = $customerBuilder;
        $this->_customerService = $customerService;
        $this->_customerAddressService = $customerAddressService;
        $this->_url = $url;
    }


    /**
     * {@inheritdoc}
     */
    public function sendConfirmation($email, $redirectUrl = '')
    {
        $customer = $this->_customerFactory->create();
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $customer->setWebsiteId($websiteId)->loadByEmail($email);
        if (!$customer->getId()) {
            throw (new NoSuchEntityException('email', $email))->addField('websiteId', $websiteId);
        }
        if ($customer->getConfirmation()) {
            $customer->sendNewAccountEmail(self::NEW_ACCOUNT_EMAIL_CONFIRMATION, $redirectUrl,
                $this->_storeManager->getStore()->getId());
        } else {
            throw new StateException('No confirmation needed.', StateException::INVALID_STATE);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function activateAccount($customerId)
    {
        // load customer by id
        $customer = $this->_converter->getCustomerModel($customerId);

        // check if customer is inactive
        if (!$customer->getConfirmation()) {
            throw new StateException('Account already active', StateException::INVALID_STATE);
        }

        // activate customer
        $customer->setConfirmation(null);
        $customer->save();
        $customer->sendNewAccountEmail(self::NEW_ACCOUNT_EMAIL_CONFIRMED, '',
            $this->_storeManager->getStore()->getId());

        return $this->_converter->createCustomerFromModel($customer);
    }

    /**
     * {@inheritdoc}
     */
    public function validateAccountConfirmationKey($customerId, $confirmationKey)
    {
        // load customer by id
        $customer = $this->_converter->getCustomerModel($customerId);

        // check if customer is inactive
        if (!$customer->getConfirmation()) {
            throw new StateException('Account already active', StateException::INVALID_STATE);
        } elseif ($customer->getConfirmation() !== $confirmationKey) {
            throw new StateException('Invalid confirmation token', StateException::INPUT_MISMATCH);
        }
        return true;
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
        } catch (\Magento\Core\Exception $e) {
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

        $this->_eventManager->dispatch('customer_login', array('customer'=>$customerModel));

        return $this->_converter->createCustomerFromModel($customerModel);
    }

    /**
     * {@inheritdoc}
     */
    public function validatePassword($customerId, $password)
    {
        $customerModel = $this->_converter->getCustomerModel($customerId);
        if (!$customerModel->validatePassword($password)) {
            throw new AuthenticationException(__("Password doesn't match for this account."),
                AuthenticationException::INVALID_EMAIL_OR_PASSWORD);
        }
        return true;
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
    public function sendPasswordResetLink($email, $websiteId, $template)
    {
        $customer = $this->_customerFactory->create()
            ->setWebsiteId($websiteId)
            ->loadByEmail($email);

        if (!$customer->getId()) {
            throw (new NoSuchEntityException('email', $email))->addField('websiteId', $websiteId);
        }
        $newPasswordToken = $this->_mathRandom->getUniqueHash();
        $customer->changeResetPasswordLinkToken($newPasswordToken);
        $resetUrl = $this->_url
            ->getUrl(
                'customer/account/createPassword',
                [
                    '_query' => array('id' => $customer->getId(), 'token' => $newPasswordToken),
                    '_store' => $customer->getStoreId()
                ]
            );

        $customer->setResetPasswordUrl($resetUrl);
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
    }

    /**
     * {@inheritdoc}
     */
    public function resetPassword($customerId, $password, $resetToken)
    {
        $customerModel = $this->_validateResetPasswordToken($customerId, $resetToken);
        $customerModel->setRpToken(null);
        $customerModel->setRpTokenCreatedAt(null);
        $customerModel->setPassword($password);
        $customerModel->save();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmationStatus($customerId)
    {
        $customerModel= $this->_converter->getCustomerModel($customerId);
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
    public function createAccount(Dto\Customer $customer, array $addresses, $password = null, $redirectUrl = '')
    {
        // This logic allows an existing customer to be added to a different store.  No new account is created.
        // The plan is to move this logic into a new method called something like 'registerAccountWithStore'
        if ($customer->getCustomerId()) {
            $customerModel = $this->_converter->getCustomerModel($customer->getCustomerId());
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
            $customer = $this->_customerBuilder->populate($customer)
                ->setStoreId($storeId)
                ->create();
        }

        try {
            $customerId = $this->_customerService->saveCustomer($customer, $password);
        } catch (\Magento\Customer\Exception $e) {
            if ($e->getCode() === CustomerModel::EXCEPTION_EMAIL_EXISTS) {
                throw new StateException(__('Customer with the same email already exists in associated website.'),
                    StateException::INPUT_MISMATCH);
            }
            throw $e;
        }

        $this->_customerAddressService->saveAddresses($customerId, $addresses);

        $customerModel = $this->_converter->getCustomerModel($customerId);

        $newLinkToken = $this->_mathRandom->getUniqueHash();
        $customerModel->changeResetPasswordLinkToken($newLinkToken);

        if ($customerModel->isConfirmationRequired()) {
            $customerModel->sendNewAccountEmail(self::NEW_ACCOUNT_EMAIL_CONFIRMATION, $redirectUrl,
                $customer->getStoreId());
        } else {
            $customerModel->sendNewAccountEmail(self::NEW_ACCOUNT_EMAIL_REGISTERED, $redirectUrl,
                $customer->getStoreId());
        }
        return $this->_converter->createCustomerFromModel($customerModel);
    }

    /**
     * {@inheritdoc}
     */
    public function updateAccount(Dto\Customer $customer, array $addresses = null)
    {
        // Making this call first will ensure the customer already exists.
        $this->_customerService->getCustomer($customer->getCustomerId());
        $this->_customerService->saveCustomer($customer);

        if ($addresses != null) {
            $existingAddresses = $this->_customerAddressService->getAddresses($customer->getCustomerId());
            /** @var Dto\Address[] $deletedAddresses */
            $deletedAddresses = array_udiff($existingAddresses, $addresses,
                function (Dto\Address $existing, Dto\Address $replacement) {
                    return $existing->getId() - $replacement->getId();
                }
            );
            foreach ($deletedAddresses as $address) {
                $this->_customerAddressService->deleteAddress($address->getId());
            }
            $this->_customerAddressService->saveAddresses($customer->getCustomerId(), $addresses);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function changePassword($customerId, $newPassword)
    {
        $customerModel = $this->_converter->getCustomerModel($customerId);
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
    public function validateCustomerData(Dto\Customer $customer, array $attributes)
    {
        $customerErrors = $this->_validator->validateData(
            $customer->__toArray(),
            $attributes,
            'customer'
        );

        // FIXME: $customerErrors is a boolean but we are treating it as an array here
        if ($customerErrors !== true) {
            return array(
                'error'     => -1,
                'message'   => implode(', ', $customerErrors)
            );
        }

        $customerModel = $this->_converter->createCustomerModel($customer);

        $result = $customerModel->validate();
        if (true !== $result && is_array($result)) {
            return array(
                'error'   => -1,
                'message' => implode(', ', $result)
            );
        }
        return true;
    }

    /**
     * Validate the Reset Password Token for a customer.
     *
     * @param int $customerId
     * @param string $resetPasswordLinkToken
     * @return CustomerModel
     * @throws \Magento\Exception\StateException if token is expired or mismatched
     * @throws \Magento\Exception\InputException if token or customer id is invalid
     * @throws \Magento\Exception\NoSuchEntityException if customer doesn't exist
     */
    private function _validateResetPasswordToken($customerId, $resetPasswordLinkToken)
    {
        if (!is_int($customerId) || empty($customerId) || $customerId < 0) {
            throw InputException::create(
                InputException::INVALID_FIELD_VALUE,
                'customerId',
                $customerId
            );
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
}
