<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\SalesItem;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class to extract the order payment details
 */
class ExtractOrderPaymentDetails
{
    /**
     * @param OrderInterface $orderModel
     * @return array
     */
    public function getOrderPaymentMethodDetails(OrderInterface $orderModel): array
    {
        $orderPayment = $orderModel->getPayment();
        $additionalInformationCcType = $orderPayment->getCcType();
        $additionalInformationCcNumber = $orderPayment->getCcLast4();
        if ($orderPayment->getMethod() === 'checkmo' || $orderPayment->getMethod() === 'free') {
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
                'name' => $orderPayment->getAdditionalInformation()['method_title'],
                'type' => $orderPayment->getMethod(),
                'additional_data' => $additionalData
            ]
        ];
    }
}

