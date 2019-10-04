<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

use Magento\Customer\Api\PasswordManagementInterface;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\ForgotPasswordToken\GetCustomerByToken;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Intl\DateTimeFactory;

/**
 * Handle customer password actions
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class PasswordManagement implements PasswordManagementInterface
{
    /**
     * @var CustomerModel
     */
    private $customerModel;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var GetCustomerByToken
     */
    private $getByToken;

    /**
     * @param CustomerRegistry $customerRegistry
     * @param CustomerModel $customerModel
     * @param GetCustomerByToken|null $getByToken
     * @param DateTimeFactory|null $dateTimeFactory
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        CustomerRegistry $customerRegistry,
        CustomerModel $customerModel,
        DateTimeFactory $dateTimeFactory = null,
        GetCustomerByToken $getByToken = null
    ) {
        $this->customerRegistry = $customerRegistry;
        $this->customerModel = $customerModel;
        $objectManager = ObjectManager::getInstance();
        $this->dateTimeFactory = $dateTimeFactory ?: $objectManager->get(DateTimeFactory::class);
        $this->getByToken = $getByToken
            ?: $objectManager->get(GetCustomerByToken::class);
    }

    /**
     * @inheritdoc
     */
    public function validateResetPasswordLinkByToken($resetPasswordLinkToken)
    {
        $this->validateResetPasswordByToken($resetPasswordLinkToken);
        return true;
    }

    /**
     * Validate the Reset Password Token for a customer.
     *
     * @param string $resetPasswordLinkToken
     *
     * @return bool
     * @throws ExpiredException
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function validateResetPasswordByToken($resetPasswordLinkToken)
    {
        $customerId = $this->getByToken
            ->execute($resetPasswordLinkToken)
            ->getId();
        if (!is_string($resetPasswordLinkToken) || empty($resetPasswordLinkToken)) {
            $params = ['fieldName' => 'resetPasswordLinkToken'];
            throw new InputException(__('"%fieldName" is required. Enter and try again.', $params));
        }
        $customerSecureData = $this->customerRegistry->retrieveSecureData($customerId);
        $rpToken = $customerSecureData->getRpToken();
        $rpTokenCreatedAt = $customerSecureData->getRpTokenCreatedAt();
        if (!Security::compareStrings($rpToken, $resetPasswordLinkToken)) {
            throw new InputMismatchException(__('The password token is mismatched. Reset and try again.'));
        } elseif ($this->isResetPasswordLinkTokenExpired($rpToken, $rpTokenCreatedAt)) {
            throw new ExpiredException(__('The password token is expired. Reset and try again.'));
        }
        return true;
    }

    /**
     * Check if rpToken is expired
     *
     * @param string $rpToken
     * @param string $rpTokenCreatedAt
     * @return bool
     */
    private function isResetPasswordLinkTokenExpired($rpToken, $rpTokenCreatedAt)
    {
        if (empty($rpToken) || empty($rpTokenCreatedAt)) {
            return true;
        }

        $expirationPeriod = $this->customerModel->getResetPasswordLinkExpirationPeriod();

        $currentTimestamp = $this->dateTimeFactory->create()->getTimestamp();
        $tokenTimestamp = $this->dateTimeFactory->create($rpTokenCreatedAt)->getTimestamp();
        if ($tokenTimestamp > $currentTimestamp) {
            return true;
        }

        $hourDifference = floor(($currentTimestamp - $tokenTimestamp) / (60 * 60));
        if ($hourDifference >= $expirationPeriod) {
            return true;
        }

        return false;
    }
}
