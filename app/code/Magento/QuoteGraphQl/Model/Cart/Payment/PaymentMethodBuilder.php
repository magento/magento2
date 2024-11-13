<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\Payment;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\PaymentInterfaceFactory;

/**
 * Build payment method objects
 */
class PaymentMethodBuilder
{
    /**
     * @var PaymentInterfaceFactory
     */
    private $paymentFactory;

    /**
     * @var AdditionalDataProviderPool
     */
    private $paymentDataProvider;

    /**
     * @param PaymentInterfaceFactory $paymentFactory
     * @param AdditionalDataProviderPool $paymentDataProvider
     */
    public function __construct(
        PaymentInterfaceFactory $paymentFactory,
        AdditionalDataProviderPool $paymentDataProvider
    ) {
        $this->paymentFactory = $paymentFactory;
        $this->paymentDataProvider = $paymentDataProvider;
    }

    /**
     * Build a PaymentInterface object from the supplied data array
     *
     * @param array $paymentData
     * @return PaymentInterface
     * @throws GraphQlInputException
     */
    public function build(array $paymentData): PaymentInterface
    {
        if (!isset($paymentData['code']) || empty($paymentData['code'])) {
            throw new GraphQlInputException(__('Required parameter "code" for "payment_method" is missing.'));
        }
        $paymentMethodCode = $paymentData['code'];

        $poNumber = $paymentData['purchase_order_number'] ?? null;
        $additionalData = $this->paymentDataProvider->getData($paymentMethodCode, $paymentData);

        return $this->paymentFactory->create(
            [
                'data' => [
                    PaymentInterface::KEY_METHOD => $paymentMethodCode,
                    PaymentInterface::KEY_PO_NUMBER => $poNumber,
                    PaymentInterface::KEY_ADDITIONAL_DATA => $additionalData,
                ],
            ]
        );
    }
}
