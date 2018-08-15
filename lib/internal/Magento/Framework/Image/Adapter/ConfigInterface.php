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
     * @return string
     */
    public function getAdapterAlias();

    /**
     * @return array
     */
    public function getAdapters();
}
