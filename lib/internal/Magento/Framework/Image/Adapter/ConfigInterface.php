<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Image\Adapter;

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
