<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Interface EntityInterface
 * @api
 * @since 100.1.2
 */
interface EntityInterface
{
    /*
     * Entity ID.
     */
    const ENTITY_ID = 'entity_id';

    /*
     * Created-at timestamp.
     */
    const CREATED_AT = 'created_at';

    /**
     * Gets the created-at timestamp for the invoice.
     *
     * @return string|null Created-at timestamp.
     * @since 100.1.2
     */
    public function getCreatedAt();

    /**
     * Sets the created-at timestamp for the invoice.
     *
     * @param string $createdAt timestamp
     * @return $this
     * @since 100.1.2
     */
    public function setCreatedAt($createdAt);

    /**
     * Gets the ID for the invoice.
     *
     * @return int|null Invoice ID.
     * @since 100.1.2
     */
    public function getEntityId();

    /**
     * Sets entity ID.
     *
     * @param int $entityId
     * @return $this
     * @since 100.1.2
     */
    public function setEntityId($entityId);
}
