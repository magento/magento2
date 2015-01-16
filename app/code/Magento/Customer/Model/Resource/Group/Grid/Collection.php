<?php
/**
 * Customer group collection
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Resource\Group\Grid;

class Collection extends \Magento\Customer\Model\Resource\Group\Collection
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
