<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Wishlist item model resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Wishlist\Model\Resource;

class Item extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('wishlist_item', 'wishlist_item_id');
    }

    /**
     * Load item by wishlist, product and shared stores
     *
     * @param \Magento\Wishlist\Model\Item $object
     * @param int $wishlistId
     * @param int $productId
     * @param array $sharedStores
     * @return $this
     */
    public function loadByProductWishlist($object, $wishlistId, $productId, $sharedStores)
    {
        $adapter = $this->_getReadAdapter();
        $storeWhere = $adapter->quoteInto('store_id IN (?)', $sharedStores);
        $select = $adapter->select()->from(
            $this->getMainTable()
        )->where(
            'wishlist_id=:wishlist_id AND ' . 'product_id=:product_id AND ' . $storeWhere
        );
        $bind = array('wishlist_id' => $wishlistId, 'product_id' => $productId);
        $data = $adapter->fetchRow($select, $bind);
        if ($data) {
            $object->setData($data);
        }
        $this->_afterLoad($object);

        return $this;
    }
}
