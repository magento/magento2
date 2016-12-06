<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\DataConverter;

/**
 * Convert from one format to another
 */
interface DataConverterInterface
{
    /**
     * Convert from one format to another
     *
     * @param string $string
     * @return string
     */
    public function convert($string);
}
