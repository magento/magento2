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
use Magento\Paypal\Model\Express\Checkout\Factory as CheckoutFactory;
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
     * Express configuration
     *
     * @see \Magento\Paypal\Controller\Express\Start
     * Example: ['paypal_express' =>
     *   [
     *    'configType' => '\Magento\Paypal\Model\Config',
     *    'configMethod': 'paypal_express',
     *    'checkoutType' => '\Magento\Paypal\Model\PayflowExpress\Checkout'
     *   ]
     * ]
     *
     * @var array
     */
    private $expressConfig;

    /**
     * @param CheckoutFactory $checkoutFactory
     * @param PaypalExpressAdditionalDataProvider $paypalExpressAdditionalDataProvider
     * @param array $expressConfig
     */
    public function __construct(
        CheckoutFactory $checkoutFactory,
        PaypalExpressAdditionalDataProvider $paypalExpressAdditionalDataProvider,
        $expressConfig = []
    ) {
        $this->checkoutFactory = $checkoutFactory;
        $this->paypalExpressAdditionalDataProvider = $paypalExpressAdditionalDataProvider;
        $this->expressConfig = $expressConfig;
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

        $code = 'payflow_express';

        // validate and get payment code method
        $config = $this->getExpressConfig($code);

        $payerId = $paypalAdditionalData['payer_id'];
        $token = $paypalAdditionalData['token'];
        $cart = $resolvedValue['cart']['model'];

        $checkout = $this->checkoutFactory->create(
            $this->expressConfig[$code]['checkoutType'],
            [
                'params' => [
                    'quote' => $cart,
                    'config' => $config,
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

    /**
     * Setup paypal express depending on the code: regular express, payflow, etc.
     *
     * @param $code
     * @return \Magento\Paypal\Model\AbstractConfig
     * @throws GraphQlInputException
     */
    private function getExpressConfig(string $code) : \Magento\Paypal\Model\AbstractConfig
    {
        //validate code string
        if (empty($code)) {
            throw new GraphQlInputException(__("TODO Missing code"));
        }

        //validate code string
        if (!isset($this->expressConfig[$code]['configMethod'])) {
            throw new GraphQlInputException(__("TODO configMethod"));
        }

        //validate code string
        if ($code !== $this->expressConfig[$code]['configMethod']) {
            throw new GraphQlInputException(__("TODO code is not equal to configMethod"));
        }

        // validate config class
        if (!isset($this->expressConfig[$code]['configType'])
            && !class_exists($this->expressConfig[$code]['configType'])) {
            throw new GraphQlInputException(__("TODO Config not provided"));
        }

        /** @var \Magento\Paypal\Model\AbstractConfig $config */
        $config = $this->expressConfig[$code]['configType'];

        $config->setMethod($code);

        if (!$config->isMethodAvailable($code)) {
            throw new GraphQlInputException(__("TODO Payment method not available"));
        }

        return $config;
    }
}
