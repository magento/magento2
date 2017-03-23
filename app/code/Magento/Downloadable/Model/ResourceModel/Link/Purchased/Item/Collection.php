<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item;

/**
 * Downloadable links purchased items resource collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Downloadable\Model\Link\Purchased\Item::class,
            \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item::class
        );
    }
}
