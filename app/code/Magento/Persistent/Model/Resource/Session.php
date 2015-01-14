<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Model\Resource;

/**
 * Persistent Session Resource Model
 */
class Session extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Use is object new method for object saving
     *
     * @var bool
     */
    protected $_useIsObjectNew = true;

    /**
     * Session factory
     *
     * @var \Magento\Persistent\Model\SessionFactory
     */
    protected $_sessionFactory;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Persistent\Model\SessionFactory $sessionFactory
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Persistent\Model\SessionFactory $sessionFactory
    ) {
        $this->_sessionFactory = $sessionFactory;
        parent::__construct($resource);
    }

    /**
     * Initialize connection and define main table and primary key
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('persistent_session', 'persistent_id');
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setUpdatedAt(gmdate('Y-m-d H:i:s'));
        return parent::save($object);
    }

    /**
     * Add expiration date filter to select
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Persistent\Model\Session $object
     * @return \Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if (!$object->getLoadExpired()) {
            $tableName = $this->getMainTable();
            $select->join(
                ['customer' => $this->getTable('customer_entity')],
                'customer.entity_id = ' . $tableName . '.customer_id'
            )->where(
                $tableName . '.updated_at >= ?',
                $object->getExpiredBefore()
            );
        }

        return $select;
    }

    /**
     * Delete customer persistent session by customer id
     *
     * @param int $customerId
     * @return $this
     */
    public function deleteByCustomerId($customerId)
    {
        $this->_getWriteAdapter()->delete($this->getMainTable(), ['customer_id = ?' => $customerId]);
        return $this;
    }

    /**
     * Check if such session key allowed (not exists)
     *
     * @param string $key
     * @return bool
     */
    public function isKeyAllowed($key)
    {
        $sameSession = $this->_sessionFactory->create()->setLoadExpired();
        $sameSession->loadByCookieKey($key);
        return !$sameSession->getId();
    }

    /**
     * Delete expired persistent sessions
     *
     * @param  int $websiteId
     * @param  string $expiredBefore A formatted date string
     * @return $this
     */
    public function deleteExpired($websiteId, $expiredBefore)
    {
        $this->_getWriteAdapter()->delete(
            $this->getMainTable(),
            ['website_id = ?' => $websiteId, 'updated_at < ?' => $expiredBefore]
        );
        return $this;
    }
}
