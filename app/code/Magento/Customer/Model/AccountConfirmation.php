<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;

/**
 * Class AccountConfirmation. Checks if email confirmation required for customer.
 */
class AccountConfirmation
{
    /**
     * Configuration path for email confirmation when creating a new customer
     */
    public const XML_PATH_IS_CONFIRM = 'customer/create_account/confirm';

    /**
     * Configuration path for email confirmation when updating an existing customer's email
     */
    public const XML_PATH_IS_CONFIRM_EMAIL_CHANGED = 'customer/account_information/confirm';

    /**
     * Constant for confirmed status
     */
    private const ACCOUNT_CONFIRMED = 'account_confirmed';

    /**
     * Constant for confirmation required status
     */
    private const ACCOUNT_CONFIRMATION_REQUIRED = 'account_confirmation_required';

    /**
     * Constant for confirmation not required status
     */
    private const ACCOUNT_CONFIRMATION_NOT_REQUIRED = 'account_confirmation_not_required';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * AccountConfirmation constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Registry $registry
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Registry $registry
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->registry = $registry;
    }

    /**
     * Check if accounts confirmation is required.
     *
     * @param int|null $websiteId
     * @param int|null $customerId
     * @param string $customerEmail
     * @return bool
     */
    public function isConfirmationRequired($websiteId, $customerId, $customerEmail): bool
    {
        if ($this->canSkipConfirmation($customerId, $customerEmail)) {
            return false;
        }

        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_IS_CONFIRM,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );
    }

    /**
     * Check if accounts confirmation is required if email has been changed
     *
     * @param int|null $websiteId
     * @param int|null $customerId
     * @param string|null $customerEmail
     * @return bool
     */
    public function isEmailChangedConfirmationRequired($websiteId, $customerId, $customerEmail): bool
    {
        return !$this->canSkipConfirmation($customerId, $customerEmail)
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_IS_CONFIRM_EMAIL_CHANGED,
                ScopeInterface::SCOPE_WEBSITES,
                $websiteId
            );
    }

    /**
     * Returns an email confirmation status if email has been changed
     *
     * @param CustomerInterface $customer
     * @return string
     */
    private function getEmailChangedConfirmStatus(CustomerInterface $customer): string
    {
        $isEmailChangedConfirmationRequired = $this->isEmailChangedConfirmationRequired(
            (int)$customer->getWebsiteId(),
            (int)$customer->getId(),
            $customer->getEmail()
        );

        return $isEmailChangedConfirmationRequired
            ? $customer->getConfirmation() ? self::ACCOUNT_CONFIRMATION_REQUIRED : self::ACCOUNT_CONFIRMED
            : self::ACCOUNT_CONFIRMATION_NOT_REQUIRED;
    }

    /**
     * Checks if email confirmation is required for the customer
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    public function isCustomerEmailChangedConfirmRequired(CustomerInterface $customer):bool
    {
        return $this->getEmailChangedConfirmStatus($customer) === self::ACCOUNT_CONFIRMATION_REQUIRED;
    }

    /**
     * Check whether confirmation may be skipped when registering using certain email address.
     *
     * @param int|null $customerId
     * @param string $customerEmail
     * @return bool
     */
    private function canSkipConfirmation($customerId, $customerEmail): bool
    {
        if (!$customerId || $customerEmail === null) {
            return false;
        }

        /* If an email was used to start the registration process and it is the same email as the one
           used to register, then this can skip confirmation.
           */
        $skipConfirmationIfEmail = $this->registry->registry("skip_confirmation_if_email");
        if (!$skipConfirmationIfEmail) {
            return false;
        }

        return strtolower($skipConfirmationIfEmail) === strtolower($customerEmail);
    }
}
