<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Model\ResourceModel;

/**
 * Persistent Session Resource Model
 * @since 2.0.0
 */
class Session extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Use is object new method for object saving
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_useIsObjectNew = true;

    /**
     * Session factory
     *
     * @var \Magento\Persistent\Model\SessionFactory
     * @since 2.0.0
     */
    protected $_sessionFactory;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Persistent\Model\SessionFactory $sessionFactory
     * @param string $connectionName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Persistent\Model\SessionFactory $sessionFactory,
        $connectionName = null
    ) {
        $this->_sessionFactory = $sessionFactory;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize connection and define main table and primary key
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('persistent_session', 'persistent_id');
    }

    /**
     * Add expiration date filter to select
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Persistent\Model\Session $object
     * @return \Magento\Framework\DB\Select
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function deleteByCustomerId($customerId)
    {
        $this->getConnection()->delete($this->getMainTable(), ['customer_id = ?' => $customerId]);
        return $this;
    }

    /**
     * Check if such session key allowed (not exists)
     *
     * @param string $key
     * @return bool
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function deleteExpired($websiteId, $expiredBefore)
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            ['website_id = ?' => $websiteId, 'updated_at < ?' => $expiredBefore]
        );
        return $this;
    }
}
