<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Api\Data;

/**
 * Class OperationInterface
 * @api
 * @since 100.2.0
 */
interface OperationInterface extends \Magento\Framework\Bulk\OperationInterface
{
    public const OPERATION_ID = 'id';

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\OperationExtensionInterface|null
     * @since 100.2.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\AsynchronousOperations\Api\Data\OperationExtensionInterface $extensionAttributes
     * @return $this
     * @since 100.2.0
     */
    public function setExtensionAttributes(
        \Magento\AsynchronousOperations\Api\Data\OperationExtensionInterface $extensionAttributes
    );

    /**
     * Get operation key
     *
     * @return int|null
     * @since 103.0.1
     */
    public function getOperationKey();

    /**
     * Set operation key
     *
     * @param int|null $operationKey
     * @since 103.0.1
     */
    public function setOperationKey(?int $operationKey);
}
