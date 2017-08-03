<?php
/**
 * Customer group collection
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Group\Grid;

/**
 * Class \Magento\Customer\Model\ResourceModel\Group\Grid\Collection
 *
 */
class Collection extends \Magento\Customer\Model\ResourceModel\Group\Collection
{
    /**
     * Resource initialization
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addTaxClass();
        return $this;
    }
}
