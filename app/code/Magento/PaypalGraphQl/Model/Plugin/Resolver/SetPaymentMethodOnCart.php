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
use Magento\Paypal\Model\Express\Checkout\Factory as CheckoutFactory;
use Magento\PaypalGraphQl\Model\PaypalConfigProvider;
use Magento\PaypalGraphQl\Model\PaypalExpressAdditionalDataProvider;
use Magento\Framework\Stdlib\ArrayManager;

class SetPaymentMethodOnCart
{
    private const PATH_CODE = 'input/payment_method/code';

    /**
     * @var CheckoutFactory
     */
    private $checkoutFactory;

    /**
     * @var PaypalExpressAdditionalDataProvider
     */
    private $paypalExpressAdditionalDataProvider;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var PaypalConfigProvider
     */
    private $paypalConfigProvider;

    /**
     * @param CheckoutFactory $checkoutFactory
     * @param PaypalExpressAdditionalDataProvider $paypalExpressAdditionalDataProvider
     * @param ArrayManager $arrayManager
     * @param PaypalConfigProvider $paypalConfigProvider
     */
    public function __construct(
        CheckoutFactory $checkoutFactory,
        PaypalExpressAdditionalDataProvider $paypalExpressAdditionalDataProvider,
        ArrayManager $arrayManager,
        PaypalConfigProvider $paypalConfigProvider
    ) {
        $this->checkoutFactory = $checkoutFactory;
        $this->paypalExpressAdditionalDataProvider = $paypalExpressAdditionalDataProvider;
        $this->arrayManager = $arrayManager;
        $this->paypalConfigProvider = $paypalConfigProvider;
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
        $code = $this->arrayManager->get(self::PATH_CODE, $args) ?? '';

        $paypalAdditionalData = $this->paypalExpressAdditionalDataProvider->getData($args);
        if (empty($paypalAdditionalData)
            || empty($paypalAdditionalData['payer_id'])
            || empty($paypalAdditionalData['token'])
            || empty($code)
        ) {
            return $resolvedValue;
        }

        // validate and get payment code method
        $payerId = $paypalAdditionalData['payer_id'];
        $token = $paypalAdditionalData['token'];
        $cart = $resolvedValue['cart']['model'];
        $checkout = $this->paypalConfigProvider->getCheckout($code, $cart);

        try {
            $checkout->returnFromPaypal($token, $payerId);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return $resolvedValue;
    }
}
