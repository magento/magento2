<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Pricing\Price;

/**
 * Option price interface
 *
 * @api
 * @since 100.0.2
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

    /**
     * Return the minimal or maximal price for custom options
     *
     * @param bool $getMin
     * @return float
     */
    public function getCustomOptionRange($getMin);
}
