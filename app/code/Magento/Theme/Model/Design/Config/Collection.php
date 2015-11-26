<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config;

use Magento\Config\Model\ResourceModel\Config\Data\Collection as ConfigCollection;

class Collection extends ConfigCollection
{
    /**
     * Add paths filter to collection
     *
     * @param array $paths
     * @return $this
     */
    public function addPathsFilter(array $paths)
    {
        $this->addFieldToFilter('path', ['in' => $paths]);
        return $this;
    }
}
