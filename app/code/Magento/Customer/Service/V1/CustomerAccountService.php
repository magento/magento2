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
use Magento\Customer\Service\Entity\V1\Exception;

/**
 * Manipulate Customer Address Entities *
 */
class CustomerAccountService implements CustomerAccountServiceInterface
{

    /** @var \Magento\Customer\Model\CustomerFactory */
    private $_customerFactory;
    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    private $_eventManager = null;

    /** @var \Magento\Core\Model\StoreManagerInterface */
    private $_storeManager;

    /**
     * @var \Magento\Math\Random
     */
    private $_mathRandom;

    /**
     * @var \Magento\Customer\Model\Converter
     */
    private $_converter;

    /**
     * @var \Magento\Customer\Model\Metadata\Validator
     */
    private $_validator;

    /**
     * @var \Magento\Customer\Service\V1\Dto\Response\CreateCustomerAccountResponseBuilder
     */
    private $_createCustomerAccountResponseBuilder;

    /**
     * @var CustomerServiceInterface
     */
    private $_customerService;

    /**
     * @var CustomerAddressServiceInterface
     */
    private $_customerAddressService;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Customer\Service\V1\CustomerMetadataServiceInterface $eavMetadataService
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Math\Random $mathRandom
     * @param \Magento\Customer\Model\Converter $converter
     * @param \Magento\Customer\Model\Metadata\Validator $validator
     * @param \Magento\Customer\Service\V1\Dto\RegionBuilder $regionBuilder
     * @param \Magento\Customer\Service\V1\Dto\AddressBuilder $addressBuilder
     * @param \Magento\Customer\Service\V1\Dto\Response\CreateCustomerAccountResponseBuilder $createCustomerAccountResponseBuilder
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Math\Random $mathRandom,
        \Magento\Customer\Model\Converter $converter,
        \Magento\Customer\Model\Metadata\Validator $validator,
        Dto\Response\CreateCustomerAccountResponseBuilder $createCustomerAccountResponseBuilder,
        CustomerServiceInterface $customerService,
        CustomerAddressServiceInterface $customerAddressService
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_eventManager = $eventManager;
        $this->_storeManager = $storeManager;
        $this->_mathRandom = $mathRandom;
        $this->_converter = $converter;
        $this->_validator = $validator;
        $this->_createCustomerAccountResponseBuilder = $createCustomerAccountResponseBuilder;
        $this->_customerService = $customerService;
        $this->_customerAddressService = $customerAddressService;
    }


    /**
     * @inheritdoc
     */
    public function sendConfirmation($email)
    {
        $customer = $this->_customerFactory->create();
        $customer->setWebsiteId($this->_storeManager->getStore()->getWebsiteId())->loadByEmail($email);
        if (!$customer->getId()) {
            throw new Exception('Wrong email.', Exception::CODE_EMAIL_NOT_FOUND);
        }
        if ($customer->getConfirmation()) {
            $customer->sendNewAccountEmail('confirmation', '', $this->_storeManager->getStore()->getId());
        } else {
            throw new Exception(
                'This email does not require confirmation.',
                Exception::CODE_CONFIRMATION_NOT_NEEDED
            );
        }
    }


    /**
     * @inheritdoc
     */
    public function activateAccount($customerId, $key)
    {
        // load customer by id
        $customer = $this->_converter->getCustomerModel($customerId);

        // check if customer is inactive
        if ($customer->getConfirmation()) {
            if ($customer->getConfirmation() !== $key) {
                throw new \Magento\Core\Exception('Wrong confirmation key.');
            }

            // activate customer
            try {
                $customer->setConfirmation(null);
                $customer->save();
            } catch (\Exception $e) {
                throw new \Magento\Core\Exception('Failed to confirm customer account.');
            }
            $customer->sendNewAccountEmail('confirmed', '', $this->_storeManager->getStore()->getId());
        } else {
            throw new Exception(
                'Customer account is already active.',
                Exception::CODE_ACCT_ALREADY_ACTIVE
            );
        }

        return $this->_converter->createCustomerFromModel($customer);
    }

    /**
     * @inheritdoc
     */
    public function authenticate($username, $password)
    {
        $customerModel = $this->_customerFactory->create();
        $customerModel->setWebsiteId($this->_storeManager->getStore()->getWebsiteId());
        try {
            $customerModel->authenticate($username, $password);
        } catch (\Magento\Core\Exception $e) {
            switch ($e->getCode()) {
                case \Magento\Customer\Model\Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                    $code = Exception::CODE_EMAIL_NOT_CONFIRMED;
                    break;
                case \Magento\Customer\Model\Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                    $code = Exception::CODE_INVALID_EMAIL_OR_PASSWORD;
                    break;
                default:
                    $code = Exception::CODE_UNKNOWN;
            }
            throw new Exception($e->getMessage(), $code, $e);
        }

        $this->_eventManager->dispatch('customer_login', array('customer'=>$customerModel));

        return $this->_converter->createCustomerFromModel($customerModel);
    }

    /**
     * @inheritdoc
     */
    public function validateResetPasswordLinkToken($customerId, $resetPasswordLinkToken)
    {
        $this->_validateResetPasswordToken($customerId, $resetPasswordLinkToken);
    }

    /**
     * @inheritdoc
     */
    public function sendPasswordResetLink($email, $websiteId)
    {
        $customer = $this->_customerFactory->create()
            ->setWebsiteId($websiteId)
            ->loadByEmail($email);

        if (!$customer->getId()) {
            throw new Exception(
                'No customer found for the provided email and website ID.', Exception::CODE_EMAIL_NOT_FOUND);
        }
        try {
            $newPasswordToken = $this->_mathRandom->getUniqueHash();
            $customer->changeResetPasswordLinkToken($newPasswordToken);
            $customer->sendPasswordResetConfirmationEmail();
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage(), Exception::CODE_UNKNOWN, $exception);
        }
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function createAccount(
        Dto\Customer $customer,
        array $addresses,
        $password = null,
        $confirmationBackUrl = '',
        $registeredBackUrl = '',
        $storeId = 0
    ) {
        $customerId = $customer->getCustomerId();
        if ($customerId) {
            $customerModel = $this->_converter->getCustomerModel($customerId);
            if ($customerModel->isInStore($storeId)) {
                return $this->_createCustomerAccountResponseBuilder->setCustomerId($customerId)
                    ->setStatus('')
                    ->create();
            }
        }
        $customerId = $this->_customerService->saveCustomer($customer, $password);
        $this->_customerAddressService->saveAddresses($customerId, $addresses);

        $customerModel = $this->_converter->getCustomerModel($customerId);

        $newLinkToken = $this->_mathRandom->getUniqueHash();
        $customerModel->changeResetPasswordLinkToken($newLinkToken);

        if (!$storeId) {
            $storeId = $this->_storeManager->getStore()->getId();
        }

        if ($customerModel->isConfirmationRequired()) {
            $customerModel->sendNewAccountEmail('confirmation', $confirmationBackUrl, $storeId);
            return $this->_createCustomerAccountResponseBuilder->setCustomerId($customerId)
                ->setStatus(self::ACCOUNT_CONFIRMATION)
                ->create();
        } else {
            $customerModel->sendNewAccountEmail('registered', $registeredBackUrl, $storeId);
            return $this->_createCustomerAccountResponseBuilder->setCustomerId($customerId)
                ->setStatus(self::ACCOUNT_REGISTERED)
                ->create();
        }
    }

    /**
     * @inheritdoc
     */
    public function validateCustomerData(Dto\Customer $customer, array $attributes)
    {
        $customerErrors = $this->_validator->validateData(
            $customer->__toArray(),
            $attributes,
            'customer'
        );

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
     * @param $customerId
     * @param $resetPasswordLinkToken
     * @return \Magento\Customer\Model\Customer
     * @throws Exception
     */
    private function _validateResetPasswordToken($customerId, $resetPasswordLinkToken)
    {
        if (!is_int($customerId)
            || !is_string($resetPasswordLinkToken)
            || empty($resetPasswordLinkToken)
            || empty($customerId)
            || $customerId < 0
        ) {
            throw new Exception('Invalid password reset token.', Exception::CODE_INVALID_RESET_TOKEN);
        }

        $customerModel = $this->_converter->getCustomerModel($customerId);

        $customerToken = $customerModel->getRpToken();
        if (strcmp($customerToken, $resetPasswordLinkToken) !== 0
            || $customerModel->isResetPasswordLinkTokenExpired($customerId)
        ) {
            throw new Exception('Your password reset link has expired.', Exception::CODE_RESET_TOKEN_EXPIRED);
        }

        return $customerModel;
    }
}
