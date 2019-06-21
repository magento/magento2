<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\PaymentInterfaceFactory;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderPool;

/**
 * Set payment method on cart
 */
class SetPaymentMethodOnCart
{
    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentMethodManagement;

    /**
     * @var PaymentInterfaceFactory
     */
    private $paymentFactory;

    /**
     * @var AdditionalDataProviderPool
     */
    private $additionalDataProviderPool;

    /**
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param PaymentInterfaceFactory $paymentFactory
     * @param AdditionalDataProviderPool $additionalDataProviderPool
     */
    public function __construct(
        PaymentMethodManagementInterface $paymentMethodManagement,
        PaymentInterfaceFactory $paymentFactory,
        AdditionalDataProviderPool $additionalDataProviderPool
    ) {
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->paymentFactory = $paymentFactory;
        $this->additionalDataProviderPool = $additionalDataProviderPool;
    }

    /**
     * Set payment method on cart
     *
     * @param Quote $cart
     * @param array $paymentData
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function execute(Quote $cart, array $paymentData): void
    {
        if (!isset($paymentData['code']) || empty($paymentData['code'])) {
            throw new GraphQlInputException(__('Required parameter "code" for "payment_method" is missing.'));
        }
        $paymentMethodCode = $paymentData['code'];

        $poNumber = $paymentData['purchase_order_number'] ?? null;
        $additionalData = isset($paymentData['additional_data'])
            ? $this->additionalDataProviderPool->getData($paymentMethodCode, $paymentData['additional_data'])
            : [];

        $payment = $this->paymentFactory->create(
            [
                'data' =>
                    [
                        PaymentInterface::KEY_METHOD => $paymentMethodCode,
                        PaymentInterface::KEY_PO_NUMBER => $poNumber,
                        PaymentInterface::KEY_ADDITIONAL_DATA => $additionalData,
                    ],
            ]
        );

        try {
            $this->paymentMethodManagement->set($cart->getId(), $payment);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }
    }
}
