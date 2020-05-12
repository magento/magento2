<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Image\Adapter;

/**
 * Interface \Magento\Framework\Image\Adapter\ConfigInterface
 *
 */
interface ConfigInterface
{
    /**
     * Get adapter alias
     *
     * @return string
     */
    public function getAdapterAlias();

    /**
     * Get adapters
     *
     * @return array
     */
    public function getAdapters();
}
