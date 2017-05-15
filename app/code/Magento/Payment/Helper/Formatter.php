<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Helper;

/**
 * Class Formatter
 * @api
 */
trait Formatter
{
    /**
     * Format price to 0.00 format
     *
     * @param mixed $price
     * @return string
     */
    public function formatPrice($price)
    {
        return sprintf('%.2F', $price);
    }
}
