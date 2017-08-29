<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Image\Process;

class Cache extends \Magento\Catalog\Model\Product\Image\Cache
{
    /**
     * This method makes it possible to preload the data
     * in the image cache model before forking php processes.
     *
     * This prevents errors like:
     * Warning: Error while sending QUERY packet. PID=xxxxx
     *
     * @return array
     */
    public function preloadData()
    {
        return $this->getData();
    }
}
