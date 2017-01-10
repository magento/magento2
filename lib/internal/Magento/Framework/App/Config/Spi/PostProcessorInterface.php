<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\Spi;

use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\App\Config\Reader\Source\SourceInterface;

/**
 * Allows to use custom callbacks and functions after collecting config from all sources
 *
 * @see SourceInterface
 * @see ConfigTypeInterface
 * @package Magento\Framework\App\Config\Spi
 */
interface PostProcessorInterface
{
    /**
     * Process config after reading and converting to appropriate format
     *
     * @param array $config
     * @return array
     */
    public function process(array $config);
}
