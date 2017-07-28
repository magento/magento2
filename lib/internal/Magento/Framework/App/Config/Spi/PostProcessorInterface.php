<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * @since 2.2.0
 */
interface PostProcessorInterface
{
    /**
     * Process config after reading and converting to appropriate format
     *
     * @param array $config
     * @return array
     * @since 2.2.0
     */
    public function process(array $config);
}
