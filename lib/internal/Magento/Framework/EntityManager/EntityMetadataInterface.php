<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager;

/**
 * Interface EntityMetadataInterface
 * @since 2.1.0
 */
interface EntityMetadataInterface
{
    /**
     * @return string
     * @since 2.1.0
     */
    public function getIdentifierField();

    /**
     * @return string
     * @since 2.1.0
     */
    public function getLinkField();

    /**
     * @return string
     * @since 2.1.0
     */
    public function getEntityTable();

    /**
     * @return string
     * @since 2.1.0
     */
    public function getEntityConnectionName();

    /**
     * @return null|string
     * @since 2.1.0
     */
    public function generateIdentifier();

    /**
     * @return string[]
     * @since 2.1.0
     */
    public function getEntityContext();

    /**
     * @return null|string
     * @since 2.1.0
     */
    public function getEavEntityType();

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    public function getEntityConnection();
}
