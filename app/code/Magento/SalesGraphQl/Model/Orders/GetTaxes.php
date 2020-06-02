<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Orders;

use Magento\Sales\Model\Order;

/**
 * Taxes applied to the order
 */
class GetTaxes
{
    /**
     * @param Order $orderModel
     * @param array $appliedTaxesArray
     * @return array|null
     */
    public function execute($orderModel, $appliedTaxesArray)
    {
        return $this->getAppliedTaxesDetails($orderModel, $appliedTaxesArray);
    }

    /**
     * Returns taxes applied to the current order
     *
     * @param Order $orderModel
     * @param array $appliedTaxesArray
     * @return array|null
     */
    private function getAppliedTaxesDetails(Order $orderModel, array $appliedTaxesArray): array
    {
        if (empty($appliedTaxesArray)) {
            $taxes [] = null;
        } else {
            $taxes[] = [
                        'rate' => $appliedTaxesArray['percent'],
                        'title' => $appliedTaxesArray['title'],
                        'amount' => [ 'value' =>  $orderModel->getTaxAmount(), 'currency' => $orderModel->getOrderCurrencyCode()
                        ]
                    ];
        }
        return $taxes;
    }
}
