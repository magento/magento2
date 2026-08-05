<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

/**
 * Config DOM-to-array converter interface.
 *
 * @api
 * @since 100.0.2
 */
interface ConverterInterface
{
    /**
     * Convert config
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source);
}
