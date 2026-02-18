<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config\Spi;

/**
 * Allows to use custom callbacks and functions before applying fallback
 *
 * @api
 */
interface PreProcessorInterface
{
    /**
     * Pre-processing of config
     *
     * @param array $config
     * @return array
     */
    public function process(array $config);
}
