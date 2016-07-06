<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Bulk\Api\Data;

/**
 * Interface OperationInterface
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
    public function setOperationId($id);

    /**
     * @return string
     */
    public function getBulkId();

    /**
     * @param string $bulkId
     * @return $this
     */
    public function setBulkId($bulkId);

    /**
     * @return string Message Queue Topic
     */
    public function getTopicName();

    /**
     * @param string $topic
     * @return $this
     */
    public function setTopicName($topic);

    /**
     * @return string Serialized Data
     */
    public function getSerializedData();

    /**
     * @param string $serializedData
     * @return $this
     */
    public function setSerializedData($serializedData);

    /**
     * @return int OPEN | COMPLETE | RETRIABLY_FAILED | NOT_RETRIABLY_FAILED
     */
    public function getStatus();

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getResultMessage();

    /**
     * @param string $resultMessage
     * @return $this
     */
    public function setResultMessage($resultMessage);

    /**
     * @return int
     */
    public function getErrorCode();

    /**
     * @param int $errorCode
     * @return $this
     */
    public function setErrorCode($errorCode);

}
