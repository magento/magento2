<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sendfriend\Model\Resource\Sendfriend;

/**
 * Sendfriend log resource collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Init resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sendfriend\Model\Sendfriend', 'Magento\Sendfriend\Model\Resource\Sendfriend');
    }
}
