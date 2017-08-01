<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Pricing\Price;

/**
 * Option price interface
 *
 * @api
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getOptions();

    /**
     * Return the minimal or maximal price for custom options
     *
     * @param bool $getMin
     * @return float
     * @since 2.0.0
     */
    public function getCustomOptionRange($getMin);
}
