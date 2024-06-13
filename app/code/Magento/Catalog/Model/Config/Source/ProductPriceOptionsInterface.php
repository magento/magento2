<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Interface ProductPriceOptionsInterface
 *
 * @api
 */
interface ProductPriceOptionsInterface extends OptionSourceInterface
{
    /**#@+
     * Values
     */
    const VALUE_FIXED = 'fixed';
    const VALUE_PERCENT = 'percent';
    /**#@-*/
}
