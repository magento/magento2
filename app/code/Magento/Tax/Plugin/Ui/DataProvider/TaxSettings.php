<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Plugin\Ui\DataProvider;

use Magento\Framework\App\Config;

/**
 * Plugin on Data Provider for frontend ui components (Components are responsible
 * for rendering product on front)
 * This plugin provides displayTaxes setting
 * @since 2.2.0
 */
class TaxSettings
{
    /**
     * @var Config
     * @since 2.2.0
     */
    private $config;

    /**
     * TaxSettings constructor.
     * @param Config $config
     * @since 2.2.0
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Add tax data to result
     *
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterGetData(\Magento\Catalog\Ui\DataProvider\Product\Listing\DataProvider $subject, $result)
    {
        $result['displayTaxes'] = $this->config
            ->getValue(\Magento\Tax\Model\Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE);

        return $result;
    }
}
