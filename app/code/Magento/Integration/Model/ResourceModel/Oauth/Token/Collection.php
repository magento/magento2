<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\ResourceModel\Oauth\Token;

/**
 * OAuth token resource collection model
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize collection model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Integration\Model\Oauth\Token::class,
            \Magento\Integration\Model\ResourceModel\Oauth\Token::class
        );
    }

    /**
     * Load collection with consumer data
     *
     * Method use for show applications list (token-consumer)
     *
     * @return $this
     */
    public function joinConsumerAsApplication()
    {
        $select = $this->getSelect();
        $select->joinLeft(
            ['c' => $this->getTable('oauth_consumer')],
            'c.entity_id = main_table.consumer_id',
            'name'
        );

        return $this;
    }

    /**
     * Add filter by admin ID
     *
     * @param int $adminId
     * @return $this
     */
    public function addFilterByAdminId($adminId)
    {
        $this->addFilter('main_table.admin_id', $adminId);
        return $this;
    }

    /**
     * Add filter by customer ID
     *
     * @param int $customerId
     * @return $this
     */
    public function addFilterByCustomerId($customerId)
    {
        $this->addFilter('main_table.customer_id', $customerId);
        return $this;
    }

    /**
     * Add filter by consumer ID
     *
     * @param int $consumerId
     * @return $this
     */
    public function addFilterByConsumerId($consumerId)
    {
        $this->addFilter('main_table.consumer_id', $consumerId);
        return $this;
    }

    /**
     * Add filter by type
     *
     * @param string $type
     * @return $this
     */
    public function addFilterByType($type)
    {
        $this->addFilter('main_table.type', $type);
        return $this;
    }

    /**
     * Add filter by ID
     *
     * @param array|int $tokenId
     * @return $this
     */
    public function addFilterById($tokenId)
    {
        $this->addFilter('main_table.entity_id', ['in' => $tokenId], 'public');
        return $this;
    }

    /**
     * Add filter by "Is Revoked" status
     *
     * @param bool|int $flag
     * @return $this
     */
    public function addFilterByRevoked($flag)
    {
        $this->addFilter('main_table.revoked', (int)$flag, 'public');
        return $this;
    }
}
