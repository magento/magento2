<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Model\Checkout\Plugin;

use Magento\CheckoutAgreements\Model\AgreementsProvider;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Validation
 */
class GuestValidation
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfiguration;

    /**
     * @var \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface
     */
    private $checkoutAgreementsRepository;

    /**
     * @var \Magento\Checkout\Api\AgreementsValidatorInterface
     */
    private $agreementsValidator;

    /**
     * @param \Magento\Checkout\Api\AgreementsValidatorInterface $agreementsValidator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration
     * @param \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface $checkoutAgreementsRepository
     */
    public function __construct(
        \Magento\Checkout\Api\AgreementsValidatorInterface $agreementsValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration,
        \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface $checkoutAgreementsRepository
    ) {
        $this->agreementsValidator = $agreementsValidator;
        $this->scopeConfiguration = $scopeConfiguration;
        $this->checkoutAgreementsRepository = $checkoutAgreementsRepository;
    }

    /**
     * @param \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $subject
     * @param string $cartId
     * @param string $email
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $subject,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        if ($this->isAgreementEnabled()) {
            $this->validateAgreements($paymentMethod);
        }
    }

    /**
     * @param \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $subject
     * @param string $cartId
     * @param string $email
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSavePaymentInformation(
        \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $subject,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        if ($this->isAgreementEnabled()) {
            $this->validateAgreements($paymentMethod);
        }
    }

    /**
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return void
     */
    private function validateAgreements(\Magento\Quote\Api\Data\PaymentInterface $paymentMethod)
    {
        $agreements = $paymentMethod->getExtensionAttributes() === null
            ? []
            : $paymentMethod->getExtensionAttributes()->getAgreementIds();

        if (!$this->agreementsValidator->isValid($agreements)) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Please agree to all the terms and conditions before placing the order.')
            );
        }
    }

    /**
     * Verify if agreement validation needed
     * @return bool
     */
    private function isAgreementEnabled()
    {
        $isAgreementsEnabled = $this->scopeConfiguration->isSetFlag(
            AgreementsProvider::PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
        $agreementsList = $isAgreementsEnabled ? $this->checkoutAgreementsRepository->getList() : [];
        return (bool)($isAgreementsEnabled && count($agreementsList) > 0);
    }
}
