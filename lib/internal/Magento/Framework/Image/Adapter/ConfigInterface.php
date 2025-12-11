<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Image\Adapter;

/**
 * Interface \Magento\Framework\Image\Adapter\ConfigInterface
 *
 * @api
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
