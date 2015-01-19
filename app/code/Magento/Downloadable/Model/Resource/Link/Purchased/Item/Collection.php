<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Resource\Link\Purchased\Item;

/**
 * Downloadable links purchased items resource collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Magento\Downloadable\Model\Link\Purchased\Item',
            'Magento\Downloadable\Model\Resource\Link\Purchased\Item'
        );
    }
}
