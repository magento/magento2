<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Wishlist\Model\ResourceModel;

/**
 * @api
 * @since 2.0.0
 */
class Wishlist extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Store wishlist items count
     *
     * @var null|int
     * @since 2.0.0
     */
    protected $_itemsCount = null;

    /**
     * Store customer ID field name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_customerIdFieldName = 'customer_id';

    /**
     * Set main entity table name and primary key field name
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('wishlist', 'wishlist_id');
    }

    /**
     * Prepare wishlist load select query
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\DB\Select
     * @since 2.0.0
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($field == $this->_customerIdFieldName) {
            $select->order('wishlist_id ' . \Magento\Framework\DB\Select::SQL_ASC)->limit(1);
        }
        return $select;
    }

    /**
     * Getter for customer ID field name
     *
     * @return string
     * @since 2.0.0
     */
    public function getCustomerIdFieldName()
    {
        return $this->_customerIdFieldName;
    }

    /**
     * Setter for customer ID field name
     *
     * @param string $fieldName
     * @return $this
     * @since 2.0.0
     */
    public function setCustomerIdFieldName($fieldName)
    {
        $this->_customerIdFieldName = $fieldName;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setHasDataChanges(true);
        return parent::save($object);
    }
}
