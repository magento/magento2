<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Interface ProductPriceOptionsInterface
 * @since 2.2.0
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
