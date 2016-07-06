<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Bulk\Api\Data;

/**
 * Interface OperationInterface
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
    public function setId($id);

    /**
     * @return string
     */
    public function getBulkUuid();

    /**
     * @param string $bulkId
     * @return $this
     */
    public function setBulkUuid($bulkId);

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

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Framework\Bulk\Api\Data\OperationExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Framework\Bulk\Api\Data\OperationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Framework\Bulk\Api\Data\OperationExtensionInterface $extensionAttributes
    );

}
