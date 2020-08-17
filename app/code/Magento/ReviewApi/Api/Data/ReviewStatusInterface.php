<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface ReviewStatusInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const STATUS_ID = 'status_id';
    const STATUS_CODE = 'status_code';

    /**
     * Get status id
     *
     * @return int|null
     */
    public function getStatusId(): ?int;

    /**
     * Set status id
     *
     * @param int|null $statusId
     * @return $this
     */
    public function setStatusId(?int $statusId): ReviewStatusInterface;

    /**
     * Get status code
     *
     * @return string
     */
    public function getStatusCode(): string;

    /**
     * Set status code
     *
     * @param string $statusCode
     * @return $this
     */
    public function setStatusCode(string $statusCode): ReviewStatusInterface;

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Magento\ReviewApi\Api\Data\ReviewStatusExtensionInterface
     */
    public function getExtensionAttributes(): \Magento\ReviewApi\Api\Data\ReviewStatusExtensionInterface;

    /**
     * Set an extension attributes object
     *
     * @param \Magento\ReviewApi\Api\Data\ReviewStatusExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\ReviewApi\Api\Data\ReviewStatusExtensionInterface $extensionAttributes
    ): ReviewStatusInterface;
}
