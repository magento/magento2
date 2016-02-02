<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Helper;

use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Backend\App\ConfigInterface;

/**
 * Customer helper for account management.
 */
class AccountManagement extends \Magento\Framework\App\Helper\AbstractHelper
{
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
     * AccountManagement constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param CustomerRegistry $customerRegistry
     * @param ConfigInterface $backendConfig
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CustomerRegistry $customerRegistry,
        ConfigInterface $backendConfig,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {
        parent::__construct($context);
        $this->customerRegistry = $customerRegistry;
        $this->backendConfig = $backendConfig;
        $this->dateTime = $dateTime;
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

        /** @noinspection PhpAssignmentInConditionInspection */
        if ($firstFailureDate = $customerSecure->getFirstFailure()) {
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
    public function unlock($customerId)
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
        return $this->backendConfig->getValue('customer/password/lockout_threshold') * 60;
    }

    /**
     * Retrieve max password login failure number
     *
     * @return int
     */
    protected function getMaxFailures()
    {
        return $this->backendConfig->getValue('customer/password/lockout_failures');
    }
}
