<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Helper;

use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Backend\App\ConfigInterface;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Event\ManagerInterface;

/**
 * Customer helper for account management.
 */
class AccountManagement extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Configuration path to customer lockout threshold
     */
    const LOCKOUT_THRESHOLD_PATH = 'customer/password/lockout_threshold';

    /**
     * Configuration path to customer max login failures number
     */
    const MAX_FAILURES_PATH = 'customer/password/lockout_failures';

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * Backend configuration interface
     *
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $backendConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var Encryptor
     */
    protected $encryptor;

    /**
     * @var ManagerInterface
     */
    //protected $eventManager;

    /**
     * AccountManagement constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param CustomerRegistry $customerRegistry
     * @param ConfigInterface $backendConfig
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param Encryptor $encryptor
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CustomerRegistry $customerRegistry,
        ConfigInterface $backendConfig,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        Encryptor $encryptor,
        ManagerInterface $eventManager
    ) {
        parent::__construct($context);
        $this->customerRegistry = $customerRegistry;
        $this->backendConfig = $backendConfig;
        $this->dateTime = $dateTime;
        $this->encryptor = $encryptor;
        //$this->eventManager = $eventManager;
    }

    /**
     * Processes customer lockout data
     *
     * @param int $customerId
     * @return void
     */
    public function processCustomerLockoutData($customerId)
    {
        $now = new \DateTime();
        $lockThreshold = $this->getLockThreshold();
        $maxFailures =  $this->getMaxFailures();
        $customerSecure = $this->customerRegistry->retrieveSecureData($customerId);

        if (!($lockThreshold && $maxFailures)) {
            return;
        }
        $failuresNum = (int)$customerSecure->getFailuresNum() + 1;

        $firstFailureDate = $customerSecure->getFirstFailure();
        if ($firstFailureDate) {
            $firstFailureDate = new \DateTime($firstFailureDate);
        }

        $lockThreshInterval = new \DateInterval('PT' . $lockThreshold . 'S');
        // set first failure date when this is first failure or last first failure expired
        if (1 === $failuresNum || !$firstFailureDate || $now->diff($firstFailureDate) > $lockThreshInterval) {
            $customerSecure->setFirstFailure($this->dateTime->formatDate($now));
            $failuresNum = 1;
            // otherwise lock customer
        } elseif ($failuresNum >= $maxFailures) {
            $customerSecure->setLockExpires($this->dateTime->formatDate($now->add($lockThreshInterval)));
        }

        $customerSecure->setFailuresNum($failuresNum);
    }

    /**
     * Unlock customer
     * @param $customerId
     * @return void
     */
    public function processUnlockData($customerId)
    {
        $customerSecure = $this->customerRegistry->retrieveSecureData($customerId);
        $customerSecure->setFailuresNum(0);
        $customerSecure->setFirstFailure(null);
        $customerSecure->setLockExpires(null);
    }

    /**
     * Retrieve lock threshold
     *
     * @return int
     */
    protected function getLockThreshold()
    {
        return $this->backendConfig->getValue(self::LOCKOUT_THRESHOLD_PATH) * 60;
    }

    /**
     * Retrieve max password login failure number
     *
     * @return int
     */
    protected function getMaxFailures()
    {
        return $this->backendConfig->getValue(self::MAX_FAILURES_PATH);
    }

    /**
     * Validate that password is correct and customer is not locked
     *
     * @param string $password
     * @return bool true on success
     * @throws InvalidEmailOrPasswordException
     */
    public function validatePasswordAndLockStatus(\Magento\Customer\Api\Data\CustomerInterface $customer, $password)
    {
        $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
        $hash = $customerSecure->getPasswordHash();
        if (!$this->encryptor->validateHash($password, $hash)) {
            $this->eventManager->dispatch(
                'customer_password_invalid',
                [
                    'username' => $customer->getEmail(),
                    'password' => $password
                ]
            );
            $this->checkIfLocked($customer);
            throw new InvalidEmailOrPasswordException(__('The password doesn\'t match this account.'));
        }
        return true;
    }

    /**
     * Check if customer is locked and throw exception.
     *
     * @api
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @throws \Magento\Framework\Exception\State\UserLockedException
     * @return void
     */
    public function checkIfLocked(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        $currentCustomer = $this->customerRegistry->retrieve($customer->getId());
        if ($currentCustomer->isCustomerLocked()) {
            throw new UserLockedException(
                __(
                    'The account is locked. Please wait and try again or contact %1.',
                    $this->scopeConfig->getValue('contact/email/recipient_email')
                )
            );
        }
    }
}
