<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BraintreeGraphQl\Plugin;

use Magento\Braintree\Gateway\Command\GetPaymentNonceCommand;
use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Psr\Log\LoggerInterface;

/**
 * Plugin creating nonce from Magento Vault Braintree public hash
 *
 * @deprecated Starting from Magento 2.3.6 Braintree payment method core integration is deprecated
 * in favor of official payment integration available on the marketplace
 */
class SetVaultPaymentNonce
{
    /**
     * @var GetPaymentNonceCommand
     */
    private $command;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param GetPaymentNonceCommand $command
     * @param LoggerInterface $logger
     */
    public function __construct(
        GetPaymentNonceCommand $command,
        LoggerInterface $logger
    ) {
        $this->command = $command;
        $this->logger = $logger;
    }

    /**
     * Set Braintree nonce from public hash
     *
     * @param \Magento\QuoteGraphQl\Model\Cart\SetPaymentMethodOnCart $subject
     * @param \Magento\Quote\Model\Quote $quote
     * @param array $paymentData
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(
        \Magento\QuoteGraphQl\Model\Cart\SetPaymentMethodOnCart $subject,
        \Magento\Quote\Model\Quote $quote,
        array $paymentData
    ): array {
        if ($paymentData['code'] !== ConfigProvider::CC_VAULT_CODE
            || !isset($paymentData[ConfigProvider::CC_VAULT_CODE])
            || !isset($paymentData[ConfigProvider::CC_VAULT_CODE]['public_hash'])
        ) {
            return [$quote, $paymentData];
        }

        $subject = [
            'public_hash' => $paymentData[ConfigProvider::CC_VAULT_CODE]['public_hash'],
            'customer_id' => $quote->getCustomerId(),
            'store_id' => $quote->getStoreId(),
        ];

        try {
            $result = $this->command->execute($subject)->get();
            $paymentData[ConfigProvider::CC_VAULT_CODE]['payment_method_nonce'] = $result['paymentMethodNonce'];
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new GraphQlInputException(__('Sorry, but something went wrong'));
        }

        return [$quote, $paymentData];
    }
}
