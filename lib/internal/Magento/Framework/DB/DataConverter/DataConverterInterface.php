<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\DataConverter;

/**
 * Convert from one format to another
 */
interface DataConverterInterface
{
    /**
     * Convert from one format to another
     *
     * @param string $value
     * @return string
     */
    public function convert($value);
}
