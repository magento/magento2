<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Plugin\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Phrase;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Express\Checkout\Factory as CheckoutFactory;
use Magento\Paypal\Model\Express\Checkout;
use Magento\PaypalGraphQl\Model\PaypalExpressAdditionalDataProvider;

class SetPaymentMethodOnCart
{
    /**
     * @var CheckoutFactory
     */
    private $checkoutFactory;

    /**
     * @var PaypalExpressAdditionalDataProvider
     */
    private $paypalExpressAdditionalDataProvider;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param CheckoutFactory $checkoutFactory
     * @param Config $config
     * @param PaypalExpressAdditionalDataProvider $paypalExpressAdditionalDataProvider
     */
    public function __construct(
        CheckoutFactory $checkoutFactory,
        Config $config,
        PaypalExpressAdditionalDataProvider $paypalExpressAdditionalDataProvider
    ) {
        $this->checkoutFactory = $checkoutFactory;
        $this->config = $config;
        $this->paypalExpressAdditionalDataProvider = $paypalExpressAdditionalDataProvider;
    }

    /**
     * Update Paypal payment information on cart
     *
     * @param ResolverInterface $subject
     * @param $resolvedValue
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed
     * @throws GraphQlInputException
     */
    public function afterResolve(
        ResolverInterface $subject,
        $resolvedValue,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $paypalAdditionalData = $this->paypalExpressAdditionalDataProvider->getData($args);
        if (empty($paypalAdditionalData)
            || empty($paypalAdditionalData['payer_id'])
            || empty($paypalAdditionalData['token'])
        ) {
            return $resolvedValue;
        }
        $this->config->setMethod(Config::METHOD_EXPRESS); //TODO dynamic, based on input maybe
        $payerId = $paypalAdditionalData['payer_id'];
        $token = $paypalAdditionalData['token'];
        $cart = $resolvedValue['cart']['model'];

        $checkout = $this->checkoutFactory->create(
            Checkout::class,
            [
                'params' => [
                    'quote' => $cart,
                    'config' => $this->config,
                ],
            ]
        );

        try {
            $checkout->returnFromPaypal($token, $payerId);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(new Phrase($e->getMessage()));
        }

        return $resolvedValue;
    }

}