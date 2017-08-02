<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Paypal\Helper;

use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * Class ShippingMethodUpdater
 * @since 2.1.0
 */
class ShippingMethodUpdater extends AbstractHelper
{
    /**
     * @var Config
     * @since 2.1.0
     */
    private $config;

    /**
     * @var CartRepositoryInterface
     * @since 2.1.0
     */
    private $quoteRepository;

    /**
     * Constructor
     *
     * @param Config $config
     * @param CartRepositoryInterface $quoteRepository
     * @since 2.1.0
     */
    public function __construct(
        Config $config,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->config = $config;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Execute operation
     *
     * @param string $shippingMethod
     * @param Quote $quote
     * @return void
     * @throws \InvalidArgumentException
     * @since 2.1.0
     */
    public function execute($shippingMethod, Quote $quote)
    {
        if (empty($shippingMethod)) {
            throw new \InvalidArgumentException('The "shippingMethod" field does not exists.');
        }

        if (!$quote->getIsVirtual()) {
            $shippingAddress = $quote->getShippingAddress();
            if ($shippingMethod !== $shippingAddress->getShippingMethod()) {
                $this->disabledQuoteAddressValidation($quote);

                $shippingAddress->setShippingMethod($shippingMethod);
                $shippingAddress->setCollectShippingRates(true);

                $quote->collectTotals();

                $this->quoteRepository->save($quote);
            }
        }
    }
}
