<?php
/**
 * Customer group collection
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
