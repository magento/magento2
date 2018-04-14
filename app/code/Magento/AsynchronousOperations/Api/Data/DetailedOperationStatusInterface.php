<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Api\Data;

/**
 * @api
 */
interface DetailedOperationStatusInterface extends OperationInterface
{
    /**
     * Result serialized Data
     *
     * @return string
     */
    public function getResultSerializedData();

    /**
     * Set result serialized data
     *
     * @param string $resultSerializedData
     * @return $this
     */
    public function setResultSerializedData($resultSerializedData);
}
