<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Bulk;

/**
 * Interface OperationInterface
 * @api
 */
interface OperationInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const BULK_ID = 'bulk_uuid';
    const TOPIC_NAME = 'topic_name';
    const SERIALIZED_DATA = 'serialized_data';
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
    /**#@-*/
    
    /**
     * Operation id
     *
     * @return int
     */
    public function getId();

    /**
     * Set operation id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get bulk uuid
     * 
     * @return string
     */
    public function getBulkUuid();

    /**
     * Set bulk uuid
     * 
     * @param string $bulkId
     * @return $this
     */
    public function setBulkUuid($bulkId);

    /**
     * Message Queue Topic
     * 
     * @return string
     */
    public function getTopicName();

    /**
     * Set message queue topic
     * 
     * @param string $topic
     * @return $this
     */
    public function setTopicName($topic);

    /**
     * Serialized Data
     * 
     * @return string 
     */
    public function getSerializedData();

    /**
     * Set serialized data
     * 
     * @param string $serializedData
     * @return $this
     */
    public function setSerializedData($serializedData);

    /**
     * Get operation status
     * 
     * OPEN | COMPLETE | RETRIABLY_FAILED | NOT_RETRIABLY_FAILED
     * 
     * @return int 
     */
    public function getStatus();

    /**
     * Set status
     * 
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get result message
     * 
     * @return string
     */
    public function getResultMessage();

    /**
     * Set result message
     * 
     * @param string $resultMessage
     * @return $this
     */
    public function setResultMessage($resultMessage);

    /**
     * Get error code
     * 
     * @return int
     */
    public function getErrorCode();

    /**
     * Set error code
     * 
     * @param int $errorCode
     * @return $this
     */
    public function setErrorCode($errorCode);
}
