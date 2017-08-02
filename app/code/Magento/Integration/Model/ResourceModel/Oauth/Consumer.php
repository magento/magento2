<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\ResourceModel\Oauth;

/**
 * Class \Magento\Integration\Model\ResourceModel\Oauth\Consumer
 *
 * @since 2.0.0
 */
class Consumer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string $connectionName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('oauth_consumer', 'entity_id');
    }

    /**
     * Delete all Nonce entries associated with the consumer
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @since 2.0.0
     */
    public function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $connection = $this->getConnection();
        $connection->delete($this->getTable('oauth_nonce'), ['consumer_id = ?' => (int)$object->getId()]);
        $connection->delete($this->getTable('oauth_token'), ['consumer_id = ?' => (int)$object->getId()]);
        return parent::_afterDelete($object);
    }

    /**
     * Compute time in seconds since consumer was created.
     *
     * @deprecated 2.1.0
     *
     * @param int $consumerId - The consumer id
     * @return int - time lapsed in seconds
     * @since 2.0.0
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
