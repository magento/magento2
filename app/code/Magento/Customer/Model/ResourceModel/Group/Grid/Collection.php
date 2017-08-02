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
 * @since 2.0.0
 */
class Collection extends \Magento\Customer\Model\ResourceModel\Group\Collection
{
    /**
     * Resource initialization
     * @return $this
     * @since 2.0.0
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addTaxClass();
        return $this;
    }
}
