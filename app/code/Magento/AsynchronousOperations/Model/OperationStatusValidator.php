<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Model\OperationStatusPool;
use Magento\Framework\Exception\NoSuchEntityException;
use Doctrine\Instantiator\Exception\InvalidArgumentException;

/**
 * Class OperationStatusValidator to validate operation status
 */
class OperationStatusValidator
{
    /**
     * @var OperationStatusPool
     */
    private $operationStatusPool;

    /**
     * OperationStatusValidator constructor.
     *
     * @param OperationStatusPool $operationStatusPool
     */
    public function __construct(OperationStatusPool $operationStatusPool)
    {
        $this->operationStatusPool = $operationStatusPool;
    }

    /**
     * Validate method
     *
     * @param int $status
     * @throws \InvalidArgumentException
     * @return void
     */
    public function validate($status)
    {
        $statuses = $this->operationStatusPool->getStatuses();

        if (!in_array($status, $statuses)) {
            throw new \InvalidArgumentException('Invalid Operation Status.');
        }
    }
}
