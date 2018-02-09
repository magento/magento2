<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config\Converter\Type;

use Magento\Framework\GraphQl\Type\Schema;

/**
 * Format GraphQL Type and Interface array structures to allow for configuration mapping for a @see Schema.
 */
interface FormatterInterface
{
    /**
     * Format configured interface or type array to mapping-readable format
     *
     * @param array $entry
     * @return array
     */
    public function format(array $entry) : array;
}
