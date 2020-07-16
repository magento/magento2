<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class to fetch the order payment details
 */
class OrderPayments
{
    /**
     * @param OrderInterface $orderModel
     * @return array
     */
    public function getOrderPaymentMethod(OrderInterface $orderModel): array
    {
        $orderPayment = $orderModel->getPayment();
        $paymentAdditionalInfo =  $orderModel->getExtensionAttributes()->getPaymentAdditionalInfo();
        $paymentAdditionalData = [];
        foreach ($paymentAdditionalInfo as $key => $paymentAdditionalInfoDetails) {
            $paymentAdditionalData[$key]['name'] = $paymentAdditionalInfoDetails->getKey();
            $paymentAdditionalData[$key]['value'] = $paymentAdditionalInfoDetails->getValue();
        }
        $additionalInformationCcType = $orderPayment->getCcType();
        $additionalInformationCcNumber = $orderPayment->getCcLast4();
        if ($orderPayment->getMethod() === 'checkmo' || $orderPayment->getMethod() === 'free' ||
            $orderPayment->getMethod() === 'purchaseorder' ||$orderPayment->getMethod() === 'cashondelivery' ||
            $orderPayment->getMethod() === 'banktransfer'
        ) {
            $additionalData = [];
        } else {
            $additionalData = [
                [
                    'name' => 'Credit Card Type',
                    'value' => $additionalInformationCcType ?? null
                ],
                [
                    'name' => 'Credit Card Number',
                    'value' => $additionalInformationCcNumber ?? null
                ]
            ];
        }

        return [
            [
                'name' => $orderPayment->getAdditionalInformation()['method_title'] ?? null,
                'type' => $orderPayment->getMethod() ?? null,
                'additional_data' => $additionalData
            ]
        ];
    }
}
