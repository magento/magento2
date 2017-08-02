<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Newsletter problems collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Model\ResourceModel\Grid;

/**
 * Class \Magento\Newsletter\Model\ResourceModel\Grid\Collection
 *
 * @since 2.0.0
 */
class Collection extends \Magento\Newsletter\Model\ResourceModel\Problem\Collection
{
    /**
     * Adds queue info to grid
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|\Magento\Newsletter\Model\ResourceModel\Grid\Collection
     * @since 2.0.0
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addSubscriberInfo()->addQueueInfo();
        return $this;
    }
}
