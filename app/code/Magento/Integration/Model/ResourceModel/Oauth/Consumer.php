<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\ResourceModel\Oauth;

class Consumer extends \Magento\Framework\Model\ModelResource\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * @param \Magento\Framework\Model\ModelResource\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ModelResource\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        $connectionName = null
    ) {
        $this->_dateTime = $dateTime;
        parent::__construct($context, $connectionName);
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
        $connection = $this->getConnection();
        $connection->delete($this->getTable('oauth_nonce'), ['consumer_id' => $object->getId()]);
        $connection->delete($this->getTable('oauth_token'), ['consumer_id' => $object->getId()]);
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
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns(new \Zend_Db_Expr('CURRENT_TIMESTAMP() - created_at'))
            ->where('entity_id = ?', $consumerId);

        return $connection->fetchOne($select);
    }
}
