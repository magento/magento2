<?php
/**
 * Sales Rules resource collection model
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
