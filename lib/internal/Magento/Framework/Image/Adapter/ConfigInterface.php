<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Image\Adapter;

/**
 * Interface \Magento\Framework\Image\Adapter\ConfigInterface
 *
 * @since 2.0.0
 */
interface ConfigInterface
{
    /**
     * @return string
     * @since 2.0.0
     */
    public function getAdapterAlias();

    /**
     * @return array
     * @since 2.0.0
     */
    public function getAdapters();
}
