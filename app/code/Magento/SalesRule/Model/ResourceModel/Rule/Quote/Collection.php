<?php
/**
 * Sales Rules resource collection model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\ResourceModel\Rule\Quote;

/**
 * Class \Magento\SalesRule\Model\ResourceModel\Rule\Quote\Collection
 *
 */
class Collection extends \Magento\SalesRule\Model\ResourceModel\Rule\Collection
{
    /**
     * Add websites for load
     *
     * @return $this
     */
    public function _initSelect()
    {
        parent::_initSelect();
        $this->addWebsitesToResult();
        return $this;
    }
}
