<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Plugin\Ui\DataProvider;

use Magento\Catalog\Ui\DataProvider\Product\Listing\DataProvider;
use Magento\Checkout\CustomerData\Cart as CustomerDataCart;
use Magento\Framework\App\Config;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * Plugin on Data Provider for frontend ui components (Components are responsible
 * for rendering product on front)
 * This plugin provides displayTaxes setting
 */
class TaxSettings
{
    /**
     * TaxSettings constructor.
     * @param Config $config
     */
    public function __construct(
        private readonly Config $config
    ) {
    }

    /**
     * Add tax data to result
     *
     * @param CustomerDataCart $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(DataProvider $subject, $result)
    {
        $result['displayTaxes'] = $this->config
            ->getValue(TaxConfig::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE);

        return $result;
    }
}
