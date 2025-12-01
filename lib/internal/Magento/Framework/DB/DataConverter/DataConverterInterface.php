<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\DataConverter;

/**
 * Convert from one format to another
 *
 * @api
 */
interface DataConverterInterface
{
    /**
     * Convert from one format to another
     *
     * @param string $value
     * @return string
     *
     * @throws DataConversionException
     */
    public function convert($value);
}
