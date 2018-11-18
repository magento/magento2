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
interface RatingEntityInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const ENTITY_ID = 'entity_id';
    const ENTITY_CODE = 'code';

    /**
     * Get entity id
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Set entity id
     *
     * @param int|null $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Get entity code
     *
     * @return string
     */
    public function getEntityCode(): string;

    /**
     * Set entity code
     *
     * @param string $entityCode
     * @return $this
     */
    public function setEntityCode(string $entityCode): RatingEntityInterface;

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Magento\ReviewApi\Api\Data\RatingEntityExtensionInterface
     */
    public function getExtensionAttributes(): \Magento\ReviewApi\Api\Data\RatingEntityExtensionInterface;

    /**
     * Set an extension attributes object
     *
     * @param \Magento\ReviewApi\Api\Data\RatingEntityExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\ReviewApi\Api\Data\RatingEntityExtensionInterface $extensionAttributes
    ): RatingEntityInterface;
}
