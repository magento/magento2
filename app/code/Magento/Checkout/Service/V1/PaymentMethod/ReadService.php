<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\PaymentMethod;

use Magento\Sales\Model\QuoteRepository;
use Magento\Checkout\Service\V1\Data\Cart\PaymentMethod\Converter as QuoteMethodConverter;
use Magento\Checkout\Service\V1\Data\PaymentMethod\Converter as PaymentMethodConverter;
use Magento\Payment\Model\MethodList;

/**
 * Payment method read service object.
 */
class ReadService implements ReadServiceInterface
{
    /**
     * Quote repository.
     *
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * Quote method converter.
     *
     * @var QuoteMethodConverter
     */
    protected $quoteMethodConverter;

    /**
     * Payment method converter.
     *
     * @var PaymentMethodConverter
     */
    protected $paymentMethodConverter;

    /**
     * Method list.
     *
     * @var MethodList
     */
    protected $methodList;

    /**
     * Constructs a payment method read service object.
     *
     * @param QuoteRepository $quoteRepository Quote repository.
     * @param QuoteMethodConverter $quoteMethodConverter Quote method converter.
     * @param PaymentMethodConverter $paymentMethodConverter Payment method converter.
     * @param MethodList $methodList Method list.
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        QuoteMethodConverter $quoteMethodConverter,
        PaymentMethodConverter $paymentMethodConverter,
        MethodList $methodList
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->quoteMethodConverter = $quoteMethodConverter;
        $this->paymentMethodConverter = $paymentMethodConverter;
        $this->methodList = $methodList;
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Checkout\Service\V1\Data\Cart\PaymentMethod  Payment method object.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function getPayment($cartId)
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $payment = $quote->getPayment();
        if (!$payment->getId()) {
            return null;
        }
        return $this->quoteMethodConverter->toDataObject($payment);
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Checkout\Service\V1\Data\PaymentMethod[] Array of payment methods.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function getList($cartId)
    {
        $output = [];
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        foreach ($this->methodList->getAvailableMethods($quote) as $method) {
            $output[] = $this->paymentMethodConverter->toDataObject($method);
        }
        return $output;
    }
}
