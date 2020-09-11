<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Provider;

use Magento\Paypal\Model\AbstractConfig;
use Magento\Paypal\Model\Express\Checkout as ExpressCheckout;
use Magento\Paypal\Model\Express\Checkout\Factory as CheckoutFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Provides correct Checkout instance for payment method
 */
class Checkout
{
    /**
     * @var array
     */
    private $checkoutTypes;

    /**
     * @var CheckoutFactory
     */
    private $checkoutFactory;

    /**
     * @param CheckoutFactory $checkoutFactory
     * @param array $checkoutTypes
     */
    public function __construct(
        CheckoutFactory $checkoutFactory,
        array $checkoutTypes
    ) {
        $this->checkoutFactory = $checkoutFactory;
        $this->checkoutTypes = $checkoutTypes;
    }

    /**
     * Get Checkout model by payment method code
     *
     * @param AbstractConfig $config
     * @param CartInterface $cart
     * @return ExpressCheckout
     * @throws GraphQlInputException
     */
    public function getCheckout(AbstractConfig $config, CartInterface $cart): ExpressCheckout
    {
        try {
            $checkout = $this->checkoutFactory->create(
                $this->checkoutTypes[$config->getMethodCode()],
                [
                    'params' => [
                        'quote' => $cart,
                        'config' => $config,
                    ],
                ]
            );
        } catch (\Exception $e) {
            throw new GraphQlInputException(__('The requested Payment Method is not available.'));
        }

        return $checkout;
    }
}
