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
     * @var Dto\Response\CreateCustomerAccountResponseBuilder
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

    /** @var \Magento\ObjectManager */
    protected $_objectManager;

    /**
     * Constructor
     *
     * @param CustomerFactory $customerFactory
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Random $mathRandom
     * @param Converter $converter
     * @param Validator $validator
     * @param Dto\Response\CreateCustomerAccountResponseBuilder $createCustomerAccountResponseBuilder
     * @param CustomerServiceInterface $customerService
     * @param CustomerAddressServiceInterface $customerAddressService
     * @param \Magento\ObjectManager $objectManager
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
        Dto\Response\CreateCustomerAccountResponseBuilder $createCustomerAccountResponseBuilder,
        CustomerServiceInterface $customerService,
        CustomerAddressServiceInterface $customerAddressService,
        \Magento\ObjectManager $objectManager
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
        $this->_objectManager = $objectManager;
    }


    /**
     * {@inheritdoc}
     */
    public function sendConfirmation($email)
    {
        $customer = $this->_customerFactory->create();
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $customer->setWebsiteId($websiteId)->loadByEmail($email);
        if (!$customer->getId()) {
            throw (new NoSuchEntityException('email', $email))->addField('websiteId', $websiteId);
        }
        if ($customer->getConfirmation()) {
            $customer->sendNewAccountEmail('confirmation', '', $this->_storeManager->getStore()->getId());
        } else {
            throw new StateException('No confirmation needed.', StateException::INVALID_STATE);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function activateAccount($customerId, $key)
    {
        // load customer by id
        $customer = $this->_converter->getCustomerModel($customerId);

        // check if customer is inactive
        if ($customer->getConfirmation()) {
            if ($customer->getConfirmation() !== $key) {
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
    public function validateResetPasswordLinkToken($customerId, $resetPasswordLinkToken)
    {
        $this->_validateResetPasswordToken($customerId, $resetPasswordLinkToken);
    }

    /**
     * {@inheritdoc}
     */
    public function sendPasswordResetLink($email, $websiteId)
    {
        $customer = $this->_customerFactory->create()
            ->setWebsiteId($websiteId)
            ->loadByEmail($email);

        if (!$customer->getId()) {
            throw (new NoSuchEntityException('email', $email))->addField('websiteId', $websiteId);
        }
        $newPasswordToken = $this->_mathRandom->getUniqueHash();
        $customer->changeResetPasswordLinkToken($newPasswordToken);
        $customer->sendPasswordResetConfirmationEmail();
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
            // We can't pass it through DI because going to get circular dependency
            /** @var \Magento\Customer\Helper\Data $customerHelper */
            $customerHelper = $this->_objectManager->get('Magento\Customer\Helper\Data');
            if ($customerHelper->isCustomerInStore($customerModel->getWebsiteId(), $storeId)) {
                return $this->_createCustomerAccountResponseBuilder->setCustomerId($customerId)
                    ->setStatus('')
                    ->create();
            }
        }
        try {
            $customerId = $this->_customerService->saveCustomer($customer, $password);
        } catch (\Magento\Customer\Exception $e) {
            if ($e->getCode() === CustomerModel::EXCEPTION_EMAIL_EXISTS) {
                throw new StateException('Provided email already exists.', StateException::INPUT_MISMATCH);
            }
            throw $e;
        }

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
     * @param $customerId
     * @param $resetPasswordLinkToken
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
