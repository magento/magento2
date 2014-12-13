<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Api\Data;

/**
 * Interface OrderStatusHistoryInterface
 */
interface OrderStatusHistoryInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const PARENT_ID = 'parent_id';
    const IS_CUSTOMER_NOTIFIED = 'is_customer_notified';
    const IS_VISIBLE_ON_FRONT = 'is_visible_on_front';
    const COMMENT = 'comment';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';
    const ENTITY_NAME = 'entity_name';

    /**
     * Returns comment
     *
     * @return string
     */
    public function getComment();

    /**
     * Returns created_at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Returns entity_id
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Returns entity_name
     *
     * @return string
     */
    public function getEntityName();

    /**
     * Returns is_customer_notified
     *
     * @return int
     */
    public function getIsCustomerNotified();

    /**
     * Returns is_visible_on_front
     *
     * @return int
     */
    public function getIsVisibleOnFront();

    /**
     * Returns parent_id
     *
     * @return int
     */
    public function getParentId();

    /**
     * Returns status
     *
     * @return string
     */
    public function getStatus();
}
