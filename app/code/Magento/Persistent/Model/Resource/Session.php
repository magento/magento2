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
                array('customer' => $this->getTable('customer_entity')),
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
        $this->_getWriteAdapter()->delete($this->getMainTable(), array('customer_id = ?' => $customerId));
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
            array('website_id = ?' => $websiteId, 'updated_at < ?' => $expiredBefore)
        );
        return $this;
    }
}
