<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\ResourceModel\Theme\Data;

/**
 * Theme data collection
 */
class Collection extends \Magento\Theme\Model\ResourceModel\Theme\Collection implements
    \Magento\Framework\View\Design\Theme\Label\ListInterface,
    \Magento\Framework\View\Design\Theme\ListInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('Magento\Theme\Model\Theme\Data', 'Magento\Theme\Model\ResourceModel\Theme');
    }
}
