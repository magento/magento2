<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Checkout\Api\Exception\PaymentProcessingRateLimitExceededException;
use Magento\Checkout\Api\PaymentProcessingRateLimiterInterface;
use Magento\Checkout\Api\PaymentSavingRateLimiterInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\Payment\PaymentMethodBuilder;

/**
 * Saves related payment method info for a cart.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SetPaymentMethodOnCart
{
    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentMethodManagement;

    /**
     * @var PaymentMethodBuilder
     */
    private $paymentMethodBuilder;

    /**
     * @var PaymentSavingRateLimiterInterface
     */
    private $paymentRateLimiter;

    /**
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param PaymentMethodBuilder $paymentMethodBuilder
     * @param PaymentProcessingRateLimiterInterface|null $paymentRateLimiter
     * @param PaymentSavingRateLimiterInterface|null $savingRateLimiter
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        PaymentMethodManagementInterface $paymentMethodManagement,
        PaymentMethodBuilder $paymentMethodBuilder,
        ?PaymentProcessingRateLimiterInterface $paymentRateLimiter = null,
        ?PaymentSavingRateLimiterInterface $savingRateLimiter = null
    ) {
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->paymentMethodBuilder = $paymentMethodBuilder;
        $this->paymentRateLimiter = $savingRateLimiter
            ?? ObjectManager::getInstance()->get(PaymentSavingRateLimiterInterface::class);
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
        try {
            try {
                $this->paymentRateLimiter->limit();
            } catch (PaymentProcessingRateLimitExceededException $ex) {
                //Limit reached
                return;
            }
        } catch (PaymentProcessingRateLimitExceededException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()), $exception);
        }

        $payment = $this->paymentMethodBuilder->build($paymentData);

        try {
            $this->paymentMethodManagement->set($cart->getId(), $payment);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }
    }
}
