<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

/**
 * Config DOM-to-array converter interface.
 *
 * @api
 * @since 2.0.0
 */
interface ConverterInterface
{
    /**
     * Convert config
     *
     * @param \DOMDocument $source
     * @return array
     * @since 2.0.0
     */
    public function convert($source);
}
