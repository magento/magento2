<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PaypalGraphQl\Observer;

use Magento\Customer\Model\Session as CustomerModelSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Class PayflowProSetCcData set CcData to quote payment
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class PayflowProSetCcData extends AbstractDataAssignObserver
{
    const XML_PATH_PAYMENT_PAYFLOWPRO_CC_VAULT_ACTIVE = "payment/payflowpro_cc_vault/active";
    const IS_ACTIVE_PAYMENT_TOKEN_ENABLER = "is_active_payment_token_enabler";

    /**
     * @var CustomerModelSession
     */
    private $customerSession;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param CustomerModelSession $customerSession
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CustomerModelSession $customerSession,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Set CcData
     *
     * @param Observer $observer
     *
     * @throws GraphQlInputException
     */
    public function execute(Observer $observer)
    {
        $dataObject = $this->readDataArgument($observer);
        $additionalData = $dataObject->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        $paymentModel = $this->readPaymentModelArgument($observer);

        if (!isset($additionalData['cc_details'])) {
            return;
        }

        if ($this->customerSession->isLoggedIn() && $this->isPayflowProVaultEnable()) {
            if (!isset($additionalData[self::IS_ACTIVE_PAYMENT_TOKEN_ENABLER])) {
                throw new GraphQlInputException(
                    __('Required parameter "is_active_payment_token_enabler" is missing.')
                );
            }

            $paymentModel->setData(
                self::IS_ACTIVE_PAYMENT_TOKEN_ENABLER,
                $additionalData[self::IS_ACTIVE_PAYMENT_TOKEN_ENABLER]
            );
        } else {
            $paymentModel->setData(self::IS_ACTIVE_PAYMENT_TOKEN_ENABLER, false);
        }

        $ccData = $additionalData['cc_details'];
        $paymentModel->setCcType($ccData['cc_type']);
        $paymentModel->setCcExpYear($ccData['cc_exp_year']);
        $paymentModel->setCcExpMonth($ccData['cc_exp_month']);
        $paymentModel->setCcLast4($ccData['cc_last_4']);
    }

    /**
     * Check if payflowpro vault is enable
     *
     * @return bool
     */
    private function isPayflowProVaultEnable()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_PAYMENT_PAYFLOWPRO_CC_VAULT_ACTIVE);
    }
}
