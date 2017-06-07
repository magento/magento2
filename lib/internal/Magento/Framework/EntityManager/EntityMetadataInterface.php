<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager;

/**
 * Interface EntityMetadataInterface
 */
interface EntityMetadataInterface
{
    /**
     * @return string
     */
    public function getIdentifierField();

    /**
     * @return string
     */
    public function getLinkField();

    /**
     * @return string
     */
    public function getEntityTable();

    /**
     * @return string
     */
    public function getEntityConnectionName();

    /**
     * @return null|string
     */
    public function generateIdentifier();

    /**
     * @return string[]
     */
    public function getEntityContext();

    /**
     * @return null|string
     */
    public function getEavEntityType();

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @deprecated
     */
    public function getEntityConnection();
}
