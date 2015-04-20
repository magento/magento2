<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Fixture\OrderInjectable;

/**
 * Data keys:
 *  - preset (Price data verification preset name)
 */
class Price extends \Magento\Catalog\Test\Fixture\CatalogProductSimple\Price
{
    /**
     * @constructor
     * @param array $params
     * @param array $data
     */
    public function __construct(array $params, array $data = [])
    {
        $this->params = $params;
        if (isset($data['preset'])) {
            $this->currentPreset = $data['preset'];
            $this->data = $this->getPreset();
        }
    }

    /**
     * Get preset array
     *
     * @return array|null
     */
    public function getPreset()
    {
        $presets = [
            'default_with_discount' => [
                'subtotal' => 560,
                'discount' => 280,
            ],
            'full_invoice' => [
                [
                    'grand_order_total' => 565,
                    'grand_invoice_total' => 565,
                ],
            ],
            'partial_invoice' => [
                [
                    'grand_order_total' => 210,
                    'grand_invoice_total' => 110,
                ],
            ],
            'full_refund' => [
                [
                    'grand_creditmemo_total' => 565,
                ],
            ],
            'full_refund_with_zero_shipping_refund' => [
                [
                    'grand_creditmemo_total' => 555,
                ],
            ],
            'partial_refund' => [
                [
                    'grand_creditmemo_total' => 110,
                ],
            ],
        ];
        if (!isset($presets[$this->currentPreset])) {
            return null;
        }
        return $presets[$this->currentPreset];
    }
}
