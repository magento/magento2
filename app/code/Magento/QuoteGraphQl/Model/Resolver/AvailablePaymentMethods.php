<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;

/**
 * Get list of active payment methods resolver.
 */
class AvailablePaymentMethods implements ResolverInterface
{
    public const FREE_SHIPPING_METHOD = 'freeshipping';

    public const FREE_PAYMENT_METHOD_CODE = 'free';

    /**
     * @var PaymentInformationManagementInterface
     */
    private PaymentInformationManagementInterface $informationManagement;

    /**
     * @var ShippingMethodManagementInterface
     */
    private ShippingMethodManagementInterface $informationShipping;

    /**
     * @param PaymentInformationManagementInterface $informationManagement
     * @param ShippingMethodManagementInterface $informationShipping
     */
    public function __construct(
        PaymentInformationManagementInterface $informationManagement,
        ShippingMethodManagementInterface $informationShipping
    ) {
        $this->informationManagement = $informationManagement;
        $this->informationShipping = $informationShipping;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $cart = $value['model'];
        return $this->getPaymentMethodsData($cart);
    }

    /**
     * Collect and return information about available payment methods
     *
     * @param CartInterface $cart
     * @return array
     * @throws GraphQlInputException
     */
    private function getPaymentMethodsData(CartInterface $cart): array
    {
        $paymentInformation = $this->informationManagement->getPaymentInformation($cart->getId());
        $paymentMethods = $paymentInformation->getPaymentMethods();
        $shippingData = $this->getShippingData($cart->getId());
        $carrierCode = $shippingData['carrier_code'] ?? null;
        $grandTotal = $shippingData['grand_total'] ?? 0;
        $paymentMethodsData = [];
        foreach ($paymentMethods as $paymentMethod) {
            /**
             * Checking payment method and shipping method for zero price product
             */
            if ((int)$grandTotal === 0 && $carrierCode === self::FREE_SHIPPING_METHOD &&
            $paymentMethod->getCode() === self::FREE_PAYMENT_METHOD_CODE
            ) {
                return [
                    [
                        'title' => $paymentMethod->getTitle(),
                        'code' => $paymentMethod->getCode()
                    ]
                ];
            } elseif ((int)$grandTotal >= 0) {
                $paymentMethodsData[] = [
                    'title' => $paymentMethod->getTitle(),
                    'code' => $paymentMethod->getCode()
                ];
            }
        }
        return $paymentMethodsData;
    }

    /**
     * Retrieve selected shipping method
     *
     * @param string $cartId
     * @return array
     */
    private function getShippingData(string $cartId): array
    {
        $shippingData = [];
        try {
            $shippingMethod = $this->informationShipping->get($cartId);
            if ($shippingMethod) {
                $shippingData['carrier_code'] = $shippingMethod->getCarrierCode();
                $shippingData['grand_total'] = $shippingMethod->getBaseAmount();
            }
        } catch (LocalizedException $exception) {
            $shippingData = [];
        }
        return $shippingData;
    }
}
