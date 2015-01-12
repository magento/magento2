<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * GoogleShopping Types collection
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\GoogleShopping\Model\Resource\Grid;

class Collection extends \Magento\GoogleShopping\Model\Resource\Type\Collection
{
    /**
     *  Add total count of Items for each type
     *
     * @return \Magento\GoogleShopping\Model\Resource\Grid\Collection|\Magento\GoogleShopping\Model\Resource\Type\Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addItemsCount();
        return $this;
    }
}
