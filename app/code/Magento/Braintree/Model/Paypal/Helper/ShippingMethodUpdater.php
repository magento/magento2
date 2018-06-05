<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Paypal\Helper;

use Magento\Quote\Model\Quote;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Braintree\Gateway\Config\PayPal\Config;

/**
 * Class ShippingMethodUpdater
 */
class ShippingMethodUpdater extends AbstractHelper
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * Constructor
     *
     * @param Config $config
     * @param CartRepositoryInterface $quoteRepository
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
