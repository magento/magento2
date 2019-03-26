<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Bulk;

/**
 * Interface OperationInterface
 * @api
 * @since 102.0.1
 */
interface OperationInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const BULK_ID = 'bulk_uuid';
    const TOPIC_NAME = 'topic_name';
    const SERIALIZED_DATA = 'serialized_data';
    const RESULT_SERIALIZED_DATA = 'result_serialized_data';
    const STATUS = 'status';
    const RESULT_MESSAGE = 'result_message';
    const ERROR_CODE = 'error_code';
    /**#@-*/

    /**#@+
     * Status types
     */
    const STATUS_TYPE_COMPLETE = 1;
    const STATUS_TYPE_RETRIABLY_FAILED = 2;
    const STATUS_TYPE_NOT_RETRIABLY_FAILED = 3;
    const STATUS_TYPE_OPEN = 4;
    const STATUS_TYPE_REJECTED = 5;
    /**#@-*/

    /**
     * Operation id
     *
     * @return int
     * @since 102.0.1
     */
    public function getId();

    /**
     * Set operation id
     *
     * @param int $id
     * @return $this
     * @since 102.0.1
     */
    public function setId($id);

    /**
     * Get bulk uuid
     *
     * @return string
     * @since 102.0.1
     */
    public function getBulkUuid();

    /**
     * Set bulk uuid
     *
     * @param string $bulkId
     * @return $this
     * @since 102.0.1
     */
    public function setBulkUuid($bulkId);

    /**
     * Message Queue Topic
     *
     * @return string
     * @since 102.0.1
     */
    public function getTopicName();

    /**
     * Set message queue topic
     *
     * @param string $topic
     * @return $this
     * @since 102.0.1
     */
    public function setTopicName($topic);

    /**
     * Serialized Data
     *
     * @return string
     * @since 102.0.1
     */
    public function getSerializedData();

    /**
     * Set serialized data
     *
     * @param string $serializedData
     * @return $this
     * @since 102.0.1
     */
    public function setSerializedData($serializedData);

    /**
     * Result serialized Data
     *
     * @return string
     * @since 102.0.1
     */
    public function getResultSerializedData();

    /**
     * Set result serialized data
     *
     * @param string $resultSerializedData
     * @return $this
     * @since 102.0.1
     */
    public function setResultSerializedData($resultSerializedData);

    /**
     * Get operation status
     *
     * OPEN | COMPLETE | RETRIABLY_FAILED | NOT_RETRIABLY_FAILED
     *
     * @return int
     * @since 102.0.1
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param int $status
     * @return $this
     * @since 102.0.1
     */
    public function setStatus($status);

    /**
     * Get result message
     *
     * @return string
     * @since 102.0.1
     */
    public function getResultMessage();

    /**
     * Set result message
     *
     * @param string $resultMessage
     * @return $this
     * @since 102.0.1
     */
    public function setResultMessage($resultMessage);

    /**
     * Get error code
     *
     * @return int
     * @since 102.0.1
     */
    public function getErrorCode();

    /**
     * Set error code
     *
     * @param int $errorCode
     * @return $this
     * @since 102.0.1
     */
    public function setErrorCode($errorCode);
}
