<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Pricing\Price;

/**
 * Option price interface
 */
interface CustomOptionPriceInterface
{
    /**
     * Flag to indicate the price is for configuration option of a product
     */
    const CONFIGURATION_OPTION_FLAG = 'configuration_option_flag';

    /**
     * Return calculated options
     *
     * @return array
     */
    public function getOptions();
}
