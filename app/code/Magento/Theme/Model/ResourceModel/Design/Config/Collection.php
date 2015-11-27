<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\ResourceModel\Design\Config;

use Magento\Config\Model\ResourceModel\Config\Data\Collection as ConfigCollection;
use Magento\Framework\DB\Select;

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

    /**
     * {@inheritdoc}
     */
    public function addFieldToFilter($field, $condition = null)
    {
        $resultCondition = $this->_translateCondition($field, $condition);

        $this->_select->reset(Select::WHERE);
        $this->_select->where($resultCondition, null, Select::TYPE_CONDITION);

        return $this;
    }
}
