<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Model;

/**
 * Class OperationStatusPool
 *
 * Pool of statuses that require validate
 */
class OperationStatusPool
{
    /**
     * @var array
     */
    private $statuses;

    /**
     * @param array $statuses
     */
    public function __construct(array $statuses = [])
    {
        $this->statuses = $statuses;
    }

    /**
     * Retrieve statuses that require validate
     *
     * @return array
     */
    public function getStatuses()
    {
        return $this->statuses;
    }
}
