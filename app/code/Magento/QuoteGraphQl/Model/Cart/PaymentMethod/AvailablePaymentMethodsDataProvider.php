<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\PaymentMethod;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Get array of available payment methods.
 */
class AvailablePaymentMethodsDataProvider
{
    /**
     * @var PaymentInformationManagementInterface
     */
    private $informationManagement;

    /**
     * AvailablePaymentMethodsDataProvider constructor.
     * @param PaymentInformationManagementInterface $informationManagement
     */
    public function __construct(PaymentInformationManagementInterface $informationManagement)
    {
        $this->informationManagement = $informationManagement;
    }

    /**
     * Collect and return information about available payment methods
     *
     * @param CartInterface $cart
     * @return array
     */
    public function getPaymentMethods(CartInterface $cart): array
    {
        $paymentInformation = $this->informationManagement->getPaymentInformation($cart->getId());
        $paymentMethods = $paymentInformation->getPaymentMethods();

        $paymentMethodsNested = [];
        foreach ($paymentMethods as $paymentMethod) {
            $paymentMethodsNested[] = [
                'title' => $paymentMethod->getTitle(),
                'code' => $paymentMethod->getCode()
            ];
        }

        return $paymentMethodsNested;
    }
}
