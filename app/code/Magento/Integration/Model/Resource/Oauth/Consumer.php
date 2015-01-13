<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\Resource\Oauth;

class Consumer extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(\Magento\Framework\App\Resource $resource, \Magento\Framework\Stdlib\DateTime $dateTime)
    {
        $this->_dateTime = $dateTime;
        parent::__construct($resource);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('oauth_consumer', 'entity_id');
    }

    /**
     * Set updated_at automatically before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    public function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setUpdatedAt($this->_dateTime->formatDate(time()));
        return parent::_beforeSave($object);
    }

    /**
     * Delete all Nonce entries associated with the consumer
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    public function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->delete($this->getTable('oauth_nonce'), ['consumer_id' => $object->getId()]);
        $adapter->delete($this->getTable('oauth_token'), ['consumer_id' => $object->getId()]);
        return parent::_afterDelete($object);
    }

    /**
     * Compute time in seconds since consumer was created.
     *
     * @param int $consumerId - The consumer id
     * @return int - time lapsed in seconds
     */
    public function getTimeInSecondsSinceCreation($consumerId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable())
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns('CURRENT_TIMESTAMP() - created_at')
            ->where('entity_id = ?', $consumerId);

        return $adapter->fetchOne($select);
    }
}
