<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\InstantPurchase\Model\QuoteManagement\PaymentConfiguration;
use Magento\InstantPurchase\Model\QuoteManagement\Purchase;
use Magento\InstantPurchase\Model\QuoteManagement\QuoteCreation;
use Magento\InstantPurchase\Model\QuoteManagement\QuoteFilling;
use Magento\InstantPurchase\Model\QuoteManagement\ShippingConfiguration;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\Store;
use \Throwable;

/**
 * Place an order using instant purchase option.
 *
 * @api
 * @since 100.2.0
 */
class PlaceOrder
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var QuoteCreation
     */
    private $quoteCreation;

    /**
     * @var QuoteFilling
     */
    private $quoteFilling;

    /**
     * @var ShippingConfiguration
     */
    private $shippingConfiguration;

    /**
     * @var PaymentConfiguration
     */
    private $paymentConfiguration;

    /**
     * @var Purchase
     */
    private $purchase;

    /**
     * PlaceOrder constructor.
     * @param CartRepositoryInterface $quoteRepository
     * @param QuoteCreation $quoteCreation
     * @param QuoteFilling $quoteFilling
     * @param ShippingConfiguration $shippingConfiguration
     * @param PaymentConfiguration $paymentConfiguration
     * @param Purchase $purchase
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        QuoteCreation $quoteCreation,
        QuoteFilling $quoteFilling,
        ShippingConfiguration $shippingConfiguration,
        PaymentConfiguration $paymentConfiguration,
        Purchase $purchase
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->quoteCreation = $quoteCreation;
        $this->quoteFilling = $quoteFilling;
        $this->shippingConfiguration = $shippingConfiguration;
        $this->paymentConfiguration = $paymentConfiguration;
        $this->purchase = $purchase;
    }

    /**
     * Place an order.
     *
     * @param Store $store
     * @param Customer $customer
     * @param InstantPurchaseOption $instantPurchaseOption
     * @param Product $product
     * @param array $productRequest
     * @return int order identifier
     * @throws LocalizedException if order can not be placed.
     * @throws Throwable if unpredictable error occurred.
     * @since 100.2.0
     */
    public function placeOrder(
        Store $store,
        Customer $customer,
        InstantPurchaseOption $instantPurchaseOption,
        Product $product,
        array $productRequest
    ) : int {
        $quote = $this->quoteCreation->createQuote(
            $store,
            $customer,
            $instantPurchaseOption->getShippingAddress(),
            $instantPurchaseOption->getBillingAddress()
        );
        $quote = $this->quoteFilling->fillQuote(
            $quote,
            $product,
            $productRequest
        );

        $quote->collectTotals();
        $this->quoteRepository->save($quote);
        $quote = $this->quoteRepository->get($quote->getId());

        try {
            $quote = $this->shippingConfiguration->configureShippingMethod(
                $quote,
                $instantPurchaseOption->getShippingMethod()
            );
            $quote = $this->paymentConfiguration->configurePayment(
                $quote,
                $instantPurchaseOption->getPaymentToken()
            );
            $orderId = $this->purchase->purchase(
                $quote
            );
            return $orderId;
        } catch (Throwable $e) {
            $quote->setIsActive(false);
            $this->quoteRepository->save($quote);
            throw $e;
        }
    }
}
