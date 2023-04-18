<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\CustomerData;

use Magento\Customer\CustomerData\JsLayoutDataProviderInterface;
use Magento\Tax\Model\Config;

/**
 * Checkout totals js layout data provider
 */
class CheckoutTotalsJsLayoutDataProvider implements JsLayoutDataProviderInterface
{
    /**
     * @param Config $taxConfig
     */
    public function __construct(
        protected readonly Config $taxConfig
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return [
            'components' => [
                'minicart_content' => [
                    'children' => [
                        'subtotal.container' => [
                            'children' => [
                                'subtotal' => [
                                    'children' => [
                                        'subtotal.totals' => [
                                            'config' => $this->getTotalsConfig(),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get totals config
     *
     * @return array
     */
    protected function getTotalsConfig()
    {
        return [
            'display_cart_subtotal_incl_tax' => (int)$this->taxConfig->displayCartSubtotalInclTax(),
            'display_cart_subtotal_excl_tax' => (int)$this->taxConfig->displayCartSubtotalExclTax(),
        ];
    }
}
