<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Model\Checkout\Plugin;

use Magento\Checkout\Api\AgreementsValidatorInterface;
use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface;
use Magento\CheckoutAgreements\Model\AgreementsProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use Magento\CheckoutAgreements\Model\Api\SearchCriteria\ActiveStoreAgreementsFilter;

/**
 * Guest checkout agreements validation.
 *
 * Plugin that checks if checkout agreement enabled and validates all agreements.
 * Current plugin is duplicate from Magento\CheckoutAgreements\Model\Checkout\Plugin\Validation due to different
 * interfaces of payment information and makes check before processing of payment information.
 */
class GuestValidation
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfiguration;

    /**
     * @var \Magento\Checkout\Api\AgreementsValidatorInterface
     */
    private $agreementsValidator;

    /**
     * @var \Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface
     */
    private $checkoutAgreementsList;

    /**
     * @var \Magento\CheckoutAgreements\Model\Api\SearchCriteria\ActiveStoreAgreementsFilter
     */
    private $activeStoreAgreementsFilter;

    /**
     * @var GuestCartRepositoryInterface
     */
    private GuestCartRepositoryInterface $quoteRepository;

    /**
     * @var Emulation
     */
    private Emulation $storeEmulation;

    /**
     * @param AgreementsValidatorInterface $agreementsValidator
     * @param ScopeConfigInterface $scopeConfiguration
     * @param CheckoutAgreementsListInterface $checkoutAgreementsList
     * @param ActiveStoreAgreementsFilter $activeStoreAgreementsFilter
     * @param GuestCartRepositoryInterface $quoteRepository
     * @param Emulation $storeEmulation
     */
    public function __construct(
        \Magento\Checkout\Api\AgreementsValidatorInterface $agreementsValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration,
        \Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface $checkoutAgreementsList,
        \Magento\CheckoutAgreements\Model\Api\SearchCriteria\ActiveStoreAgreementsFilter $activeStoreAgreementsFilter,
        GuestCartRepositoryInterface $quoteRepository,
        Emulation $storeEmulation
    ) {
        $this->agreementsValidator = $agreementsValidator;
        $this->scopeConfiguration = $scopeConfiguration;
        $this->checkoutAgreementsList = $checkoutAgreementsList;
        $this->activeStoreAgreementsFilter = $activeStoreAgreementsFilter;
        $this->quoteRepository = $quoteRepository;
        $this->storeEmulation = $storeEmulation;
    }

    /**
     * Validates agreements before save payment information and  order placing.
     *
     * @param GuestPaymentInformationManagementInterface $subject
     * @param string $cartId
     * @param string $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws CouldNotSaveException|NoSuchEntityException
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        GuestPaymentInformationManagementInterface $subject,
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        ?AddressInterface $billingAddress = null
    ) {
        if ($this->isAgreementEnabled()) {
            $quote = $this->quoteRepository->get($cartId);
            $storeId = $quote->getStoreId();
            $this->validateAgreements($paymentMethod, $storeId);
        }
    }

    /**
     * Validates agreements.
     *
     * @param PaymentInterface $paymentMethod
     * @param int $storeId
     * @return void
     * @throws CouldNotSaveException
     */
    private function validateAgreements(PaymentInterface $paymentMethod, int $storeId)
    {
        $agreements = $paymentMethod->getExtensionAttributes() === null
            ? []
            : $paymentMethod->getExtensionAttributes()->getAgreementIds();

        $this->storeEmulation->startEnvironmentEmulation($storeId);
        $isValid = $this->agreementsValidator->isValid($agreements);
        $this->storeEmulation->stopEnvironmentEmulation();

        if (!$isValid) {
            throw new CouldNotSaveException(
                __(
                    "The order wasn't placed. "
                    . "First, agree to the terms and conditions, then try placing your order again."
                )
            );
        }
    }

    /**
     * Verify if agreement validation needed.
     *
     * @return bool
     */
    private function isAgreementEnabled()
    {
        $isAgreementsEnabled = $this->scopeConfiguration->isSetFlag(
            AgreementsProvider::PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
        $agreementsList = $isAgreementsEnabled
            ? $this->checkoutAgreementsList->getList($this->activeStoreAgreementsFilter->buildSearchCriteria())
            : [];
        return (bool)($isAgreementsEnabled && count($agreementsList) > 0);
    }
}
