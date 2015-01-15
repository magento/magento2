<?php
/**
 * Sales Rules resource collection model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Resource\Rule\Quote;

class Collection extends \Magento\SalesRule\Model\Resource\Rule\Collection
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
