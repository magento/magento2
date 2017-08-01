<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\CustomerData;

use Magento\Customer\CustomerData\JsLayoutDataProviderInterface;

/**
 * Checkout totals js layout data provider
 * @since 2.0.0
 */
class CheckoutTotalsJsLayoutDataProvider implements JsLayoutDataProviderInterface
{
    /**
     * @var \Magento\Tax\Model\Config
     * @since 2.0.0
     */
    protected $taxConfig;

    /**
     * @param \Magento\Tax\Model\Config $taxConfig
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Tax\Model\Config $taxConfig
    ) {
        $this->taxConfig = $taxConfig;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function getTotalsConfig()
    {
        return [
            'display_cart_subtotal_incl_tax' => (int)$this->taxConfig->displayCartSubtotalInclTax(),
            'display_cart_subtotal_excl_tax' => (int)$this->taxConfig->displayCartSubtotalExclTax(),
        ];
    }
}
